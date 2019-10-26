<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Domain\Model;

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

class Field
{

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var mixed
     */
    protected $value;

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
     *
     * @param string|null $name
     * @param mixed|null $value
     * @param mixed|null $targetValue
     * @param bool $multiple
     * @param Result $result
     */
    public function __construct(string $name = null, $value = null, $targetValue = null, $multiple = false, Result $result = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->targetValue = $targetValue;
        $this->result = $result;
        $this->multiple = $multiple;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $targetValue
     */
    public function setTargetValue($targetValue): void
    {
        $this->targetValue = $targetValue;
    }

    /**
     * @return mixed
     */
    public function getTargetValue()
    {
        return $this->targetValue;
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
}
