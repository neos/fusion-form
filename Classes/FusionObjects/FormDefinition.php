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

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Fusion\Form\Domain\Model\Form;
use Neos\Error\Messages\Result;
use Neos\Fusion\Form\Eel\FormHelper;

class FormDefinition extends AbstractFusionObject
{
    /**
     * @var FormHelper
     * @Flow\Inject
     */
    protected $formHelper;

    /**
     * @return Form
     */
    public function evaluate(): Form
    {
        $request = $this->fusionValue('request');
        $data = $this->fusionValue('data');
        $fieldNamePrefix = $this->fusionValue('fieldNamePrefix');
        $target = $this->fusionValue('target');
        $method = $this->fusionValue('method');
        $encoding = $this->fusionValue('encoding');

        return new Form (
            $request,
            $data,
            $fieldNamePrefix ?: ($request ? $request->getArgumentNamespace() : ''),
            $request ? $request->getInternalArgument('__submittedArguments') : [],
            $request ? $request->getInternalArgument('__submittedArgumentValidationResults') : new Result(),
            $target,
            $method,
            $encoding
        );
    }
}
