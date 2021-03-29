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

    /**
     * @test
     */
    public function createActionThrowsExceptionIfClassDoesNotExist()
    {
        $this->mockObjectManager->expects(self::once())
            ->method('isRegistered')
            ->with('Vendor\Site\Action\ExampleAction')
            ->willReturn(false);

        $this->expectException(NoSuchActionException::class);
        $this->actionResolver->createAction('Vendor\Site\Action\ExampleAction');
    }

    /**
     * @test
     */
    public function createActionThrowsExceptionIfIdentifierCannotBeResolved()
    {
        $this->mockObjectManager->expects(self::exactly(2))
            ->method('isRegistered')
            ->withConsecutive(['Vendor.Site:Example'], ['Vendor\Site\Action\ExampleAction'])
            ->willReturn(false);

        $this->expectException(NoSuchActionException::class);
        $this->actionResolver->createAction('Vendor.Site:Example');
    }

    /**
     * @test
     */
    public function createActionReturnsActionIfIdentifierCanBeResolved()
    {
        $mockAction = $this->createMock(ActionInterface::class);

        $this->mockObjectManager->expects(self::exactly(2))
            ->method('isRegistered')
            ->withConsecutive(['Vendor.Site:Example'], ['Vendor\Site\Action\ExampleAction'])
            ->willReturnOnConsecutiveCalls(false, 'Vendor\Site\Action\ExampleAction');

        $this->mockObjectManager->expects(self::once())
            ->method('get')
            ->with('Vendor\Site\Action\ExampleAction')
            ->willReturn($mockAction);

        $action = $this->actionResolver->createAction('Vendor.Site:Example');
        $this->assertSame($mockAction, $action);
    }

    /**
     * @test
     */
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
