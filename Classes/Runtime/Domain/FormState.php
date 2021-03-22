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

class FormState
{
    /**
     * @var mixed[][]
     */
    protected $parts = [];

    /**
     * FormState constructor.
     * @param mixed[][] $parts
     */
    public function __construct(array $parts = [])
    {
        foreach ($parts as $key => $value) {
            // ensure only strings are used as partNames
            if (is_string($key)) {
                $this->parts[$key] = $value;
            }
        }
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
     * @param mixed[] $partData
     */
    public function commitPart(string $partName, array $partData = []): void
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
     * @return string[]
     */
    public function getCommittedPartNames(): array
    {
        // the filter function ensures that only string keys are returned.
        // while probably obsolete this is needed for the static analysis
        return array_filter(
            array_keys($this->parts),
            function ($item) {
                return is_string($item);
            }
        );
    }

    /**
     * @return mixed[][]
     */
    public function getAllParts(): array
    {
        return $this->parts;
    }
}
