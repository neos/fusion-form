<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Domain;

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
use Neos\Flow\Persistence\PersistenceManagerInterface;

abstract class AbstractFormObject implements ProtectedContextAwareInterface
{

    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     */
    protected $persistenceManager;

    /**
     * Convert a value to a string representation for beeing rendered as an html form value
     *
     * @param mixed $value
     * @return string|null
     */
    protected function stringifyValue($value): string
    {
        if (is_object($value)) {
            $identifier = $this->persistenceManager->getIdentifierByObject($value);
            if ($identifier !== null) {
                return $identifier;
            }
        }
        return (string)$value;
    }

    /**
     * Convert an array of values to an array of string representation for beeing rendered as an html form value
     *
     * @param iterable $value
     * @return array|null
     */
    protected function stringifyMultivalue(iterable $value = null): array
    {
        if (is_iterable($value)) {
            $result = [];
            foreach ($value as $key => $item) {
                $result[$key] = $this->stringifyValue($item);
            }
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Prepend the given fieldNamePrefix to the fieldName the
     *
     * @param string $name
     * @param string|null $prefix
     * @return string
     */
    protected function prefixFieldName(string $fieldName, string $fieldNamePrefix = null): string
    {
        if (!$fieldNamePrefix) {
            return $fieldName;
        } else {
            $fieldNameSegments = explode('[', $fieldName, 2);
            $fieldName = $fieldNamePrefix . '[' . $fieldNameSegments[0] . ']';
            if (count($fieldNameSegments) > 1) {
                $fieldName .= '[' . $fieldNameSegments[1];
            }
            return $fieldName;
        }
    }

    /**
     * Convert the given html fieldName to a dot seperated path
     *
     * @param $name
     * @return string
     */
    protected function fieldNameToPath($name): string
    {
        $path = preg_replace('/(\]\[|\[|\])/', '.', $name);
        return trim($path, '.');
    }

    /**
     * Convert the given dot seperated $path to an html fieldName
     *
     * @param $name
     * @return string
     */
    protected function pathToFieldName($path): string
    {
        $pathSegments = explode('.', $path);
        $fieldName = array_shift($pathSegments);
        foreach ($pathSegments as $pathSegment) {
            $fieldName .= '[' . $pathSegment . ']';
        }
        return $fieldName;
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
