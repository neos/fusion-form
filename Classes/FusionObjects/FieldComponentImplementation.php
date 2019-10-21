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
use Neos\Fusion\Form\Eel\FormHelper;
use Neos\Fusion\Form\Domain\Model\Form;
use Neos\Fusion\Form\Domain\Model\Field;
use Neos\Fusion\FusionObjects\ComponentImplementation;
use Neos\Utility\ObjectAccess;

class FieldComponentImplementation extends ComponentImplementation
{
    /**
     * @var FormHelper
     * @Flow\Inject
     */
    protected $formHelper;

    /**
     * Properties that are ignored and not included into the ``props`` context
     *
     * @var array
     */
    protected $ignoreProperties = ['__meta', 'renderer', 'field'];

    /**
     * Prepare the context for the renderer with the ``field``
     *
     * @param array $context
     * @return array
     */
    protected function prepare($context)
    {
        $form = $this->fusionValue('field/form');
        $name = $this->fusionValue('field/name');
        $multiple = $this->fusionValue('field/multiple') ?: false;

        //
        // create and the `field` to the context before the props are evaluated
        // if no `name` is defined an existing `field` is reused
        //
        if ($name || !array_key_exists('field', $context)) {
            $context['field'] = $this->createField($form, $name, $multiple);

            // evaluate props with field in context
            $this->runtime->pushContextArray($context);
            $context = parent::prepare($context);
            $this->runtime->popContext();
        } else {
            $context = parent::prepare($context);
        }
        return $context;
    }

    /**
     * Create a field definition object
     *
     * @param Form|null $form
     * @param string $name
     * @param bool $multiple
     * @return Field
     */
    public function createField(Form $form = null, string $name = null, bool $multiple = false): Field
    {
        if (!$name) {
            return new Field(null, null, null);
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

        return new Field(
            $fieldName,
            $fieldValue,
            $multiple,
            $fieldResult
        );
    }
}
