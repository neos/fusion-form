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
     * @param string|null $name
     * @param string|null $fieldNamePrefix
     * @param object|null $object
     * @return FormDefinition
     */
    public function createFormDefinition(ActionRequest $request = null, string $name = null, string $fieldNamePrefix = null, object $object = null): FormDefinition
    {
        return new FormDefinition(
            $name,
            $object,
            $fieldNamePrefix ?: ($request ? $request->getArgumentNamespace() : ''),
            $request ? $request->getInternalArgument('__submittedArguments') : [],
            $request ? $request->getInternalArgument('__submittedArgumentValidationResults') : new Result()
        );
    }

    /**
     * @param FormDefinition|null $form
     * @param string|null $name
     * @param string $property
     * @param mixed|null $value
     * @return FieldDefinition
     */
    public function createFieldDefinition(FormDefinition $form = null, string $name = null, string $property = null, $value = null): FieldDefinition
    {
        // determine path
        if ($property) {
            $fieldNameParts = explode('.', $property);
        } elseif ($name) {
            $path = preg_replace('/(\]\[|\[|\])/', '.', $name);
            $fieldNameParts = explode('.', $path);
        } else {
            return new FieldDefinition(null, $value, null);
        }

        if ($form && $fieldNameParts) {
            array_unshift($fieldNameParts, $form->getName());
            if ($form->getFieldNamePrefix()) {
                array_unshift($fieldNameParts, $form->getFieldNamePrefix());
            }
        }

        $fieldPath = implode('.', $fieldNameParts);
        $fieldName = array_shift($fieldNameParts);
        foreach ($fieldNameParts as $nameSegment) {
            $fieldName .= '[' . $nameSegment . ']';
        }

        // determine current value, according to the following algorithm:
        $current = null;

        if ($form && $form->getMappingResults() !== null && $form->getMappingResults()->hasErrors()) {
            // 1) if a validation error has occurred, pull the value from the submitted form values.
            $current = ObjectAccess::getPropertyPath($form->getSubmittedValues(), $fieldPath);
        } elseif ($property && $form && $form->getObject()) {
            // 2) else, if "property" is specified, take the value from the bound object.
            $current = ObjectAccess::getPropertyPath($form->getObject(), $property);
        }

        // determine ValidationResult for the single property
        $fieldResult = null;
        if ($form && $form->getMappingResults() && $form->getMappingResults()->hasErrors()) {
            $fieldResult = $form->getMappingResults()->forProperty($fieldPath);
        }

        return new FieldDefinition(
            $fieldName,
            $value,
            $current,
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
