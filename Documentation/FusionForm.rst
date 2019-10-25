.. _'Neos.Fusion.Form':

==========================
Neos.Fusion.Form Reference
==========================

Neos.Fusion.Form:FormComponent
------------------------------

The Form component is a base prototype for rendering forms in afx. The prototype populates the
`form` context variable that is available to all the fusion that is rendered as `content`.

:form: (DataStructure) used to populate the `form` context but are not available as `props`
:form.request: (ActionRequest, defaults to the the current `request`) The data the form is bound to. Can contain objects, scalar and nested values.
:form.namespacePrefix: (string, defaults to `request.getArgumentNamespace()`) The data the form is bound to. Can contain objects, scalar and nested values.
:form.data: (mixed, defaults to `Neos.Fusion:DataStructure`) The data the form is bound to. Can contain objects, scalar and nested values.
:form.target: (string, default to `Neos.Fusion:UriBuilder`) The target uri the form will be sent to.
:form.method:  (string, default to `post`) The form method.
:form.encoding: (string, default to `multipart/form-data` when `form.method` == `post`) The form enctype `multipart/form.data` is required for file-uploads.

Attributes to be used by the renderer of derived types:

:attributes: (DataStructure) form attributes, will override all automatically rendered ones
:content: (string) form content, supported where needed

The FormComponent does not define any rendering and extended props like `name` or `class`.
It is up to derived prototypes like `Neos.Fusion.Form:Form`_ to implement the renderer.
It is that derived components support all props and render them as attributes.

