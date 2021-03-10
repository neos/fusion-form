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
    public function type(string $typeName): SchemaInterface
    {
        return new SchemaDefinition($typeName);
    }

    #
    # basic types
    #
    public function string(): SchemaInterface
    {
        return new SchemaDefinition('string');
    }

    public function integer(): SchemaInterface
    {
        return new SchemaDefinition('integer');
    }

    public function float(): SchemaInterface
    {
        return new SchemaDefinition('float');
    }

    public function boolean(): SchemaInterface
    {
        return new SchemaDefinition('boolean');
    }

    #
    # Convenience methods for common object types
    #

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
    # Comon abbreviated names for boolean and integer
    #

    public function int(): SchemaInterface
    {
        return $this->integer();
    }

    public function bool(): SchemaInterface
    {
        return $this->boolean();
    }


    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
