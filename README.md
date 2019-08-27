Fusion Form
-----------

Pure fusion form rendering with afx support! 

!!! ATTENTION all this is WIP and can change at any time !!!

**Fusion prototypes**

- `Neos.Fusion.Form:Form` Component that instantiates the `form` context which contains `Neos.Fusion:FormDefinition` before 
    props and renderer are evaluated. Then it renders a form-tag with the given `content` and adds the hidden fields for trustedProperties, csrfTokens and referrers.
    
    ```
    request = ${request}
    fieldnamePrefix = null
    data = Neos.Fusion:DataStructure {
        example = ${example}
    }
    
    action = Neos.Fusion:UriBuilder
    method = 'post'
    enctype = null
    ```    

- `Neos.Fusion:.Form:FieldComponent`: Component that instantiates the `field` context which contains `Neos.Fusion:FieldDefinition` 
    before props and renderer are evaluated. This is the base prototype for implementing custom fields. If no property is defined 
    the component uses the existing `field` from the context.
    
    ```
    form = ${form} 
    name = null
    property = ${Form.fieldNameToPath(this.name)}
    ```
    
- `Neos.Fusion:.Form:FieldContainer`: Component that instantiates the `field` context which contains `Neos.Fusion:FieldDefinition` 
    before props and renderer are evaluated. This component will render a container tag a label a list of error messages. The concrete 
    fields are passed to the component as afx content and will use the `field` provided by this container.
    
    ```
    form = ${form}  
    name = null
    property = ${this.name ? Form.fieldNameToPath(this.name) : null}
    
    label = null
    errorClass = 'error'  
    ```

- `Neos.Fusion.Form:Fragment`: A Fragment that allows to place afx conditions without extra markup.

**Field Prototypes: based on Neos.Fusion.Form:FieldComponent**

All field types allow to define `id`, `class` `name`, `property`, `value` and `attributes`. 

- `Neos.Fusion.Form:Input`
- `Neos.Fusion.Form:Hidden`
- `Neos.Fusion.Form:Textfield`
- `Neos.Fusion.Form:Textarea`
- `Neos.Fusion.Form:Password`
- `Neos.Fusion.Form:Radio` additional options `checked`:bool
- `Neos.Fusion.Form:Checkbox` additional options `multiple`:bool and `checked`:bool
- `Neos.Fusion.Form:Select` additional options `multiple`:bool and `content` for rendering Options via afx
- `Neos.Fusion.Form:Select.Option` options: `value`, `selected`:bool and `content`
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
       
       method="post"
       attributes.data-foo="foo"
   >
       <fieldset>
           <Neos.Fusion.Form:Textfield property="exampleValue" />

           <Neos.Fusion.Form:Select property="exampleObject.bar" >
               <Neos.Fusion.Form:Select.Option value="123" >-- 123 -- </Neos.Fusion.Form:Select.Option>
               <Neos.Fusion.Form:Select.Option value="455" >-- 456 -- </Neos.Fusion.Form:Select.Option>
           </Neos.Fusion.Form:Select>

           <Neos.Fusion.Form:Select multiple property="exampleObject.baz" >
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
