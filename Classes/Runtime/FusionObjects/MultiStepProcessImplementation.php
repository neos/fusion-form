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
use Psr\Log\LoggerInterface;

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
     * @var bool
     */
    protected $attemptFinishing = false;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;


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
        $stateArgument = $internalArguments['__state'] ?? null;
        $currentStepArgument = $internalArguments['__current'] ?? null;
        $targetStepArgument = $internalArguments['__target'] ?? null;
        $finishProcessArgument = $internalArguments['__finish'] ?? null;

        // restore state
        if ($stateArgument) {
            $this->state = $this->formStateService->unserializeState($stateArgument);
        }

        // make the current `data` available to the context before sub processes are evaluated
        // as those may have conditions that rely on previous data
        $this->runtime->pushContext('data', $this->getData());

        // evaluate the subprocesses this has to be done after the state was restored
        // as the current data may affect @if conditions
        $subProcesses = $this->getSubProcesses();
        $subProcessKeys = array_keys($subProcesses);
        $firstSubProcessKey = (string)reset($subProcessKeys);

        // do not commit when the target was prepended with exclamation mark
        if ($targetStepArgument && (substr($targetStepArgument, 0, 1) === '!')) {
            $resultCommittingIsAllowed = false;
        } else {
            $resultCommittingIsAllowed = true;
        }

        // find current subprocess and handle the request
        if ($currentStepArgument) {
            $this->currentSubProcessKey = $currentStepArgument;
            $currentSubProcess = $subProcesses[$this->currentSubProcessKey];
            $currentSubProcess->handle($request, $this->data);
            if ($currentStepArgument && $resultCommittingIsAllowed) {
                if (!$this->state) {
                    $this->state = new FormState();
                }
                $this->state->commitPart(
                    $this->currentSubProcessKey,
                    $currentSubProcess->getData(),
                    $currentSubProcess->isFinished()
                );
            }
        } else {
            $this->currentSubProcessKey = $firstSubProcessKey;
        }

        // find target subprocess, but only if it already was submitted
        if ($targetStepArgument && $this->state) {
            if (substr($targetStepArgument, 0, 1) === '!') {
                $target = substr($targetStepArgument, 1);
            } else {
                $target = $targetStepArgument;
            }
            if ($currentSubProcess->isFinished()) {
                $this->targetSubProcessKey = $target;
            } else {
                if ($this->state->hasPart($target)) {
                    $this->targetSubProcessKey = $target;
                }
            }
        }

        // ensure no unfinished subprocesses are before the target
        if ($this->state && $this->targetSubProcessKey) {
            foreach ($subProcesses as $subProcessKey => $subProcesses) {
                if ($subProcessKey === $this->targetSubProcessKey) {
                    break;
                }
                if ($this->state->hasPart($subProcessKey, false)) {
                    $this->targetSubProcessKey = $subProcessKey;
                }
                if ($subProcessKey === $this->currentSubProcessKey) {
                    break;
                }
            }
        }

        // determine target if none was defined yet
        if (!$this->targetSubProcessKey) {
            if (!$this->state) {
                $this->targetSubProcessKey = $firstSubProcessKey;
            } else {
                foreach ($subProcesses as $subProcessKey => $subProcess) {
                    if ($this->state->hasPart($subProcessKey, false)) {
                        $this->targetSubProcessKey = $subProcessKey;
                        break;
                    }
                }
            }
        }

        // is this an attempt to finish the process
        if ($finishProcessArgument) {
            $this->attemptFinishing = true;
        }

        // restore fusion context to the state before data was pushed
        $this->runtime->popContext();
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        if (!$this->state) {
            return false;
        }

        if (!$this->attemptFinishing) {
            return false;
        }

        $subProcesses = $this->getSubProcesses();
        foreach ($subProcesses as $subProcessKey => $subProcess) {
            if ($this->state->hasPart($subProcessKey, true) == false) {
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

        if (isset($this->targetSubProcessKey) && $this->targetSubProcessKey && array_key_exists($this->targetSubProcessKey, $subProcesses)) {
            $this->getRuntime()->pushContext('process', $this->prepareProcessInformation($this->targetSubProcessKey, $subProcesses));
            $hiddenFields = $this->runtime->evaluate($this->path . '/hiddenFields') ?? '';
            $header = $this->runtime->evaluate($this->path . '/header') ?? '';
            $body = $subProcesses[$this->targetSubProcessKey]->render();
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
            $data = Arrays::arrayMergeRecursiveOverrule($data, $this->state->getData());
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
