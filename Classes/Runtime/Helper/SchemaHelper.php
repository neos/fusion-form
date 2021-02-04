<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Helper;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Fusion\Form\Runtime\Domain\SchemaInterface;

class SchemaHelper implements ProtectedContextAwareInterface
{
    public function type(string $typeName): SchemaInterface
    {
        return new SchemaDefinitionToken($typeName);
    }

    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
