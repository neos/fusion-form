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
use Neos\Fusion\Form\Eel\FormHelper;
use Neos\Fusion\Form\Eel\FieldHelper;
use Neos\Error\Messages\Result;
use Neos\Utility\ObjectAccess;

class FieldDefinition extends AbstractFusionObject
{

    /**
     * @return FormHelper|null
     */
    protected function getForm(): ?FormHelper
    {
        return $this->fusionValue('form');
    }

    /**
     * @return FieldHelper|null
     */
    protected function getField(): ?FieldHelper
    {
        return $this->fusionValue('field');
    }

    /**
     * @return string|null
     */
    protected function getName(): ?string
    {
        return $this->fusionValue('name');
    }

    /**
     * @return mixed
     */
    protected function getValue()
    {
        return $this->fusionValue('value');
    }

    /**
     * @return bool
     */
    protected function getMultiple(): bool
    {
        return (bool)$this->fusionValue('multiple');
    }

    /**
     * @return FieldHelper
     */
    public function evaluate(): FieldHelper
    {
        $form = $this->getForm();
        $field = $this->getField();
        $name = $this->getName();
        $value = $this->getValue();
        $multiple = $this->getMultiple();

        // reuse outer field if no name is given
        if (!$name && $field) {
            return $field->withTargetValue($value);
        }

        return new FieldHelper(
            $form,
            $name,
            $value,
            $multiple
        );
    }
}
