<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Domain;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * This object describes a single target value for a form field. Usually this is
 * instantiated by the fusion prototype `Neos.Fusion.Form:Definition.Option`
 * and can be accessed as `option` in the fusion context.
 *
 * @package Neos\Fusion\Form\Domain
 */
class Option extends AbstractFormObject
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
     * @return mixed The target value of the option
     */
    public function getTargetValue()
    {
        return $this->targetValue;
    }

    /**
     * @return string The target value of the option converted to string for being used as html option value
     */
    public function getTargetValueStringified(): string
    {
        return $this->stringifyValue($this->targetValue);
    }
}
