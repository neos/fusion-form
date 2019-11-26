.. _'Neos.Fusion.Form':

==========================
Neos.Fusion.Form Reference
==========================

Neos.Fusion.Form:Form
---------------------

The main component for defining forms in afx that extends `Neos.Fusion.Form:Component.Form`_. The prototype is
responsible for rendering the actual form.

In addition the form component will also:
- Render hidden `__referrer` fields for the current and parent request to allow Flow to send the request back in case of validation errors.
- Render hidden `__trustedProperties` fields to enable the Flow property-mapping for the submitted values.
- Render hidden `__csrfToken` for all forms that do not use the method `post`.
- Render hidden `__identity` fields for all fields that are bound to properties of persisted objects.
- Render hidden `empty` fields for `checkbox` and `submit[multiple]` fields make sure unselected values are send to the controller.

:form: (`Neos.Fusion.Form:Definition.Form`_) used to populate the `form` context but is not available via `props`
:form.request: (ActionRequest, defaults to the the current `request`) The data the form is bound to. Can contain objects, scalar and nested values.
:form.namespacePrefix: (string, defaults to `request.getArgumentNamespace()`) The data the form is bound to. Can contain objects, scalar and nested values.
:form.data: (mixed, defaults to `Neos.Fusion:DataStructure`) The data the form is bound to. Can contain objects, scalar and nested values.
:form.target: (string, default to `Neos.Fusion:UriBuilder`) The target uri the form will be sent to.
:form.method:  (string, default to `post`) The form method.
:form.encoding: (string, default to `multipart/form-data` when `form.method` == `post`) The form enctype `multipart/form.data` is required for file-uploads.:attributes: (string), all props are rendered as attributes to the form tag
:attributes: (`Neos.Fusion:DataStructure`_) form attributes, will override all automatically rendered ones
:content: (string, defaults to '') afx content with the form controls

Example::

    afx`
        <Neos.Fusion.Form:Form form.data.customer={customer} form.data.deliveryAddress={deliveryAddress} form.target.action="submit">
            <Neos.Fusion.Form:Component.Field field.name="user[firstName]" label="First Name">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:Component.Field>

            <Neos.Fusion.Form:Component.Field field.name="user[lastName]" label="Last Name">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:Component.Field>

            <Neos.Fusion.Form:Component.Field field.name="deliveryAddress[street]" label="Street">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:Component.Field>

            <Neos.Fusion.Form:Component.Field field.name="deliveryAddress[zip]" label="Zip">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:Component.Field>

            <Neos.Fusion.Form:Submit field.value="submit" />
        </Neos.Fusion.Form:Form>
    `

Neos.Fusion.Form:Fields
=======================

All fields are derived from the abstract prototype `Neos.Fusion.Form:Component.Field`_ which defines the following fusion api:

:field: (`Neos.Fusion.Form:Definition.Field`_) used to populate the `field` context
:field.form: (Form, defaults to `form` from fusion-context) The form the field is rendered for. Usually defined by a `Neos.Fusion.Form:Definition.Form`_.
:field.field: (Field, defaults to `field`) An possible field that may have been predefined in a container. If no name is given the outer field will be reused.
:field.name: (string) The fieldname, use square bracket syntax for nested properties.
:field.multiple: (boolean, default = false) Determine whether the field can contain multiple values like checkboxes or selects.
:field.value: (any, default = null) The target value of fields (for checkbox, radio and button)
:attributes: (`Neos.Fusion:DataStructure`_) input attributes, will override all automatically rendered ones
:content: (string) field content, supported where needed

Neos.Fusion.Form:Input
----------------------

The `Neos.Fusion.Form:Input`_ component extends the `Neos.Fusion.Form:Component.Field`_ and renders an input-tag.

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

Extends `Neos.Fusion.Form:Component.Field`_ to render an input of type "checkbox".

Neos.Fusion.Form:Radio
----------------------

