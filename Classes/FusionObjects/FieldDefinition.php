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
use Neos\Fusion\Form\Domain\Model\Field;
use Neos\Fusion\Form\Eel\FormHelper;
use Neos\Error\Messages\Result;
use Neos\Utility\ObjectAccess;

class FieldDefinition extends AbstractFusionObject
{
    /**
     * @var FormHelper
     * @Flow\Inject
     */
    protected $formHelper;

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

        // early return
        if (!$name) {
            if ($outerField && $outerField instanceof Field) {
                $field = clone $outerField;
                $field->setTargetValue($value);
                return $field;
            }
            return new Field(null, null, null, $value);
        }

        // render fieldName
        if ($form && $form->getFieldNamePrefix()) {
            $fieldName = $this->formHelper->prefixFieldName($name, $form->getFieldNamePrefix());
        } else {
            $fieldName = $name;
        }
        if ($multiple) {
            $fieldName .= '[]';
        }

        // create property path from fieldname
        $path = $this->formHelper->fieldNameToPath($name);

        // determine value, according to the following algorithm:
        if ($form && $form->getResult() !== null && $form->getResult()->hasErrors()) {
            // 1) if a validation error has occurred, pull the value from the submitted form values.
            $fieldValue = ObjectAccess::getPropertyPath($form->getSubmittedValues(), $path);
        } elseif ($path && $form && $form->getData()) {
            // 2) else, if "property" is specified, take the value from the bound object.
            $fieldValue = ObjectAccess::getPropertyPath($form->getData(), $path);
        } else {
            $fieldValue = null;
        }

        // determine ValidationResult for the single property
        $fieldResult = null;
        if ($form && $form->getResult() && $form->getResult()->hasErrors()) {
            $fieldResult = $form->getResult()->forProperty($path);
        }

        return new Field (
            $fieldName,
            $fieldValue,
            $value,
            $multiple,
            $fieldResult
        );
    }
}
