<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Helper;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

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
     * @param string $targetType The expected type the submitted value will be converted to
     * @param mixed[] $validators The validators to use
     * @param mixed[] $validatorOptions The validators to use
     */
    public function __construct(string $targetType = 'string', array $validators = [], array $validatorOptions = [])
    {
        $this->targetType = $targetType;
        $this->validators = $validators;
        $this->typeConverterOptions = $validatorOptions;
    }

    /**
     * Add a validator to the schema
     *
     * @param string $type The validaor indentifier or className
     * @param mixed[]|null $options The options to set for the validator
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
     * Add a typeConverter option to the schema
     *
     * @param string $className The typeConverter className to set options for
     * @param string $optionName The option name
     * @param mixed $optionValue The value to set
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
     * Add a NotEmpty Validator
     * @return $this
     */
    public function isRequired(): SchemaDefinition
    {
        return $this->validator(NotEmptyValidator::class);
    }

    #
    # Methods required by the SchemaInterface
    #

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
     * @return mixed|null
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

    #
    # Method required by the ProtectedContextAwareInterface
    #

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        if (in_array($methodName, ['__construct', 'convert', 'validate'])) {
            return false;
        }
        return true;
    }
}
