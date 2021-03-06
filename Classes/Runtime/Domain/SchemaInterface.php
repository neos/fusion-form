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

use Neos\Error\Messages\Result;

interface SchemaInterface
{
    /**
     * @param mixed[] $data
     * @return Result
     */
    public function validate($data): Result;

    /**
     * @param mixed[] $data
     * @return mixed
     */
    public function convert($data);
}
