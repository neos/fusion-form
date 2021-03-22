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
use Neos\Flow\Mvc\ActionRequest;
use Neos\Fusion\Form\Domain\Form;

interface ProcessInterface
{
    /**
     * @param ActionRequest $request
     * @param mixed[] $data
     */
    public function handle(ActionRequest $request, array $data = []): void;

    /**
     * @return bool
     */
    public function isFinished(): bool;

    /**
     * @return string
     */
    public function render(): string;

    /**
     * @return mixed[]
     */
    public function getData(): array;
}
