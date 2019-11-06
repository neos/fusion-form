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

use Neos\Fusion\Form\Domain\Option;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class OptionDefinitionImplementation extends AbstractFusionObject
{
    /**
     * @return mixed
     */
    protected function getValue()
    {
        return $this->fusionValue('value');
    }

    /**
     * @return Option
     */
    public function evaluate(): Option
    {
        $value = $this->getValue();
        return new Option($value);
    }
}
