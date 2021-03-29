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

use Neos\Flow\Mvc\ActionResponse;

interface ConfigurableActionInterface extends ActionInterface
{
    /**
     * @param mixed[] $options
     */
    public function withOptions(array $options = []): ConfigurableActionInterface;
}
