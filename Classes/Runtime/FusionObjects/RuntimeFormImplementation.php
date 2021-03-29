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
        $this->runtime->pushContext('request', $formRequest);
        $process->handle($formRequest, $data);
        if ($process->isFinished() === false) {
            $result = $this->renderForm($process, $formRequest);
        } else {
            $result = $this->performAction($process->getData());
        }
        $this->runtime->popContext();
        return $result;
    }

    /**
     * @param ProcessInterface $process
     * @param ActionRequest $formRequest
     * @return mixed|string|null
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Fusion\Exception\RuntimeException
     */
    protected function renderForm(ProcessInterface $process, ActionRequest $formRequest)
    {
        $data = $process->getData();
        $form = new Form(
            $formRequest,
            $data,
            null,
            null,
            'post',
            'multipart/form-data'
        );

        $context = $this->runtime->getCurrentContext();
        $context['form'] = $form;
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
