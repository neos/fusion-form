Fusion Form
===========

Pure fusion form rendering with afx support!

Development targets 
-------------------

The main target for the development of the fusion form package is to make 
rendering of forms with data binding and error rendering possible in pure 
fusion with afx. 

The rendering of forms should be extensible by new control types and 
the generated markup should reusable and allow full control via fusion.

The hidden fields Neos.Flow needs to perform validation, security and 
persistence magic have to be generated.

Unneeded limitation of the previous fluid forms like binding to a single 
object should be removed.

**Important Deviations from Fluid Form ViewHelpers**

The following deviations are probably the ones fluid developers will 
stumble over. There are many more deviations but those are breaking 
concept changes you should be aware of.

- Instead of binding a single `object` a `data` DataStructure is bound to the form.
- The fields use `name` to establish the reference to data and validation instead of `property`.
- Select options are defined as afx children and not `options`.

Fusion prototypes
-----------------

The full fusion documentation can be found [here](Documentation/FusionForm.rst)

Usage
-----

**Frontend**

This example defines a ShipmentForm component that accepts the props `customer`,
`shipment` and `targetAction`. The form content is defined as afx. The form data is defined 
with both values `customer` and `shipment` that are both modified and sent to the target
controller.

```
prototype(Form.Test:Component.ShipmentForm) < prototype(Neos.Fusion:Component) {
    customer = null
    shipment = null
    targetAction = null
    
    renderer = afx`
        <Neos.Fusion.Form:Form data.customer={props.customer} data.shipment={props.shipment} actionUri.action={props.targetAction} >

            <label for="firstName" >First Name</label>	
            <Neos.Fusion.Form:Input id="firstName" name="customer[lastName]" />
    
            <label for="lastName">Last Name</label>
            <Neos.Fusion.Form:Input id="lastName" name="customer[lastName]" />
    
            <label>Shipment method</label>
            <label><Neos.Fusion.Form:Radio name="shipment[method]" value="ups" />UPS</label>
            <label><Neos.Fusion.Form:Radio name="shipment[method]" value="dhl" />DHL</label>
            <label><Neos.Fusion.Form:Radio name="shipment[method]" value="pickup" />Pickup</label>
    
            <label for="street">Street</label>
            <Neos.Fusion.Form:Input id="street" name="customer[street]" />
            
            <label for="city">City</label>
            <Neos.Fusion.Form:Input id="city" name="customer[city]" />
    
            <label for="country" >Country</label>
            <Neos.Fusion.Form:Select id=country name="shipment[country]">
                <Neos.Fusion.Form:Select.Option value="de" >Germany</Neos.Fusion.Form:Select.Option>
                <Neos.Fusion.Form:Select.Option value="at" >Austria</Neos.Fusion.Form:Select.Option>
                <Neos.Fusion.Form:Select.Option value="ch" > Switzerland </Neos.Fusion.Form:Select.Option>
            </Neos.Fusion.Form:Select>    
                
            <Neos.Fusion.Form:Button>Submit Order</Neos.Fusion.Form:Button>

        </Neos.Fusion.Form:Form>
    `
}
```

**Backend Module**

Forms for backend modules basically work the same as in the frontend 
but the additional prototyoe `Neos.Fusion.Form:Neos.BackendModule.FieldContainer` 
can be used to render fields with translated labels and error messages
using the dafault markup of the neos backend.

HINT: To render a backend module with fusion you have to set the 
`defaultViwObjectName` to the `Neos\Fusion\View\FusionView::class` in the
controller class and be aware that you have to include all required fusion
explicitly.

ATTENTION: This prototype is not meant to be used in the frontend. Create 
project specific field containers instead.

