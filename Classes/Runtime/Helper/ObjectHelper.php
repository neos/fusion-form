<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Helper;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;

class ObjectHelper implements ProtectedContextAwareInterface
{
    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     */
    protected $persistenceManager;

    public function getObjectIdentifier($subject): ?string
    {
        if (is_object($subject)) {
            return $this->persistenceManager->getIdentifierByObject($subject);
        }
    }

    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
