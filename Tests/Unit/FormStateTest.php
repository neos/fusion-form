<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Tests\Unit;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Fusion\Form\Runtime\Domain\FormState;
use PHPUnit\Framework\TestCase;

class FormStateTest extends TestCase
{
    /**
     * @var FormState
     */
    protected $formState;

    public function setUp(): void
    {
        $this->formState = new FormState();
    }

    /**
     * @test
     */
    public function emptyStateHasNoParts()
    {
        $state = new FormState();
        $this->assertFalse($state->hasPart('example'));
        $this->assertNull($state->getPartData('example'));
        $this->assertEquals([], $state->getCommittedPartNames());
    }

    public function partsCanBeAcessedAfterBeingComittedDataProvider(): array
    {
        return [
            ['example1', ['value1' => 'exampleValue1'], true],
            ['example2', ['value2' => 'exampleValue2'], false],
        ];
    }

    /**
     * @test
     * @dataProvider partsCanBeAcessedAfterBeingComittedDataProvider
     */
    public function partsCanBeAcessedAfterBeingComitted($name, $data, $finished)
    {
        $state = new FormState();
        $state->commitPart($name, $data, $finished);
        $this->assertTrue($state->hasPart($name));
        $this->assertEquals($data, $state->getPartData($name));
        $this->assertEquals($finished, $state->isPartFinished($name));
        $this->assertEquals([$name], $state->getCommittedPartNames());
    }

    public function comittedPartsOverwriteExistingPartsWithSameNameDataProvider(): array
    {
        return [
            [['value1' => 'exampleValue1'], true, ['value1' => 'exampleValue1'], false],
            [['value2' => 'exampleValue2'], false, ['value2' => 'exampleValue2'], true ],
            [['value1' => 'exampleValue2'], true, [], false ],
            [[], false, ['value1' => 'exampleValue2'], true ],
        ];
    }


    /**
     * @test
     * @dataProvider comittedPartsOverwriteExistingPartsWithSameNameDataProvider
     */
    public function comittedPartsOverwriteExistingPartsWithSameName($dataOriginal, $finishedOriginal, $dataAfter, $finishedAfter)
    {
        $state = new FormState();
        $state->commitPart('example', $dataOriginal, $finishedOriginal);
        $state->commitPart('example', $dataAfter, $finishedAfter);

        $this->assertTrue($state->hasPart('example'));
        $this->assertEquals($dataAfter, $state->getPartData('example'));
        $this->assertEquals($finishedAfter, $state->isPartFinished('example'));
    }

    /**
     * @test
     */
    public function comittedPartsAreAddedIfNamesAreDifferent()
    {
        $state = new FormState();
        $state->commitPart('example1', ['value' => 'exampleValue']);
        $this->assertEquals(['example1'], $state->getCommittedPartNames());
        $this->assertTrue($state->hasPart('example1'));
        $this->assertFalse($state->hasPart('example2'));

        $state->commitPart('example2', ['another' => 'exampleValue']);
        $this->assertTrue($state->hasPart('example1'));
        $this->assertTrue($state->hasPart('example2'));
        $this->assertEquals(['value' => 'exampleValue'], $state->getPartData('example1'));
        $this->assertEquals(['another' => 'exampleValue'], $state->getPartData('example2'));
        $this->assertEquals(['example1', 'example2'], $state->getCommittedPartNames());
    }
}
