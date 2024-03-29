.. _'Neos.Fusion.Form:Runtime':

==================================
Neos.Fusion.Form:Runtime Reference
==================================

The runtime part of the Neos.Fusion.Form package provides means to easily connect single or multistep forms
with validation and actions that handle the submitted data without implementing custom controllers.

Neos.Fusion.Form:Runtime.RuntimeForm
------------------------------------

The runtime form prototype will pass the submitted data from `namespace` to the `process` until
this is finished. Afterwards the resulting `data` is passed to the `action`. Inside the runtime form
the fusion `request` is replaced by a subrequest for the form `namespace`.

The algorithm is as follows:

1. Create a subRequest in form namespace and pass all submitted values that have trustedProperties.
2. Push the formRequest as `request` to the fusion context.
3. Let the `proccess` handle the created subrequest.
4. If the `process` is not finished render form tag and delegate the body rendering to the `process`.
5. If the `process` is finished execute the `action` and pass the data.
6. Restore `request` to global request again

:namespace: (`string`), Form argument namespace. If no namespace is defined the hash of the current fusion path is used.
:data: (`mixed`, defaults to `Neos.Fusion:DataStructure`_) The initial data that is given to the form process as input value.
:process: (`ProcessInterface`, defaults to `Neos.Fusion.Form:Runtime.SingleStepProcess`_) The process will handle each request until it declares itself finished.
:action: (`ActionInterface`, defaults to `Neos.Fusion.Form:Runtime.ActionCollection`_) The action that is executed after the process is finished.
:attributes: (`Neos.Fusion:DataStructure`_) form attributes, will override all automatically rendered ones

.. note:: While the `process` renders and the `action` is executed the current `data` is always in the Fusion context and can be used by Fusion.

Example::

    renderer = Neos.Fusion.Form:Runtime.RuntimeForm {
        # the form argument namespace
        namespace = 'example'

        # initial form data to prefill the fields
        data = Neos.Fusion:DataStructure

        # the form process that is responsible for rendering the form and
        # collecting the data
        process = Neos.Fusion.Form:Runtime.SingleStepProcess

        # action that is processed after the form process is finished
        action =  Neos.Fusion.Form:Runtime.Actions
    }

Neos.Fusion.Form:Runtime.SingleStepProcess
------------------------------------------

The most common and simple form process consists of a single step. The actual form is defined as `content`. The `schema`
is responsible for converting and validating the data.

.. note:: Only values that are backed by a schema are added to the form data.

:content: (`string`) The form body to be rendered.
:schema: (`SchemaInterface`, defaults to `Neos.Fusion.Form:Runtime.SchemaCollection`_) The schema to convert and validate the submitted data with.
:header: (`string`) The form header is rendered before the body. By default this is empty, create derived prototypes to change this.
:footer: (`string`, defaults to a single submit button) The form footer contains a single submit button by default, create derived prototypes to change this.

Example::

    prototype(Form.Test:Content.ExampleForm) < prototype(Neos.Fusion.Form:Runtime.RuntimeForm) {

        namespace = "singleStepExample"

        process {
            content = afx`
                <Neos.Fusion.Form:FieldContainer field.name="firstName" label="First Name">
                    <Neos.Fusion.Form:Input />
                </Neos.Fusion.Form:FieldContainer>
                <Neos.Fusion.Form:FieldContainer field.name="lastName" label="Last Name">
                    <Neos.Fusion.Form:Input />
                </Neos.Fusion.Form:FieldContainer>
                <Neos.Fusion.Form:FieldContainer field.name="file" label="File">
                    <Neos.Fusion.Form:Upload />
                </Neos.Fusion.Form:FieldContainer>
            `
            schema {
                firstName = ${Form.Schema.string().isRequired()}
                lastName = ${Form.Schema.string().isRequired().validator('StringLength', {minimum: 6, maximum: 12})}
                file = ${Form.Schema.resource().isRequired().validator('Neos\Fusion\Form\Runtime\Validation\Validator\FileTypeValidator', {allowedExtensions:['txt', 'jpg']})}
            }
        }

        action {
            message {
                type = 'Neos.Fusion.Form.Runtime:Message'
                options {
                    message = afx`<h1>Thank you {data.firstName} {data.lastName}</h1>`
                }
            }
            email {
                type = 'Neos.Fusion.Form.Runtime:Email'
                options {
                    senderAddress = ${q(node).property('mailFrom')}
                    recipientAddress = ${q(node).property('mailTo')}
                    subject = ${q(node).property('mailSubject')}
                    text = afx`Thank you {data.firstName} {data.lastName}`
                    html = afx`<h1>Thank you {data.firstName} {data.lastName}</h1>`
                    attachments {
                        upload = ${data.file}
                    }
                }
            }
        }
    }

