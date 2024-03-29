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

use PHPUnit\Framework\TestCase;
use Neos\Flow\Security\Cryptography\HashService;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Fusion\Form\Runtime\Domain\FormRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use Neos\Flow\Security\Exception\InvalidHashException;

class FormRequestFactoryTest extends TestCase
{
    /**
     * @var FormRequestFactory
     */
    protected $formRequestFactory;

    /**
     * @var HashService
     */
    protected $mockHashService;

    /**
     * @var ServerRequestInterface
     */
    protected $mockHttpRequest;

    public function setUp(): void
    {
        $this->formRequestFactory = new FormRequestFactory();
        $this->mockHashService = $this->createMock(HashService::class);
        $this->mockHttpRequest = $this->createMock(ServerRequestInterface::class);

        $reflection = new \ReflectionClass($this->formRequestFactory);
        $reflection_property = $reflection->getProperty('hashService');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->formRequestFactory, $this->mockHashService);
    }

    /**
     * @test
     */
    public function formRequestIsEmptyIfNothingWasSubmitted()
    {
        $identifier = 'example';

        $actionRequest = ActionRequest::fromHttpRequest($this->mockHttpRequest);

        $formRequest = $this->formRequestFactory->createFormRequest($actionRequest, $identifier);

        $this->assertInstanceOf(ActionRequest::class, $formRequest);
        $this->assertEquals($identifier, $formRequest->getArgumentNamespace());
        $this->assertEquals($actionRequest, $formRequest->getParentRequest());
        $this->assertEquals($this->mockHttpRequest, $formRequest->getHttpRequest());
        $this->assertEmpty($formRequest->getInternalArguments());
        $this->assertEmpty($formRequest->getArguments());
    }

    /**
     * @test
     */
    public function formRequestContainsArgumentsWithTrustedProperties()
    {
        $identifier = 'example';
        $trustedProperties = '__trusted_properties__';

        $actionRequest = ActionRequest::fromHttpRequest($this->mockHttpRequest);
        $actionRequest->setArguments([
            $identifier => [
                'trusted' => 'trustedValue_0',
                'notTrusted' => 'notTrustedValue_0',
                'nested_1' => [
                    'trusted' => 'trustedValue_1',
                    'notTrusted' => 'notTrustedValue_1',
                    'nested_2' => [
                        'trusted' => 'trustedValue_2',
                        'notTrusted' => 'notTrusted_2'
                    ]
                ],
                '__trustedProperties' => $trustedProperties
            ]
        ]);

        $this->mockHashService->expects(self::once())
            ->method('validateAndStripHmac')
            ->with($trustedProperties)
            ->willReturn(serialize(
                [
                    'trusted' => 1,
                    'nested_1' => [
                        'trusted' => 1,
                        'nested_2' => [
                            'trusted' => 1
                        ]
                    ]
                ]
            ));

        $formRequest = $this->formRequestFactory->createFormRequest($actionRequest, $identifier);

        $this->assertInstanceOf(ActionRequest::class, $formRequest);
        $this->assertEquals($identifier, $formRequest->getArgumentNamespace());
        $this->assertEquals($actionRequest, $formRequest->getParentRequest());
        $this->assertEquals($this->mockHttpRequest, $formRequest->getHttpRequest());

        $arguments = $formRequest->getArguments();

        $this->assertSame('trustedValue_0', $arguments['trusted']);
        $this->assertSame('trustedValue_1', $arguments['nested_1']['trusted']);
        $this->assertSame('trustedValue_2', $arguments['nested_1']['nested_2']['trusted']);

        $this->assertNull($arguments['notTrusted'] ?? null);
        $this->assertNull($arguments['nested_1']['notTrusted'] ?? null);
        $this->assertNull($arguments['nested_1']['nested_2']['notTrusted'] ?? null);

        $this->assertEmpty($formRequest->getInternalArguments());
    }

    /**
     * @test
     */
    public function formRequestContainsInternalArgumentsWithTrustedProperties()
    {
        $identifier = 'example';
        $trustedProperties = '__trusted_properties__';

        $actionRequest = ActionRequest::fromHttpRequest($this->mockHttpRequest);
        $actionRequest->setArguments([
            $identifier => [
                '__trusted' => 'trustedValue',
                '__notTrusted' => 'notTrustedValue',
                '__trustedProperties' => $trustedProperties
            ]
        ]);

        $this->mockHashService->expects(self::once())
            ->method('validateAndStripHmac')
            ->with($trustedProperties)
            ->willReturn(serialize(['__trusted' => 1]));

        $formRequest = $this->formRequestFactory->createFormRequest($actionRequest, $identifier);

        $this->assertInstanceOf(ActionRequest::class, $formRequest);
        $this->assertEquals($identifier, $formRequest->getArgumentNamespace());
        $this->assertEquals($actionRequest, $formRequest->getParentRequest());
        $this->assertArrayHasKey('__trusted', $formRequest->getInternalArguments());
        $this->assertArrayNotHasKey('__notTrusted', $formRequest->getInternalArguments());
        $this->assertEquals(['__trusted' => 'trustedValue'], $formRequest->getInternalArguments());
        $this->assertEmpty($formRequest->getArguments());
    }

    /**
     * @test
     */
    public function formRequestIgnoresArgumentsInForeignNamespaces()
    {
        $identifier = 'example';
        $trustedProperties = '__truested_properties__';

        $actionRequest = ActionRequest::fromHttpRequest($this->mockHttpRequest);
        $actionRequest->setArguments(
            [
                'something_different' =>
                    [
                        'any' => 'anyValue',
                        '__current' => 'first',
                        '__trustedProperties' => $trustedProperties
                    ]
            ]
        );

        $this->mockHashService->expects(self::never())
            ->method('validateAndStripHmac');

        $formRequest = $this->formRequestFactory->createFormRequest($actionRequest, $identifier);

        $this->assertInstanceOf(ActionRequest::class, $formRequest);
        $this->assertEquals($identifier, $formRequest->getArgumentNamespace());
        $this->assertEquals($actionRequest, $formRequest->getParentRequest());
        $this->assertEmpty($formRequest->getInternalArguments());
        $this->assertEmpty($formRequest->getArguments());
    }

    /**
     * @test
     */
    public function invalidHashExceptionsAreNotCaught()
    {
        $identifier = 'example';
        $trustedProperties = '__trusted_properties__';

        $actionRequest = ActionRequest::fromHttpRequest($this->mockHttpRequest);
        $actionRequest->setArguments(
            [
                $identifier =>
                    [
                        'any' => 'anyValue',
                        '__current' => 'first',
                        '__trustedProperties' => $trustedProperties
                    ]
            ]
        );

        $this->mockHashService->expects(self::once())
            ->method('validateAndStripHmac')
            ->with($trustedProperties)
            ->willThrowException(new InvalidHashException());

        $this->expectException(InvalidHashException::class);

        $this->formRequestFactory->createFormRequest($actionRequest, $identifier);
    }
}
