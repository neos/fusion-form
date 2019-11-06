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
     * @var oolean
     */
    protected $multiple;

    /**
     * @var bool
     */
    protected $result;

    /**
     * Field constructor.
     * @param Form|null $form
     * @param string|null $name
     * @param null $targetValue
     * @param bool $multiple
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

    /**
     * Return the name of the field with applied prefix and [] for multiple fields
     * @return string|null
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
     * @return bool
     */
    public function hasCurrentValue(): bool
    {
        return !is_null($this->currentValue);
    }

    /**
     * @return mixed|null
     */
    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    /**
     * @return string
     */
    public function getCurrentValueStringified(): string
    {
        return $this->stringifyValue($this->currentValue);
    }

    /**
     * @return array
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
     * @return mixed|null
     */
    public function getTargetValue()
    {
        return $this->targetValue;
    }

    /**
     * @return string
     */
    public function getTargetValueStringified(): string
    {
        return $this->stringifyValue($this->targetValue);
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * @return Result|null
     */
    public function getResult(): ?Result
    {
        return $this->result;
    }

    /**
     * @return bool
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
