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

use Neos\Error\Messages\Result;
use Neos\Fusion\Form\Runtime\Domain\SchemaInterface;

class ArrayOfSchemaDefinition extends SchemaDefinition
{
    /**
     * @var SchemaInterface|null
     */
    protected $itemSchema;

    /**
     * ArrayOfSchemaDefinition constructor.
     * @param SchemaInterface|null $itemSchema
     * @param mixed[] $validators
     * @param mixed[] $validatorOptions
     */
    public function __construct(SchemaInterface $itemSchema = null, array $validators = [], array $validatorOptions = [])
    {
        parent::__construct('array', $validators, $validatorOptions);
        $this->itemSchema = $itemSchema;
    }

    /**
     * @param mixed $data
     * @return Result
     * @throws \Neos\Flow\Validation\Exception\InvalidValidationConfigurationException
     * @throws \Neos\Flow\Validation\Exception\NoSuchValidatorException
     */
    public function validate($data): Result
    {
        // validate array
        $result = parent::validate($data);
        // validate keys with itemSchema
        if ($this->itemSchema) {
            foreach ($data as $key => $value) {
                $result->forProperty((string)$key)->merge($this->itemSchema->validate($value));
            }
        }
        return $result;
    }

    /**
     * @param mixed $data
     * @return mixed|\Neos\Error\Messages\Error|null
     * @throws \Neos\Flow\Property\Exception
     * @throws \Neos\Flow\Security\Exception
     */
    public function convert($data)
    {
        // convert to array
        $arrayData = parent::convert($data);
        // convert keys with itemSchema
        if ($this->itemSchema) {
            $result = [];
            foreach ($arrayData as $key => $value) {
                $result[$key] = $this->itemSchema->convert($value);
            }
            return $result;
        } else {
            return $arrayData;
        }
    }
}
