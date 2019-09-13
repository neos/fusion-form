<?php
namespace Neos\Fusion\Form\Tests\Functional;

use phpDocumentor\Reflection\Types\Object_;
use PHPUnit\Framework\TestCase;
use Neos\Fusion\Form\Domain\Model\FormDefinition;
use Neos\Fusion\Form\Domain\Model\FieldDefinition;
use Neos\Fusion\Form\Eel\FormHelper;

use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Flow\Security\Cryptography\HashService;
use Neos\Flow\Mvc\Controller\MvcPropertyMappingConfigurationService;

use Neos\Flow\Http\Request as HttpRequest;
use Neos\Flow\Mvc\ActionRequest;

class FormHelperTest extends TestCase
{
    /**
     * @var FormHelper
     */
    protected $formHelper;

    protected $persistenceManager;
    protected $securityContext;
    protected $hashService;
    protected $mvcPropertyMappingConfigurationService;

    public function setUp(): void
    {
        $formHelper = new FormHelper();

        $this->persistenceManager = $this->createMock(PersistenceManagerInterface::class);
        $this->securityContext = $this->createMock(SecurityContext::class);
        $this->hashService = $this->createMock(HashService::class);
        $this->mvcPropertyMappingConfigurationService = $this->createMock(MvcPropertyMappingConfigurationService::class);

        $this->injectDependency($formHelper, 'persistenceManager', $this->persistenceManager);
        $this->injectDependency($formHelper, 'securityContext', $this->securityContext);
        $this->injectDependency($formHelper, 'hashService', $this->hashService);
        $this->injectDependency($formHelper, 'mvcPropertyMappingConfigurationService', $this->mvcPropertyMappingConfigurationService);

        $this->formHelper = $formHelper;
    }

