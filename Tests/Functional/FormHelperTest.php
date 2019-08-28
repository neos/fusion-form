<?php
namespace Neos\Fusion\Form\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Neos\Fusion\Form\Domain\Model\FormDefinition;
use Neos\Fusion\Form\Domain\Model\FieldDefinition;
use Neos\Fusion\Form\Eel\FormHelper;

class FormHelperTest extends TestCase
{
    /**
     * @var FormHelper
     */
    protected $formHelper;

    public function setUp()
    {
        $this->formHelper = $this->getMockBuilder(FormHelper::class)
            ->setMethods(['getTrustedPropertiesToken', 'getCsrfProtectionToken', 'getArgumentsWithHmac'])
            ->getMock();
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsReturnsOnlyCsrfAndTrustedPropertiesTokenIfNoFormOrContentIsGiven()
    {
        $this->formHelper->expects($this->once())
            ->method('getCsrfProtectionToken')
            ->with()
            ->willReturn('foo');

        $this->formHelper->expects($this->once())
            ->method('getTrustedPropertiesToken')
            ->with([])
            ->willReturn('bar');

        $result = $this->formHelper->calculateHiddenFields(null, null);

        $expectation = ['__trustedProperties' => 'bar', '__csrfToken' => 'foo'];
        $this->assertEquals($expectation, $result);
    }
}
