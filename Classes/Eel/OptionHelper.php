<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Eel;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Error\Messages\Result;
use Neos\Eel\ProtectedContextAwareInterface;

class OptionHelper extends AbstractFormHelper
{

    /**
     * @var mixed
     */
    protected $targetValue;

    /**
     * Option constructor.
     *
     * @param mixed|null $targetValue
     */
    public function __construct($targetValue = null)
    {
        $this->targetValue = $targetValue;
    }

    /**
     * @return mixed
     */
    public function getTargetValue()
    {
        return $this->targetValue;
    }

    /**
     * @return string
     */
    public function getTargetValueStringified(): string
    {
        return $this->stringifyValue($this->targetValue);
    }
}
