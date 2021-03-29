<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\FusionObjects;

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
use Neos\Error\Messages\Result;
use Neos\Flow\Cli\Request;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Fusion\Form\Domain\Form;
use Neos\Fusion\Form\Runtime\Domain\ProcessInterface;
use Neos\Fusion\Form\Runtime\Domain\SchemaInterface;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class SingleStepProcessImplementation extends AbstractFusionObject implements ProcessInterface
{
    /**
     * @var mixed[]
     */
    protected $data = [];

    /**
     * @var boolean
     */
    protected $isFinished = false;

    /**
     * Return reference to self during fusion evaluation
     * @return $this
     */
    public function evaluate(): self
    {
        return $this;
    }

    /**
     * @param ActionRequest $request
     * @param mixed[] $data
     * @throws \Neos\Flow\Mvc\Exception\InvalidActionNameException
     * @throws \Neos\Flow\Mvc\Exception\InvalidArgumentNameException
     * @throws \Neos\Flow\Mvc\Exception\InvalidArgumentTypeException
     * @throws \Neos\Flow\Mvc\Exception\InvalidControllerNameException
     */
    public function handle(ActionRequest $request, array $data = []): void
    {
        $this->data = $data;

        $arguments = $request->getArguments();
        $internalArguments = $request->getInternalArguments();
        $schema = $this->getSchema();

        if ($arguments || $internalArguments) {
            $data = $schema->convert($arguments);
            $result = $schema->validate($data);
            $request->setArgument('__submittedArguments', $data);
            $request->setArgument('__submittedArgumentValidationResults', $result);
            if (!$result->hasErrors()) {
                $this->data = $data;
                $this->isFinished = true;
            }
        }
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    /**
     * @return string
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Fusion\Exception\RuntimeException
     */
    public function render(): string
    {
        $header = $this->runtime->evaluate($this->path . '/header', $this) ?? '';
        $content = $this->runtime->evaluate($this->path . '/content', $this) ?? '';
        $footer = $this->runtime->evaluate($this->path . '/footer', $this) ?? '';
        return $header . $content . $footer;
    }


    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return SchemaInterface
     */
    protected function getSchema(): SchemaInterface
    {
        return $this->fusionValue('schema');
    }
}
