.. _'Neos.Fusion.Form:Runtime':

==================================
Neos.Fusion.Form:Runtime Reference
==================================

The runtime part of the Neos.Fusion.Form package provides means to easily connect single or multistep forms
with validation and actions that handle the submitted data without implementing custom controllers.

Neos.Fusion.Form:Runtime.RuntimeForm
------------------------------------

The runtime form prototype will pass the submitted arguments in the form-namespace in `identifier` to the `process` until
this is finished. Then the resulting `data` is passed to the `action`.

The algorithm is as follows:

1. Create a subRequest in form namespace and pass all submitted values that have trustedProperties.
2. Let the `proccess` handle the created subrequest.
3. If the `process` is not finished render form tag an delegates the body rendering to the `process`.
4. If the `process` is finished the the data and execute the `action`.

Example::

  renderer = Neos.Fusion.Form:Runtime.RuntimeForm {
    # the form argument namespace
    identifier = 'example'

    # initial form data to prefill the fields
    data = Neos.Fusion:DataStructure

    # the form process that is responsible for rendering the form and
    # collecting the data
    process = Neos.Fusion.Form:Runtime.SingleStepForm

    # action that is processed after the form process is finished
    action =  Neos.Fusion.Form:Runtime.Actions
  }

:identifier: (`string`), Form argument namespace. If no identifier is defined the hash of the current fusion path is used as namespace.
:data: (`mixed`, defaults to `Neos.Fusion:DataStructure`_) The initial data that is given to the form process as input value.
:process: (`ProcessInterface`, defaults to `Neos.Fusion.Form:Runtime.SingleStepProcess`_) The process will handle each request until it declares itself finished.
:action: (`ActionInterface`, defaults to `Neos.Fusion.Form:Runtime.ActionCollection`_) The action that is executed after the process is finished.

.. note:: While the `process` renders and the `action` is executed the current `data` is always in the Fusion context and can be used by Fusion.

Neos.Fusion.Form:Runtime.SingleStepProcess
------------------------------------------

The most common and simple form process consists of a single page. The actual form is defined as `content`
and may be afx or a prototype. It is responsible for rendering the form

Example::

	prototype(Form.Test:Content.ExampleForm) < prototype(Neos.Neos:ContentComponent) {
		renderer = Neos.Fusion.Form:Runtime.RuntimeForm {
			identifier = "singleStepExample"

			process = Neos.Fusion.Form:Runtime.SingleStepProcess {

				content = afx`
					<fieldset>
						<legend>name</legend>
						<Neos.Fusion.Form:FieldContainer field.name="firstName" label="First Name">
							<Neos.Fusion.Form:Input />
						</Neos.Fusion.Form:FieldContainer>
						<Neos.Fusion.Form:FieldContainer field.name="lastName" label="Last Name">
							<Neos.Fusion.Form:Input />
						</Neos.Fusion.Form:FieldContainer>
					</fieldset>
					<fieldset>
						<legend>file</legend>
						<Neos.Fusion.Form:FieldContainer field.name="file" label="File">
							<Neos.Fusion.Form:Upload />
						</Neos.Fusion.Form:FieldContainer>
					</fieldset>
				`

				schema {
					firstName = ${Schema.type("string").validator('NotEmpty')}
					lastName = ${Schema.type("string").validator('NotEmpty').validator('StringLength', {minimum: 6, maximum: 12})}
					file = ${Schema.type("Neos\Flow\ResourceManagement\PersistentResource").validator('NotEmpty').validator('Neos\Fusion\Form\Runtime\Validation\Validator\FileTypeValidator', {allowedExtensions:['txt', 'jpg']})}

				}
			}

			action {
				message {
					type = 'Neos.Fusion.Form.Runtime:Message'
				   message = afx`<h1>Thank you {data.firstName} {data.lastName}</h1>`
				}
				email {
					type = 'Neos.Fusion.Form.Runtime:Email'
					senderAddress = ${q(node).property('mailFrom')}
					recipientAddress = ${q(node).property('mailTo')}
					subject = ${q(node).property('mailSubject')}
					text = afx`Thank you {data.firstName} {data.lastName}`
					html = afx`<h1>Thank you {data.firstName} {data.lastName}</h1></p>`
					attachments {
						upload = ${data.file}
					}
				}
			}
		}
	}

