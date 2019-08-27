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

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Error\Messages\Result;

/**
 * Used to output an HTML <form> tag which is targeted at the specified action, in the current controller and package.
 */
class FormDefinition implements ProtectedContextAwareInterface
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var string
     */
    protected $fieldNamePrefix;

    /**
     * @var array
     */
    protected $submittedValues;

    /**
     * @var Result
     */
    protected $result;

    /**
     * FormDefinition constructor.
     * @param mixed|null $data
     * @param string|null $fieldNamePrefix
     * @param array|null $submittedValues
     * @param Result|null $results
     */
    public function __construct($data = null, string $fieldNamePrefix = null, array $submittedValues = null, Result $results = null)
    {
        $this->data = $data;
        $this->fieldNamePrefix = $fieldNamePrefix;
        $this->submittedValues = $submittedValues;
        $this->result = $results;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getFieldNamePrefix(): ?string
    {
        return $this->fieldNamePrefix;
    }

    /**
     * @return array
     */
    public function getSubmittedValues(): ?array
    {
        return $this->submittedValues;
    }

    /**
     * @return Result
     */
    public function getResult(): ?Result
    {
        return $this->result;
    }

    public function hasErrors(): bool
    {
        if ($this->result) {
            return $this->result->hasErrors();
        }
        return false;
    }

    /**
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
