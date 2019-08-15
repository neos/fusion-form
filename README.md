Fusion Form
-----------

Pure fusion form rendering with afx support! 

!!! ATTENTION all this is WIP and can change at any time !!!

**Fusion prototypes**

- `Neos.Fusion.Form:Form` Component that instantiates the `form` context which contains `Neos.Fusion:FormDefinition` before 
    props and renderer are evaluated. Then it renders a form-tag with the given `content` and adds the hidden fields for trustedProperties, csrfTokens and referrers.
    
    request = ${request}
    name = null
    fieldnamePrefix` = null
    object = null
    action = Neos.Fusion:UriBuilder
    method = 'post'
    enctype = null
    ```

- `Neos.Fusion:.Form:Field`: Component that instantiates the `field` context which contains `Neos.Fusion:FieldDefinition` 
    before props and renderer are evaluated. This is the base prototype for implementing custom fields.
    
    ```
    form = ${form} 
    id = null
    class = null
    attributes = Neos.Fusion:DataStructure
    name = null
    value = null
    required = false
    property = null
    ```
    
- `Neos.Fusion.Form:Fragment`: A Fragment that allows to place afx conditions without extra markup.

**Field Prototypes:**

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
       object={example}
       name="example"
       method="post"
       attributes.data-foo="foo"
   >
       <fieldset>
           <Neos.Fusion.Form:Textfield property="foo" />

           <Neos.Fusion.Form:Select property="bar" >
               <Neos.Fusion.Form:Select.Option value="123" >-- 123 -- </Neos.Fusion.Form:Select.Option>
               <Neos.Fusion.Form:Select.Option value="455" >-- 456 -- </Neos.Fusion.Form:Select.Option>
           </Neos.Fusion.Form:Select>

           <Neos.Fusion.Form:Select multiple property="baz" >
               <Neos.Fusion.Form:Select.Option value="123">-- 123 -- </Neos.Fusion.Form:Select.Option>
               <Neos.Fusion.Form:Select.Option value="455">-- 456 -- </Neos.Fusion.Form:Select.Option>
               <Neos.Fusion.Form:Select.Option value="789">-- 789 -- </Neos.Fusion.Form:Select.Option>
           </Neos.Fusion.Form:Select>

           <Neos.Fusion.Form:Submit />
       </fieldset>
   </Neos.Fusion.Form:Form>
`
