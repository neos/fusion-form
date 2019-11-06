.. _`Fusion Form Helper Reference`:

Fusion Form Helper Reference
============================

This reference was automatically generated from code on 2019-11-06


.. _`Fusion Form Helper Reference: Neos\Fusion\Form\Domain\Field`:

Neos\Fusion\Form\Domain\Field
-----------------------------

This object describes a single editable value in a form. Usually this is
instantiated by the fusion prototype `Neos.Fusion.Form:Definition.Field`
and can be accessed as `field` in the fusion context.

Implemented in: ``Neos\Fusion\Form\Domain\Field``

Neos\Fusion\Form\Domain\Field.getCurrentMultivalueStringified()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (array) The current value of the field converted to an array of strings. Only used in multifields.

Neos\Fusion\Form\Domain\Field.getCurrentValue()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (mixed|null) The current value of the field

Neos\Fusion\Form\Domain\Field.getCurrentValueStringified()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (string) The current value of the field converted to string for being used as html field value

Neos\Fusion\Form\Domain\Field.getName()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Neos\Fusion\Form\Domain\Field.getResult()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (Result|null) The current result of the field if the field was already submitted

Neos\Fusion\Form\Domain\Field.getTargetValue()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (mixed|null) The target value that is assigned to the field

Neos\Fusion\Form\Domain\Field.getTargetValueStringified()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (string) The target value of the field converted to string for being used as html field value

Neos\Fusion\Form\Domain\Field.hasCurrentValue()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (bool) True if the current value of the field is not null

Neos\Fusion\Form\Domain\Field.hasErrors()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (bool) Return whether the field has validation errors

Neos\Fusion\Form\Domain\Field.isMultiple()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (bool) True if the field is configured as multiple

Neos\Fusion\Form\Domain\Field.withTargetValue(targetValue)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Create and return a copy of this field with alternate value
This is used to render multiple checkboxes or radio in a single field container

* ``targetValue`` (mixed|null, *optional*)

**Return** (Field)






.. _`Fusion Form Helper Reference: Neos\Fusion\Form\Domain\Form`:

Neos\Fusion\Form\Domain\Form
----------------------------

This object describes a the main properties of a form. Usually this is
instantiated by the fusion prototype `Neos.Fusion.Form:Definition.Form`
and can be accessed as `form` in the fusion context.

Implemented in: ``Neos\Fusion\Form\Domain\Form``

Neos\Fusion\Form\Domain\Form.calculateHiddenFields(content)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Calculate the hidden fields for the given form content as key-value array

* ``content`` (string, *optional*) The form html body, usually renderd via afx

**Return** (array) hiddenFields as key value pairs

Neos\Fusion\Form\Domain\Form.getData()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (mixed) The data that was bound to the form, usually a DataStructure

Neos\Fusion\Form\Domain\Form.getEncoding()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (string|null) The encoding for the form

Neos\Fusion\Form\Domain\Form.getFieldNamePrefix()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (string|null) The fieldname prefix that was assigned or determined from the request

Neos\Fusion\Form\Domain\Form.getMethod()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (string|null) The http method for submitting the form

Neos\Fusion\Form\Domain\Form.getRequest()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (ActionRequest|null) The ActionRequest the form is rendered with

Neos\Fusion\Form\Domain\Form.getResult()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (Result) The result for the whole form, can be used to render validation messahes in a central place

Neos\Fusion\Form\Domain\Form.getSubmittedValues()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (array|null) The previously submitted values when validation errors prevented processing the data

Neos\Fusion\Form\Domain\Form.getTarget()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (string|null) The target uri for the form, usually defined as Neos.Fusion:UriBuilder

Neos\Fusion\Form\Domain\Form.hasErrors()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (bool) Return whether the form had validation errors in a previous submit






.. _`Fusion Form Helper Reference: Neos\Fusion\Form\Domain\Option`:

Neos\Fusion\Form\Domain\Option
------------------------------

This object describes a single target value for a form field. Usually this is
instantiated by the fusion prototype `Neos.Fusion.Form:Definition.Option`
and can be accessed as `option` in the fusion context.

Implemented in: ``Neos\Fusion\Form\Domain\Option``

Neos\Fusion\Form\Domain\Option.getTargetValue()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (mixed) The target value of the option

Neos\Fusion\Form\Domain\Option.getTargetValueStringified()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (string) The target value of the option converted to string for being used as html option value





