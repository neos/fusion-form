<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Domain;

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
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Fusion\Form\Runtime\Domain\ActionInterface;
use Neos\Fusion\Form\Runtime\Domain\Exception\NoSuchActionException;

class ActionResolver
{

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param string $handlerType
     * @return ActionInterface
     * @throws NoSuchActionException
     */
    public function createAction(string $handlerType): ActionInterface
    {
        if ($objectName = $this->resolveActionObjectName($handlerType)) {
            $actionHandler = $this->objectManager->get($objectName);
        } else {
            throw new NoSuchActionException('The action handler "' . $handlerType . '" was could not be resolved!', 1581362538);
        }

        if (!($actionHandler instanceof ActionInterface)) {
            throw new NoSuchActionException(sprintf('The action handler "%s" does not implement %s!', $handlerType, ActionInterface::class), 1581362552);
        }

        return $actionHandler;
    }

    /**
     * @param string $handlerType Either the fully qualified class name of the action handler or the short name
     * @return string|null Class name of the action handler or false if not available
     */
    protected function resolveActionObjectName($handlerType): ?string
    {
        $handlerType = ltrim($handlerType, '\\');

        if ($this->objectManager->isRegistered($handlerType)) {
            return $handlerType;
        }

        if (strpos($handlerType, ':') !== false) {
            list($packageName, $packageActionHandlerType) = explode(':', $handlerType);
            $possibleClassName = sprintf(
                '%s\Action\%sAction',
                str_replace('.', '\\', $packageName),
                str_replace('.', '\\', $packageActionHandlerType)
            );
            if ($this->objectManager->isRegistered($possibleClassName)) {
                return $possibleClassName;
            }
        }

        return null;
    }
}
