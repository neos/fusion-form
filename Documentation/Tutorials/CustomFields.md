# Implementing custom Form Fields

The most obvious extension point is the definition of custom fieldtypes.
To do so you have to extend the `Neos.Fusion.Form:Component.Field` prototype
and implement the renderer you need. 

For the rendering you have access to the `field` in the fusion context gives
you access to the current value from data binding and the target value.

HINT: By default all field components support setting `attributes` which
are expected to override all autmatically assigned attributes and whenever 
it makes sense also `content` which is usually defined via afx.  

```
prototypeVendor.Site:Form.Textarea)  < prototype(Neos.Fusion.Form:Component.Field) {
    renderer = afx`
        <textarea
            name={field.name}
            {...props.attributes}
        >
            {String.htmlspecialchars(field.getCurrentValueStringified() || props.content)}
        </textarea>
    `
}
```

### Implementing a custom DatetimeLocal field

Sometimes custom fieldtypes are needed or to implement a different value conversion
to the html form than the default field offer.

This example shows a datetime-local field that implements a custom stringification 
for DateTime and integer values.  

```
prototype(Vendor.Site:Form.DatetimeLocal) < prototype(Neos.Fusion.Form:Component.Field) {

    # 
    # Since we want calculate the value via fusion but want to avoid 
    # making value an api a wrapper component is used  
    #
    renderer = Neos.Fusion:Component {

        # the `field` provides the name
        name = ${field.getName()}
        
        #
        # the value is fetched from the `field` with fallback to target value
        #
        value = ${field.getCurrentValue() || field.getTargetValue()}
        
        #
        # the value might be an object so we have to process it to a string for html
        #
        value.@process.formatDatime = Neos.Fusion:Case {
            isDateTime {
                condition = ${(Type.getType(value) == 'object') && Type.instance(value , '\DateTime') }
                renderer = ${Date.format(value, 'Y-m-d\TH:i')}
            }
            isInteger {
                condition = ${(Type.getType(value) == 'integer')}
                renderer = ${Date.format(Date.create('@' + value), 'Y-m-d\TH:i')}
            }
            default {
                condition = true
                renderer = ${field.getCurrentValueStringified() || field.getTargetValueStringified()}
            }
        }
        
        #
        # attributes are passed down 
        #
        attributes = ${props.attributes}


        #
        # the actual markup
        #
        renderer = afx`
            <input
                    type="datetime-local"
                    name={props.name}
                    value={props.value}
                    {...props.attributes}
            />
        `
    }
} 
```
