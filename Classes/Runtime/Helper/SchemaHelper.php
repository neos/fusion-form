<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Helper;

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

    #
    # Common abbreviated names for boolean and integer
    #

    /**
     * Alias for integer
     * @return SchemaInterface
     */
    public function int(): SchemaInterface
    {
        return $this->integer();
    }

    /**
     * Alias for boolean
     * @return SchemaInterface
     */
    public function bool(): SchemaInterface
    {
        return $this->boolean();
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
