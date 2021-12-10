# Fusion Form

Pure fusion form rendering with afx support!

## Documentation

### *Neos.Fusion.Form* - Form Rendering and Data Binding

This part covers the rendering of forms that are bound to objects or data and 
will be submitted to a custom Controller Action.

- [Fusion Form Basics](Documentation/FormBasics.md)
- Tutorials
  - [Implementing custom input elements](Documentation/Tutorials/CustomFields.md)
  - [Fusion rendering for backend modules](Documentation/Tutorials/FusionFormsInBackendModules.md)
  - [Custom field container](Documentation/Tutorials/CustomFieldContainer.md)
- Examples
  - [FrontendForm](Documentation/Examples/FrontendForm.md)
- [Fusion Prototypes](Documentation/FusionReference.rst)
- [Eel Helpers](Documentation/HelperReference.rst)

### *Form.Fusion.Form:Runtime* - Runtime Single/Multistep Forms with Actions

This part covers the definition of forms with validation and finishing actions
via Fusion. Use cases like contact forms and newsletter subscriptions should 
be implemented like this.   

- [Runtime Fusion Form Basics](Documentation/RuntimeFormBasics.md)
- Tutorials
  - [Custom form action](Documentation/Tutorials/CustomFormAction.md)
  - [Honeypot field](Documentation/Tutorials/HoneypotField.md)
  - [Conditional fields and steps](Documentation/Tutorials/ConditionalFieldsAndSteps.md)
  - [Custom Validators](Documentation/Tutorials/CustomValidators.md)
- Examples
  - [SingleStepForm](Documentation/Examples/SingleStepForm.md)
  - [MultiStepForm](Documentation/Examples/MultiStepForm.md)
- [Fusion Prototypes](Documentation/FusionRuntimeReference.rst)
- [Eel Helpers](Documentation/RuntimeHelperReference.rst)
- [Form Actions](Documentation/RuntimeActionReference.rst)

## Development targets 

- Form rendering in fusion with afx and data binding 
- Clear separation of automation and magic for understandable results
- Flexibility:
  - Make no assumption about the required markup like classNames
  - Allow to override all automatically assigned attributes
  - Enable form fragments as fusion components 
  - Allow to bind multiple objects to a single form
  - Enable to create custom controls with  
  - Respect form elements that are defined as plain html when rendering __trustedProperties
- Convenience:
  - Render hidden fields for validation, security and persistence magic
  - Provide validation-errors and restore previously submitted values
  - Prefix fieldnames with the current request namespace if needed 
- Make writing of fusion backend modules easy:  
  - Create a backend field container with translated labels and error messages 
  - Adjust field markup inside the field container for the neos-backend
  
### Important Deviations from Fluid Form ViewHelpers

The following deviations are probably the ones fluid developers will 
stumble over. There are many more deviations but those are breaking 
concept changes you should be aware of.

- Instead of binding a single `object` a `data` DataStructure is bound to the form.
- Form data-binding is defined via `form.data.object={object}` instead of `objectName` and `object`.
- Field data-binding is defined vis `field.name="object[title]"` with the object name and square brackets for nesting.
- Data binding with `property` path syntax is not supported.
- Select options and groups are defined directly as afx `content` and not `options`.

### Important deviations from the concepts of the Neos.Form package 

- The definitions for form rendering and validation are separated into `content` and `schema` of the `process`.
- By default the `SingleStepProcess` is used as this is the most common case. If you need multiple steps the `process` has to be altered to `MultiStepProcess` 
- Settings, form data and node properties can be used in a unified way via Fusion to define actions and control the process.
- Confirmations are no special feature but can be defined as "step" in a MultiStepProcess   
- The concept of finishers is replaced with `actions`. 
- Actions cannot decide to send the user back into the form process. 








