<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\FusionObjects;

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
use Neos\Error\Messages\Result;
use Neos\Flow\Property\PropertyMapper;
use Neos\Flow\Property\PropertyMappingConfiguration;
use Neos\Flow\Validation\Validator\ValidatorInterface;
use Neos\Fusion\Form\Runtime\Domain\SchemaInterface;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class SchemaImplementation extends AbstractFusionObject implements SchemaInterface
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
     * Return reference to self during fusion evaluation
     * @return $this
     */
    public function evaluate()
    {
        return $this;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function convert($data)
    {
        $typeConverterOptions = $this->getTypeConverterOptions();
        if ($typeConverterOptions) {
            foreach ($typeConverterOptions as $typeConverterOption) {
                if (array_key_exists('class', $typeConverterOption) && array_key_exists('option', $typeConverterOption)) {
                    $this->propertyMappingConfiguration->setTypeConverterOption(
                        $typeConverterOption['class'],
                        $typeConverterOption['option'],
                        $typeConverterOption['value'] ?? null
                    );
                }
            }
        }

        $mappedValue = $this->propertyMapper->convert($data, $this->getType(), $this->propertyMappingConfiguration);
        $mappingResult = $this->propertyMapper->getMessages();
        if ($mappingResult->hasErrors()) {
            return null;
        } else {
            return $mappedValue;
        }
    }

    /**
     * @param mixed $data
     * @return Result
     */
    public function validate($data): Result
    {
        $validator = $this->getValidator();
        return $validator->validate($data);
    }

    /**
     * @return string
     */
    protected function getType():string
    {
        return $this->fusionValue('type');
    }

    /**
     * @return mixed[]|null
     */
    protected function getTypeConverterOptions(): ?array
    {
        return $this->fusionValue('typeConverterOptions');
    }

    /**
     * @return ValidatorInterface
     */
    protected function getValidator(): ValidatorInterface
    {
        return $this->fusionValue('validator');
    }
}
