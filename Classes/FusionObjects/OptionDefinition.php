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
use Neos\Fusion\Form\Domain\Model\Option;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Fusion\Form\Domain\Model\Form;
use Neos\Fusion\Form\Domain\Model\Field;
use Neos\Fusion\Form\Eel\FormHelper;
use Neos\Error\Messages\Result;
use Neos\Utility\ObjectAccess;

class OptionDefinition extends AbstractFusionObject
{
    /**
     * @var FormHelper
     * @Flow\Inject
     */
    protected $formHelper;

    /**
     * @return Option
     */
    public function evaluate(): Option
    {
        $value = $this->fusionValue('value');
        return new Option ($value);
    }
}
