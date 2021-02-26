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
use Neos\Flow\Security\Cryptography\HashService;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Fusion\Form\Runtime\Domain\FormState;
use Neos\Fusion\Form\Runtime\Domain\ProcessInterface;
use Neos\Fusion\Form\Runtime\Domain\ProcessCollectionInterface;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Utility\Arrays;

class MultiStepProcessImplementation extends AbstractFusionObject implements ProcessInterface
{
    /**
     * @Flow\Inject
     * @var HashService
     * @internal
     */
    protected $hashService;

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
     * @return $this
     */
    public function evaluate()
    {
        return $this;
    }

    /**
     * @param mixed[]|null $data
     * @param ActionRequest $request
     */
    public function handle($data = null, ActionRequest $request): void
    {
        $this->data = $data;

        $internalArguments = $request->getInternalArguments();

        // restore state
        if (array_key_exists('__state', $internalArguments)
            && $internalArguments['__state']
        ) {
            $validatedState = $this->hashService->validateAndStripHmac($internalArguments['__state']);
            $this->state = unserialize(base64_decode($validatedState), ['allowed_classes' => [FormState::class]]);
        }

        // evaluate the subprocesses this has to be done after tge state was restored
        // as the current data may affect @if conditions
        $subProcesses = $this->getCurrentSubProcesses();

        // select current subprocess
        if (array_key_exists('__current', $internalArguments)
            && $internalArguments['__current']
        ) {
            $this->currentSubProcessKey = $internalArguments['__current'];
        } else {
            $subProcessKeys = array_keys($subProcesses);
            $this->currentSubProcessKey = (string)reset($subProcessKeys);
        }

        // find current and handle
        $currentSubProcess = $subProcesses[$this->currentSubProcessKey];
        $currentSubProcess->handle($this->data, $request);

        if ($currentSubProcess->isFinished()) {
            if (!$this->state) {
                $this->state = new FormState();
            }

            $this->state->commitPart(
                $this->currentSubProcessKey,
                $currentSubProcess->getData()
            );
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

        $subProcesses = $this->getCurrentSubProcesses();

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
        $subProcesses = $this->getCurrentSubProcesses();
        if ($this->targetSubProcessKey) {
            $current = $this->targetSubProcessKey;
        } else {
            foreach ($subProcesses as $subProcessKey => $subProcess) {
                if (!$this->state || !$this->state->hasPart($subProcessKey)) {
                    $current = $subProcessKey;
                    break;
                }
            }
        }

        if (isset($current) && $current && array_key_exists($current, $subProcesses)) {
            $context = $this->getRuntime()->getCurrentContext();
            $context['state'] = $this->state ? $this->hashService->appendHmac(base64_encode(serialize($this->state))) : null;
            $context['current'] = $current;
            $context['content'] = $subProcesses[$current]->render();
            $this->getRuntime()->pushContextArray($context);
            $result = $this->runtime->evaluate($this->path . '/renderer');
            $this->getRuntime()->popContext();
            return $result;
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
            foreach ($this->state->getAll() as $subProcessKey => $subProcessData) {
                $data = Arrays::arrayMergeRecursiveOverrule(
                    $data,
                    $subProcessData
                );
            }
        }

        return $data;
    }

    /**
     * @return ProcessInterface[]
     */
    protected function getCurrentSubProcesses(): array
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