```
#
# In backend modules all includes have to be done explicitly
# the default fusion from Neos.Fusion and Neos.Fusion.Form 
# is needed for rendering of basic forms
#
include: resource://Neos.Fusion/Private/Fusion/Root.fusion
include: resource://Neos.Fusion.Form/Private/Fusion/Root.fusion

#
# By default the fusion view creates the render pathes from the
# current package controller and action. 
#
Form.Test.FusionController.index = Form.Test:Backend.UserForm
Form.Test.FusionController.updateUser = Form.Test:Backend.UserForm

#
# The rendering of the form is centralizes in a single prototype 
# that expects the values `title`, `user` and `targetAction` in the context
#
prototype(Form.Test:Backend.UserForm) < prototype(Neos.Fusion:Component) {

    renderer = afx`
        <h2>{title}</h2>

        <Neos.Fusion.Form:Form data.user={user} actionUri.action={targetAction} >

            <Neos.Fusion.Form:Neos.BackendModule.FieldContainer name="user[firstName]" label="user.firstName">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:Neos.BackendModule.FieldContainer>

            <Neos.Fusion.Form:Neos.BackendModule.FieldContainer name="user[firstName]" label="user.lastName">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:Neos.BackendModule.FieldContainer>

            <Neos.Fusion.Form:Neos.BackendModule.FieldContainer name="user[roles]" label="user.role" multiple>
                <label>Restricted Editor <Neos.Fusion.Form:Checkbox value="Neos.Neos:RestrictedEditor" /></label>
                <label>Editor <Neos.Fusion.Form:Checkbox value="Neos.Neos:Editor" /></label>
                <label>Administrator <Neos.Fusion.Form:Checkbox value="Neos.Neos:Administrator" /></label>
            </Neos.Fusion.Form:Neos.BackendModule.FieldContainer>

            <Neos.Fusion.Form:Neos.BackendModule.FieldContainer name="user[language]" label="user.language" >
                <Neos.Fusion.Form:Select>
                    <Neos.Fusion.Form:Select.Option value="en" >Englisch</Neos.Fusion.Form:Select.Option>
                    <Neos.Fusion.Form:Select.Option value="de" >Deutsch</Neos.Fusion.Form:Select.Option>
                    <Neos.Fusion.Form:Select.Option value="ru" >Russian</Neos.Fusion.Form:Select.Option>
                    <Neos.Fusion.Form:Select.Option value="kg" >Klingon</Neos.Fusion.Form:Select.Option>
                </Neos.Fusion.Form:Select>
            </Neos.Fusion.Form:Neos.BackendModule.FieldContainer>

            <Neos.Fusion.Form:Button>submit</Neos.Fusion.Form:Button>

        </Neos.Fusion.Form:Form>
    `
}
```

Extending Neos.Fusion-Form:
---------------------------

**Custom Form Fields**

The most obvious extension point is the definition of custom fieldtypes.
To do so you have to extend the `Neos.Fusion.Form:FieldContainer` prototype
and implement the renderer you need. 

For the rendering you have access to the `field` in the fusion context which 
allows you to get the current `value`. You should use this value to 
access bound data and values that were already submitted. The `field.value`
hast to be stringified for the html rendering as the bound data may be of any
type. 

```
prototype(Neos.Fusion.Form:Textarea)  < prototype(Neos.Fusion.Form:FieldComponent) {
    content = ''

    renderer = afx`
        <textarea
            id={props.id}
            class={props.class}
            type="text"
            name={field.name}
            required={props.required}
            {...props.attributes}
        >
            {Form.stringifyValue(field.value || props.content)}
        </textarea>
    `
}
```

**Custom Container with translated labels and errors**

A custom field container is a component that renders label and errors for
a field but expects the field itself as afx content. This pattern allows
to centralize error and label rendering while the field-controls are still 
decided for each field separately.

```
prototype(Vendor.Site:Form.FieldContainer)  < prototype(Neos.Fusion:FieldComponent) {

    name = null
    multiple = false
    label = null
    content = null

    renderer = afx`
        <div class={field.errors ? "error"}>
            <label for={field.name} @if.has={props.label}>
                {I18n.translate(props.label, props.label, [], 'Main', 'Vendor.Site')}
            </label>
            
           {props.content}
            
            <ul @if.hasErrors={field.errors} class="errors">
                <Neos.Fusion:Loop items={field.result.flattenedErrors} itemName="errors" >
                    <Neos.Fusion:Loop items={errors} itemName="error" >
                        <li>
                            {I18n.translate(error.code, error, [], 'ValidationErrors', 'Vendor.Site')}
                        </li>
                    </Neos.Fusion:Loop>
                </Neos.Fusion:Loop>
            </ul>
        </div>
    `

    #
    # FieldComponent that are rendered inside field container are adjusted
    # if no specific name is given the field from the container is reused
    # if nop specific id is given the name from the comntainer is used
    #
    prototype(Neos.Fusion.Form:FieldComponent) {
        field = ${this.name ? Form.createField(form, this.name, this.multiple) : field}
        id = ${field.name}
    }
}
```

Using such components is done similar to the `Neos.Fusion.Form:Neos.BackendModule.FieldContainer`

```
prototype(Vendor.Site:Form.FieldContainer)  < prototype(Neos.Fusion:FieldComponent) {
    renderer = afx`

        <Neos.Fusion.Form:Form>
     
            <Vendor.Site:Form.FieldContainer name="user[firstName]" label="user.firstName">
                <Neos.Fusion.Form:Input />
            </Vendor.Site:Form.FieldContainer>

            <Vendor.Site:Form.FieldContainer name="user[firstName]" label="user.lastName">
                <Neos.Fusion.Form:Input />
            </Vendor.Site:Form.FieldContainer>

            <Vendor.Site:Form.FieldContainer name="user[roles]" label="user.role" multiple>
                <label>Restricted Editor <Neos.Fusion.Form:Checkbox value="Neos.Neos:RestrictedEditor" /></label>
                <label>Editor <Neos.Fusion.Form:Checkbox value="Neos.Neos:Editor" /></label>
                <label>Administrator <Neos.Fusion.Form:Checkbox value="Neos.Neos:Administrator" /></label>
            </Vendor.Site:Form.FieldContainer>
        </Neos.Fusion.Form:Form>
    `
}                       
```
