<?php
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

class FieldDefinition
{

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $value;

    /**
     * @var boolean
     */
    protected $multiple;

    /**
     * @var bool
     */
    protected $result;

    /**
     * FieldDefinition constructor.
     *
     * @param string|null $name
     * @param array|string|null $value
     * @param bool $multiple
     * @param Result $result
     */
    public function __construct(string $name = null, $value = null, $multiple = false, Result $result = null)
    {
        $this->name = $name;
        $this->value = $value;
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
     * @return string|array|null
     */
    public function getValue()
    {
        return $this->value;
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
