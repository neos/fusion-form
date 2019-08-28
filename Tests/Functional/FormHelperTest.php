<?php
namespace Neos\Fusion\Form\Tests\Functional;

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

        $result = $this->formHelper->calculateHiddenFields(null, null);

        $expectation = ['__trustedProperties' => 'bar', '__csrfToken' => 'foo'];
        $this->assertEquals($expectation, $result);
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

        $result = $this->formHelper->calculateHiddenFields($form, null);

        $this->assertEquals('Vendor.Example', $result['__referrer[@package]']);
        $this->assertEquals('Application', $result['__referrer[@subpackage]']);
        $this->assertEquals('Main', $result['__referrer[@controller]']);
        $this->assertEquals('List', $result['__referrer[@action]']);
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
        $parentRequest->method('getControllerActionName')->willReturn('Somthing');
        $parentRequest->method('isMainRequest')->willReturn(true);

        $request = $this->getMockBuilder(ActionRequest::class)->disableOriginalConstructor()->getMock();
        $request->method('getControllerPackageKey')->willReturn('Vendor.Bar');
        $request->method('getControllerSubpackageKey')->willReturn('');
        $request->method('getControllerName')->willReturn('Child');
        $request->method('getControllerActionName')->willReturn('SomthingElse');
        $request->method('getArgumentNamespace')->willReturn('childNamespace');
        $request->method('isMainRequest')->willReturn(false);
        $request->method('getParentRequest')->willReturn($parentRequest);

        $form = new FormDefinition($request);

        $result = $this->formHelper->calculateHiddenFields($form, null);

        $this->assertEquals('Vendor.Foo', $result['__referrer[@package]']);
        $this->assertEquals('Application', $result['__referrer[@subpackage]']);
        $this->assertEquals('Parent', $result['__referrer[@controller]']);
        $this->assertEquals('Somthing', $result['__referrer[@action]']);

        $this->assertEquals('Vendor.Bar', $result['childNamespace[__referrer][@package]']);
        $this->assertEquals('', $result['childNamespace[__referrer][@subpackage]']);
        $this->assertEquals('Child', $result['childNamespace[__referrer][@controller]']);
        $this->assertEquals('SomthingElse', $result['childNamespace[__referrer][@action]']);
    }
}
