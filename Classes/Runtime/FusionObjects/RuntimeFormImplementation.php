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
use Neos\Flow\Security\Cryptography\HashService;

class RuntimeFormImplementation extends AbstractFusionObject
{
    /**
     * @Flow\Inject
     * @var HashService
     * @internal
     */
    protected $hashService;

    /**
     * @return string
     */
    protected function getIdentifier(): string
    {
        $identifier = $this->fusionValue('identifier');
        if ($identifier) {
            return $identifier;
        } else {
            return md5($this->path);
        }
    }

    /**
     * @return mixed[]
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
     * @return ActionInterface
     */
    protected function getAction(): ActionInterface
    {
        return  $this->fusionValue('action');
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
     * @return string
     */
    public function evaluate(): string
    {
        $identifier = $this->getIdentifier();
        $data = $this->getData();
        $process = $this->getProcess();

        $formRequest = $this->createFormRequest($identifier);
        $process->handle($data, $formRequest);
        if ($process->isFinished() === false) {
            return $this->renderForm($identifier, $formRequest, $process);
        } else {
            return $this->performAction($process->getData());
        }
    }

    /**
     * Prepare subrequest for the identifier namespace and transfer the arguments
     * only arguments present in __trustedProperties are transferred
     *
     * @param string $identifier
     * @return ActionRequest
     */
    protected function createFormRequest(string $identifier): ActionRequest
    {
        $request = $this->getCurrentActionRequest();
        $formRequest = $request->createSubRequest();
        $formRequest->setArgumentNamespace($identifier);
        if ($request->hasArgument($identifier) === true && is_array($request->getArgument($identifier))) {
            $submittedData = $request->getArgument($identifier);
            $subrequestArguments = [];
            if ($submittedData['__trustedProperties']) {
                $trustedProperties = unserialize($this->hashService->validateAndStripHmac($submittedData['__trustedProperties']), ['allowed_classes' => false]);
                foreach ($trustedProperties as $field => $number) {
                    if (array_key_exists($field, $submittedData)) {
                        $subrequestArguments[$field] = $submittedData[$field];
                    }
                }
            }
            $formRequest->setArguments($subrequestArguments);
        }
        return $formRequest;
    }

    /**
     * @param string $identifier
     * @param ActionRequest $formRequest
     * @param ProcessInterface $process
     * @return mixed|string|null
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     * @throws \Neos\Fusion\Exception\RuntimeException
     */
    protected function renderForm(string $identifier, ActionRequest $formRequest, ProcessInterface $process)
    {
        $data = $process->getData();
        $form = new Form(
            $formRequest,
            $data,
            $identifier,
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
        $action = $this->getAction();
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
