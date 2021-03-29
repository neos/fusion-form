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

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Fusion\Form\Runtime\Domain\ActionInterface;
use Neos\Flow\Log\PsrLoggerFactoryInterface;

class LogAction extends AbstractAction
{
    /**
     * @Flow\Inject
     * @var PsrLoggerFactoryInterface
     */
    protected $loggerFactory;

    /**
     * @return ActionResponse|null
     */
    public function perform(): ?ActionResponse
    {
        $logger = $this->loggerFactory->get($this->options['logger'] ?? 'systemLogger');

        $logger->log(
            $this->options['level'] ?? 'info',
            $this->options['message'] ?? '',
            $this->options['context'] ?? []
        );

        return null;
    }
}
