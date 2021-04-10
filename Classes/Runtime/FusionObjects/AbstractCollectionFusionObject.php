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

use Neos\Fusion\Core\Parser;
use Neos\Fusion\Core\Runtime;
use Neos\Utility\Exception\InvalidPositionException;
use Neos\Utility\PositionalArraySorter;
use Neos\Fusion\FusionObjects\AbstractArrayFusionObject;

use Neos\Fusion;

abstract class AbstractCollectionFusionObject extends AbstractArrayFusionObject
{
    /**
     * Interface that the collection items have to fulfil
     * @var string|null
     */
    protected $itemInterface = null;

    /**
     * The fusion prototype name for being used on untyped keys
     * @var string|null
     */
    protected $itemPrototype = null;

    /**
     * Return reference to self during fusion evaluation
     * @return $this
     */
    public function evaluate()
    {
        return $this;
    }

    /**
     * Evaluate the subitems without caching and return the items of the collection.
     * If a $itemPrototype is defined this prototype is used to evaluate untyped items.
     * If an $itemInterface is defined the children are checked for this interface and otherwise an
     * exception is thrown.
     *
     * @return mixed[]
     * @throws Fusion\Exception
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @throws \Neos\Flow\Security\Exception
     */
    public function getItems(): array
    {
        $children = [];
        $keys = $this->sortNestedFusionKeys();

        if (count($keys) === 0) {
            return [];
        }

        foreach ($keys as $key) {
            $propertyPath = $key;
            if ($this->isUntypedProperty($this->properties[$key]) && $this->itemPrototype) {
                $propertyPath .= '<' . $this->itemPrototype . '>';
            }
            try {
                $value = $this->runtime->evaluate($this->path . '/' . $propertyPath, $this);
            } catch (\Exception $e) {
                $value = $this->runtime->handleRenderingException($this->path . '/' . $key, $e);
            }
            if ($value === null && $this->runtime->getLastEvaluationStatus() === Runtime::EVALUATION_SKIPPED) {
                continue;
            }
            if ($this->itemInterface && is_a($value, $this->itemInterface) == false) {
                throw new \InvalidArgumentException(sprintf('Children of %s have to implement interface %s, %s contained %s', static::class, $this->itemInterface, $key, is_object($value) ? get_class($value) : gettype($value)));
            }
            $children[$key] = $value;
        }
        return $children;
    }

    /**
     * Sort the Fusion objects inside $this->properties depending on:
     * - numerical ordering
     * - position meta-property
     *
     * This will ignore all properties defined in "@ignoreProperties" in Fusion
     *
     * @see PositionalArraySorter
     *
     * @return string[] an ordered list of key value pairs
     * @throws Fusion\Exception if the positional string has an unsupported format
     */
    protected function sortNestedFusionKeys()
    {
        $arraySorter = new PositionalArraySorter($this->properties, '__meta.position');
        try {
            $sortedFusionKeys = $arraySorter->getSortedKeys();
        } catch (InvalidPositionException $exception) {
            throw new Fusion\Exception('Invalid position string', 1345126502, $exception);
        }

        foreach ($this->ignoreProperties as $ignoredPropertyName) {
            $key = array_search($ignoredPropertyName, $sortedFusionKeys);
            if ($key !== false) {
                unset($sortedFusionKeys[$key]);
            }
        }
        return $sortedFusionKeys;
    }

    /**
     * Returns TRUE if the given property has no object type assigned
     *
     * @param mixed $property
     * @return bool
     */
    protected function isUntypedProperty($property): bool
    {
        if (!is_array($property)) {
            return false;
        }
        return !isset($property['__objectType']) && !isset($property['__eelExpression']);
    }
}
