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
use Neos\Fusion\Form\Runtime\Domain\SchemaInterface;

class SchemaCollectionImplementation extends AbstractCollectionFusionObject implements SchemaInterface
{
    protected $itemInterface = SchemaInterface::class;

    protected $itemPrototype = 'Neos.Fusion.Form:Runtime.Schema';

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

        /**
         * @var SchemaInterface[] $subschemas
         */
        $subschemas = $this->getItems();

        foreach ($subschemas as $fieldName => $fieldSchema) {
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

        /**
         * @var SchemaInterface[] $subschemas
         */
        $subschemas = $this->getItems();

        foreach ($subschemas as $fieldName => $fieldSchema) {
            if ($fieldSchema instanceof SchemaInterface) {
                $fieldValue = $data[$fieldName] ?? null;
                $result->forProperty($fieldName)->merge($fieldSchema->validate($fieldValue));
            }
        }
        return $result;
    }
}
