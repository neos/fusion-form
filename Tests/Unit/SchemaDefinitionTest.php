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

use Neos\Flow\Property\TypeConverter\DateTimeConverter;
use Neos\Fusion\Form\Runtime\Helper\SchemaDefinition;
use PHPUnit\Framework\TestCase;

class SchemaDefinitionTest extends TestCase
{
    /**
     * @test
     */
    public function getDateFormatReturnsNullForStringSchema(): void
    {
        $schema = new SchemaDefinition('string');
        $this->assertNull($schema->getDateFormat());
    }

    /**
     * @test
     */
    public function getDateFormatReturnsNullWhenNoDateTimeConverterOption(): void
    {
        $schema = new SchemaDefinition(\DateTime::class);
        $this->assertNull($schema->getDateFormat());
    }

    /**
     * @test
     */
    public function getDateFormatReturnsConfiguredFormat(): void
    {
        $schema = new SchemaDefinition(\DateTime::class);
        $schema->typeConverterOption(
            DateTimeConverter::class,
            DateTimeConverter::CONFIGURATION_DATE_FORMAT,
            'd.m.Y'
        );
        $this->assertSame('d.m.Y', $schema->getDateFormat());
    }

    /**
     * @test
     */
    public function getDateFormatReturnsDefaultYmdFormat(): void
    {
        $schema = new SchemaDefinition(\DateTime::class);
        $schema->typeConverterOption(
            DateTimeConverter::class,
            DateTimeConverter::CONFIGURATION_DATE_FORMAT,
            'Y-m-d'
        );
        $this->assertSame('Y-m-d', $schema->getDateFormat());
    }

    /**
     * @test
     */
    public function getDateFormatIgnoresOtherTypeConverterOptions(): void
    {
        $schema = new SchemaDefinition(\DateTime::class);
        $schema->typeConverterOption('SomeOtherConverter', 'someOption', 'someValue');
        $this->assertNull($schema->getDateFormat());
    }
}
