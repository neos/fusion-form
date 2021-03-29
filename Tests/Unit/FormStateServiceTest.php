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

use Neos\Fusion\Form\Runtime\Domain\FormState;
use Neos\Fusion\Form\Runtime\Domain\FormStateService;
use PHPUnit\Framework\TestCase;
use Neos\Flow\Security\Cryptography\HashService;
use Neos\Flow\Security\Exception\InvalidHashException;

class FormStateServiceTest extends TestCase
{
    /**
     * @var FormStateService
     */
    protected $formStateService;

    /**
     * @var HashService
     */
    protected $mockHashService;

    public function setUp(): void
    {
        $this->formStateService = new FormStateService();
        $this->mockHashService = $this->createMock(HashService::class);

        $reflection = new \ReflectionClass($this->formStateService);
        $reflection_property = $reflection->getProperty('hashService');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->formStateService, $this->mockHashService);
    }

    /**
     * @test
     */
    public function formStateCanBeSerializedAndUnserialized()
    {
        $stateParts = [
            'first' => ['value1' => 'foo', 'value2' => 'bar'],
            'second' => ['value2' => 'foo', 'value4' => 'bar']
        ];
        $state = new FormState($stateParts);

        $statePartsSerialized = base64_encode(serialize($state));

        $this->mockHashService->expects(self::once())
            ->method('appendHmac')
            ->with($statePartsSerialized)
            ->willReturn($statePartsSerialized . '__mock_hmac__');

        $this->mockHashService->expects(self::once())
            ->method('validateAndStripHmac')
            ->with($statePartsSerialized . '__mock_hmac__')
            ->willReturn($statePartsSerialized);

        $serializedState = $this->formStateService->serializeState($state);
        $unserializedState = $this->formStateService->unserializeState($serializedState);

        $this->assertEquals($stateParts, $unserializedState->getAllParts());
    }

    /**
     * @test
     */
    public function afterMesssingWithSerializedStateAnExceptionIsThrowm()
    {
        $stateString = '__somethingNotSerializedByMe__';

        $this->mockHashService->expects(self::once())
            ->method('validateAndStripHmac')
            ->with($stateString)
            ->willThrowException(new InvalidHashException());

        $this->expectException(InvalidHashException::class);

        $this->formStateService->unserializeState($stateString);
    }
}
