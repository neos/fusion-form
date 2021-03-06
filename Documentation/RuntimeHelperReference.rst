.. _`Fusion Form Helper Reference`:

Fusion Form Helper Reference
============================

This reference was automatically generated from code on 2021-03-22


.. _`Fusion Form Helper Reference: Neos\Fusion\Form\Runtime\Helper\ArrayOfSchemaDefinition`:

Neos\Fusion\Form\Runtime\Helper\ArrayOfSchemaDefinition
-------------------------------------------------------



Implemented in: ``Neos\Fusion\Form\Runtime\Helper\ArrayOfSchemaDefinition``

Neos\Fusion\Form\Runtime\Helper\ArrayOfSchemaDefinition.isRequired()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Add a NotEmpty Validator

**Return** ($this)

Neos\Fusion\Form\Runtime\Helper\ArrayOfSchemaDefinition.typeConverterOption(className, optionName, optionValue)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Add a typeConverter option to the schema

* ``className`` (string) The typeConverter className to set options for
* ``optionName`` (string) The option name
* ``optionValue`` (mixed) The value to set

**Return** ($this)

Neos\Fusion\Form\Runtime\Helper\ArrayOfSchemaDefinition.validator(type, options)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Add a validator to the schema

* ``type`` (string) The validaor indentifier or className
* ``options`` (mixed[]|null, *optional*) The options to set for the validator

**Return** ($this)






.. _`Fusion Form Helper Reference: Neos\Fusion\Form\Runtime\Helper\SchemaDefinition`:

Neos\Fusion\Form\Runtime\Helper\SchemaDefinition
------------------------------------------------



Implemented in: ``Neos\Fusion\Form\Runtime\Helper\SchemaDefinition``

Neos\Fusion\Form\Runtime\Helper\SchemaDefinition.isRequired()
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Add a NotEmpty Validator

**Return** ($this)

Neos\Fusion\Form\Runtime\Helper\SchemaDefinition.typeConverterOption(className, optionName, optionValue)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Add a typeConverter option to the schema

* ``className`` (string) The typeConverter className to set options for
* ``optionName`` (string) The option name
* ``optionValue`` (mixed) The value to set

**Return** ($this)

Neos\Fusion\Form\Runtime\Helper\SchemaDefinition.validator(type, options)
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Add a validator to the schema

* ``type`` (string) The validaor indentifier or className
* ``options`` (mixed[]|null, *optional*) The options to set for the validator

**Return** ($this)






.. _`Fusion Form Helper Reference: Schema`:

Schema
------



Implemented in: ``Neos\Fusion\Form\Runtime\Helper\SchemaHelper``

Schema.array()
^^^^^^^^^^^^^^

Create an array schema.

**Return** (SchemaInterface)

Schema.arrayOf(schema)
^^^^^^^^^^^^^^^^^^^^^^

Create a date schema for an array by providing a schema for all items

* ``schema`` (SchemaInterface) The schema for the items of the array

**Return** (SchemaInterface)

Schema.boolean()
^^^^^^^^^^^^^^^^

Create a boolean schema

**Return** (SchemaInterface)

Schema.date(format)
^^^^^^^^^^^^^^^^^^^

Create a date schema. The php value will be DateTime

* ``format`` (string, *optional*) The format default is "Y-m-d

**Return** (SchemaInterface)

Schema.float()
^^^^^^^^^^^^^^

Create a float schema

**Return** (SchemaInterface)

Schema.forType(type)
^^^^^^^^^^^^^^^^^^^^

Create a schema for the given type

* ``type`` (string) The type or className that is expected

**Return** (SchemaInterface)

Schema.integer()
^^^^^^^^^^^^^^^^

Create a integer schema

**Return** (SchemaInterface)

Schema.resource(collection)
^^^^^^^^^^^^^^^^^^^^^^^^^^^

Create a resource schema

* ``collection`` (string, *optional*) The collection new resources are put into

**Return** (SchemaInterface)

Schema.string()
^^^^^^^^^^^^^^^

Create a string schema

**Return** (SchemaInterface)





