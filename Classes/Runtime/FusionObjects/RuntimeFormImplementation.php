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
    protected function getActions(): ActionInterface
    {
        return  $this->fusionValue('actions');
    }

    /**
     * @return string
     */
    public function evaluate(): string
    {
        $identifier = $this->getIdentifier();
        $process = $this->getProcess();
        $data = $this->getData();

        //
        // prepare subrequest for the form id-namespace and transfer the arguments
        // only arguments present in __trustedProperties are transferred
        //
        $request =  $this->getRuntime()->getControllerContext()->getRequest();
        $formRequest = $request->createSubRequest();
        $formRequest->setArgumentNamespace($identifier);
        if ($request->hasArgument($identifier) === true && is_array($request->getArgument($identifier))) {
            $submittedData = $request->getArgument($identifier);
            $subrequestArguments = [];
            if ($submittedData['__trustedProperties']) {
                $trustedProperties = unserialize($this->hashService->validateAndStripHmac($submittedData['__trustedProperties']), ['allowed_classes'=>false]);
                foreach ($trustedProperties as $field => $number) {
                    if (array_key_exists($field, $submittedData)) {
                        $subrequestArguments[$field] = $submittedData[$field];
                    }
                }
            }
            $formRequest->setArguments($subrequestArguments);
        }

        //
        // let the process handle the formRequest
        //
        $process->handle($data, $formRequest);

        //
        // if more data is needed the process is asked to render the form
        //
        if ($process->isFinished() === false) {
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
            $context['header'] = $this->runtime->evaluate($this->path . '/formHeader', $this);
            $context['content'] = $process->render();
            $context['footer'] = $this->runtime->evaluate($this->path . '/formFooter', $this);
            $this->runtime->pushContextArray($context);
            $result = $this->runtime->evaluate($this->path . '/renderer', $this);
            $this->runtime->popContext();
            $this->runtime->popContext();
            return $result;
        }

        //
        // return the text content of the action response, headers are merged  into the the main response
        //
        $this->getRuntime()->pushContext('data', $process->getData());
        $actions = $this->getActions();
        $actionResponse = $actions->perform();
        $this->getRuntime()->popContext();
        if ($actionResponse) {
            $result = $actionResponse->getContent();
            $actionResponse->setContent('');
            $actionResponse->mergeIntoParentResponse($this->getRuntime()->getControllerContext()->getResponse());
            return $result;
        } else {
            return '';
        }
    }
}
