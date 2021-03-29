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

use Neos\Fusion\Form\Runtime\Domain\ConfigurableActionInterface;

abstract class AbstractAction implements ConfigurableActionInterface
{
    /**
     * @var mixed[]
     */
    protected $options;

    /**
     * @param mixed[] $options
     */
    public function withOptions(array $options = []): ConfigurableActionInterface
    {
        if ($this->options) {
            $options = array_merge($this->options, $options);
        }
        $subject = new static();
        $subject->options = $options;
        return $subject;
    }
}