Extends `Neos.Fusion.Form:Component.Field`_ to render an input of type "radio".

Neos.Fusion.Form:Textarea
-------------------------

Extends `Neos.Fusion.Form:Component.Field`_ to render an textarea tag.

Neos.Fusion.Form:Select
-----------------------

Extends `Neos.Fusion.Form:Component.Field`_ and renders a select tag. The options are expected as afx `content`.
If the prototype `Neos.Fusion.Form:Select.Option`_ is used for defining the options the selected state is
applied automatically by comparing the stringified `field.value` with `option.value`.

Neos.Fusion.Form:Select.Option
------------------------------

Render an option tag inside a `Neos.Fusion.Form:Select`_.

:option: (`Neos.Fusion.Form:Definition.Option`_) used to populate the `field` context
:option.value: (any, default = null) The target value
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

For use in Neos Backend Modules a special component is created that renders a label and validation results
for the defined field using the class and html structures for the neos backend. The actual input elements are passed
as afx-content to the container. The container extends `Neos.Fusion.Form:Compnent.Field` which allows to define a
`field` that will be used by all fields inside that do not have another `field.name` defined. The container also adjusts
the rendering of checkboxes and radio inputs to the needs of the Neos backend.

.. note:
  Do not use this container in frontend projects. It will be modified in the future as the Neos backend evolves.
  Instead use this prototype as template to create project specific field-containers.

