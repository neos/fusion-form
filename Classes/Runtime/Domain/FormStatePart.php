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

use Exception;

class FormStatePart
{
    /**
     * @var mixed[]
     */
    protected $data = [];

    /**
     * @var bool
     */
    protected $finished = false;

    /**
     * @param mixed[] $data
     * @param bool $finished
     */
    public function __construct(array $data, bool $finished)
    {
        $this->data = $data;
        $this->finished = $finished;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->finished;
    }
}
