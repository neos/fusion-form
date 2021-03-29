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

use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Fusion\Form\Runtime\Domain\Exception\ActionException;

class RedirectAction extends AbstractAction
{
    /**
     * @return ActionResponse|null
     * @throws ActionException
     */
    public function perform(): ?ActionResponse
    {
        $uri = $this->options['uri'];

        if (!$uri) {
            throw new ActionException('No uri for redirect action was define.', 1583249244);
        }

        $status = $this->options['status'] ?? 303;

        $response = new ActionResponse();
        $response->setRedirectUri(new Uri($uri), $status);
        return $response;
    }
}
