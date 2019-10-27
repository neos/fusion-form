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
use Neos\Flow\Mvc\ActionRequest;
use Neos\Eel\ProtectedContextAwareInterface;

class Form implements ProtectedContextAwareInterface
{
    /**
     * @var ActionRequest
     */
    protected $request;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var string
     */
    protected $target;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $encoding;

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
     * Form constructor.
     * @param ActionRequest $request
     * @param mixed|null $data
     * @param string|null $fieldNamePrefix
     * @param array|null $submittedValues
     * @param Result|null $results
     * @param string|null $target
     * @param string|null $method
     * @param string|null $encoding
     */
    public function __construct(ActionRequest $request = null, $data = null, string $fieldNamePrefix = null, array $submittedValues = null, Result $results = null, string $target = null, string $method = "get", string $encoding = null)
    {
        $this->request = $request;
        $this->data = $data;
        $this->fieldNamePrefix = $fieldNamePrefix;
        $this->submittedValues = $submittedValues;
        $this->result = $results;
        $this->target = $target;
        $this->method = $method;
        $this->encoding = $encoding;
    }

    /**
     * @return ActionRequest|null
     */
    public function getRequest(): ?ActionRequest
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string|null
     */
    public function getFieldNamePrefix(): ?string
    {
        return $this->fieldNamePrefix;
    }

    /**
     * @return array|null
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

    /**
     * @return string|null
     */
    public function getTarget(): ?string
    {
        return $this->target;
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @return string|null
     */
    public function getEncoding(): ?string
    {
        return $this->encoding;
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
     * @return bool
     */
    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