Neos.Fusion.Form:Runtime.MultiStepProcess
-----------------------------------------

The multistep process allows to use multiple `steps` that are of type `SingleStepProcess`. The multistep process
persists the current form state as hidden field and otherwise passes the rendering of the form-body to the currently active
sub process. A multistep process is considered to be finished once all steps were successfully submitted.

:steps: (`ProcessCollectionInterface`, defaults to `Neos.Fusion.Form:Runtime.ProcessCollection`_)
:header: (`string`) The form header is rendered before the body. By default this is empty, create derived prototypes to change this.
:footer: (`string`, defaults to Next/Back and Submit buttons) The form footer contains a pre/next/submit button by default, create derived prototypes to change this.

During rendering a `process` variable is added to the context that contains the following information:

:process.state: (`string|null`) Serialized and signed form state, if a previous state is present
:process.current: (`string`) Current subprocess key
:process.prev: (`string|null`) Previous subprocess key
:process.next: (`string|null`) Next subprocess key
:process.all: (`array`) List of all subprocess keys
:process.submitted: (`array`) List of all already submitted subprocess keys
:process.isFirst: (`boolean`) True if the current subprocess is the first one
:process.isLast: (`boolean`) True if the current subprocess is the last one

.. note:: Inside the MultiStepProcess the header and footer of the SingleStepProcess used as subprocess elements are suppressed.

