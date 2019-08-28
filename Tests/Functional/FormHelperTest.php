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
}
