<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Domain;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Error\Messages\Result;
use Neos\Utility\ObjectAccess;

/**
 * This object describes a single editable value in a form. Usually this is
 * instantiated by the fusion prototype `Neos.Fusion.Form:Definition.Field`
 * and can be accessed as `field` in the fusion context.
 *
 * @package Neos\Fusion\Form\Domain
 */
class Field extends AbstractFormObject
{

    /**
     * @var
     */
    protected $form;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $currentValue;

    /**
     * @var mixed
     */
    protected $targetValue;

    /**
     * @var boolean
     */
    protected $multiple;

    /**
     * @var bool
     */
    protected $result;

    /**
     * Field constructor.
     * @param Form|null $form The form the field is created for, is used to access bound or submitted data and results
     * @param string|null $name The field name, nesting is defined by using square brackets
     * @param mixed|null $targetValue The target value for the field, only used for check, radio and button
     * @param bool $multiple Shall the field contain multiple or a single value, used for checkboxes and selects
     */
    public function __construct(Form $form = null, string $name = null, $targetValue = null, $multiple = false)
    {
        $this->form = $form;
        $this->name = $name;
        $this->targetValue = $targetValue;
        $this->multiple = $multiple;

        // determine current value and result
        $path = $this->fieldNameToPath($this->name);
        $this->currentValue = $this->findCurrentValueByPath($path);
        $this->result = $this->findResultByPath($path);
    }

    /**
     * Create and return a copy of this field with alternate value
     * This is used to render multiple checkboxes or radio in a single field container
     *
     * @param mixed|null $targetValue
     * @return Field
     */
    public function withTargetValue($targetValue = null): Field
    {
        $new = clone $this;
        $new->targetValue = $targetValue;
        return $new;
    }

    /**
     * Determine the current value of a field by using previously submitted values
     * in case of validation errors or data bound to the form.
     *
     * @param string $path
     * @return mixed|null
     */
    protected function findCurrentValueByPath(string $path)
    {
        // determine value, according to the following algorithm:
        if ($this->form && $this->form->getResult() !== null && $this->form->getResult()->hasErrors()) {
            // 1) if a validation error has occurred, pull the value from the submitted form values.
            $fieldValue = ObjectAccess::getPropertyPath($this->form->getSubmittedValues(), $path);
        } elseif ($path && $this->form && $this->form->getData()) {
            // 2) else, if "property" is specified, take the value from the bound object.
            $fieldValue = ObjectAccess::getPropertyPath($this->form->getData(), $path);
        } else {
            $fieldValue = null;
        }
        return $fieldValue;
    }

    /**
     * Determine the current result of a field in case of validation errors
     *
     * @param $path
     * @return Result|null
     */
    protected function findResultByPath($path): ?Result
    {
        // determine ValidationResult for the single property
        $fieldResult = null;
        if ($this->form && $this->form->getResult() && $this->form->getResult()->hasErrors()) {
            $fieldResult = $this->form->getResult()->forProperty($path);
        }
        return $fieldResult;
    }

    /***
     * @return string|null The full html name attribute of the field with applied namespace prefixes
     */
    public function getName(): ?string
    {
        if ($this->name) {
            if ($this->form && $this->form->getFieldNamePrefix()) {
                return $this->prefixFieldName($this->name, $this->form->getFieldNamePrefix()) . ($this->multiple ? '[]' : '');
            }
            return $this->name . ($this->multiple ? '[]' : '');
        }
        return null;
    }

    /**
     * @return bool True if the current value of the field is not null
     */
    public function hasCurrentValue(): bool
    {
        return !is_null($this->currentValue);
    }

    /**
     * @return mixed|null The current value of the field
     */
    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    /**
     * @return string The current value of the field converted to string for being used as html field value
     */
    public function getCurrentValueStringified(): string
    {
        return $this->stringifyValue($this->currentValue);
    }

    /**
     * @return array The current value of the field converted to an array of strings. Only used in multifields.
     */
    public function getCurrentMultivalueStringified(): array
    {
        if (is_iterable($this->currentValue)) {
            return $this->stringifyMultivalue($this->currentValue);
        } else {
            return [];
        }
    }

    /**
     * @return mixed|null The target value that is assigned to the field
     */
    public function getTargetValue()
    {
        return $this->targetValue;
    }

    /**
     * @return string The target value of the field converted to string for being used as html field value
     */
    public function getTargetValueStringified(): string
    {
        return $this->stringifyValue($this->targetValue);
    }

    /**
     * @return bool True if the field is configured as multiple
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * @return Result|null The current result of the field if the field was already submitted
     */
    public function getResult(): ?Result
    {
        return $this->result;
    }

    /**
     * @return bool Return whether the field has validation errors
     */
    public function hasErrors(): bool
    {
        if ($this->result) {
            return $this->result->hasErrors();
        }
        return false;
    }

    public function allowsCallOfMethod($methodName): bool
    {
        return $methodName !== 'withValue';
    }
}
