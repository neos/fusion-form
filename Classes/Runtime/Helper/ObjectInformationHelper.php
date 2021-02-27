<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Helper;

use Neos\Flow\Annotations as Flow;
use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;

class ObjectInformationHelper implements ProtectedContextAwareInterface
{
    /**
     * @var PersistenceManagerInterface
     * @Flow\Inject
     */
    protected $persistenceManager;

    /**
     * @param mixed $subject
     * @return string|null
     */
    public function getObjectIdentifier($subject): ?string
    {
        if (is_object($subject)) {
            return $this->persistenceManager->getIdentifierByObject($subject);
        }
        return null;
    }

    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
