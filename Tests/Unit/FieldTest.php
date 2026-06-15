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

use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Fusion\Form\Domain\Field;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    protected function createField($currentValue, ?string $dateFormat = null): Field
    {
        $field = new Field(null, null, null, false, $dateFormat);

        // inject current value directly via reflection (no form/request needed)
        $reflection = new \ReflectionObject($field);
        $prop = $reflection->getProperty('currentValue');
        $prop->setAccessible(true);
        $prop->setValue($field, $currentValue);

        $persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $persistenceManager->method('getIdentifierByObject')->willReturn(null);
        $prop = $reflection->getProperty('persistenceManager');
        $prop->setAccessible(true);
        $prop->setValue($field, $persistenceManager);

        return $field;
    }

    /**
     * @test
     */
    public function dateTimeIsStringifiedWithDefaultFormat(): void
    {
        $date = new \DateTime('2026-06-15');
        $field = $this->createField($date);

        $this->assertSame('2026-06-15', $field->getCurrentValueStringified());
    }

    /**
     * @test
     */
    public function dateTimeIsStringifiedWithCustomFormat(): void
    {
        $date = new \DateTime('2026-06-15');
        $field = $this->createField($date, 'd.m.Y');

        $this->assertSame('15.06.2026', $field->getCurrentValueStringified());
    }

    /**
     * @test
     */
    public function dateTimeImmutableIsStringifiedWithDefaultFormat(): void
    {
        $date = new \DateTimeImmutable('2026-06-15');
        $field = $this->createField($date);

        $this->assertSame('2026-06-15', $field->getCurrentValueStringified());
    }

    /**
     * @test
     */
    public function dateTimeImmutableIsStringifiedWithCustomFormat(): void
    {
        $date = new \DateTimeImmutable('2026-06-15');
        $field = $this->createField($date, 'd.m.Y');

        $this->assertSame('15.06.2026', $field->getCurrentValueStringified());
    }

    /**
     * @test
     */
    public function arrayOfDateTimesIsStringifiedWithDefaultFormat(): void
    {
        $dates = [new \DateTime('2026-06-15'), new \DateTime('2026-12-31')];
        $field = $this->createField($dates);

        $this->assertSame(['2026-06-15', '2026-12-31'], $field->getCurrentMultivalueStringified());
    }

    /**
     * @test
     */
    public function arrayOfDateTimesIsStringifiedWithCustomFormat(): void
    {
        $dates = [new \DateTime('2026-06-15'), new \DateTime('2026-12-31')];
        $field = $this->createField($dates, 'd.m.Y');

        $this->assertSame(['15.06.2026', '31.12.2026'], $field->getCurrentMultivalueStringified());
    }

    /**
     * @test
     */
    public function stringValueIsPassedThroughUnchanged(): void
    {
        $field = $this->createField('hello');

        $this->assertSame('hello', $field->getCurrentValueStringified());
    }

    /**
     * @test
     */
    public function nullValueIsStringifiedToEmptyString(): void
    {
        $field = $this->createField(null);

        $this->assertSame('', $field->getCurrentValueStringified());
    }

    /**
     * @test
     */
    public function withDateFormatReturnsCopyWithNewFormat(): void
    {
        $date = new \DateTime('2026-06-15');
        $field = $this->createField($date, 'Y-m-d');
        $copy = $field->withDateFormat('d.m.Y');

        $this->assertNotSame($field, $copy);
        $this->assertSame('2026-06-15', $field->getCurrentValueStringified());
        $this->assertSame('15.06.2026', $copy->getCurrentValueStringified());
    }

    /**
     * @test
     */
    public function withDateFormatWithNullRemovesFormat(): void
    {
        $date = new \DateTime('2026-06-15');
        $field = $this->createField($date, 'd.m.Y');
        $copy = $field->withDateFormat(null);

        $this->assertSame('2026-06-15', $copy->getCurrentValueStringified());
    }
}
