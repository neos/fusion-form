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

        $elements = $xpath->query("//*[@name]");
        $formFieldNames = [];
        foreach($elements as $element) {
            $formFieldNames[] = (string)$element->getAttribute('name');
        }
        return $this->mvcPropertyMappingConfigurationService->generateTrustedPropertiesToken($formFieldNames, $fieldNamePrefix);
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
     * @return array|string|null
     */
    public function stringifyValue($value)
    {
        if (is_object($value)) {
            $identifier = $this->persistenceManager->getIdentifierByObject($value);
            if ($identifier !== null) {
                $value = $identifier;
            }
        }

        if (is_array($value)) {
            return $value;
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
     * @param string|null $value
     * @return FieldDefinition
     */
    public function createFieldDefinition(FormDefinition $form = null, string $name = null, string $property = null, string $value = null): FieldDefinition
    {
        // determine path
        if  ($property) {
            $fieldNameParts = explode('.', $property);
        } elseif ($name)  {
            $path = preg_replace('/(\]\[|\[|\])/', '.', $name);
            $fieldNameParts  = explode('.', $path);
        } else {
            return new FieldDefinition(
                null,
                $this->stringifyValue($value),
                null
            );
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

        // determine value
        $fieldValue = $value;
        if ($form && $form->getMappingResults() !== null && $form->getMappingResults()->hasErrors()) {
            $fieldValue = ObjectAccess::getPropertyPath($form->getSubmittedValues(), $fieldPath);
        }

        if ($fieldValue == null && $property && $form && $form->getObject()) {
            $fieldValue = ObjectAccess::getPropertyPath($form->getObject(), $property);
        }

        if (is_array($fieldValue)) {
            $helper = $this;
            $fieldValue = array_map(
                function($value) use ($helper) {
                    return $helper->stringifyValue($value);
                },
                $fieldValue
            );
        } else {
            $fieldValue = $this->stringifyValue($fieldValue);
        }

        // determine result
        if ($form && $form->getMappingResults() && $form->getMappingResults()->hasErrors()) {
            $fieldResult = ObjectAccess::getPropertyPath($form->getSubmittedValues(), $fieldPath);
        } else {
            $fieldResult = null;
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
