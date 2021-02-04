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
use Neos\Fusion\Form\Runtime\Helper\SchemaDefinitionToken;
use Neos\Fusion\FusionObjects\DataStructureImplementation;

class SchemaImplementation extends DataStructureImplementation implements SchemaInterface
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

    public function evaluate()
    {
        $subValues = parent::evaluate();
        foreach ($subValues as $fieldName => $subValue) {
            if ($subValue instanceof SchemaInterface) {
                $this->subschemas[$fieldName] = $subValue;
            } elseif (is_string($subValue)) {
                $this->subschemas[$fieldName] = new SchemaDefinitionToken($subValue);
            } else {
                throw new \InvalidArgumentException('Schema fields have to implement the SchemaInterface');
            }
        }
        return $this;
    }

    /**
     * @param array $data
     * @return array
     * @throws \Neos\Flow\Property\Exception
     * @throws \Neos\Flow\Security\Exception
     */
    public function convert($data): array
    {
        if (!is_iterable($data)) {
            throw new \InvalidArgumentException('The nested schema can only handle iterables');
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
     * @param $data
     * @return Result
     */
    public function validate($data): Result
    {
        if (!is_iterable($data)) {
            throw new \InvalidArgumentException('The nested schema can only handle iterables');
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
