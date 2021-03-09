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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Fusion\Form\Runtime\Domain\ActionResolver;
use Neos\Fusion\Form\Runtime\Domain\ActionInterface;
use Neos\Fusion\Form\Runtime\Domain\ConfigurableActionInterface;
use Neos\Fusion\FusionObjects\AbstractArrayFusionObject;
use Neos\Fusion\FusionObjects\DataStructureImplementation;

class ActionImplementation extends AbstractArrayFusionObject implements ActionInterface
{

    /**
     * @var ActionResolver
     * @Flow\Inject
     */
    protected $actionResolver;

    /**
     * @return $this
     */
    public function evaluate(): self
    {
        return $this;
    }

    /**
     * @return ActionResponse|null
     * @throws \Neos\Fusion\Form\Runtime\Domain\Exception\NoSuchActionException
     */
    public function perform(): ?ActionResponse
    {
        $type = null;
        $options = [];
        foreach ($this->properties as $propertyName => $propertyConfiguration) {
            if (in_array($propertyName, $this->ignoreProperties)) {
                continue;
            }
            if ($propertyName  == 'type') {
                $type = $this->fusionValue($propertyName);
            } else {
                $options[$propertyName] = $this->fusionValue($propertyName);
            }
        }
        $action = $this->actionResolver->createAction($type);
        if ($action instanceof ConfigurableActionInterface) {
            $action = $action->withOptions($options);
        }
        return $action->perform();
    }
}
