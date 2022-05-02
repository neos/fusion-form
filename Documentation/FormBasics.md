# Fusion Form Basics

Forms usually are defined by using the `Neos.Fusion.Form:Form` prototype 
in afx. The `form.target` can be passed as a string but since it is 
predefined as a `Neos.Fusion:UriBuilder` so in most cases only the target 
`form.target.action` has to be defined. The current `package` and
`controller` are assumed automatically. By default the `form.method` is `post` but 
other methods can be used aswell. 
 
```
renderer = afx`
    <Neos.Fusion.Form:Form form.target.action="sendOrder" form.target.controller="Order" >

    </Neos.Fusion.Form:Form>
`
```

Since forms are used to manipulate existing data those objects or data structures 
are bound the form as `form.data`. The fusion forms allow to manipulate multiple 
objects at once that are sent to the target controller as separate arguments. 

```
renderer = afx`
    <Neos.Fusion.Form:Form form.data.customer={customer} form.data.shipmentAddress={shipmentAddress}>

    </Neos.Fusion.Form:Form>
`
```

The actual input elements, fieldsets and labels are defined as afx content
for the form.

```
renderer = afx`
    <Neos.Fusion.Form:Form form.data.customer={customer}>
        <fieldset>
            <legend for="example">Example</legend>
            <input type="text" name="title" />
        </fieldset>
    </Neos.Fusion.Form:Form>
`
```

While html inputs can be used they provide no magic like data-binding and 
automatic namespaces. 

To render controls that access the data bound to the form prototypes like 
`Neos.Fusion.Form:Input` are used. Those prototypes are derived from 
`Neos.Fusion.Form:Component.Field` which is responsible for establishing 
the relation between form and field. 

There are plenty of different fieldTypes already that can be found in the 
[Neos.Neos.Form Fusion Documentation](FusionReference.rst) but 
it is also easily possible to create new input types for project-specific
purposes.

```
renderer = afx`
    <Neos.Fusion.Form:Form form.data.customer={customer}>
        <Neos.Fusion.Form:Input field.name="customer[firstName]" />
        <Neos.Fusion.Form:Input field.name="customer[lastName]" />
        <Neos.Fusion.Form:Button >Submit</Neos.Fusion.Form:Button>
    </Neos.Fusion.Form:Form>
`
```

It is possible to create field components with translated label and error 
rendering. The prototype `Neos.Fusion.Form:Neos.BackendModule.FieldContainer` 
is an example for that which implements the required markup for Neos backend modules.
Labels are added and translated using the translations from `Neos.Neos:Main` 
and validation errors are translated using the source `Neos.Flow:ValidationErrors` 
as translation source. 

```
renderer = afx`
    <Neos.Fusion.Form:Form form.data.customer={customer}>
        <Neos.Fusion.Form:FieldContainer field.name="customer[firstName]">
            <Neos.Fusion.Form:Input />
        </Neos.Fusion.Form:FieldContainer>
        <Neos.Fusion.Form:Button >Submit</Neos.Fusion.Form:Button>
    </Neos.Fusion.Form:Form>
`
```
