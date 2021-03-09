.. _'Neos.Fusion.Form:Runtime':

==================================
Neos.Fusion.Form:Runtime Reference
==================================

Neos.Fusion.Form:Runtime.RuntimeForm
------------------------------------

:identifier: (`string` form identifier), if no identifier is given the hash of the current fusion path is used
:data: (`mixed`, initial data, defaults to `Neos.Fusion:DataStructure`_)
:process: (`ProcessInterface`, defaults to `Neos.Fusion.Form:Runtime.SingleStepProcess`_)
:action: (`ActionInterface`, defaults to `Neos.Fusion.Form:Runtime.ActionCollection`_)

Neos.Fusion.Form:Runtime.SingleStepProcess
------------------------------------------

:content: (`string`)
:schema: (`SchemaInterface`, defaults to `Neos.Fusion.Form:Runtime.SchemaCollection`_)
:header: (`string`)
:footer: (`string`, defaults to a single submit button` )

Neos.Fusion.Form:Runtime.MultiStepProcess
-----------------------------------------

:steps: (`ProcessCollectionInterface`, defaults to `Neos.Fusion.Form:Runtime.ProcessCollection`_)
:header: (`string`)
:footer: (`string`, defaults to Next/Back and Submit buttons)

Neos.Fusion.Form:Runtime.Action
-------------------------------

:type: (`string`)
:options: (`array` defaults to `Neos.Fusion:DataStructure`_)

Neos.Fusion.Form:Runtime.Schema
-------------------------------

:type: (`string`)
:validators: (`array` defaults to `Neos.Fusion:DataStructure`_)
:validators.[key].type: (`string`)
:validators.[key].options: (`array`, validator options)

Neos.Fusion.Form:Runtime.ActionCollection
-----------------------------------------

:[key]: (`ActionInterface`, defaults to `Neos.Fusion.Form:Runtime.Action`_)

Neos.Fusion.Form:Runtime.SchemaCollection
-----------------------------------------

:[key]: (`SchemaInterface`, defaults to `Neos.Fusion.Form:Runtime.Schema`_)

Neos.Fusion.Form:Runtime.ProcessCollection
------------------------------------------

:[key]: (`ProcessInterface`, defaults to `Neos.Fusion.Form:Runtime.SingleStepProcess`_)
