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
        $this->assertEmpty($state->getAll());
        $this->assertFalse($state->hasPart('example'));
        $this->assertNull($state->getPart('example'));
        $this->assertEquals([], $state->getCommittedPartNames());
    }

    /**
     * @test
     */
    public function partsCanBeAcessedAfterBeingComitted()
    {
        $state = new FormState();
        $state->commitPart('example', ['value' => 'exampleValue']);
        $this->assertTrue($state->hasPart('example'));
        $this->assertEquals(['value' => 'exampleValue'], $state->getPart('example'));
        $this->assertEquals(['example' => ['value' => 'exampleValue']], $state->getAll());
        $this->assertEquals(['example'], $state->getCommittedPartNames());
    }

    /**
     * @test
     */
    public function initialPartsCanBeAcessed()
    {
        $state = new FormState(['example' => ['value' => 'exampleValue']]);
        $this->assertTrue($state->hasPart('example'));
        $this->assertEquals(['value' => 'exampleValue'], $state->getPart('example'));
        $this->assertEquals(['example' => ['value' => 'exampleValue']], $state->getAll());
        $this->assertEquals(['example'], $state->getCommittedPartNames());
    }

    /**
     * @test
     */
    public function comittedPartsOverwriteExistingPartsWithSameName()
    {
        $state = new FormState(['example' => ['value' => 'exampleValue']]);
        $state->commitPart('example', ['another' => 'exampleValue']);
        $this->assertTrue($state->hasPart('example'));
        $this->assertEquals(['another' => 'exampleValue'], $state->getPart('example'));
        $this->assertEquals(['example' => ['another' => 'exampleValue']], $state->getAll());
        $this->assertEquals(['example'], $state->getCommittedPartNames());
    }

    /**
     * @test
     */
    public function comittedPartsAreAddedIfNamesAreDifferent()
    {
        $state = new FormState(['example1' => ['value' => 'exampleValue']]);
        $state->commitPart('example2', ['another' => 'exampleValue']);
        $this->assertTrue($state->hasPart('example1'));
        $this->assertTrue($state->hasPart('example2'));
        $this->assertEquals(['value' => 'exampleValue'], $state->getPart('example1'));
        $this->assertEquals(['another' => 'exampleValue'], $state->getPart('example2'));
        $this->assertEquals(['example1', 'example2'], $state->getCommittedPartNames());
    }
}
