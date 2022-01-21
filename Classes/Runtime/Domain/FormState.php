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

use Neos\Utility\Arrays;

class FormState implements \JsonSerializable
{
    /**
     * @var FormStatePart[]
     */
    protected $parts = [];

    /**
     * @param string $partName
     * @param null|bool $finished
     * @return bool
     */
    public function hasPart(string $partName, ?bool $finished = null): bool
    {
        if (is_null($finished)) {
            return array_key_exists($partName, $this->parts);
        } else {
            return array_key_exists($partName, $this->parts) && ($this->parts[$partName]->isFinished() === $finished);
        }
    }

    /**
     * @param string $partName
     * @param mixed[] $partData
     * @param bool $finished
     */
    public function commitPart(string $partName, array $partData = [], bool $finished = false): void
    {
        $part = new FormStatePart($partData, $finished);
        $this->parts[$partName] = $part;
    }

    /**
     * @param string $partName
     * @return mixed[]|null
     */
    public function getPart(string $partName): ?array
    {
        if (array_key_exists($partName, $this->parts)) {
            return $this->parts[$partName]->getData();
        }
        return null;
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
     * @param boolean $finished
     * @return FormStatePart[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        $data = [];
        foreach ($this->parts as $part) {
            $data = Arrays::arrayMergeRecursiveOverrule(
                $data,
                $part->getData()
            );
        }
        return $data;
    }

    public function jsonSerialize()
    {
        $json = [];
        foreach ($this->parts as $name => $part) {
            $json[$name] = [
                'data' => $part->getData(),
                'finished' => $part->isFinished()
            ];
        }
        return $json;
    }
}
