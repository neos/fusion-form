# Using fusion forms in the frontend

This example defines a ShipmentForm component that accepts the props `customer`,
`shipment` and `targetAction`. The form content is defined as afx. The form data is defined 
with both values `customer` and `shipment` that are both modified and sent to the target
controller.

```
prototype(Form.Test:Component.ShipmentForm) < prototype(Neos.Fusion:Component) {
    customer = null
    shipment = null
    targetAction = null
    
    renderer = afx`
        <Neos.Fusion.Form:Form form.data.customer={props.customer} form.data.shipment={props.shipment} form.target.action={props.targetAction}>

            <label for="firstName">First Name</label>	
            <Neos.Fusion.Form:Input attributes.id="firstName" field.name="customer[firstName]"/>
    
            <label for="lastName">Last Name</label>
            <Neos.Fusion.Form:Input attributes.id="lastName" field.name="customer[lastName]"/>
    
            <label>Shipment method</label>
            <label><Neos.Fusion.Form:Radio field.name="shipment[method]" field.value="ups"/>UPS</label>
            <label><Neos.Fusion.Form:Radio field.name="shipment[method]" field.value="dhl"/>DHL</label>
            <label><Neos.Fusion.Form:Radio field.name="shipment[method]" field.value="pickup"/>Pickup</label>
    
            <label for="street">Street</label>
            <Neos.Fusion.Form:Input attributes.id="street" field.name="customer[street]"/>
            
            <label for="city">City</label>
            <Neos.Fusion.Form:Input attributes.id="city" field.name="customer[city]"/>
    
            <label for="country">Country</label>
            <Neos.Fusion.Form:Select attributes.id=country field.name="shipment[country]">
                <Neos.Fusion.Form:Select.Option option.value="de">Germany</Neos.Fusion.Form:Select.Option>
                <Neos.Fusion.Form:Select.Option option.value="at">Austria</Neos.Fusion.Form:Select.Option>
                <Neos.Fusion.Form:Select.Option option.value="ch">Switzerland</Neos.Fusion.Form:Select.Option>
            </Neos.Fusion.Form:Select>
            
            <Neos.Fusion.Form:Button>Submit Order</Neos.Fusion.Form:Button>

        </Neos.Fusion.Form:Form>
    `
}
```
