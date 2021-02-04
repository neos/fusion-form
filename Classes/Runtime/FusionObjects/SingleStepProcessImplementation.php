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
     * @var array
     */
    protected $data = [];

    /**
     * @var boolean
     */
    protected $isFinished = false;

    public function evaluate()
    {
        return $this;
    }

    public function handle(ActionRequest $request)
    {
        $arguments = $request->getArguments();
        $internalArguments = $request->getInternalArguments();
        $schema = $this->getSchema();

        if ($arguments || $internalArguments) {
            $this->data = $schema->convert($arguments);
            $result = $schema->validate($this->data);
            $request->setArgument('__submittedArguments', $this->data);
            $request->setArgument('__submittedArgumentValidationResults', $result);
            if (!$result->hasErrors()) {
                $this->isFinished = true;
            }
        }
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function render(): string
    {
        return $this->runtime->evaluate($this->path . '/content', $this);
    }

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    protected function getSchema(): SchemaInterface
    {
        return $this->fusionValue('schema');
    }
}
