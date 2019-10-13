.. __Neos_Fusion_Form-reference:

=====================
Fusion Form Reference
=====================

.. _Neos_Fusion_Form__Form:

Neos.Fusion.Form:Form
---------------------

The main component for defining forms in afx. The prototype is responsible for rendering the actual form-tab and
instantiating the `field` context variable that is available to all the fusion that is rendered as `content`

In addition the form component will also:
- Render a hidden `__csrfToken` field, to ensure that the form was actually rendered by Neos.
- Render hidden `__referrer` fields for the current and parent request to allow Flow to send the request back in case of validation errors.
- Render hidden `__trustedProperties` fields to enable the Flow property-mapping for the submitted values.
- Render hidden `__identity` fields for all fields that are bound to properties of persisted objects.
- Render hidden `empty` fields for `checkbox` and `submit[multiple]` fields make sure unselected values are send to the controller.

:data: (mixed) The data-structure the form is bound to. Can contain objects, scalar and nested values.
:action: (string, defaults to `Neos.Fusion:UriBuilder`)
:method: (string, defaults to 'post')
:enctype: (string, defaults to 'multipart/form-data')
:request: (\Neos\Flow\Mvc\ActionRequest, defaults to the current request in the fusion context) The action request object the form is rendered for.
:fieldnamePrefix: (string, defauklt to the argumentNamespace of the `request`) The prefix for all fieldnames in this form.

:content: (string) The content of the form. This can be any html that contains form fields. Usually this is defined via afx.
:id: (string) The id-attribute for the form-tag.
:class: (string) The class-attribute for the form-tag.
:attributes: (array, default to Neos.Fusion:DataStructure) Generic attributes other than the above for the form-tag.

