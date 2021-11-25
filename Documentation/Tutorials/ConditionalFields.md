# Conditional fields

The data from previous steps can be used to only show field in certain conditions.

```
renderer = Neos.Fusion.Form:Runtime.RuntimeForm {
    process {
        content = afx`
            <Neos.Fusion.Form:FieldContainer 
                @if.hasOtherValue = ${data.otherValue}
                label="Conditional Field" 
                field.name="conditionalField"
            >
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:FieldContainer>
        `
    }
}
```

If a field shall be always present but be required once other fields are filled the schema can be adjusted via @process and @if. 

```
renderer = Neos.Fusion.Form:Runtime.RuntimeForm {
    process {
        schema {
            conditionalField = ${Form.Schema.string()}
            conditionalField.@process.makeRequired = ${value.isRequired()}
            conditionalField.@process.makeRequired.@if.hasOtherValue = ${data.otherValue || request.arguments.otherValue}
        }
    }
}
```

The condition checks the submitted data from the current request `request.arguments.otherField` and the 
successfully submitted data from previus requests `data.otherField`. 