    /**
     * Injects $dependency into property $name of $target
     *
     * @param object $target The instance which needs the dependency
     * @param string $name Name of the property to be injected
     * @param mixed $dependency The dependency to inject – usually an object but can also be any other type
     * @return void
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function injectDependency($target, $name, $dependency)
    {
        if (!is_object($target)) {
            throw new \InvalidArgumentException('Wrong type for argument $target, must be object.');
        }

        $objectReflection = new \ReflectionObject($target);
        if ($objectReflection->hasProperty($name)) {
            $property = $objectReflection->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($target, $dependency);
        } else {
            throw new \RuntimeException('Could not inject ' . $name . ' into object of type ' . get_class($target));
        }
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsReturnsOnlyCsrfAndTrustedPropertiesTokenIfNoFormOrContentIsGiven()
    {
        $this->securityContext->expects($this->once())
            ->method('getCsrfProtectionToken')
            ->with()
            ->willReturn('foo');

        $this->mvcPropertyMappingConfigurationService->expects($this->once())
            ->method('generateTrustedPropertiesToken')
            ->with([])
            ->willReturn('bar');

        $hiddenFields = $this->formHelper->calculateHiddenFields(null, null);

        $expectation = ['__trustedProperties' => 'bar', '__csrfToken' => 'foo'];
        $this->assertEquals($expectation, $hiddenFields);
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsCreatesTrustedPropertiesForAllFieldsInContent()
    {
        $content = <<<CONTENT
            <input name="foo" />
            <input name="bar[baz]" />
CONTENT;

        $this->mvcPropertyMappingConfigurationService
            ->expects($this->once())
            ->method('generateTrustedPropertiesToken')
            ->with(['foo', 'bar[baz]'])
            ->willReturn('--example--');

        $hiddenFields = $this->formHelper->calculateHiddenFields(null, $content);

        $this->assertEquals($hiddenFields['__trustedProperties'], '--example--');
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsCreatesTrustedPropertiesForAllFieldsWithFieldnamePrefix()
    {
        $form = new FormDefinition(null, null, 'prefix');

        $content = <<<CONTENT
            <input name="prefix[foo]" />
            <input name="prefix[bar][baz]" />
            <input name="different" />
CONTENT;

        $this->mvcPropertyMappingConfigurationService
            ->expects($this->once())
            ->method('generateTrustedPropertiesToken')
            ->with(['prefix[foo]', 'prefix[bar][baz]'], 'prefix')
            ->willReturn('--example--');

        $hiddenFields = $this->formHelper->calculateHiddenFields($form, $content);

        $this->assertEquals($hiddenFields['prefix[__trustedProperties]'], '--example--');
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsAddsReferrerFieldsIfFormWithActionRequestIsGiven()
    {
        $request = $this->getMockBuilder(ActionRequest::class)->disableOriginalConstructor()->getMock();
        $request->method('getControllerPackageKey')->willReturn('Vendor.Example');
        $request->method('getControllerSubpackageKey')->willReturn('Application');
        $request->method('getControllerName')->willReturn('Main');
        $request->method('getControllerActionName')->willReturn('List');
        $request->method('isMainRequest')->willReturn(true);
        $request->method('getArguments')->willReturn([]);
        $request->method('getArgumentNamespace')->willReturn('');

        $form = new FormDefinition($request);

        $hiddenFields = $this->formHelper->calculateHiddenFields($form, null);

        $this->assertEquals('Vendor.Example', $hiddenFields['__referrer[@package]']);
        $this->assertEquals('Application', $hiddenFields['__referrer[@subpackage]']);
        $this->assertEquals('Main', $hiddenFields['__referrer[@controller]']);
        $this->assertEquals('List', $hiddenFields['__referrer[@action]']);
        $this->assertArrayNotHasKey('__referrer[arguments]', $hiddenFields);
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsAddsReferrerFieldsIfFormWithNestedActionRequestIsGiven()
    {
        $parentRequest = $this->getMockBuilder(ActionRequest::class)->disableOriginalConstructor()->getMock();
        $parentRequest->method('getControllerPackageKey')->willReturn('Vendor.Foo');
        $parentRequest->method('getControllerSubpackageKey')->willReturn('Application');
        $parentRequest->method('getControllerName')->willReturn('Parent');
        $parentRequest->method('getControllerActionName')->willReturn('Something');
        $parentRequest->method('isMainRequest')->willReturn(true);

        $request = $this->getMockBuilder(ActionRequest::class)->disableOriginalConstructor()->getMock();
        $request->method('getControllerPackageKey')->willReturn('Vendor.Bar');
        $request->method('getControllerSubpackageKey')->willReturn('');
        $request->method('getControllerName')->willReturn('Child');
        $request->method('getControllerActionName')->willReturn('SomethingElse');
        $request->method('getArgumentNamespace')->willReturn('childNamespace');
        $request->method('isMainRequest')->willReturn(false);
        $request->method('getParentRequest')->willReturn($parentRequest);

        $form = new FormDefinition($request);

        $hiddenFields = $this->formHelper->calculateHiddenFields($form, null);

        $this->assertEquals('Vendor.Foo', $hiddenFields['__referrer[@package]']);
        $this->assertEquals('Application', $hiddenFields['__referrer[@subpackage]']);
        $this->assertEquals('Parent', $hiddenFields['__referrer[@controller]']);
        $this->assertEquals('Something', $hiddenFields['__referrer[@action]']);

        $this->assertEquals('Vendor.Bar', $hiddenFields['childNamespace[__referrer][@package]']);
        $this->assertEquals('', $hiddenFields['childNamespace[__referrer][@subpackage]']);
        $this->assertEquals('Child', $hiddenFields['childNamespace[__referrer][@controller]']);
        $this->assertEquals('SomethingElse', $hiddenFields['childNamespace[__referrer][@action]']);

        $this->assertArrayNotHasKey('__referrer[arguments]', $hiddenFields);
        $this->assertArrayNotHasKey('childNamespace[__referrer][arguments]', $hiddenFields);
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsAddsReferrerFieldArgumentsIfFormWithNestedActionRequestIsGiven()
    {
        $childRequestArguments = ['foo' => 456, 'bar' => 'another string'];
        $parentRequestArguments = ['foo' => 123, 'bar' => 'string'];
        $parentWithChildRequestArguments = array_merge($parentRequestArguments, ['childNamespace' => $childRequestArguments]);

        $parentRequest = $this->getMockBuilder(ActionRequest::class)->disableOriginalConstructor()->getMock();
        $parentRequest->method('getControllerPackageKey')->willReturn('Vendor.Foo');
        $parentRequest->method('getControllerSubpackageKey')->willReturn('Application');
        $parentRequest->method('getControllerName')->willReturn('Parent');
        $parentRequest->method('getControllerActionName')->willReturn('Something');
        $parentRequest->method('getArguments')->willReturn($parentWithChildRequestArguments);
        $parentRequest->method('getArgumentNamespace')->willReturn('');
        $parentRequest->method('isMainRequest')->willReturn(true);

        $request = $this->getMockBuilder(ActionRequest::class)->disableOriginalConstructor()->getMock();
        $request->method('getControllerPackageKey')->willReturn('Vendor.Bar');
        $request->method('getControllerSubpackageKey')->willReturn('');
        $request->method('getControllerName')->willReturn('Child');
        $request->method('getControllerActionName')->willReturn('SomethingElse');
        $request->method('getArguments')->willReturn($childRequestArguments);
        $request->method('getArgumentNamespace')->willReturn('childNamespace');
        $request->method('isMainRequest')->willReturn(false);
        $request->method('getParentRequest')->willReturn($parentRequest);

        // only arguments in each requests namespace are passed to the hashing service
        // so for the parent request the child request namespace is excluded
        $this->hashService
            ->method('appendHmac')
            ->withConsecutive(
                [base64_encode(serialize($childRequestArguments))],
                [base64_encode(serialize($parentRequestArguments))]
            )
            ->willReturn('--argumentsWithHmac--');

        $form = new FormDefinition($request);
        $hiddenFields = $this->formHelper->calculateHiddenFields($form, null);

        $this->assertEquals('--argumentsWithHmac--', $hiddenFields['__referrer[arguments]']);
        $this->assertEquals('--argumentsWithHmac--', $hiddenFields['childNamespace[__referrer][arguments]']);
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsAddsEmptyFieldsForCheckboxesAndMultipleSelect()
    {
        $content = <<<CONTENT
            <select name="select[multiple][]" multiple></select>
            <input name="input[checkbox]" type="checkbox" value="foo" />
            <input name="input[checkbox]" type="checkbox" value="bar" />
            <input name="input[checkbox]" type="checkbox" value="baz" />   
            <input name="input[checkboxMultiple][]" type="checkbox" value="foo" />
            <input name="input[checkboxMultiple][]" type="checkbox" value="bar" />
            <input name="input[checkboxMultiple][]" type="checkbox" value="baz" />
CONTENT;

        $hiddenFields = $this->formHelper->calculateHiddenFields(null, $content);

        $this->assertEquals("", $hiddenFields['select[multiple]']);
        $this->assertEquals("", $hiddenFields['input[checkbox]']);
        $this->assertEquals("", $hiddenFields['input[checkboxMultiple]']);
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsDoesNotAddsEmptyFieldsForOtherFormControls()
    {
        $content = <<<CONTENT
            <select name="select[single]"></select>
            <input name="input[text]" type="text" />            
            <input name="input[radio]" type="radio" value="foo" />
            <input name="input[radio]" type="radio" value="bar" />
            <input name="input[radio]" type="radio" value="baz" /> 
CONTENT;

        $hiddenFields = $this->formHelper->calculateHiddenFields(null, $content);

        $this->assertArrayNotHasKey('select[single]', $hiddenFields);
        $this->assertArrayNotHasKey('input[text]', $hiddenFields);
        $this->assertArrayNotHasKey('input[radio]', $hiddenFields);
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsAddsIdentityFieldsForPersistedObjectsInFormData()
    {
        $object1 = (object) ['id' => 12345, 'isNew' => false];
        $object2 = (object) ['id' => 56789, 'isNew' => false];

        $this->persistenceManager->method('isNewObject')
            ->will($this->returnCallback(function ($item) {
                return $item->isNew;
            }));
        $this->persistenceManager->method('getIdentifierByObject')
            ->will($this->returnCallback(function ($item) {
                return $item->id;
            }));

        $data = ['item1' => $object1, 'item2' => $object2];

        $form = new FormDefinition(null, $data, '');

        $content = <<<CONTENT
            <input name="item1[text]" type="text" />
            <input name="item2[text]" type="text" />
CONTENT;

        $hiddenFields = $this->formHelper->calculateHiddenFields($form, $content);

        $this->assertEquals("12345", $hiddenFields['item1[__identity]']);
        $this->assertEquals("56789", $hiddenFields['item2[__identity]']);
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsAddsIdentityIgnoresUnusedObjectsInFormData()
    {
        $object1 = (object) ['id' => "12345", 'isNew' => false];
        $object2 = (object) ['id' => "56789", 'isNew' => false];

        $this->persistenceManager->method('isNewObject')
            ->will($this->returnCallback(function ($item) {
                return $item->isNew;
            }));
        $this->persistenceManager->method('getIdentifierByObject')
            ->will($this->returnCallback(function ($item) {
                return $item->id;
            }));

        $data = ['item1' => $object1, 'item2' => $object2];

        $form = new FormDefinition(null, $data, '');
        $content = <<<CONTENT
            <input name="item1[text]" type="text" />
            <input name="item3[text]" type="text" />
CONTENT;

        $hiddenFields = $this->formHelper->calculateHiddenFields($form, $content);

        $this->assertEquals("12345", $hiddenFields['item1[__identity]']);
        $this->assertArrayNotHasKey('item2[__identity]', $hiddenFields);
        $this->assertArrayNotHasKey('item3[__identity]', $hiddenFields);
    }

    /**
     * @test
     */
    public function calculateHiddenFieldsAddsIdentityFieldsForNewObjectsInFormData()
    {
        $object1 = (object) ['id' => 12345, 'isNew' => true];
        $object2 = (object) ['id' => 56789, 'isNew' => true];

        $this->persistenceManager->method('isNewObject')
            ->will($this->returnCallback(function ($item) {
                return $item->isNew;
            }));
        $this->persistenceManager->method('getIdentifierByObject')
            ->will($this->returnCallback(function ($item) {
                return $item->id;
            }));

        $data = ['item1' => $object1, 'item2' => $object2];

        $form = new FormDefinition(null, $data, '');

        $content = <<<CONTENT
            <input name="item1[text]" type="text" />
            <input name="item2[text]" type="text" />
CONTENT;

        $hiddenFields = $this->formHelper->calculateHiddenFields($form, $content);

        $this->assertArrayNotHasKey('item1[__identity]', $hiddenFields);
        $this->assertArrayNotHasKey('item2[__identity]', $hiddenFields);
    }
}
