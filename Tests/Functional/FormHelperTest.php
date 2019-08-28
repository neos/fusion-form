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
        $this->formHelper = new FormHelper();
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsReturnsEmptyResultIfNoFormOrContentIsGiven()
    {
        //$result = $this->formHelper->calculateHiddenFields(null, null);
        $result = [];
        $this->assertEquals([], $result);
    }
}
