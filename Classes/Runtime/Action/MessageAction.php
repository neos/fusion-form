<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Action;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Fusion\Form\Runtime\Domain\ActionInterface;
use Neos\Flow\Mvc\ActionResponse;

class MessageAction extends AbstractAction
{
    /**
     * @return ActionResponse|null
     */
    public function perform(): ?ActionResponse
    {
        $response = new ActionResponse();
        $response->setContent($this->options['message']);
        return $response;
    }
}
