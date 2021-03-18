# A multi step runtime form with actions  

```
prototype(Form.Test:Content.ExampleForm2) < prototype(Neos.Fusion.Form:Runtime.RuntimeForm) {

    identifier = "testform2"

    data = Neos.Fusion:DataStructure {
        firstName = "foo"
        street = "bar"
    }

    process = Neos.Fusion.Form:Runtime.MultiStepProcess {
        steps {
            first {
                content = afx`
                    <Neos.Fusion.Form:FieldContainer field.name="firstName" label="First Name">
                        <Neos.Fusion.Form:Input @validate />
                    </Neos.Fusion.Form:FieldContainer>
                    <Neos.Fusion.Form:FieldContainer field.name="lastName" label="Last Name">
                        <Neos.Fusion.Form:Input />
                    </Neos.Fusion.Form:FieldContainer>
                `
                schema {
                    firstName = ${Schema.string().isRequired()}
                    lastName = ${Schema.string().isRequired().validator('StringLength', {minimum: 6, maximum: 12})}
                }
            }

            second {
                content = afx`
                    <Neos.Fusion.Form:FieldContainer field.name="street" label="Street">
                        <Neos.Fusion.Form:Input />
                    </Neos.Fusion.Form:FieldContainer>
                    <Neos.Fusion.Form:FieldContainer field.name="city" label="City">
                        <Neos.Fusion.Form:Input />
                    </Neos.Fusion.Form:FieldContainer>
                `
                schema {
                    street = ${Schema.string().isRequird()}
                    city = ${Schema.string().isRequird()}
                }
            }

            third {
                content = afx`
                    <Neos.Fusion.Form:FieldContainer field.name="file" label="File">
                        <Neos.Fusion.Form:Upload />
                    </Neos.Fusion.Form:FieldContainer>
                `
                schema {
                    file = ${Schema.resource().isRequired.validator('Neos\Fusion\Form\Runtime\Validation\Validator\FileTypeValidator', {allowedExtensions:['txt', 'jpg']})}
                }
            }
            confirmation {
                content = afx`
                    <h1>Confirm to submit {data.firstName} {first.data.lastName} from {data.city}, {data.street}</h1>
                `
            }
        }
    }

    action {
        message {
            type = 'Neos.Fusion.Form.Runtime:Message'
            options.message = afx`<h1>Thank you {data.firstName} {data.lastName} from {data.city}, {data.street}</h1>`
        }

        email {
            type = 'Neos.Fusion.Form.Runtime:Email'
            options {
                senderAddress = ${q(node).property('mailFrom')}
                recipientAddress = ${q(node).property('mailTo')}

                subject = ${q(node).property('mailSubject')}
                text = afx`Thank you {data.firstName} {data.lastName} from {data.city}, {data.street}`
                html = afx`<h1>Thank you {data.firstName} {data.lastName}</h1><p>from {data.city}, {data.street}</p>`

                attachments {
                    upload = ${data.file}
                    resource = "resource://Form.Test/Private/Fusion/Test.translation.csv"
                    jsonFile {
                        content = ${Json.stringify(data)}
                        name = 'data.json'
                    }
                }
            }
        }

        log {
            type = 'Neos.Fusion.Form.Runtime:Log'
            options {
              logger = 'systemLogger'
              level = 'info'
              message = 'Form was submitted'
              context = ${data}
            }
        }

        redirect {
            type = 'Neos.Fusion.Form.Runtime:Redirect'
            options.uri = Neos.Neos:NodeUri {
                node = ${q(node).property('thankyou')}
            }
        }
    }
}
```
