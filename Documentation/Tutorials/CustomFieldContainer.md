# Custom Field Container with translated labels and errors

A custom field container is a component that renders label and errors for
a field but expects the field itself as afx content. This pattern allows
to centralize error and label rendering while the field-controls are still 
decided for each field separately.


```
prototype(Vendor.Site:Form.FieldContainer)  < prototype(Neos.Fusion.Form:FieldContainer) {

    # disable the inherited rendering
    renderer > 
    
    # define custom rendering with lanbel translations
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
    # all FieldComponents will render the field.name as id so
    # the label for from the FieldContainer references them correctly 
    #
    prototype(Neos.Fusion.Form:Component.Field) {
        attributes.id = ${field.name}
    }
}
```
