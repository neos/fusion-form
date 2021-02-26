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
use Neos\Fusion\Core\Parser;
use Neos\Fusion\Core\Runtime;
use Neos\Fusion\Form\Runtime\Domain\FormState;
use Neos\Fusion\Form\Runtime\Domain\ProcessInterface;
use Neos\Utility\Arrays;

class MultiStepProcessImplementation extends AbstractCollectionFusionObject implements ProcessInterface
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
     * @var \ArrayIterator<string, ProcessInterface>
     */
    protected $subProcessIterator;

    /**
     * @var string[]
     */
    protected $subProcessKeys;

    /**
     * @var mixed[]
     */
    protected $data;

    /**
     *
     * @return $this
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     */
    public function evaluate(): self
    {
        $subProcessKeys = $this->sortNestedFusionKeys();

        if (count($subProcessKeys) === 0) {
            throw new \Neos\Fusion\Exception("No Subprocesses found");
        }

        $subProcesses = [];
        foreach ($subProcessKeys as $key) {
            $propertyPath = $key;
            if ($this->isUntypedProperty($this->properties[$key])) {
                $propertyPath .= '<Neos.Fusion.Form:Runtime.SingleStepProcess>';
            }
            try {
                $value = $this->fusionValue($propertyPath);
            } catch (\Exception $e) {
                $value = $this->runtime->handleRenderingException($this->path . '/' . $key, $e);
            }
            if ($value === null && $this->runtime->getLastEvaluationStatus() === Runtime::EVALUATION_SKIPPED) {
                continue;
            }
            if ($value instanceof ProcessInterface) {
                $subProcesses[$key] = $value;
            }
        }

        $this->subProcessIterator = new \ArrayIterator($subProcesses);
        $this->subProcessKeys = $subProcessKeys;

        return $this;
    }

    /**
     * @param mixed[]|null $data
     * @param ActionRequest $request
     * @throws \Neos\Flow\Security\Exception\InvalidArgumentForHashGenerationException
     * @throws \Neos\Flow\Security\Exception\InvalidHashException
     */
    public function handle($data = null, ActionRequest $request): void
    {
        $this->data = $data;

        $internalArguments = $request->getInternalArguments();

        // restore/init state
        if (array_key_exists('__state', $internalArguments)
            && $internalArguments['__state']
        ) {
            $validatedState = $this->hashService->validateAndStripHmac($internalArguments['__state']);
            $this->state = unserialize(base64_decode($validatedState), ['allowed_classes' => [FormState::class]]);
        }

//        // restore/initialize subprocess state
//        $this->subProcessIterator->rewind();
//        foreach ($this->subProcessIterator as $key => $subprocess) {
//            if ($this->state && $this->state->hasPart($key)) {
//                $subprocess->setData($this->state->getPart($key));
//            } else {
//                $subprocess->setData($this->data);
//            }
//        }

        // select current page
        if (array_key_exists('__current', $internalArguments)
            && $internalArguments['__current']
            && $this->subProcessIterator->offsetExists($internalArguments['__current'])
        ) {
            $this->subProcessIterator->rewind();
            while ($this->subProcessIterator->key() !== $internalArguments['__current']) {
                $this->subProcessIterator->next();
            }
        } else {
            $this->subProcessIterator->rewind();
        }

        // pass request to current subprocess
        $this->subProcessIterator->current()->handle($this->data, $request);

        if ($this->subProcessIterator->current()->isFinished()) {
            if (!$this->state) {
                $this->state = new FormState();
            }

            $this->state->commitPart(
                $this->subProcessIterator->key(),
                $this->subProcessIterator->current()->getData()
            );

            // find missing pages
            $this->subProcessIterator->rewind();
            while ($this->subProcessIterator->valid()) {
                if ($this->state->hasPart($this->subProcessIterator->key()) == false) {
                    break;
                }
                $this->subProcessIterator->next();
            }

            if (!$this->subProcessIterator->valid()) {
                $this->subProcessIterator->rewind();
            }
        }

        // select target but only for previously submitted parts
        if (array_key_exists('__target', $internalArguments)
            && $internalArguments['__target']
            && $this->subProcessIterator->offsetExists($internalArguments['__target'])
            && $this->state
            && $this->state->hasPart($internalArguments['__target'])
        ) {
            $this->subProcessIterator->rewind();
            while ($this->subProcessIterator->key() !== $internalArguments['__target']) {
                $this->subProcessIterator->next();
            }
        }
    }

    public function isFinished(): bool
    {
        if (!$this->state) {
            return false;
        }
        $result = true;
        foreach ($this->subProcessKeys as $key) {
            if ($this->state->hasPart($key) == false) {
                $result =  false;
                break;
            }
        }
        return $result;
    }

    public function render(): string
    {
        $content = $this->subProcessIterator->current()->render();
        $state =  $this->runtime->evaluate($this->path . '/state', $this);
        return $state . $content;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        // initial data
        $data = $this->data;

        // internal arguments for form navigation
        /**
         * @var int $currentIndex
         */
        $currentIndex = array_search($this->subProcessIterator->key(), $this->subProcessKeys);
        $data['__state'] = $this->state ? $this->hashService->appendHmac(base64_encode(serialize($this->state))) : null;
        $data['__current'] = $this->subProcessIterator->key();
        $data['__prev'] = ($currentIndex > 0) ? $this->subProcessKeys[$currentIndex - 1]: null ;
        $data['__next'] = ($currentIndex < (count($this->subProcessKeys) - 1)) ? $this->subProcessKeys[$currentIndex + 1] : null;
        $data['__all'] = $this->subProcessKeys;

        // collect subprocess data from state
        if ($this->state) {
            foreach ($this->subProcessKeys as $key) {
                if ($this->state->hasPart($key)) {
                    $data = Arrays::arrayMergeRecursiveOverrule(
                        $data,
                        $this->state->getPart($key)
                    );
                }
            }
        }

        return $data;
    }
}
