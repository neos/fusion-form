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
use Neos\Flow\Mvc\ActionResponse;
use Neos\Fusion\Form\Domain\Form;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Fusion\Form\Runtime\Domain\ActionInterface;
use Neos\Fusion\Form\Runtime\Domain\ProcessInterface;
use Neos\Fusion\Form\Runtime\Domain\FormRequestFactory;

class RuntimeFormImplementation extends AbstractFusionObject
{
    /**
     * @var FormRequestFactory
     * @Flow\Inject
     */
    protected $formRequestFactory;

    /**
     * @return string
     */
    protected function getNamespace(): string
    {
        $namespace = $this->fusionValue('namespace');
        if ($namespace) {
            return $namespace;
        } else {
            return md5($this->path);
        }
    }

    /**
     * @return mixed[]
     */
    protected function getAttributes(): array
    {
        $attributes = $this->fusionValue('attributes');
        if (is_array($attributes)) {
            return $attributes;
        } else {
            return [];
        }
    }

    /**
     * @return mixed[]|null
     */
    protected function getData(): ?array
    {
        return $this->fusionValue('data');
    }

    /**
     * @return ProcessInterface
     */
    protected function getProcess(): ProcessInterface
    {
        return $this->fusionValue('process');
    }

    /**
     * @return ActionRequest
     */
    protected function getCurrentActionRequest(): ActionRequest
    {
        return $this->getRuntime()->getControllerContext()->getRequest();
    }

    /**
     * @return ActionResponse
     */
    protected function getCurrentActionResponse(): ActionResponse
    {
        return $this->getRuntime()->getControllerContext()->getResponse();
    }

    /**
     * Render the form or the action result depending on wether the process is finished
     * @return string
     */
    public function evaluate(): string
    {
        $namespace = $this->getNamespace();
        $data = $this->getData() ?? [];
        $process = $this->getProcess();

        $formRequest = $this->formRequestFactory->createFormRequest($this->getCurrentActionRequest(), $namespace);
        $context = $this->runtime->getCurrentContext();
        /**
          * The internal method "pushContextArray" allows some creative use,
          * as that we can override the "request" context.
          * This is not permitted via public / official api and probably an unwise idea to do.
          * {@see \Neos\Fusion\Core\FusionGlobals}
          */
        $context['request'] = $formRequest;
        $this->runtime->pushContextArray($context);
        $process->handle($formRequest, $data);
        if ($process->isFinished() === false) {
            $result = $this->renderForm($process, $formRequest, $this->getAttributes());
        } else {
            $result = $this->performAction($process->getData());
        }
        $this->runtime->popContext();
        return $result;
    }

    /**
     * @param ProcessInterface $process
     * @param ActionRequest $formRequest
     * @param mixed[] $attributes
     * @return mixed|string|null
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Fusion\Exception\RuntimeException
     */
    protected function renderForm(ProcessInterface $process, ActionRequest $formRequest, array $attributes)
    {
        $data = $process->getData();

        // @todo adjust after raising min php version to 8+
        // new Form(request:$formRequest, data: $data, method: 'post', encoding:'multipart/form-data', enableReferrer: false);
        $form = new Form(
            $formRequest,
            $data,
            null,
            null,
            'post',
            'multipart/form-data',
            false,
            true
        );

        $context = $this->runtime->getCurrentContext();
        $context['form'] = $form;
        $context['attributes'] = $attributes;
        $context['data'] = $data;
        $this->runtime->pushContextArray($context);
        $context['content'] = $process->render();
        $this->runtime->pushContextArray($context);
        $result = $this->runtime->evaluate($this->path . '/form', $this);
        $this->runtime->popContext();
        $this->runtime->popContext();
        return $result;
    }

    /**
     * Perform action and return the text content of the action response,
     * headers are merged  into the the main response
     *
     * @param mixed[] $data
     * @return string
     */
    protected function performAction($data): string
    {
        $this->getRuntime()->pushContext('data', $data);
        $action = $this->runtime->evaluate($this->path . '/action', $this);
        assert($action instanceof ActionInterface);
        $actionResponse = $action->perform();
        $this->getRuntime()->popContext();
        if ($actionResponse) {
            $result = $actionResponse->getContent();
            $actionResponse->setContent('');
            $actionResponse->mergeIntoParentResponse($this->getCurrentActionResponse());
        } else {
            $result = '';
        }
        return $result;
    }
}
