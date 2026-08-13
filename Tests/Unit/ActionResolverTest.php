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
use PHPUnit\Framework\Attributes\Test;
use Neos\Fusion\Form\Runtime\Domain\ActionInterface;
use PHPUnit\Framework\TestCase;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Fusion\Form\Runtime\Domain\ActionResolver;
use Neos\Fusion\Form\Runtime\Domain\Exception\NoSuchActionException;

/**
 * Testcase for the action resolver
 *
 */
class ActionResolverTest extends TestCase
{
    /**
     * @var ActionResolver
     */
    protected $actionResolver;

    /**
     * @var ObjectManagerInterface
     */
    protected $mockObjectManager;

    protected function setUp(): void
    {
        $this->actionResolver = new ActionResolver();

        $this->mockObjectManager = $this->createMock(ObjectManagerInterface::class);

        $reflection = new \ReflectionClass($this->actionResolver);
        $reflection_property = $reflection->getProperty('objectManager');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->actionResolver, $this->mockObjectManager);
    }

    #[Test]
    public function createActionThrowsExceptionIfClassDoesNotExist()
    {
        $this->mockObjectManager->expects(self::once())
            ->method('isRegistered')
            ->with('Vendor\Site\Action\ExampleAction')
            ->willReturn(false);

        $this->expectException(NoSuchActionException::class);
        $this->actionResolver->createAction('Vendor\Site\Action\ExampleAction');
    }

    #[Test]
    public function createActionThrowsExceptionIfIdentifierCannotBeResolved()
    {
        $matcher = self::exactly(2);
        $this->mockObjectManager->expects($matcher)
            ->method('isRegistered')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('Vendor.Site:Example', $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('Vendor\Site\Action\ExampleAction', $parameters[0]);
                }
                return false;
            });

        $this->expectException(NoSuchActionException::class);
        $this->actionResolver->createAction('Vendor.Site:Example');
    }

    #[Test]
    public function createActionReturnsActionIfIdentifierCanBeResolved()
    {
        $mockAction = $this->createMock(ActionInterface::class);
        $matcher = self::exactly(2);

        $this->mockObjectManager->expects($matcher)
            ->method('isRegistered')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('Vendor.Site:Example', $parameters[0]);
                    return false;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('Vendor\Site\Action\ExampleAction', $parameters[0]);
                    return 'Vendor\Site\Action\ExampleAction';
                }
            });

        $this->mockObjectManager->expects(self::once())
            ->method('get')
            ->with('Vendor\Site\Action\ExampleAction')
            ->willReturn($mockAction);

        $action = $this->actionResolver->createAction('Vendor.Site:Example');
        $this->assertSame($mockAction, $action);
    }

    #[Test]
    public function createActionReturnsActionIfActionClassExists()
    {
        $mockAction = $this->createMock(ActionInterface::class);

        $this->mockObjectManager->expects(self::once())
            ->method('isRegistered')
            ->with('Vendor\Site\Action\ExampleAction')
            ->willReturn('Vendor\Site\Action\ExampleAction');

        $this->mockObjectManager->expects(self::once())
            ->method('get')
            ->with('Vendor\Site\Action\ExampleAction')
            ->willReturn($mockAction);

        $action = $this->actionResolver->createAction('Vendor\Site\Action\ExampleAction');
        $this->assertSame($mockAction, $action);
    }
}
