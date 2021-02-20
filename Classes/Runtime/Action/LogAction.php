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

class LogAction implements ActionInterface
{
    /**
     * @Flow\Inject
     * @var PsrLoggerFactoryInterface
     */
    protected $loggerFactory;

    /**
     * @param mixed[] $options
     * @return ActionResponse|null
     */
    public function handle(array $options = []): ?ActionResponse
    {
        $logger = $this->loggerFactory->get($options['logger'] ?? 'systemLogger');

        $logger->log(
            $options['level'] ?? 'info',
            $options['message'] ?? '',
            $options['context'] ?? []
        );

        return null;
    }
}
