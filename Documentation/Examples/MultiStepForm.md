# A multi step runtime form with actions  

```
prototype(Vendor.Site:Content.MultiStepFormExample) < prototype(Neos.Fusion.Form:Runtime.RuntimeForm) {

    namespace = "multi_step_form_example"

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
                    firstName = ${Form.Schema.string().isRequired()}
                    lastName = ${Form.Schema.string().isRequired().validator('StringLength', {minimum: 6, maximum: 12})}
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
                    street = ${Form.Schema.string().isRequired()}
                    city = ${Form.Schema.string().isRequired()}
                }
            }

            third {
                content = afx`
                    <Neos.Fusion.Form:FieldContainer field.name="sports" field.multiple label="Sports">
                        <Neos.Fusion.Form:Select>
                            <Neos.Fusion.Form:Select.Option option.value="climbing" />
                            <Neos.Fusion.Form:Select.Option option.value="biking" />
                            <Neos.Fusion.Form:Select.Option option.value="hiking" />
                            <Neos.Fusion.Form:Select.Option option.value="swimming" />
                            <Neos.Fusion.Form:Select.Option option.value="running" />
                        </Neos.Fusion.Form:Select>
                    </Neos.Fusion.Form:FieldContainer>
                    <Neos.Fusion.Form:FieldContainer field.name="file" label="File">
                        <Neos.Fusion.Form:Upload />
                    </Neos.Fusion.Form:FieldContainer>
                `
                schema {
                    sports = ${Form.Schema.arrayOf( Form.Schema.string() ).validator('Count', {minimum: 1, maximum: 2})}
                    file = ${Form.Schema.resource().isRequired().validator('Neos\Fusion\Form\Runtime\Validation\Validator\FileTypeValidator', {allowedExtensions:['txt', 'jpg']})}
                }
            }
            confirmation {
                content = afx`
                    <h1>Confirm to submit {data.firstName} {first.data.lastName} from {data.city}, {data.street}</h1>
                    <Neos.Fusion.Form:FieldContainer field.name="gdprAgreed">
                        <Neos.Fusion.Form:Checkbox field.value="1" >Agree to data storage</Neos.Fusion.Form:Checkbox>
                    </Neos.Fusion.Form:FieldContainer>
                `
                schema {
                    gdprAgreed = ${Form.Schema.boolean().validator('BooleanValue', {expectedValue:true})}
                }
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