Example::

    prototype(Form.Test:Content.ExampleForm2) < prototype(Neos.Fusion.Form:Runtime.RuntimeForm) {

        namespace = "multiStepExample"

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
                        <Neos.Fusion.Form:FieldContainer field.name="file" label="File">
                            <Neos.Fusion.Form:Upload />
                        </Neos.Fusion.Form:FieldContainer>
                    `
                    schema {
                        file = ${Form.Schema.resource().isRequired().validator('Neos\Fusion\Form\Runtime\Validation\Validator\FileTypeValidator', {allowedExtensions:['txt', 'jpg']})}
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
                    }
                }
            }

            redirect {
                type = 'Neos.Fusion.Form.Runtime:Redirect'
                options {
                    uri = Neos.Neos:NodeUri {
                        node = ${q(node).property('thankyou')}
                    }
                }
            }
        }
    }



Neos.Fusion.Form:Runtime.ActionCollection
-----------------------------------------

The `ActionCollection` implements the ActionInterface. It will execute all subactions
and merge the results into a single response that is returned to the process.

:[key]: (`ActionInterface`, defaults to `Neos.Fusion.Form:Runtime.Action`_)

.. note:: When the items are evaluated it is checked that all items satisfy the ActionInterface.
If untyped items are found they are evaluated as `Neos.Fusion.Form:Runtime.Action`.

Neos.Fusion.Form:Runtime.Action
-------------------------------

The `Action` implements the `ActionInterface` and allows to connect a php class that has to implement the ActionInterface to a form.
The form package already comes with the following action types `Email`, `Log`, `Message` and `Redirect`.

Example::

    messageAction = Neos.Fusion.Form:Runtime.Action {
        type = 'Neos.Fusion.Form.Runtime:Message'
        message = afx`<h1>Thank you {data.firstName} {data.lastName}</h1>`
    }

:type: (`string`) Type to be used by the Action resolver to determine the implementation class. Can be an Identifier or a ClassName.
:options: (`array` defaults to `Neos.Fusion:DataStructure`) The options that are set on ConfigurableActions

Neos.Fusion.Form:Runtime.SchemaCollection
-----------------------------------------

The `SchemaCollection` implements the `SchemaInterface` for an array of multiple named properties.
It will execute all subschemas that are defined for each subkey and merge the results into one.
The subschemas can be created with the Eel `Schema.forType(...)` helper or the `Neos.Fusion.Form:Runtime.Schema`
prototype.

Example::

    schema = Neos.Fusion.Form:Runtime.SchemaCollection {
        firstName = ${Form.Schema.forType("string").validator('NotEmpty')}
        lastName = ${Form.Schema.string().isRequired().validator('StringLength', {minimum: 10, maximum: 40})}
    }

:[key]: (`SchemaInterface`, defaults to `Neos.Fusion.Form:Runtime.Schema`_)

.. note:: When the items are evaluated it is checked that all items satisfy the `SchemaInterface`.
If untyped items are found they are evaluated as `Neos.Fusion.Form:Runtime.Schema`.


Neos.Fusion.Form:Runtime.Schema
-------------------------------

The `Schema` implements the `SchemaInterface` and allows to define a target type and validators for a property.
The `type` property identifies the the target type for the property mapping. The key `validator` allows to define
one or more validators.

Example::

    firstName = Neos.Fusion.Form:Runtime.Schema {
        type = "string"
        validator.notEmpty.type = "NotEmpty"
        validator.stringLength.type = "NotEmpty"
        validator.stringLength.options.minimum = 10
        validator.stringLength.options.maximum = 40
    }

    file = Neos.Fusion.Form:Runtime.Schema {
        type = "Neos\Flow\ResourceManagement\PersistentResource"
        validator.file.type = 'Neos\Fusion\Form\Runtime\Validation\Validator\FileTypeValidator'
        validator.file.options.allowedExtensions:['txt', 'jpg']
    }

    date {
        type = "DateTime"
        typeConverterOptions.datetime {
            class = "Neos\\Flow\\Property\\TypeConverter\\DateTimeConverter"
            option = "dateFormat"
            value = "Y-m-d"
        }
        validator.notEmpty.type = 'NotEmpty'
    }

:type: (`string`) A type that is used by the property mapper for converting the submitted date.
:validator: (`ValidatorInterface`, defaults to `Neos.Fusion.Form:Runtime.ValidatorCollection`_)
:typeConverterOptions: (array, defaults to `Neos.Fusion:DataStructure`) array of {class, option, value} objects

Neos.Fusion.Form:Runtime.ProcessCollection
------------------------------------------

The `ProcessCollection` implements the `ProcessCollectionInterface` and allows to define a list of processes implementing
the `ProcessInterface` that are to be rendered by the `Neos.Fusion.Form:Runtime.MultiStepProcess`_.

:[key]: (`ProcessInterface`, defaults to `Neos.Fusion.Form:Runtime.SingleStepProcess`_)

.. note:: All properties that have no prototype specified will be evaluated as `Neos.Fusion.Form:Runtime.SingleStepProcess`.


Neos.Fusion.Form:Runtime.ValidatorCollection
--------------------------------------------

The `ValidatorCollection` implements the `validatorInterface` for an array of multiple named properties.
It will execute all validators that are defined and merge the results into one.

:[key]: (`ValidatorInterface`, defaults to `Neos.Fusion.Form:Runtime.Validator`_)

.. note:: When the items are evaluated it is checked that all items satisfy the `ValidatorInterface`.
If untyped items are found they are evaluated as `Neos.Fusion.Form:Runtime.Validator`.

Neos.Fusion.Form:Runtime.Validator
----------------------------------

The `Validator` implements the `ValidatorInterface` the given `type` is used to resolve the implementation
class and the `options` are used to configure the validation.

Example::

    stringLength = Neos.Fusion.Form:Runtime.Validator {
        type = "NotEmpty"
        options {
            minimum = 10
            maximum = 40
        }
    }
    fileType = Neos.Fusion.Form:Runtime.Validator {
        type = "Neos\Flow\ResourceManagement\PersistentResource"
        options.allowedExtensions:['txt', 'jpg']
    }

:type: (`string`) A class name or identifier to be resolved by the validator resolver.
:options: (`array`, defaults to `Neos.Fusion:DataStructure`_)
