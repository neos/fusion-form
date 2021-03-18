# Runtime Fusion Form Basics

The core of the form runtime is the `Neos.Fusion.Form:Runtime.RuntimeForm` prototype. This prototyoe will
internally use a process and an action to perform its tasks that are defined via fusion. 

```
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
```
