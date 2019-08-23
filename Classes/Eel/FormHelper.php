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
     * Calculate the trusted properties token for the given form content
     *
     * @param array $arguments
     * @param string|null $fieldNamePrefix
     */
    public function argumentsWithHmac(array $arguments = [], string $excludeNamespace = '')
    {
        if ($excludeNamespace !== null && isset($arguments[$excludeNamespace])) {
            unset($arguments[$excludeNamespace]);
        }
        return $this->hashService->appendHmac(base64_encode(serialize($arguments)));
    }

    /**
     * Calculate the trusted properties token for the given form content
     *
     * @param string $content
     * @param string|null $fieldNamePrefix
     */
    public function trustedPropertiesToken(string $content, string $fieldNamePrefix = '')
    {
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        // ignore parsing errors
        $useInternalErrorsBackup = libxml_use_internal_errors(true);
        $domDocument->loadHTML($content);
        $xpath = new \DOMXPath($domDocument);
        if ($useInternalErrorsBackup !== true) {
            libxml_use_internal_errors($useInternalErrorsBackup);
        }

        $elements = $xpath->query("//*[(local-name()='input' or local-name()='select' or local-name()='button' or local-name()='textarea') and @name]");
        $formFieldNames = [];
        foreach($elements as $element) {
            $name = (string)$element->getAttribute('name');
            if (substr_compare($name, $fieldNamePrefix, 0, strlen($fieldNamePrefix)) === 0) {
                $formFieldNames[] = $name;
            }
        }
        return $this->mvcPropertyMappingConfigurationService->generateTrustedPropertiesToken($formFieldNames, $fieldNamePrefix);
    }

    /**
     * Detect the required empty hidden fieldnames for the given form content
     *
     * @param string $content
     * @param string|null $fieldNamePrefix
     */
    public function emptyHiddenFieldnames(string $content, string $fieldNamePrefix = '')
    {
        $domDocument = new \DOMDocument('1.0', 'UTF-8');
        // ignore parsing errors
        $useInternalErrorsBackup = libxml_use_internal_errors(true);
        $domDocument->loadHTML($content);
        $xpath = new \DOMXPath($domDocument);
        if ($useInternalErrorsBackup !== true) {
            libxml_use_internal_errors($useInternalErrorsBackup);
        }

        $elements = $xpath->query("//*[(local-name()='input' and @type='checkbox') or (local-name()='select' and @multiple)]");
        $hiddenFieldnames = [];
        foreach($elements as $element) {
            $name = (string)$element->getAttribute('name');
            if (substr_compare($name, $fieldNamePrefix, 0, strlen($fieldNamePrefix)) === 0) {
                if (substr_compare($name, '[]', -2, 2) === 0) {
                    $hiddenFieldnames[] = substr($name, 0, -2);
                } else {
                    $hiddenFieldnames[] = $name;
                }
            }
        }

        return array_unique($hiddenFieldnames);
    }

    /**
     * Returns CSRF token which is required for "unsafe" requests (e.g. POST, PUT, DELETE, ...)
     *
     * @return string
     */
    public function csrfToken(): string
    {
        return $this->securityContext->getCsrfProtectionToken();
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
            return implode(', ', array_map (
                function($value) use ($helper) {
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
            $data,
            $fieldNamePrefix ?: ($request ? $request->getArgumentNamespace() : ''),
            $request ? $request->getInternalArgument('__submittedArguments') : [],
            $request ? $request->getInternalArgument('__submittedArgumentValidationResults') : new Result()
        );
    }

    /**
     * @param FormDefinition|null $form
     * @param string $path
     * @return FieldDefinition
     */
    public function createFieldDefinition(FormDefinition $form = null, string $path = null): FieldDefinition
    {
        if (!$path) {
            return new FieldDefinition(null, null, null);
        }
        // render fieldName
        if ($form && $form->getFieldNamePrefix()) {
            $fieldName = $this->pathToFieldName( $form->getFieldNamePrefix() . '.' . $path);
        } else {
            $fieldName = $this->pathToFieldName($path);
        }

        // determine value, according to the following algorithm:
        if ($form && $form->getResult() !== null && $form->getResult()->hasErrors()) {
            // 1) if a validation error has occurred, pull the value from the submitted form values.
            $fieldValue = ObjectAccess::getPropertyPath($form->getSubmittedValues(), $path);
        } elseif ($path && $form && $form->getData()) {
            // 2) else, if "property" is specified, take the value from the bound object.
            $fieldValue = ObjectAccess::getPropertyPath($form->getData(), $path);
        } else {
            $fieldValue = null;
        }

        // determine ValidationResult for the single property
        $fieldResult = null;
        if ($form && $form->getResult() && $form->getResult()->hasErrors()) {
            $fieldResult = $form->getResult()->forProperty($path);
        }

        return new FieldDefinition(
            $fieldName,
            $fieldValue,
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
