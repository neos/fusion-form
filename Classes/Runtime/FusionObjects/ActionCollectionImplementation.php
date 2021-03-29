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

class ActionCollectionImplementation extends AbstractCollectionFusionObject implements ActionInterface
{
    protected $itemInterface = ActionInterface::class;

    protected $itemPrototype = 'Neos.Fusion.Form:Runtime.Action';

    /**
     * @return $this
     */
    public function evaluate(): self
    {
        return $this;
    }

    /**
     * @return ActionResponse|null
     */
    public function perform(): ?ActionResponse
    {
        $response = new ActionResponse();

        /**
         * @var ActionInterface[] $subActions
         */
        $subActions = $this->getItems();

        foreach ($subActions as $subAction) {
            $subActionResponse = $subAction->perform();
            if ($subActionResponse) {
                // content of multiple responses is concatenated
                if ($subActionResponse->getContent()) {
                    $mergedContent = $response->getContent() . $subActionResponse->getContent();
                    $subActionResponse->setContent($mergedContent);
                }
                // preserve non 200 status codes that would otherwise be overwritten
                if ($response->getStatusCode() !== 200 && $subActionResponse->getStatusCode() == 200) {
                    $subActionResponse->setStatusCode($response->getStatusCode());
                }
                $subActionResponse->mergeIntoParentResponse($response);
            }
        }
        return $response;
    }
}
