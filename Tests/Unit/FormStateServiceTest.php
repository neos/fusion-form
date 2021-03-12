<?php
namespace Neos\Fusion\Form\Tests\Unit;

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
        $this->formStateService->injectHashService($this->mockHashService);
    }

    /**
     * @test
     */
    public function formStatesCanBeSerializedAndUnserialized()
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

        $this->assertEquals($stateParts, $unserializedState->getAll());
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
