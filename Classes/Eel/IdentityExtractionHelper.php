<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Eel;

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
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Utility\ObjectAccess;

class IdentityExtractionHelper implements ProtectedContextAwareInterface
{

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @param mixed $subject An object to get the identity for
     * @return mixed identity
     */
    public function convertToIdentity($subject)
    {
        return $this->persistenceManager->getIdentifierByObject($subject);
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
