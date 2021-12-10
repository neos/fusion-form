# Custom Validators

Custom Validators allow to implement specific rules and error messages for the submitted values. 
Validators implement the `\Neos\Flow\Validation\Validator\ValidatorInterface` which is made comfortable with the 
`\Neos\Flow\Validation\Validator\AbstractValidator` base class that reduces the effort to implementing a single 
`isValid()` Method. The behavior of validators can be configured via options. All found errors passed to the 
`addError()` method and will be combined with results other validators may return.  

ATTENTION: The validator resolver searches validators in the namespace `__PackageNamespace__\Validation\Validator\__ValidatorName__Validator` 
when addressed via `__PackageKey__:__ValidatorName__`.

Flow validation documentation: https://flowframework.readthedocs.io/en/stable/TheDefinitiveGuide/PartIII/Validation.html

```
<?php
declare(strict_types=1);

namespace Vendor\Site\Validation\Validator;

use Neos\Flow\Validation\Validator\AbstractValidator;

class AllowedValuesValidator extends AbstractValidator
{
    /**
     * @var boolean
     */
    protected $acceptsEmptyValues = false;

    /**
     * @var mixed[]
     */
    protected $supportedOptions = array(
        'allowedValues' => array([], 'Array of allowed values', 'array', false),
    );

    /**
     * @param mixed $value
     * @return void
     */
    protected function isValid($value)
    {
        if (!in_array( $value, $this->options['allowedValues'])) {
            $this->addError('Some values are more correct than others.', 123456789);
        }
    }
}
```

The custom validator can now be used in fusion forms.

```
prototype(Vendor.Site:Content.FormExample) < prototype(Neos.Fusion.Form:Runtime.RuntimeForm) {

    process {
        content = afx`
            <Neos.Fusion.Form:FieldContainer field.name="favoriteMovie" label="Favorite Movie">
                <Neos.Fusion.Form:Input />
            </Neos.Fusion.Form:FieldContainer>
        `

        schema {
            #
            # A custom validator is identified via PackageKey and ValidatorName seperated by a colon. 
            # The implementation is expected to be in the php namespace `__PackageNamespace__\Validation\Validator\__ValidatorName__Validator`
            # 
            favoriteMovie = ${Form.Schema.string().validator('Vendor.Site:AllowedValues', {allowedValues: ['Star Wars', 'Simpsons']})}
        }
    }
    
    ... 
    
```    