The `form` context:
```````````````````

The `form` context is instantiated before all props or content for the form are created with a
\Neos\Fusion\Form\Domain\Model\FieldDefinition objkect. It is responsible for making making the current form data
available to all fields that are rendered inside to access validation errors and previously submitted values once
a submit failed.

The following properties are accessible via eel-expression in afx:
:form.request: (\Neos\Flow\Mvc\ActionRequest)
:form.data: (array) The data structure the form is bound to.
:form.fieldNamePrefix: (string) The fieldname prefix of the form.
:form.submittedValues: (array) Data structure that contains previously submitted values if errors occurred.
:form.result: (\Neos\Error\Messages\Result) The validation result for the whole form if errors occurred.
:form.errors: (boolean) Return true when the `result` contains errors.

Example::

    afx`
        <Neos.Fusion.Form:Form data={{customer: customer}} action.action="submit">
            <Neos.Fusion.Form:FieldContainer name="user[firstName]" label="First Name">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:FieldContainer>

            <Neos.Fusion.Form:FieldContainer name="user[lastName]" label="Last Name">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:FieldContainer>

            <Neos.Fusion.Form:Submit value="submit" />
        </Neos.Fusion.Form:Form>
    `

.. _Neos_Fusion_Form__FieldComponent:

Neos.Fusion.Form:FieldComponent
-------------------------------

A field component is expected to render an input for a given fieldname. However since the actual
markup for fields is extensible this prototype does not render actual markup. Instead it populates the
`field` context variable and establishes the connection to the parent `form` for data-binding abd error
rendering.


:name: (string) The fieldname. Use square bracket syntax for nested properties.
:multiple: (boolean, default = false) Determine wether the field can contain multiple values like checkboxes or selects.
:form: (FormDefinition, by default this is taken from the `form` context), the formDefinition the field shall be bound to.

The following additional props are defined on the fieldComponent to be available in derived types.

:type: (string defaults to 'text') The type attribute for the input tag
:id: (string) The type attribute for the input tag
:class: (string) The type attribute for the input tag
:required: (boolean) The required attribute for the input tag
:value: (mixed) The value attribute for the input tag, this value is only used when neither `form.data` or `from.submittedValues` are present for the current `field`.
:attributes: (array, default to Neos.Fusion:DataStructure) Generic attributes for the input-tag.


The `field` context:
````````````````````
The `field` context is instantiated before the props of the fieldComponent are rendered and makes the following
properties accessible via eel.

:field.name: (string): The final name for the field with applied `namespacePrefix` from the `form`.
:field.value: (string|array): The value the field currently has from the `form.data` or `from.submittedValues`.
:field.multiple: (boolean): Return true when the field may contain multiple values.
:field.result: (\Neos\Error\Messages\Result) The validation result for field if errors occurred.
:field.errors: (boolean) Return true when the `result` contains errors.

.. _Neos_Fusion_Form__Input:

Neos.Fusion.Form:Input
----------------------

The `Neos.Fusion.Form:Input` component extends the `Neos.Fusion.Form:FieldComponent` and renders an input-tag.

The props `name`, `multiple`, `type`, `id`, `class`, `required`, `value`, `attributes` and `form` are inherited from `Neos_Fusion_Form__FieldComponent`_.

.. _Neos_Fusion_Form__Textfield:

Neos.Fusion.Form:Textfield
--------------------------

Extends Neos_Fusion_Form__Input_ and uses the default type `text`.

The props `name`, `multiple`, `type`, `id`, `class`, `required`, `value`, `attributes` and `form` are inherited from `Neos_Fusion_Form__FieldComponent`_.

Neos.Fusion.Form:Upload
-----------------------

Extends Neos_Fusion_Form__Input_ and uses the default type `file`.

The props `name`, `multiple`, `type`, `id`, `class`, `required`, `value`, `attributes` and `form` are inherited from `Neos_Fusion_Form__FieldComponent`_.

Neos.Fusion.Form:Password
-------------------------

Extends Neos_Fusion_Form__Input_ and uses the default type `password`.

The props `name`, `multiple`, `type`, `id`, `class`, `required`, `value`, `attributes` and `form` are inherited from `Neos_Fusion_Form__FieldComponent`_.

Neos.Fusion.Form:Hidden
-----------------------

Extends Neos_Fusion_Form__Input_ and uses the default type `hidden`.

The props `name`, `multiple`, `type`, `id`, `class`, `required`, `value`, `attributes` and `form` are inherited from `Neos_Fusion_Form__FieldComponent`_.

Neos.Fusion.Form:Submit
-----------------------

Extends Neos_Fusion_Form__Input_ and uses the default type `submit`.

The props `name`, `multiple`, `type`, `id`, `class`, `required`, `value`, `attributes` and `form` are inherited from `Neos_Fusion_Form__FieldComponent`_.

Neos.Fusion.Form:Checkbox
-------------------------

Render an input of type "checkbox".

:checked: (boolean, default = false) Wether this box is checked by default.

The props `name`, `multiple`, `type`, `id`, `class`, `required`, `value`, `attributes` and `form` are inherited from `Neos_Fusion_Form__FieldComponent`_.

Neos.Fusion.Form:Radio
----------------------

Render an input of type "radio".

:checked: (boolean, default = false) Wether this box is checked by default.

The props `name`, `multiple`, `type`, `id`, `class`, `required`, `value`, `attributes` and `form` are inherited from `Neos_Fusion_Form__FieldComponent`_.


Neos.Fusion.Form:Textarea
-------------------------

Render an textarea tag.

The props `name`, `multiple`, `type`, `id`, `class`, `required`, `value`, `attributes` and `form` are inherited from `Neos_Fusion_Form__FieldComponent`_.

.. _Neos_Fusion_Form__Select:


Neos.Fusion.Form:Select
-----------------------

Render a select tag. The options are expected as afx `content`. If the prototype Neos_Fusion_Form__Select_Option_
is used for defining the options the selected state is applied automaticvally by comparing `field.value` with `option.value`.

The props `name`, `multiple`, `type`, `id`, `class`, `required`, `value`, `attributes` and `form` are inherited from `Neos_Fusion_Form__FieldComponent`_.

.. _Neos_Fusion_Form__Select_Option:

Neos.Fusion.Form:Select.Option
------------------------------

Render an option tag inside a Neos_Fusion_Form__Select_.

:value: (mixed) The value the option represents.
:selected: (mixed) The initial select state that us overridden by `field.value` if this is present.
:content: (string) The content of the option tag that is displayes as label.

Example::

    renderer = afx`
        <Neos.Fusion.Form:Select name="user[gender]">
            <Neos.Fusion.Form:Select.Option valuer="male">Male</Neos.Fusion.Form:Select.Option>
            <Neos.Fusion.Form:Select.Option valuer="female">Female</Neos.Fusion.Form:Select.Option>
            <Neos.Fusion.Form:Select.Option valuer="diverse">Diverse</Neos.Fusion.Form:Select.Option>
        </Neos.Fusion.Form:Select>
    `
