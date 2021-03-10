<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Helper;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Error\Messages\Result;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\Property\PropertyMappingConfiguration;
use Neos\Flow\Validation\Validator\NotEmptyValidator;
use Neos\Flow\Validation\ValidatorResolver;
use Neos\Fusion\Form\Runtime\Domain\SchemaInterface;

class SchemaDefinition implements ProtectedContextAwareInterface, SchemaInterface
{

    /**
     * @var PropertyMapper
     * @Flow\Inject
     */
    protected $propertyMapper;

    /**
     * @var PropertyMappingConfiguration
     * @Flow\Inject
     */
    protected $propertyMappingConfiguration;

    /**
     * @var ValidatorResolver
     * @Flow\Inject
     */
    protected $validatorResolver;

    /**
     * @var string
     */
    protected $targetType;

    /**
     * @var mixed[]
     */
    protected $validators = [];

    /**
     * @var mixed[]
     */
    protected $typeConverterOptions;

    /**
     * SchemaDefinitionToken constructor.
     * @param string $targetType
     * @param mixed[] $validators
     */
    public function __construct(string $targetType, array $validators = [])
    {
        $this->targetType = $targetType;
        $this->validators = $validators;
    }

    /**
     * @param string $type
     * @param mixed[]|null $options
     * @return $this
     */
    public function validator(string $type, ?array $options = null): self
    {
        $this->validators[] = [
            'type' => $type,
            'options' => $options
        ];
        return $this;
    }

    /**
     * @param string $className
     * @param string $optionName
     * @param mixed $optionValue
     * @return $this
     */
    public function typeConverterOption(string $className, string $optionName, $optionValue): self
    {
        $this->typeConverterOptions[] = [
            'class' => $className,
            'option' => $optionName,
            'value' => $optionValue
        ];
        return $this;
    }

    /**
     * @param mixed $data
     * @return Result
     * @throws \Neos\Flow\Validation\Exception\InvalidValidationConfigurationException
     * @throws \Neos\Flow\Validation\Exception\NoSuchValidatorException
     */
    public function validate($data): Result
    {
        $propertyValidationResult = new Result();

        foreach ($this->validators as $validationConfiguration) {
            $validator = $this->validatorResolver->createValidator(
                $validationConfiguration['type'],
                $validationConfiguration['options'] ?? []
            );
            $propertyValidationResult->merge($validator->validate($data));
        }

        return $propertyValidationResult;
    }

    /**
     * @param mixed $data
     * @return mixed|\Neos\Error\Messages\Error|null
     * @throws \Neos\Flow\Property\Exception
     * @throws \Neos\Flow\Security\Exception
     */
    public function convert($data)
    {
        if ($this->typeConverterOptions) {
            foreach ($this->typeConverterOptions as $typeConverterOption) {
                if (array_key_exists('class', $typeConverterOption) && array_key_exists('option', $typeConverterOption)) {
                    $this->propertyMappingConfiguration->setTypeConverterOption(
                        $typeConverterOption['class'],
                        $typeConverterOption['option'],
                        $typeConverterOption['value'] ?? null
                    );
                }
            }
        }

        $mappedValue = $this->propertyMapper->convert($data, $this->targetType, $this->propertyMappingConfiguration);
        $mappingResult = $this->propertyMapper->getMessages();
        if ($mappingResult->hasErrors()) {
            return null;
        } else {
            return $mappedValue;
        }
    }

    /**
     * Add a NotEmpty Validator
     * @return $this
     */
    public function isRequired(): SchemaDefinition
    {
        return $this->validator(NotEmptyValidator::class);
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        if ($methodName === 'convert' || $methodName === 'validate') {
            return false;
        }
        return true;
    }
}
