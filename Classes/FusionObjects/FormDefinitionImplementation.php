<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\FusionObjects;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Mvc\ActionRequest;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Fusion\Form\Domain\Form;
use Neos\Error\Messages\Result;

class FormDefinitionImplementation extends AbstractFusionObject
{

    /**
     * @return ActionRequest|null
     */
    protected function getRequest(): ?ActionRequest
    {
        return $this->fusionValue('request');
    }

    /**
     * @return mixed
     */
    protected function getData()
    {
        return $this->fusionValue('data');
    }

    /**
     * @return string|null
     */
    protected function getFieldnamePrefix(): ?string
    {
        return $this->fusionValue('fieldNamePrefix');
    }

    /**
     * @return string|null
     */
    protected function getTarget(): ?string
    {
        return $this->fusionValue('target');
    }

    /**
     * @return string|null
     */
    protected function getMethod(): ?string
    {
        return $this->fusionValue('method');
    }

    /**
     * @return string|null
     */
    protected function getEncoding(): ?string
    {
        return $this->fusionValue('encoding');
    }
    /**
     * @return Form
     */
    public function evaluate(): Form
    {
        $request = $this->getRequest();
        $data = $this->getData();
        $fieldNamePrefix = $this->getFieldnamePrefix();
        $target = $this->getTarget();
        $method = $this->getMethod();
        $encoding = $this->getEncoding();

        return new Form(
            $request,
            $data,
            $fieldNamePrefix,
            $target,
            $method,
            $encoding
        );
    }
}
