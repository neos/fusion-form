Fusion Form
-----------

Pure fusion form rendering with afx support! 

!!! ATTENTION all this is WIP and can change at any time !!!

**Fusion prototypes**

Definition Factory Prototypes:

- `Neos.Fusion.Form:FormDefinition `: Factory prototype that creates a formDefinition. Accepts `request` (default `${request}`), `name`, `object` and `fieldnamePrefix`.
- `Neos.Fusion.Form:FieldDefinition `: Factory prototype that creates a fieldDefinition. Accepts `form` (default `${form}`), `name`, `property`, `value`.

**Base protoypes:**

- `Neos.Fusion.Form:Form` Component that instantiates the `form` context which contains `Neos.Fusion:FormDefinition` before 
    props and renderer are evaluated. Then it renders a form-tag with the given `content` and adds the hidden fields for trustedProperties, csrfTokens and referrers.
    
    ```
    request = ${request}
    name = null
    fieldnamePrefix` = null
    object = null
    targetUri = Neos.Fusion:UriBuilder
    method = 'post'
    enctype = null
    ```

- `Neos.Fusion:.Form:Field`: Component that instantiates the `field` context which contains `Neos.Fusion:FieldDefinition` 
    before props and renderer are evaluated. This is the base prototype for implementing custom fields.
    
    ```
    form = ${form} 
    name = null
    value = null
    property = null
    ```
    
- `Neos.Fusion.Form:Fragment`: A Fragment that allows to place afx conditions without extra markup.

**Field Prototypes:**

- `Neos.Fusion.Form:Input`:
- `Neos.Fusion.Form:Hidden`:
- `Neos.Fusion.Form:Textfield`
- `Neos.Fusion.Form:Textarea`
- `Neos.Fusion.Form:Password`
- `Neos.Fusion.Form:Radio`
- `Neos.Fusion.Form:Checkbox`
- `Neos.Fusion.Form:Checkbox.Multiple`
- `Neos.Fusion.Form:Select`
- `Neos.Fusion.Form:Select.Option`
- `Neos.Fusion.Form:Select.Multiple`
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
       targetUri.action="update"
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

           <Neos.Fusion.Form:Select.Multiple property="baz" >
               <Neos.Fusion.Form:Select.Option value="123">-- 123 -- </Neos.Fusion.Form:Select.Option>
               <Neos.Fusion.Form:Select.Option value="455">-- 456 -- </Neos.Fusion.Form:Select.Option>
               <Neos.Fusion.Form:Select.Option value="789">-- 789 -- </Neos.Fusion.Form:Select.Option>
           </Neos.Fusion.Form:Select.Multiple>

           <Neos.Fusion.Form:Submit />
       </fieldset>
   </Neos.Fusion.Form:Form>
`
