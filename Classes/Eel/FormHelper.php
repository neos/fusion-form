<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Eel;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Error\Messages\Result;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Utility\ObjectAccess;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Flow\Security\Cryptography\HashService;
use Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService;
use Neos\Fusion\Form\Domain\Model\FieldDefinition;
use Neos\Fusion\Form\Domain\Model\FormDefinition;

class FormHelper implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @Flow\Inject
     * @var MvcPropertyMappingConfigurationService
     */
    protected $mvcPropertyMappingConfigurationService;

    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var HashService
     */
    protected $hashService;


    /**
     * Calculate the hidden fields for the given form content
     *
     * @param ActionRequest $request
     * @param string $content form html body
     * @param array hiddenFields as key value pairs
     */
    public function calculateHiddenFields(FormDefinition $form = null, string $content = null): array
    {
        $hiddenFields = [];

        if ($form) {
            $request = $form->getRequest();
            $fieldNamePrefix = $form->getFieldNamePrefix();
            $data = $form->getData();
        } else {
            $request = null;
            $fieldNamePrefix = '';
            $data = null;
        }

        // parse given content to render hidden fields for
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        if ($content) {
            $useInternalErrorsBackup = libxml_use_internal_errors(true);
            $domDocument->loadHTML($content);
            if ($useInternalErrorsBackup !== true) {
                libxml_use_internal_errors($useInternalErrorsBackup);
            }
        }
        $xpath = new \DOMXPath($domDocument);

        // 0. __request parameters
        if ($request) {
            $childRequestArgumentNamespace = null;
            while ($request) {
                $requestArgumentNamespace = $request->getArgumentNamespace();
                $hiddenFields[$this->prefixFieldName('__referrer[@package]', $requestArgumentNamespace)] = $request->getControllerPackageKey();
                $hiddenFields[$this->prefixFieldName('__referrer[@subpackage]', $requestArgumentNamespace)] = $request->getControllerSubpackageKey();
                $hiddenFields[$this->prefixFieldName('__referrer[@controller]', $requestArgumentNamespace)] = $request->getControllerName();
                $hiddenFields[$this->prefixFieldName('__referrer[@action]', $requestArgumentNamespace)] = $request->getControllerActionName();
                if ($requestArguments = $request->getArguments()) {
                    if ($childRequestArgumentNamespace && isset($requestArguments[$childRequestArgumentNamespace])) {
                        unset($requestArguments[$childRequestArgumentNamespace]);
                    }
                    if ($requestArguments) {
                        $hiddenFields[$this->prefixFieldName('__referrer[arguments]', $requestArgumentNamespace)] = $this->hashService->appendHmac(base64_encode(serialize($requestArguments)));
                    }
                }
                if ($request->isMainRequest()) {
                    break;
                }
                $childRequestArgumentNamespace = $requestArgumentNamespace;
                $request = $request->getParentRequest();
            }
        }

        // 1. empty hidden values for checkbox and multi-select values
        $elements = $xpath->query("//*[(local-name()='input' and @type='checkbox') or (local-name()='select' and @multiple)]");
        foreach ($elements as $element) {
            $name = (string)$element->getAttribute('name');
            if (substr_compare($name, $fieldNamePrefix, 0, strlen($fieldNamePrefix)) === 0) {
                if (substr_compare($name, '[]', -2, 2) === 0) {
                    $fieldName = substr($name, 0, -2);
                } else {
                    $fieldName = $name;
                }
                $hiddenFields[ $fieldName ] = "";
            }
        }

        // 2. fieldnames and hidden identity fields for modifying properties on persisted objects
        $formFieldNamesForTrustedPropertiesToken = [];
        if ($content) {
            $elements = $xpath->query("//*[(local-name()='input' or local-name()='select' or local-name()='button' or local-name()='textarea') and @name]");
            $possiblePathes = [];
            foreach ($elements as $element) {
                $name = (string)$element->getAttribute('name');
                if (substr_compare($name, $fieldNamePrefix, 0, strlen($fieldNamePrefix)) === 0) {
                    $formFieldNamesForTrustedPropertiesToken[] = $name;
                    $path = $this->fieldNameToPath(substr($name, strlen($fieldNamePrefix)));
                    $pathSegments = explode('.', $path);
                    for ($i = 1; $i < count($pathSegments); $i++) {
                        $possiblePathes[] = implode('.', array_slice($pathSegments, 0, $i));
                    }
                }
            }
            if ($data) {
                $possiblePathes = array_unique($possiblePathes);
                foreach ($possiblePathes as $path) {
                    $possibleObject = ObjectAccess::getPropertyPath($data, $path);
                    if (is_object($possibleObject) && !$this->persistenceManager->isNewObject($possibleObject)) {
                        $identifier = $this->persistenceManager->getIdentifierByObject($possibleObject);
                        $name = $this->prefixFieldName($this->pathToFieldName($path), $fieldNamePrefix);
                        $formFieldNamesForTrustedPropertiesToken[] = $name . '[__identity]';
                        $hiddenFields[$name . '[__identity]'] = $identifier;
                    }
                }
            }
        }

        // 3. trusted properties token
        $hiddenFields[ $this->prefixFieldName('__trustedProperties', $fieldNamePrefix) ] = $this->mvcPropertyMappingConfigurationService->generateTrustedPropertiesToken(array_unique($formFieldNamesForTrustedPropertiesToken), $fieldNamePrefix);

        // 4. csrf token
        $hiddenFields['__csrfToken'] = $this->securityContext->getCsrfProtectionToken();

        return $hiddenFields;
    }

    /**
     * Prepend the gigen fieldNamePrefix to the fieldName the
     *
     * @param string $name
     * @param string|null $prefix
     * @return string
     */
    public function prefixFieldName(string $fieldName, string $fieldNamePrefix = null)
    {
        if (!$fieldNamePrefix) {
            return $fieldName;
        } else {
            $fieldNameSegments = explode('[', $fieldName, 2);
            $fieldName = $fieldNamePrefix . '[' . $fieldNameSegments[0] . ']';
            if (count($fieldNameSegments) > 1) {
                $fieldName .= '[' . $fieldNameSegments[1];
            }
            return $fieldName;
        }
    }

    /**
     * Convert the given html fieldName to a dot seperated path
     *
     * @param $name
     * @return string
     */
    public function fieldNameToPath($name): string
    {
        $path = preg_replace('/(\]\[|\[|\])/', '.', $name);
        return trim($path, '.');
    }

    /**
     * Convert the given dot seperated $path to an html fieldName
     *
     * @param $name
     * @return string
     */
    public function pathToFieldName($path): string
    {
        $pathSegments = explode('.', $path);
        $fieldName = array_shift($pathSegments);
        foreach ($pathSegments as $pathSegment) {
            $fieldName .= '[' . $pathSegment . ']';
        }
        return $fieldName;
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    public function stringifyValue($value): string
    {
        if (is_object($value)) {
            $identifier = $this->persistenceManager->getIdentifierByObject($value);
            if ($identifier !== null) {
                return $identifier;
            }
        }

        if (is_array($value)) {
            $helper = $this;
            return implode(', ', array_map(
                function ($value) use ($helper) {
                    return $helper->stringifyValue($value);
                },
                $value
            ));
        } else {
            return (string)$value;
        }
    }

    /**
     * @param ActionRequest|null $request
     * @param string|null $fieldNamePrefix
     * @param mixed|null $data
     * @return FormDefinition
     */
    public function createFormDefinition(ActionRequest $request = null, string $fieldNamePrefix = null, $data = null): FormDefinition
    {
        return new FormDefinition(
            $request,
            $data,
            $fieldNamePrefix ?: ($request ? $request->getArgumentNamespace() : ''),
            $request ? $request->getInternalArgument('__submittedArguments') : [],
            $request ? $request->getInternalArgument('__submittedArgumentValidationResults') : new Result()
        );
    }

    /**
     * @param FormDefinition|null $form
     * @param string $path
     * @param bool $multiple
     * @return FieldDefinition
     */
    public function createFieldDefinition(FormDefinition $form = null, string $path = null, bool $multiple = false): FieldDefinition
    {
        if (!$path) {
            return new FieldDefinition(null, null, null);
        }
        // render fieldName
        if ($form && $form->getFieldNamePrefix()) {
            $fieldName = $this->pathToFieldName($form->getFieldNamePrefix() . '.' . $path);
        } else {
            $fieldName = $this->pathToFieldName($path);
        }
        if ($multiple) {
            $fieldName .= '[]';
        }

        // determine value, according to the following algorithm:
        if ($form && $form->getResult() !== null && $form->getResult()->hasErrors()) {
            // 1) if a validation error has occurred, pull the value from the submitted form values.
            $value = ObjectAccess::getPropertyPath($form->getSubmittedValues(), $path);
        } elseif ($path && $form && $form->getData()) {
            // 2) else, if "property" is specified, take the value from the bound object.
            $value = ObjectAccess::getPropertyPath($form->getData(), $path);
        } else {
            $value = null;
        }

        // determine ValidationResult for the single property
        $fieldResult = null;
        if ($form && $form->getResult() && $form->getResult()->hasErrors()) {
            $fieldResult = $form->getResult()->forProperty($path);
        }

        // apply stringify to values but respect multivalues
        if ($multiple) {
            $fieldValue = [];
            if (is_array($value) || $value instanceof \ArrayAccess) {
                foreach ($value as $part) {
                    $fieldValue[] = $this->stringifyValue($part);
                }
            }
        } else {
            $fieldValue = $this->stringifyValue($value);
        }

        return new FieldDefinition(
            $fieldName,
            $fieldValue,
            $multiple,
            $fieldResult
        );
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
