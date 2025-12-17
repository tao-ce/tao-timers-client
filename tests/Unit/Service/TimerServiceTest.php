<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2022-2025 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Tests\Unit\Service;

use OAT\Library\TaoTimerClient\Client\{
    CreateTimerException,
    DeleteTimerException,
    GetTimerException,
    ResponseException,
    StartTimerException,
    StopTimerException
};
use OAT\Library\TaoTimerClient\DataMapper\Contract\TimerResponseDataMapperInterface;
use OAT\Library\TaoTimerClient\Model\Contract\{
    InboundMsgInterface,
    TimerDefinitionInterface,
    TimerDetailInterface
};
use OAT\Library\TaoTimerClient\DataMapper\TimerResponseDataMapper;
use OAT\Library\TaoTimerClient\Service\Decoder\ExceptionDrivenJsonDecoder;
use OAT\Library\TaoTimerClient\Service\TimerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class TimerServiceTest extends TestCase
{
    private TimerService $subject;
    private ClientInterface|MockObject $clientMock;

    private string $deliveryExecutionId = 'deliveryExecutionId';
    private string $testHost = 'http://localhost:8080';

    private array $responseTimerDefinition = [
            'test' => [
                'id' => 'testId',
                'maxTimeRemaining' => 123,
                'maxTime' => 123,
                'started' => false,
             ],
            'testParts' => [
                [
                    'id' => 'testPartId',
                    'maxTimeRemaining' => 123,
                    'maxTime' => 123,
                    'started' => false,
                ]
            ],
            'sections' => [
                [
                    'id' => 'testSectionId',
                    'maxTimeRemaining' => 123,
                    'maxTime' => 123,
                    'started' => false,
                ]
            ],
            'items' => [
                [
                    'id' => 'testItemId',
                    'maxTimeRemaining' => 123,
                    'maxTime' => 123,
                    'started' => false,
                ]
            ],
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->clientMock = $this->createMock(ClientInterface::class);
        $dataMapperMock = new TimerResponseDataMapper(new ExceptionDrivenJsonDecoder());

        $this->subject = new TimerService(
            $this->clientMock,
            $dataMapperMock,
            $this->testHost,
            new ExceptionDrivenJsonDecoder()
        );
    }

    public function testSuccessCreateTimer(): void
    {
       $this->prepareClient($this->responseTimerDefinition);

       $inputMock = $this->createMock(TimerDefinitionInterface::class);
       $this->subject->createTimer($this->deliveryExecutionId, $inputMock);
    }

    public function testCreateTimerParseErrorMessage(): void
    {
        $this->prepareClient([
            'message' => 'failed'
        ], 'Fail');

        $this->expectException(CreateTimerException::class);
        $this->expectExceptionMessage('failed');

        $inputMock = $this->createMock(TimerDefinitionInterface::class);
        $this->subject->createTimer($this->deliveryExecutionId, $inputMock);
    }

    public function testDetectIncorrectErrorMessage(): void
    {
        $this->prepareClient([], 'Fail');

        $this->expectException(ResponseException::class);
        $this->expectExceptionMessage(sprintf(
            '%s Incorrect error message provided',
            TimerService::class
        ));

        $inputMock = $this->createMock(TimerDefinitionInterface::class);
        $this->subject->createTimer($this->deliveryExecutionId, $inputMock);
    }

    public function testGetTimer(): void
    {
        $this->prepareClient($this->responseTimerDefinition);

        $timer = $this->subject->getTimer($this->deliveryExecutionId);

        $this->assertionDefinition($this->responseTimerDefinition, $timer);
    }

    public function testGetTimerParseErrorMessage(): void
    {
        $this->prepareClient([
            'message' => 'failed'
        ], 'Not Found');

        $this->expectException(GetTimerException::class);
        $this->expectExceptionMessage('failed');

        $this->subject->getTimer($this->deliveryExecutionId);
    }

    public function testDeleteTimerParseErrorMessage(): void
    {
        $this->prepareClient([
            'message' => 'failed'
        ], 'Not Found');

        $this->expectException(DeleteTimerException::class);
        $this->expectExceptionMessage('failed');

        $this->subject->deleteTimer($this->deliveryExecutionId);
    }

    public function testStopTimer()
    {
        $this->prepareClient($this->responseTimerDefinition);

        $this->subject->stopTimer($this->deliveryExecutionId);
    }

    public function testStopTimerParseErrorMessage(): void
    {
        $this->prepareClient([
            'message' => 'failed'
        ], 'Not Found');

        $this->expectException(StopTimerException::class);
        $this->expectExceptionMessage('failed');

        $this->subject->stopTimer($this->deliveryExecutionId);
    }

    public function testStartTimer()
    {
        $messageMock = $this->createMock(InboundMsgInterface::class);
        $this->prepareClient($this->responseTimerDefinition);

        $this->subject->startTimer($this->deliveryExecutionId, $messageMock);
    }

    public function testStartTimerParseErrorMessage(): void
    {
        $messageMock = $this->createMock(InboundMsgInterface::class);
        $this->prepareClient([
            'message' => 'failed'
        ], 'Not Found');

        $this->expectException(StartTimerException::class);
        $this->expectExceptionMessage('failed');

        $this->subject->startTimer($this->deliveryExecutionId, $messageMock);
    }

    private function getResponseMock(array $body, string $status = 'Created'): ResponseInterface
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $messageMock = $this->createMock(StreamInterface::class);

        $messageMock
            ->method('getContents')
            ->willReturn(json_encode($body));

        $responseMock
            ->method('getStatusCode')
            ->willReturn(400);

        $responseMock
            ->expects(
                $this->once()
            )
            ->method('getReasonPhrase')
            ->willReturn($status);

        $responseMock
            ->method('getBody')
            ->willReturn($messageMock);

        return $responseMock;
    }

    private function prepareClient(array $responseData, string $status = 'Created', ?string $pathSuffix = null): void
    {
        $self = $this;
        $this->clientMock
            ->expects(
                $this->once()
            )
            ->method('sendRequest')
            ->willReturn(
                $this->getResponseMock($responseData, $status)
            )
        ;
    }

    private function assertionDefinition(array $responseBody, TimerDefinitionInterface $timerDefinition): void
    {
        $this->assertInstanceOf(TimerDefinitionInterface::class, $timerDefinition);
        $this->assertInstanceOf(TimerDetailInterface::class, $timerDefinition->getItems()[0]);
        $this->assertInstanceOf(TimerDetailInterface::class, $timerDefinition->getSections()[0]);
        $this->assertInstanceOf(TimerDetailInterface::class, $timerDefinition->getTestParts()[0]);

        $this->assertEquals($responseBody['testParts'][0]['id'], $timerDefinition->getTestParts()[0]->getId());
        $this->assertEquals($responseBody['sections'][0]['id'], $timerDefinition->getSections()[0]->getId());
        $this->assertEquals($responseBody['items'][0]['id'], $timerDefinition->getItems()[0]->getId());
        $this->assertEquals($responseBody['test']['id'], $timerDefinition->getTest()->getId());
    }
}
