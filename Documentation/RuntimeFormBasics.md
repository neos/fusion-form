# Runtime Fusion Form Basics

For many default cases it is overkill to implement a custom controller. Especially when the forms shall be integrated
as content into a Neos website.  

For such cases the `Runtime` section of the Neos.Fusion.Form package provides means to define the validation rules and 
configure the finishing actions that will be executed once the form is submitted. That way in many cases no custom code 
is needed at all. Runtime Forms also make it simple to implement custom actions for connecting Newsletter registrations 
and other services. 

## Form - `Neos.Fusion.Form:Runtime.RuntimeForm` 

The core of the form runtime is the `Neos.Fusion.Form:Runtime.RuntimeForm` prototype. This prototype allows to define a 
`process` usually of type SingleStepProcess and an `action`. Both a are defined via fusion.  

```
renderer = Neos.Fusion.Form:Runtime.RuntimeForm {   
    # the form process that is responsible for rendering the form and
    # collecting the data
    process = Neos.Fusion.Form:Runtime.SingleStepProcess

    # action that is processed after the form process is finished
    action =  Neos.Fusion.Form:Runtime.Actions    
}
```

The Runtime form also allows to specify an `namespace` that will be used as namespace for all form values and 
the initial `data` for cases in which the process doesn't start empty.  

## Process - `Neos.Fusion.Form:Runtime.SingleStepProcess`

The form process is responsible to aggregate the submitted data and render the form until all requirements are matched.  

To do this the single step process requires `content` and `schema`. The `content` is the form body that will be rendered. 
It can be defined inline via afx or as a separate Fusion prototype. It will internally use the Field Prototypes 
of Neos.Fusion.Form and probably a FieldContainer that renderes labels and error messages.    

The `schema` controls the type conversion and validation of the submitted data. Only properties that have a schema will 
be added to the data of the process so all fields have to be added here.

The separation of `content` and `schema` makes implementing custom rendering easy and as hassle-free as possible.

```
renderer = Neos.Fusion.Form:Runtime.RuntimeForm {
    process = Neos.Fusion.Form:Runtime.SingleStepProcess {
        
        content = afx`
            <Neos.Fusion.Form:FieldContainer field.name="firstName" label="First Name">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:FieldContainer>
            <Neos.Fusion.Form:FieldContainer field.name="lastName" label="Last Name">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:FieldContainer>
        `
        
        schema {
            firstName = ${Form.Schema.string().isRequired()}
            lastName = ${Form.Schema.string().isRequired()}
        }
    }    
```

## Action - `Neos.Fusion.Form:Runtime.Action`

Actions define what has to be done once the process is finished. Multiple actions can be configured as usually multiple
things have to occur once the process is finished. The `type` of each action is declared a className or as an identifier that 
converts to a class name via convention. Each action is configured with the defined `options` which allows to access    
form data, settings and even node properties in a unified way.  

The action types `Message`, `Redirect`, `Email` and `Log` are already implemented in Neos.Fusion.Form package.
  
```
renderer = Neos.Fusion.Form:Runtime.RuntimeForm {
    action {
        message {
            type = 'Neos.Fusion.Form.Runtime:Message'
            options.message = afx`<h1>Thank you {data.firstName} {data.lastName}</h1>`
        }
        email {
            type = 'Neos.Fusion.Form.Runtime:Email'
            options {
                testMode = ${Configuration.setting('Vendor.Site.Form.testMode') ? true : false}
                senderAddress = ${q(node).property('mailFrom')}
                recipientAddress = ${q(node).property('mailTo')}
                subject = ${q(node).property('mailSubject')}
                text = afx`Thank you {data.firstName} {data.lastName}`
                html = afx`<h1>Thank you {data.firstName} {data.lastName}</h1>`
            }
        }
    }
}
```
