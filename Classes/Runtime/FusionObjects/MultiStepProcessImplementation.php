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
use Neos\Flow\Mvc\ActionRequest;
use Neos\Error\Messages\Result;
use Neos\Fusion\Form\Runtime\Domain\FormState;
use Neos\Fusion\Form\Runtime\Domain\FormStateService;
use Neos\Fusion\Form\Runtime\Domain\ProcessInterface;
use Neos\Fusion\Form\Runtime\Domain\ProcessCollectionInterface;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Utility\Arrays;

class MultiStepProcessImplementation extends AbstractFusionObject implements ProcessInterface
{
    /**
     * @Flow\Inject
     * @var FormStateService
     */
    protected $formStateService;

    /**
     * @var FormState|null
     */
    protected $state;

    /**
     * @var mixed[]
     */
    protected $data;

    /**
     * @var string
     */
    protected $currentSubProcessKey;

    /**
     * @var string
     */
    protected $targetSubProcessKey;

    /**
     * Return reference to self during fusion evaluation
     * @return $this
     */
    public function evaluate()
    {
        return $this;
    }

    /**
     * @param ActionRequest $request
     * @param mixed[] $data
     */
    public function handle(ActionRequest $request, array $data = []): void
    {
        $this->data = $data;

        $internalArguments = $request->getInternalArguments();

        // restore state
        if (array_key_exists('__state', $internalArguments)
            && $internalArguments['__state']
        ) {
            $this->state = $this->formStateService->unserializeState($internalArguments['__state']);
        }

        // evaluate the subprocesses this has to be done after the state was restored
        // as the current data may affect @if conditions
        $subProcesses = $this->getSubProcesses();

        // select current subprocess
        if (array_key_exists('__current', $internalArguments)
            && $internalArguments['__current']
        ) {
            $this->currentSubProcessKey = (string)$internalArguments['__current'];
        } else {
            $subProcessKeys = array_keys($subProcesses);
            $this->currentSubProcessKey = (string)reset($subProcessKeys);
        }

        // store target subprocess, but only if it already was submitted
        if (array_key_exists('__target', $internalArguments)
            && $internalArguments['__target']
            && $this->state
            && $this->state->hasPart($internalArguments['__target'])
        ) {
            $this->targetSubProcessKey = (string)$internalArguments['__target'];
        }

        // find current and handle
        $currentSubProcess = $subProcesses[$this->currentSubProcessKey];
        $currentSubProcess->handle($request, $this->data);

        if ($currentSubProcess->isFinished()) {
            if (!$this->state) {
                $this->state = new FormState();
            }

            $this->state->commitPart(
                $this->currentSubProcessKey,
                $currentSubProcess->getData()
            );
        } else {
            if ($this->targetSubProcessKey) {
                $request->setArgument('__submittedArguments', []);
                $request->setArgument('__submittedArgumentValidationResults', new Result());
            }
        }
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        if (!$this->state) {
            return false;
        }

        $subProcesses = $this->getSubProcesses();

        foreach ($subProcesses as $subProcessKey => $subProcess) {
            if ($this->state->hasPart($subProcessKey) == false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $subProcesses = $this->getSubProcesses();
        if ($this->targetSubProcessKey) {
            $renderSubProcessKey = $this->targetSubProcessKey;
        } else {
            foreach ($subProcesses as $subProcessKey => $subProcess) {
                if (!$this->state || !$this->state->hasPart($subProcessKey)) {
                    $renderSubProcessKey = $subProcessKey;
                    break;
                }
            }
        }

        if (isset($renderSubProcessKey) && $renderSubProcessKey && array_key_exists($renderSubProcessKey, $subProcesses)) {
            $this->getRuntime()->pushContext('process', $this->prepareProcessInformation($renderSubProcessKey, $subProcesses));
            $hiddenFields = $this->runtime->evaluate($this->path . '/hiddenFields') ?? '';
            $header = $this->runtime->evaluate($this->path . '/header') ?? '';
            $body = $subProcesses[$renderSubProcessKey]->render();
            $footer = $this->runtime->evaluate($this->path . '/footer') ?? '';
            $this->getRuntime()->popContext();
            return $hiddenFields . $header . $body . $footer;
        }

        return '';
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        // initial data
        $data = $this->data;

        // add subprocess data from state
        if ($this->state) {
            foreach ($this->state->getAllParts() as $subProcessKey => $subProcessData) {
                $data = Arrays::arrayMergeRecursiveOverrule(
                    $data,
                    $subProcessData
                );
            }
        }

        return $data;
    }

    /**
     * @param string|int $subProcessKey
     * @param ProcessInterface[] $subProcesses
     * @return mixed[]
     */
    protected function prepareProcessInformation($subProcessKey, array $subProcesses): array
    {
        $subProcessKeys = array_keys($subProcesses);
        $currentIndex = array_search($subProcessKey, $subProcessKeys);

        $process = [];
        $process['state'] = $this->state ? $this->formStateService->serializeState($this->state) : null;
        $process['current'] = $subProcessKey;
        $process['prev'] = ($currentIndex > 0) ? $subProcessKeys[$currentIndex - 1]: null ;
        $process['next'] = ($currentIndex < (count($subProcessKeys) - 1)) ? $subProcessKeys[$currentIndex + 1] : null;
        $process['all'] = $subProcessKeys;
        $process['submitted'] = $this->state ? $this->state->getCommittedPartNames() : [];
        $process['isFirst'] = ($subProcessKey === reset($subProcessKeys));
        $process['isLast'] = ($subProcessKey === end($subProcessKeys));

        return $process;
    }

    /**
     * @return ProcessInterface[]
     */
    protected function getSubProcesses(): array
    {
        $this->runtime->pushContext('data', $this->getData());
        $collection = $this->getProcessCollection();
        $result = $collection->getItems();
        $this->runtime->popContext();
        return $result;
    }

    /**
     * @return ProcessCollectionInterface
     */
    protected function getProcessCollection(): ProcessCollectionInterface
    {
        return $this->fusionValue('steps');
    }
}