:content: (`string`) The form body to be rendered.
:schema: (`SchemaInterface`, defaults to `Neos.Fusion.Form:Runtime.SchemaCollection`_) The schema to convert and validate the submitted data with.
:header: (`string`) The form header is rendered before the body. By default this is empty, create derived prototypes to change this.
:footer: (`string`, defaults to a single submit button) The form footer contains a single submit button by default, create derived prototypes to change this.

Neos.Fusion.Form:Runtime.MultiStepProcess
-----------------------------------------

The multistep process allows to define use multiple `steps` that will usually be of type SingleStepProcess. The multistep process
persists the current form state as hidden field and otherwise passes the rendering of the form-body to the currently active
sub procces.

Example::

	prototype(Form.Test:Content.ExampleForm2) < prototype(Neos.Neos:ContentComponent) {
		renderer = Neos.Fusion.Form:Runtime.RuntimeForm {

			identifier = "multiStepExample"

			process = Neos.Fusion.Form:Runtime.MultiStepProcess {
				steps {
					first {
						content = afx`
							<fieldset>
								<legend>name</legend>
								<Neos.Fusion.Form:FieldContainer field.name="firstName" label="First Name">
									<Neos.Fusion.Form:Input @validate />
								</Neos.Fusion.Form:FieldContainer>
								<Neos.Fusion.Form:FieldContainer field.name="lastName" label="Last Name">
									<Neos.Fusion.Form:Input />
								</Neos.Fusion.Form:FieldContainer>
							</fieldset>
						`

						schema {
							firstName = ${Schema.type("string").validator('NotEmpty')}
							lastName = ${Schema.type("string").validator('NotEmpty').validator('StringLength', {minimum: 6, maximum: 12})}
						}
					}

					second {
						content = afx`
							<fieldset>
								<legend>address</legend>
								<Neos.Fusion.Form:FieldContainer field.name="street" label="Street">
									<Neos.Fusion.Form:Input />
								</Neos.Fusion.Form:FieldContainer>
								<Neos.Fusion.Form:FieldContainer field.name="city" label="City">
									<Neos.Fusion.Form:Input />
								</Neos.Fusion.Form:FieldContainer>
							</fieldset>
						`

						schema {
							street = ${Schema.type("string").validator('NotEmpty')}
							city = ${Schema.type("string").validator('NotEmpty')}
						}
					}

					third {
						content = afx`
							<fieldset>
								<legend>file</legend>
								<Neos.Fusion.Form:FieldContainer field.name="file" label="File">
									<Neos.Fusion.Form:Upload />
								</Neos.Fusion.Form:FieldContainer>
							</fieldset>
						`
						schema {
							file = ${Schema.type("Neos\Flow\ResourceManagement\PersistentResource").validator('NotEmpty').validator('Neos\Fusion\Form\Runtime\Validation\Validator\FileTypeValidator', {allowedExtensions:['txt', 'jpg']})}
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
					senderAddress = ${q(node).property('mailFrom')}
					recipientAddress = ${q(node).property('mailTo')}

					subject = ${q(node).property('mailSubject')}
					text = afx`Thank you {data.firstName} {data.lastName} from {data.city}, {data.street}`
					html = afx`<h1>Thank you {data.firstName} {data.lastName}</h1><p>from {data.city}, {data.street}</p>`

					attachments {
						upload = ${data.file}
					}
				}

				redirect {
					type = 'Neos.Fusion.Form.Runtime:Redirect'
					uri = Neos.Neos:NodeUri {
						node = ${q(node).property('thankyou')}
					}
				}
			}
		}
	}

:steps: (`ProcessCollectionInterface`, defaults to `Neos.Fusion.Form:Runtime.ProcessCollection`_)
:header: (`string`) The form header is rendered before the body. By default this is empty, create derived prototypes to change this.
:footer: (`string`, defaults to Next/Back and Submit buttons) The form footer contains a pre/next/submit button by default, create derived prototypes to change this.

During rendering a `process` variable is added to the context that contains the following information:

:process.state: (`string|null`) Serialized and signed form state, if a previous state is present
:process.current: (`string`) Current subprocess key
:process.prev: (`string|null`) Previous subprocess key
:process.next: (`string|null`) Next subprocess key
:process.all: (`array`) List of all subprocess keys
:process.isFirst: (`boolean`) True if the current subprocess is the first one
:process.isLast: (`boolean`) True if the current subprocess is the last one

.. note:: Inside the MultiStepProcess the header and footer of the SingleStepProcess used as subprocess elements are suppressed.

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

:type: (`string`) To to be used by the Action resolver to determine the implementation class. Can be an Identifier or a ClassName.
:*: (`array` defaults to `Neos.Fusion:DataStructure`_) The options that for configuring the action.

Neos.Fusion.Form:Runtime.SchemaCollection
-----------------------------------------

The `SchemaCollection` implements the `SchemaInterface` for an array of multiple named properties.
It will execute all subschemas that are defined for each subkey and merge the results into one.
The subschemas can be created with the Eeel `Schema.type(...)` helper or the `Neos.Fusion.Form:Runtime.Schema` prototype.

Example::

  schema = Neos.Fusion.Form:Runtime.SchemaCollection {
    firstName = ${Schema.type("string").validator('NotEmpty')}
    lastName = ${Schema.type("string").validator('NotEmpty').validator('StringLength', {minimum: 10, maximum: 40})}
  }

:[key]: (`SchemaInterface`, defaults to `Neos.Fusion.Form:Runtime.Schema`_)

.. note:: When the items are evaluated it is checked that all items satisfy the `SchemaInterface`.
If untyped items are found they are evaluated as `Neos.Fusion.Form:Runtime.Schems`.


Neos.Fusion.Form:Runtime.Schema
-------------------------------

The `Schema` implements the `SchemaInterface` and allows to define a target type and validators for a property.
The `type` property identifies the the target type for the property mapping. The key `validator` allows to define
one or more validators that are again identified by `type` all other properties are passed as validator options.

Example::

  firstName = Neos.Fusion.Form:Runtime.Schema {
    type = "string"
    validator.notEmpty.type = "NotEmpty"
    validator.stringLength.type = "NotEmpty"
    validator.stringLength.minimum = 10
    validator.stringLength.maximum = 40
  }

  file = Neos.Fusion.Form:Runtime.Schema {
    type = "Neos\Flow\ResourceManagement\PersistentResource"
    validator.file.type = 'Neos\Fusion\Form\Runtime\Validation\Validator\FileTypeValidator'
    validator.file.allowedExtensions:['txt', 'jpg']
  }

:type: (`string`) A type that is used by the property mapper to
:validator: (`array` defaults to `Neos.Fusion:DataStructure`_)
:validator.[key].type: (`string`) The type of the validator, className or identifier.
:validator.[key].*: (`any`) Options for the validator.

Neos.Fusion.Form:Runtime.ProcessCollection
------------------------------------------

The `ProcessCollection` implements the `ProcessCollectionInterface` and allows to define a list of processes implementing
the `ProcessInterface` that are to be rendered by the `Neos.Fusion.Form:Runtime.MultiStepProcess`_.

:[key]: (`ProcessInterface`, defaults to `Neos.Fusion.Form:Runtime.SingleStepProcess`_)

.. note:: All properties that have no prototype specified will be evaluated as `Neos.Fusion.Form:Runtime.SingleStepProcess`.
