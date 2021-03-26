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

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Property\TypeConverter\DateTimeConverter;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Flow\ResourceManagement\ResourceTypeConverter;
use Neos\Fusion\Form\Runtime\Domain\SchemaInterface;

class SchemaHelper implements ProtectedContextAwareInterface
{
    /**
     * Create a schema for the given type
     * @param string $type The type or className that is expected
     * @return SchemaInterface
     */
    public function forType(string $type): SchemaInterface
    {
        return new SchemaDefinition($type);
    }

    #
    # basic types
    #

    /**
     * Create a string schema
     * @return SchemaInterface
     */
    public function string(): SchemaInterface
    {
        return new SchemaDefinition('string');
    }

    /**
     * Create a integer schema
     * @return SchemaInterface
     */
    public function integer(): SchemaInterface
    {
        return new SchemaDefinition('integer');
    }

    /**
     * Create a float schema
     * @return SchemaInterface
     */
    public function float(): SchemaInterface
    {
        return new SchemaDefinition('float');
    }

    /**
     * Create a boolean schema
     * @return SchemaInterface
     */
    public function boolean(): SchemaInterface
    {
        return new SchemaDefinition('boolean');
    }

    #
    # Convenience methods for common object types
    #

    /**
     * Create a resource schema
     * @param string $collection The collection new resources are put into
     * @return SchemaInterface
     */
    public function resource(string $collection = 'persistent'): SchemaInterface
    {
        $helper = new SchemaDefinition(PersistentResource::class);
        $helper->typeConverterOption(
            ResourceTypeConverter::class,
            ResourceTypeConverter::CONFIGURATION_COLLECTION_NAME,
            $collection
        );
        return $helper;
    }

    /**
     * Create a date schema. The php value will be DateTime
     * @param string $format The format default is "Y-m-d"
     * @return SchemaInterface
     */
    public function date(string $format = 'Y-m-d'): SchemaInterface
    {
        $helper = new SchemaDefinition(\DateTime::class);
        $helper->typeConverterOption(
            DateTimeConverter::class,
            DateTimeConverter::CONFIGURATION_DATE_FORMAT,
            $format
        );
        return $helper;
    }

    /**
     * Create an array schema.
     * @return SchemaInterface
     */
    public function array(): SchemaInterface
    {
        $helper = new SchemaDefinition('array');
        return $helper;
    }

    /**
     * Create a date schema for an array by providing a schema for all items
     * @param SchemaInterface $schema The schema for the items of the array
     * @return SchemaInterface
     */
    public function arrayOf(SchemaInterface $schema): SchemaInterface
    {
        $helper = new ArrayOfSchemaDefinition($schema);
        return $helper;
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
        return true;
    }
}
