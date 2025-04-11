.. _`Fusion Form Action Reference`:

Fusion Form Runtime Action Reference
====================================

Neos.Fusion.Form.Runtime:Message
--------------------------------

The message action will show the defined html message directly on the document the
runtime form was rendered. Since the submitted `data` is available in the fusion context
it can be used to customize the message.

Options:

:message: (`string`) The message to show

Example::

    action {
        message {
            type = 'Neos.Fusion.Form.Runtime:Message'
            options.message = afx`<h1>Thank you {data.firstName} {data.lastName}</h1>`
        }
    }

Neos.Fusion.Form.Runtime:Redirect
---------------------------------

The redirect action will add a redirection to another page to the response. This helps to prevent multiple submits and makes it easier for editors to customize the document. The drawback is
that the submitted data can only be used to determine the redirect target. The content of the page that is redirected to cannot be modified by the form.

Options:

:uri: (`string`) The uri to redirect to.

Example::

    action {
        redirect {
            type = 'Neos.Fusion.Form.Runtime:Redirect'
            options.uri = Neos.Neos:NodeUri {
                node = ${q(node).property('thankyou')}
            }
        }
    }

Neos.Fusion.Form.Runtime:Email
------------------------------

The email action uses symfonymailer to create and send an email. It supports
multipart emails and file attachments that can even be created on the fly from
form data.

.. note:: The neos/symfonymailer package must be installed separately.

Options:

:senderAddress: (`string`|`array`)
:senderName: (`string`)
:recipientAddress: (`string`|`array`)
:recipientName: (`string`)
:replyToAddress: (`string`|`array`)
:carbonCopyAddress: (`string`|`array`)
:blindCarbonCopyAddress: (`string`|`array`)
:subject: (`string`) The email subject
:text: (`string`) The plaintext content
:html: (`string`) The html content (if `text` and `html` are defined a multipart email is created)
:attachments.[key]: (string) The string is treated as a path where the attachment is read from.
:attachments.[key]: (`UploadedFile`|`FlowResource`) The uploaded file or resource is added to the mail
:attachments.[key]: (`array`) Create a file on the fly from `name` and `content`
:testMode: (`boolean`) Show debug information instead of actually sending the email.

Example::

    actions {
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
    }

Neos.Fusion.Form.Runtime:Log
------------------------------

The log action allows to write an entry to log.

.. note:: It is recommended to use a custom log since form submits and especially form data should not end up in system logs.

Options:

:logger: (`string`, default `systemLogger`) the target logger
:level: (`string`, default `info`) the log level
:message: (`string`) the log message
:context: (`array`, default [] ) the logged context

Example::

    action {
        log {
            type = 'Neos.Fusion.Form.Runtime:Log'
            options {
              logger = 'systemLogger'
              level = 'info'
              message = 'Form was submitted'
              context = ${data}
            }
        }
    }
