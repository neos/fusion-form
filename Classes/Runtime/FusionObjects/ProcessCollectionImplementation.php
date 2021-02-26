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

use Neos\Flow\Mvc\ActionResponse;
use Neos\Fusion\Form\Runtime\Domain\ActionInterface;
use Neos\Fusion\Form\Runtime\Domain\ProcessInterface;
use Neos\Fusion\Form\Runtime\Domain\ProcessCollectionInterface;

class ProcessCollectionImplementation extends AbstractCollectionFusionObject implements ProcessCollectionInterface
{
    protected $itemInterface = ProcessInterface::class;

    protected $itemPrototype = 'Neos.Fusion.Form:Runtime.SingleStepProcess';

    /**
     * @return ProcessInterface[]
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     */
    public function getItems(): array
    {
        return parent::getItems();
    }
}
