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
use Neos\Utility\Exception\InvalidPositionException;
use Neos\Utility\PositionalArraySorter;
use Neos\Fusion\FusionObjects\AbstractArrayFusionObject;

use Neos\Fusion;

abstract class AbstractCollectionFusionObject extends AbstractArrayFusionObject
{

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
        return array_intersect_key(array_flip(Parser::$reservedParseTreeKeys), $property) === [];
    }
}
