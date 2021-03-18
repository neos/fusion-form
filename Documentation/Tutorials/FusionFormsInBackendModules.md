# Using fusion forms in a backend module

Forms for backend modules basically work the same as in the frontend 
but the additional prototype `Neos.Fusion.Form:Neos.BackendModule.FieldContainer` 
can be used to render fields with translated labels and error messages
using the default markup of the Neos backend.

HINT: To render a backend module with fusion you have to set the 
`defaultViwObjectName` to the `Neos\Fusion\View\FusionView::class` in the
controller class and be aware that you have to include all required fusion
explicitly.

```
#
# In backend modules all includes have to be done explicitly
# the default fusion from Neos.Fusion and Neos.Fusion.Form 
# is needed for rendering of basic forms
#
include: resource://Neos.Fusion/Private/Fusion/Root.fusion
include: resource://Neos.Fusion.Form/Private/Fusion/Root.fusion

#
# By default the fusion view creates the render pathes from the
# current package controller and action. 
#
Form.Test.FusionController.index = Form.Test:Backend.UserForm
Form.Test.FusionController.updateUser = Form.Test:Backend.UserForm

#
# The rendering of the form is centralized in a single prototype 
# that expects the values `title`, `user` and `targetAction` in the context
#
prototype(Form.Test:Backend.UserForm) < prototype(Neos.Fusion:Component) {

    renderer = afx`
        <h2>{title}</h2>

        <Neos.Fusion.Form:Form form.data.user={user} form.target.action={targetAction} >

            <Neos.Fusion.Form:Neos.BackendModule.FieldContainer field.name="user[firstName]" label="user.firstName">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:Neos.BackendModule.FieldContainer>

            <Neos.Fusion.Form:Neos.BackendModule.FieldContainer field.name="user[firstName]" label="user.lastName">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:Neos.BackendModule.FieldContainer>

            <Neos.Fusion.Form:Neos.BackendModule.FieldContainer field.name="user[roles]" label="user.role" field.multiple>
                <Neos.Fusion.Form:Checkbox field.value="Neos.Neos:RestrictedEditor" />
                <Neos.Fusion.Form:Checkbox field.value="Neos.Neos:Editor" />
                <Neos.Fusion.Form:Checkbox field.value="Neos.Neos:Administrator" />
            </Neos.Fusion.Form:Neos.BackendModule.FieldContainer>

            <Neos.Fusion.Form:Neos.BackendModule.FieldContainer field.name="user[language]" label="user.language" >
                <Neos.Fusion.Form:Select>
                    <Neos.Fusion.Form:Select.Option option.value="en" >Englisch</Neos.Fusion.Form:Select.Option>
                    <Neos.Fusion.Form:Select.Option option.value="de" >Deutsch</Neos.Fusion.Form:Select.Option>
                    <Neos.Fusion.Form:Select.Option option.value="ru" >Russian</Neos.Fusion.Form:Select.Option>
                    <Neos.Fusion.Form:Select.Option option.value="kg" >Klingon</Neos.Fusion.Form:Select.Option>
                </Neos.Fusion.Form:Select>
            </Neos.Fusion.Form:Neos.BackendModule.FieldContainer>

            <Neos.Fusion.Form:Button>submit</Neos.Fusion.Form:Button>

        </Neos.Fusion.Form:Form>
    `
}
```
