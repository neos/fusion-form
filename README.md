Fusion Form
-----------

Pure fusion form rendering with afx support!

**Development targets** 

The main target for the development of the fusion form package is to make rendering
of forms with data binding and error rendering easyly possible in pure fusion+afx and
provide the hidden fields flow needs to perform validation, security and persistence magic.

We also want to make it simple to define custom form controls for your project and
implement your own label and error rendering. 

**Important Deviations from Fluid Form ViewHelpers**

The following deviations are probably the ones fluid developers will stumble 
over. There are many more deviations but those are breaking concept changes you
should be aware of.

- Instead of binding a `object` a `data` DataStructure is bound to the form.
- The fields use `name` to establish the reference to data and validation instead of `property`.
- Select options are defined as afx children and not `options`.

**Fusion prototypes**

The full fusion documentation can be found [here](Documentation/FusionForm.rst)

- `Neos.Fusion.Form:Form`: The main form container.
- `Neos.Fusion:.Form:FieldComponent`: Component to implement field controls in fusion.
- `Neos.Fusion:.Form:FieldContainer`: A field wrapper with label and error rendering that accepts fields as `content`.

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

**Form Eel-Helper:**

- `Form.prefixFieldName(string $fieldName, string $fieldNamePrefix = null)`
- `Form.csrfToken(): string`
- `Form.trustedPropertiesToken(string $content, string $fieldNamePrefix = '')`
- `Form.argumentsWithHmac(array $arguments = [], string $excludeNamespace = '')`

**Example how to use this**
```
include: resource://Neos.Fusion/Private/Fusion/Root.fusion
include: resource://Neos.Fusion.Form/Private/Fusion/Root.fusion

test = afx`
   <Neos.Fusion.Form:Form
       action.action="update"
       action.package="Vendor.Site"
       action.controller="Search"
       
       data.exampleValue={exampleValue}
       data.exampleObject={exampleObject}
       >
       <fieldset>
           <Neos.Fusion.Form:Textfield name="exampleValue" />

           <Neos.Fusion.Form:Select name="exampleObject[bar]" >
               <Neos.Fusion.Form:Select.Option value="123" >-- 123 -- </Neos.Fusion.Form:Select.Option>
               <Neos.Fusion.Form:Select.Option value="455" >-- 456 -- </Neos.Fusion.Form:Select.Option>
           </Neos.Fusion.Form:Select>

           <Neos.Fusion.Form:Select multiple name="exampleObject[baz]" >
               <Neos.Fusion.Form:Select.Option value="123">-- 123 -- </Neos.Fusion.Form:Select.Option>
               <Neos.Fusion.Form:Select.Option value="455">-- 456 -- </Neos.Fusion.Form:Select.Option>
               <Neos.Fusion.Form:Select.Option value="789">-- 789 -- </Neos.Fusion.Form:Select.Option>
           </Neos.Fusion.Form:Select>

           <Neos.Fusion.Form:Submit />
       </fieldset>
   </Neos.Fusion.Form:Form>
`
```

**Render Errors for the whole form**

```
test = afx`
    <Neos.Fusion.Form:Form> 
        <ul @if.hasErrors={form.mappingResults.flattenedErrors}>
            <Neos.Fusion:Loop items={form.mappingResults.flattenedErrors} itemKey="path" itemName="errors" >
                <Neos.Fusion:Loop items={errors} itemName="error" >
                    <li>{path} {error}</li>
                </Neos.Fusion:Loop>
            </Neos.Fusion:Loop>
        </ul>
    </Neos.Fusion.Form:Form>
```

**Custom Field Prototype with label, errorClass and error rendering**

```
prototype(Test.BeModule:ExampleFieldWithLabel) < prototype(Neos.Fusion.Form:Field) {

    content = ''
    label = ''
    errorClass = 'error'

    renderer = afx`
        <div class={field.validationResult.flattenedErrors ? props.errorClass : null}>
            <label @if.has={props.label}>{props.label}</label>
            {props.content}
            <ul @if.hasErrors={field.validationResult.flattenedErrors}>
                <Neos.Fusion:Loop items={field.validationResult.flattenedErrors} itemName="errors" >
                    <Neos.Fusion:Loop items={errors} itemName="error" >
                    <li>{error}</li>
                    </Neos.Fusion:Loop>
                </Neos.Fusion:Loop>
            </ul>
        </div>
    `
}
```