The `form` context:
```````````````````

The following properties are accessible via `form`:
:form.request: (\Neos\Flow\Mvc\ActionRequest)
:form.data: (array) The data structure the form is bound to.
:form.fieldNamePrefix: (string) The fieldname prefix of the form.
:form.submittedValues: (array) Data structure that contains previously submitted values if errors occurred.
:form.result: (\Neos\Error\Messages\Result) The validation result for the whole form if errors occurred.
:form.errors: (boolean) Return true when the `result` contains errors.

Neos.Fusion.Form:FieldComponent
-------------------------------

The field component is a base prototype for creating input rendering prototypes for a given fieldname.
The prototype populates the `field` context variable and establishes the connection to the parent `form` for
data-binding and error rendering.

:form: (DataStructure) used to populate the `field` context but are not available as `props`
:field.form: (Form, defaults to `form` from fusion-context) The form the field is rendered for. Usually defined by a `Neos.Fusion.Form:FormComponent`_.
:field.name: (string) The fieldname, use square bracket syntax for nested properties.
:field.multiple: (boolean, default = false) Determine wether the field can contain multiple values like checkboxes or selects.
:field.value: (any, default = null) The target value of fields (for checkbox, radio and select.option)

Attributes to be used by the renderer of derived types:

:attributes: (DataStructure) input attributes, will override all automatically rendered ones
:content: (string) field content, supported where needed

The fieldComponent does not define any rendering and extended props like `placeholder` or `class`.
It is up to derived prototypes like `Neos.Fusion.Form:Input`_ to implement the renderer.
It is that derived components support all props and render them as attributes.

The `field` context:
````````````````````
The following properties can be accessed via `form`:
:field.name: (string): The final name for the field with applied `namespacePrefix` from the `form`.
:field.value: (string|array): The value the field currently has from the `form.data` or `from.submittedValues`.
:field.multiple: (boolean): Return true when the field may contain multiple values.
:field.result: (\Neos\Error\Messages\Result) The validation result for field if errors occurred.
:field.errors: (boolean) Return true when the `result` contains errors.

Neos.Fusion.Form:Form
---------------------

The main component for defining forms in afx that extends `Neos.Fusion.Form:FormComponent`_. The prototype is
responsible for rendering the actual form.

In addition the form component will also:
- Render hidden `__referrer` fields for the current and parent request to allow Flow to send the request back in case of validation errors.
- Render hidden `__trustedProperties` fields to enable the Flow property-mapping for the submitted values.
- Render hidden `__csrfToken` for all forms that do not use the method `post`.
- Render hidden `__identity` fields for all fields that are bound to properties of persisted objects.
- Render hidden `empty` fields for `checkbox` and `submit[multiple]` fields make sure unselected values are send to the controller.

:form: (DataStructure) see `Neos.Fusion.Form:FormComponent`_
:attributes: (string), all props are rendered as attributes to the form tag
:content: (string, defaults to '') afx content with the form controls

Example::

    afx`
        <Neos.Fusion.Form:Form form.data.customer={customer} form.data.deliveryAddress={deliveryAddress} form.target.action="submit">
            <Neos.Fusion.Form:FieldContainer field.name="user[firstName]" label="First Name">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:FieldContainer>

            <Neos.Fusion.Form:FieldContainer field.name="user[lastName]" label="Last Name">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:FieldContainer>

            <Neos.Fusion.Form:FieldContainer field.name="deliveryAddress[street]" label="Street">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:FieldContainer>

            <Neos.Fusion.Form:FieldContainer field.name="deliveryAddress[zip]" label="Zip">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:FieldContainer>

            <Neos.Fusion.Form:Submit field.value="submit" />
        </Neos.Fusion.Form:Form>
    `

Neos.Fusion.Form:Input
----------------------

The `Neos.Fusion.Form:Input`_ component extends the `Neos.Fusion.Form:FieldComponent`_ and renders an input-tag.

Neos.Fusion.Form:Textfield
--------------------------

Extends `Neos.Fusion.Form:Input`_ and uses the default type `text`.

Neos.Fusion.Form:Upload
-----------------------

Extends `Neos.Fusion.Form:Input`_ and uses the default type `file`.

Neos.Fusion.Form:Password
-------------------------

Extends `Neos.Fusion.Form:Input`_ and uses the default type `password`.

Neos.Fusion.Form:Hidden
-----------------------

Extends `Neos.Fusion.Form:Input`_ and uses the default type `hidden`.

Neos.Fusion.Form:Submit
-----------------------

Extends `Neos.Fusion.Form:Input`_ and uses the default type `submit`.

Neos.Fusion.Form:Checkbox
-------------------------

Extend `Neos.Fusion.Form:FieldComponent`_ to render an input of type "checkbox".

Neos.Fusion.Form:Radio
----------------------

Extend `Neos.Fusion.Form:FieldComponent`_ to render an input of type "radio".

Neos.Fusion.Form:Textarea
-------------------------

Extend `Neos.Fusion.Form:FieldComponent`_ to render an textarea tag.

Neos.Fusion.Form:Select
-----------------------

A `Neos.Fusion.Form:FieldComponent`_ that renders a select tag. The options are expected as afx `content`.
If the prototype `Neos.Fusion.Form:Select.Option`_ is used for defining the options the selected state is
applied automaticvally by comparing `field.value` with `option.value`.

Neos.Fusion.Form:Select.Option
------------------------------

Render an option tag inside a `Neos.Fusion.Form:Select`_.

:field.value: (mixed) The value the option represents.
:attributes: (string), all props are rendered as attributes to the option tag
:content: (string) The content of the option tag that is displayed as label.

Example::

    renderer = afx`
        <Neos.Fusion.Form:Select field.name="user[gender]">
            <Neos.Fusion.Form:Select.Option field.value="male">Male</Neos.Fusion.Form:Select.Option>
            <Neos.Fusion.Form:Select.Option field.value="female">Female</Neos.Fusion.Form:Select.Option>
            <Neos.Fusion.Form:Select.Option field.value="diverse">Diverse</Neos.Fusion.Form:Select.Option>
        </Neos.Fusion.Form:Select>
    `

Neos.Fusion.Form:Neos.BackendModule.FieldContainer
--------------------------------------------------

For use in Backend Modules a special component is created that renders a label and validation results
for the defined field. The actual input elements are passed as afx-content. The module will also override the `field` of
inner `Neos.Fusion.Form:FieldContainers`_ if they do not have a local `name`.

:field.name: (string) The fieldname. Use square bracket syntax for nested properties.
:field.multiple: (boolean, default = false) Determine wether the field can contain multiple values like checkboxes or selects.

:label: (string) The label for the field, is translated using `translation.label.package` and `translation.label.source`
:translation: (array, default {label: {package: 'Neos.Neos', source: 'Modules'}, error: {package: 'Neos.Flow', source: 'ValidationErrors'}}) the translation sources for rendering the labels and errors
:attributes: (DataStructure) attributes for the container tag
:content: (string) afx content

Example::

    renderer = afx
        <Neos.Fusion.`Form:Neos.BackendModule.FieldContainer field.name="user[firstName]" label="user.firstName">
            <Neos.Fusion.Form:Input />
        </Neos.Fusion.`Form:Neos.BackendModule.FieldContainer>
    `

In some cases multiple inputs are combined in a single FieldContainer::

    renderer = afx
        <Neos.Fusion.Form:Neos.BackendModule.FieldContainer field.name="user[roles]" label="user.role" multiple>
            <label>Restricted Editor <Neos.Fusion.Form:Checkbox field.value="Neos.Neos:RestrictedEditor" /></label>
            <label>Editor <Neos.Fusion.Form:Checkbox field.value="Neos.Neos:Editor" /></label>
            <label>Administrator <Neos.Fusion.Form:Checkbox field.value="Neos.Neos:Administrator" /></label>
        </Neos.Fusion.Form:Neos.BackendModule.FieldContainer>
    `
