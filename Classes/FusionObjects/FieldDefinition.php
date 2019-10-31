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

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Fusion\Form\Domain\Model\Form;
use Neos\Fusion\Form\Domain\Model\Field;
use Neos\Error\Messages\Result;
use Neos\Utility\ObjectAccess;

class FieldDefinition extends AbstractFusionObject
{

    /**
     * @return Field
     */
    public function evaluate(): Field
    {
        $outerField= $this->fusionValue('field');
        $form = $this->fusionValue('form');
        $name = $this->fusionValue('name');
        $value = $this->fusionValue('value');
        $multiple = $this->fusionValue('multiple');

        // reuse outerfield if no name is given
        if (!$name && $outerField && $outerField instanceof Field) {
            return $outerField->withTargetValue($value);
        }

        return new Field (
            $form,
            $name,
            $value,
            $multiple
        );
    }
}