:field: (`Neos.Fusion.Form:Definition.Field`_) used to populate the `field` context
:field.form: (Form, defaults to `form` from fusion-context) The form the field is rendered for. Usually defined by a `Neos.Fusion.Form:Definition.Form`_.
:field.field: (Field, defaults to `field`) An possible field that may have been predefined in a container. If no name is given the outer field will be reused.
:field.name: (string) The fieldname, use square bracket syntax for nested properties.
:field.multiple: (boolean, default = false) Determine wether the field can contain multiple values like checkboxes or selects.
:field.value: (any, default = null) The target value of fields (for checkbox, radio and button)
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
            <Neos.Fusion.Form:Checkbox field.value="Neos.Neos:RestrictedEditor" >Restricted Editor</Neos.Fusion.Form:Checkbox>
            <Neos.Fusion.Form:Checkbox field.value="Neos.Neos:Editor" >Editor</Neos.Fusion.Form:Checkbox>
            <Neos.Fusion.Form:Checkbox field.value="Neos.Neos:Administrator" >Administrator</Neos.Fusion.Form:Checkbox>
        </Neos.Fusion.Form:Neos.BackendModule.FieldContainer>
    `

Neos.Fusion.Form:Component
==========================

The abstract prototypes in Neos.Fusion.Form:Component instantiate the respective domain object and populate the `form`, `field`
or `option` context. The `renderer` is not defined this is done by derived prototypes in the `Neos.Fusion.Form` or custom namspaces.

Neos.Fusion.Form:Component.Form
-------------------------------

The Form component is a base prototype for rendering forms in afx. The prototype populates the
`form` context variable that is available to all the fusion that is rendered as `content`.

:form: (`Neos.Fusion.Form:Definition.Form`_) used to populate the `form` context but is not available via `props`
:form.request: (ActionRequest, defaults to the the current `request`) The data the form is bound to. Can contain objects, scalar and nested values.
:form.namespacePrefix: (string, defaults to `request.getArgumentNamespace()`) The data the form is bound to. Can contain objects, scalar and nested values.
:form.data: (mixed, defaults to `Neos.Fusion:DataStructure`) The data the form is bound to. Can contain objects, scalar and nested values.
:form.target: (string, default to `Neos.Fusion:UriBuilder`) The target uri the form will be sent to.
:form.method:  (string, default to `post`) The form method.
:form.encoding: (string, default to `multipart/form-data` when `form.method` == `post`) The form enctype `multipart/form.data` is required for file-uploads.
:attributes: (`Neos.Fusion:DataStructure`_) form attributes, will override all automatically rendered ones
:content: (string) form content, supported where needed

The FormComponent does not define any rendering and extended props like `name` or `class`.
It is up to derived prototypes like `Neos.Fusion.Form:Form`_ to implement the renderer.

Neos.Fusion.Form:Component.Field
--------------------------------

The field component is a base prototype for creating input rendering prototypes for a given fieldname.
The prototype populates the `field` context variable and establishes the connection to the parent `form` for
data-binding and error rendering.

:field: (`Neos.Fusion.Form:Definition.Field`_) used to populate the `field` context
:field.form: (Form, defaults to `form` from fusion-context) The form the field is rendered for. Usually defined by a `Neos.Fusion.Form:Definition.Form`_.
:field.field: (Field, defaults to `field`) An possible field that may have been predefined in a container. If no name is given the outer field will be reused.
:field.name: (string) The fieldname, use square bracket syntax for nested properties.
:field.multiple: (boolean, default = false) Determine wether the field can contain multiple values like checkboxes or selects.
:field.value: (any, default = null) The target value of fields (for checkbox, radio and button)
:attributes: (`Neos.Fusion:DataStructure`_) input attributes, will override all automatically rendered ones
:content: (string) field content, supported where needed

Neos.Fusion.Form:Component.Option
---------------------------------

The field component is a base prototype for creating input rendering prototypes for a given fieldname.
The prototype populates the `field` context variable and establishes the connection to the parent `form` for
data-binding and error rendering.

:option: (`Neos.Fusion.Form:Definition.Option`_) used to populate the `field` context
:attributes: (`Neos.Fusion:DataStructure`_) input attributes, will override all automatically rendered ones
:content: (string) field content, supported where needed

Neos.Fusion.Form:Definition
===========================

Neos.Fusion.Form:Definition.Form
--------------------------------

The prototype will instantiate and return a `Neos\Fusion\Form\Domain\Form`_ object which allows to access the
form informations via methods exposed to eel. Usually the regturned object will be put into the `form` context
by the `Neos.Fusion.Form:Component.Form`_ prototype.

:request: (ActionRequest, defaults to the the current `request`) The data the form is bound to. Can contain objects, scalar and nested values.
:namespacePrefix: (string, defaults to `request.getArgumentNamespace()`) The data the form is bound to. Can contain objects, scalar and nested values.
:data: (mixed, defaults to `Neos.Fusion:DataStructure`) The data the form is bound to. Can contain objects, scalar and nested values.
:target: (string, default to `Neos.Fusion:UriBuilder`) The target uri the form will be sent to.
:method:  (string, default to `post`) The form method.
:encoding: (string, default to `multipart/form-data` when `form.method` == `post`) The form enctype `multipart/form.data` is required for file-uploads.

Neos.Fusion.Form:Definition.Field
---------------------------------

The prototype will instantiate and return a `Neos\Fusion\Form\Domain\Field`_ object which allows to access the
field informations via methods exposed to eel. Usually the result will be put into the `field` context by
the `Neos.Fusion.Form:Component.Field`_ prototype.

:form: (Form, defaults to `form` from fusion-context) The form the field is rendered for. Usually defined by a `Neos.Fusion.Form:Definition.Form`_.
:field: (Field, defaults to null) An possible field that may have been predefined in a container. If no name is given the oputer field will be reused.
:name: (string) The fieldname, use square bracket syntax for nested properties.
:multiple: (boolean, default = false) Determine wether the field can contain multiple values like checkboxes or selects.
:value: (any, default = null) The target value of fields (for checkbox, radio and button)

Neos.Fusion.Form:Definition.Option
----------------------------------

The prototype will instantiate and return a `Neos\Fusion\Form\Domain\Option`_ object which allows to access the
option informations via methods exposed to eel. Usually the result will be put into the `option` context by
the `Neos.Fusion.Form:Component.Option`_ prototype.

:value: (any, default = null) The target value
