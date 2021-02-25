<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Helper;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Error\Messages\Result;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\Property\PropertyMappingConfiguration;
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
     * @param string $identifier
     * @param mixed[]|null $options
     * @return $this
     */
    public function validator(string $identifier, ?array $options = null): self
    {
        $this->validators[] = [
            'identifier' => $identifier,
            'options' => $options
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
                $validationConfiguration['identifier'],
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
        $mappedValue = $this->propertyMapper->convert($data, $this->targetType, $this->propertyMappingConfiguration);
        $mappingResult = $this->propertyMapper->getMessages();
        if ($mappingResult->hasErrors()) {
            return null;
        } else {
            return $mappedValue;
        }
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        if (in_array($methodName, ['validator'])) {
            return true;
        }
        return false;
    }
}
