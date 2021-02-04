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
use Neos\Fusion\Core\Parser;
use Neos\Fusion\Form\Runtime\Domain\Service\ActionResolver;
use Neos\Fusion\Form\Runtime\Domain\ActionInterface;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class ActionImplementation extends AbstractFusionObject implements ActionInterface
{

    /**
     * @var ActionResolver
     * @Flow\Inject
     */
    protected $actionResolver;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var array
     */
    protected $options = [];

    public function evaluate()
    {
        $this->identifier = $this->fusionValue('identifier');
        $this->options = $this->fusionValue('options');
        return $this;
    }

    public function handle(array $data = []): ?ActionResponse
    {
        $action = $this->actionResolver->createAction($this->identifier);
        return $action->handle($this->options ?? []);
    }
}
