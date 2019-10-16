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

- `Neos.Fusion.Form:Form`: The main form container.
- `Neos.Fusion:.Form:FieldComponent`: Component to implement field controls in fusion.

**Field Prototypes: based on Neos.Fusion.Form:FieldComponent**

- `Neos.Fusion.Form:Input`
- `Neos.Fusion.Form:Hidden`
- `Neos.Fusion.Form:Textfield`
- `Neos.Fusion.Form:Textarea` option `content` for rendering content via afx
- `Neos.Fusion.Form:Password`
- `Neos.Fusion.Form:Radio` additional option `checked`:bool
- `Neos.Fusion.Form:Checkbox` additional option `checked`:bool
- `Neos.Fusion.Form:Select` additional option `content` for rendering of options via afx
- `Neos.Fusion.Form:Select.Option` options `value`:any, `selected`:bool
- `Neos.Fusion.Form:Upload`
- `Neos.Fusion.Form:Button`
- `Neos.Fusion.Form:Submit`

**Backend prototypes**

- `Neos.Fusion.Form:Neos.BackendModule.FieldContainer` : A container with label and error rendering to be used in neos backend modules

Form Eel-Helper
---------------

The package contains an eel helper that is used to instantiate the `@context.form` 
and `@context.field` domain objects for fusion. 

- `Form.createForm(request, string fieldnamePrefix, mixed data)` create a \Neos\Fusion\Form\Domain\Model\Form object
- `Form.createField(form, string name, bool multiple = false)` create a \Neos\Fusion\Form\Domain\Model\Field object
- `Form.calculateHiddenFields(form, string content)` returns an key-value array for the referrer, trustedProperties hidden fields based of the given form and content 
- `Form.stringifyValue(value)` Convert the value to string, entities are converted to the identifier. 
- `Form.stringifyArray(array)` Convert an array of values to an array of stringified values using `stringifyValue` 

Usage
-----

**Frontend**

To render forms in the frontend a prototype is used that accepts the props
`customer`, `shipment` an `targetAction`.

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

In backend modules the `Neos.Fusion.Form:Neos.BackendModule.FieldContainer` 
prototype is used to render fields with labels and error messages.

HINT: To render a backend module with fusion you have to set the 
`defaultViwObjectName` to the `Neos\Fusion\View\FusionView::class` in the
controller class.

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
# The rendereing of the form is centralizes in a single prototype 
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

