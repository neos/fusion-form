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
use Neos\Fusion\Form\Domain\Form;
use Neos\Fusion\Form\Domain\Field;

class FieldDefinitionImplementation extends AbstractFusionObject
{

    /**
     * @return Form|null
     */
    protected function getForm(): ?Form
    {
        return $this->fusionValue('form');
    }

    /**
     * @return Field|null
     */
    protected function getField(): ?Field
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
     * @return Field
     */
    public function evaluate(): Field
    {
        $form = $this->getForm();
        $field = $this->getField();
        $name = $this->getName();
        $value = $this->getValue();
        $multiple = $this->getMultiple();

        // reuse outer field if no name is given
        if ($field && !$name) {
            if (is_null($value)) {
                return $field;
            } else {
                return $field->withTargetValue($value);
            }
        }

        return new Field(
            $form,
            $name,
            $value,
            $multiple
        );
    }
}
