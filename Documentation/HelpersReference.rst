.. _`Fusion Form Eel Helper Reference`:

Fusion Form Eel Helper Reference
================================

This reference was automatically generated from code on 2019-11-06


.. _`Fusion Form Eel Helper Reference: Field`:

Field
-----



Implemented in: ``Neos\Fusion\Form\Eel\FieldHelper``

Field.getCurrentMultivalueStringified()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (array)

Field.getCurrentValue()
^^^^^^^^^^^^^^^^^^^^^^^

**Return** (mixed|null)

Field.getCurrentValueStringified()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (string)

Field.getName()
^^^^^^^^^^^^^^^

Return the name of the field with applied prefix and [] for multiple fields

**Return** (string|null)

Field.getResult()
^^^^^^^^^^^^^^^^^

**Return** (Result|null)

Field.getTargetValue()
^^^^^^^^^^^^^^^^^^^^^^

**Return** (mixed|null)

Field.getTargetValueStringified()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (string)

Field.hasCurrentValue()
^^^^^^^^^^^^^^^^^^^^^^^

**Return** (bool)

Field.hasErrors()
^^^^^^^^^^^^^^^^^

**Return** (bool)

Field.isMultiple()
^^^^^^^^^^^^^^^^^^

**Return** (bool)

Field.withTargetValue(targetValue)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

* ``targetValue`` (mixed|null, *optional*)

**Return** (FieldHelper)






.. _`Fusion Form Eel Helper Reference: Form`:

Form
----



Implemented in: ``Neos\Fusion\Form\Eel\FormHelper``

Form.calculateHiddenFields(content)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Calculate the hidden fields for the given form content as key-value array

* ``content`` (string, *optional*) form html body

**Return** (array) hiddenFields as key value pairs

Form.getData()
^^^^^^^^^^^^^^

**Return** (mixed)

Form.getEncoding()
^^^^^^^^^^^^^^^^^^

**Return** (string|null)

Form.getFieldNamePrefix()
^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (string|null)

Form.getMethod()
^^^^^^^^^^^^^^^^

**Return** (string|null)

Form.getRequest()
^^^^^^^^^^^^^^^^^

**Return** (ActionRequest|null)

Form.getResult()
^^^^^^^^^^^^^^^^

**Return** (Result)

Form.getSubmittedValues()
^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (array|null)

Form.getTarget()
^^^^^^^^^^^^^^^^

**Return** (string|null)

Form.hasErrors()
^^^^^^^^^^^^^^^^






.. _`Fusion Form Eel Helper Reference: Option`:

Option
------



Implemented in: ``Neos\Fusion\Form\Eel\OptionHelper``

Option.getTargetValue()
^^^^^^^^^^^^^^^^^^^^^^^

**Return** (mixed)

Option.getTargetValueStringified()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**Return** (string)





