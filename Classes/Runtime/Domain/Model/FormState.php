<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Domain\Model;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

class FormState
{
    /**
     * @var mixed[][]|null
     */
    protected $parts = [];

    /**
     * FormState constructor.
     * @param mixed[][] $parts
     */
    public function __construct(array $parts = [])
    {
        $this->parts = $parts;
    }

    /**
     * @param string $partName
     * @return bool
     */
    public function hasPart(string $partName): bool
    {
        return array_key_exists($partName, $this->parts);
    }

    /**
     * @param string $partName
     * @param mixed[]|null $partData
     */
    public function commitPart(string $partName, ?array $partData): void
    {
        $this->parts[$partName] = $partData;
    }

    /**
     * @param string $partName
     * @return mixed[]|null
     */
    public function getPart(string $partName): ?array
    {
        return $this->parts[$partName] ?? null;
    }

    /**
     * @return mixed[][]
     */
    public function getAll(): array
    {
        return $this->parts;
    }
}
