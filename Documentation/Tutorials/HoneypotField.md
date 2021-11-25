# Honeypot fields

Honeypot fields are invisible but may be filled by bots and help to identify spam.
In fusion forms they can be implemented by combining an invisible field with an validator 
that verifies that the field honey was not filled.

```
renderer = Neos.Fusion.Form:Runtime.RuntimeForm {
    process {
        content = afx`
            .... 
            <Neos.Fusion.Form:FieldContainer 
                field.name="honey"  
                attributes.style="display:none; !important"
                attributes.autocomplete="off"
                attributes.tabindex="-1"
                >
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:FieldContainer>
        `
        schema {
            ...
            honey = ${Form.Schema.string().validator('StringLength', {minimum:0, maximum:0})}
        }
    }
}
```

If you want to handle the spam detection silently you may choose to still show the success message
but not trigger the email action. In this case instead of the validator a condition for actions may be used.

```
renderer = Neos.Fusion.Form:Runtime.RuntimeForm {
    process {
        content = afx`
            .... 
            <Neos.Fusion.Form:FieldContainer 
                field.name="honey"  
                attributes.style="display:none; !important"
                attributes.autocomplete="off"
                attributes.tabindex="-1"
                >
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:FieldContainer>
        `
        schema {
            ...
            honey = ${Form.Schema.string()}
        }
    }
    action {
        # the message is always shown
        message {
            type = 'Neos.Fusion.Form.Runtime:Message'
            options.message = ${q(node).property('message')}
        }
        # but the mail is only sent if no honey is found
        email {
            @if.noHoney = ${data.honey ? false : true}
            type = 'Neos.Fusion.Form.Runtime:Email' {    
               ...
            }   
        }    
    }    
}
```
