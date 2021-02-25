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
use Neos\Flow\Validation\ValidatorResolver;
use Neos\Fusion\Form\Runtime\Domain\Model\ModelResult;
use Neos\Fusion\Form\Runtime\Domain\SchemaInterface;
use Neos\Fusion\Form\Runtime\Helper\SchemaDefinition;

class SchemaImplementation extends AbstractCollectionFusionObject implements SchemaInterface
{

    /**
     * @var ValidatorResolver
     * @Flow\Inject
     */
    protected $validatorResolver;

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
     * @var SchemaInterface[]
     */
    protected $subschemas = [];

    /**
     * @return $this
     */
    public function evaluate(): self
    {
        $keys = $this->sortNestedFusionKeys();
        foreach ($keys as $name) {
            $value = $this->fusionValue($name);
            if ($value instanceof SchemaInterface) {
                $this->subschemas[$name] = $value;
            } elseif (is_string($value)) {
                $this->subschemas[$name] = new SchemaDefinition($value);
            } else {
                throw new \InvalidArgumentException('Schema fields have to implement the SchemaInterface');
            }
        }
        return $this;
    }

    /**
     * @param mixed $data
     * @return mixed[]
     */
    public function convert($data): array
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('The nested schema can only handle arrays');
        }
        $result = [];
        foreach ($this->subschemas as $fieldName => $fieldSchema) {
            if ($fieldSchema instanceof SchemaInterface) {
                $fieldValue = $data[$fieldName] ?? null;
                $result[$fieldName] = $fieldSchema->convert($fieldValue);
            }
        }
        return $result;
    }

    /**
     * @param mixed $data
     * @return Result
     */
    public function validate($data): Result
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('The nested schema can only handle arrays');
        }
        $result = new Result();
        foreach ($this->subschemas as $fieldName => $fieldSchema) {
            if ($fieldSchema instanceof SchemaInterface) {
                $fieldValue = $data[$fieldName] ?? null;
                $result->forProperty($fieldName)->merge($fieldSchema->validate($fieldValue));
            }
        }
        return $result;
    }
}
