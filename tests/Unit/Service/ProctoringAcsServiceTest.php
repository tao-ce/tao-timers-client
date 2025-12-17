<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2022 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Tests\Unit\Service;

use DateTime;
use GuzzleHttp\Psr7\Request;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLink;
use OAT\Library\Lti1p3Proctoring\Model\AcsControl;
use OAT\Library\TaoTimerClient\Model\ProctoringAcsAction;
use OAT\Library\TaoTimerClient\Model\ProctoringAcsActionUser;
use OAT\Library\TaoTimerClient\Service\Decoder\ExceptionDrivenJsonDecoder;
use OAT\Library\TaoTimerClient\Service\Exception\ProctoringAcsGetExtraTimeFailedException;
use OAT\Library\TaoTimerClient\Service\Exception\ProctoringAcsSendActionFailedException;
use OAT\Library\TaoTimerClient\Service\ProctoringAcsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ProctoringAcsServiceTest extends TestCase
{
    private ProctoringAcsService $subject;
    private ClientInterface|MockObject $clientMock;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(ClientInterface::class);
        $this->subject = new ProctoringAcsService($this->clientMock, 'https://base.uri', new ExceptionDrivenJsonDecoder());
    }

    public function testGetExtraTime(): void
    {
        $expectedRequest = new Request('GET', 'https://base.uri/proctoring/acs/deliveryExecutionId/extra-time', ['Content-Type' => 'application/json']);

        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('{"extraTime":5}');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);
        $responseMock
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock);

        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with($expectedRequest)
            ->willReturn($responseMock);

        $this->assertSame(5, $this->subject->getExtraTime('deliveryExecutionId'));
    }

    public function testGetExtraTimeFails(): void
    {
        $expectedRequest = new Request('GET', 'https://base.uri/proctoring/acs/deliveryExecutionId/extra-time', ['Content-Type' => 'application/json']);

        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('{"message":"reason"}');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(500);
        $responseMock
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock);

        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with($expectedRequest)
            ->willReturn($responseMock);

        $this->expectException(ProctoringAcsGetExtraTimeFailedException::class);
        $this->expectExceptionMessage('reason');

        $this->subject->getExtraTime('deliveryExecutionId');
    }

    public function testSendAction(): void
    {
        $acsControl = new AcsControl(
            new LtiResourceLink('identifier'),
            'userIdentifier',
            'terminate',
            new DateTime(),
            2,
            'issuerIdentifier',
            4,
            0.5,
            'reasonCode',
            'reasonMessage',
        );

        $expectedRequest = new Request(
            'POST',
            'https://base.uri/proctoring/acs/deliveryExecutionId/action',
            ['Content-Type' => 'application/json'],
            json_encode($acsControl),
        );

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(201);

        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function($arg) use ($expectedRequest) {
                return
                    $arg instanceof Request
                    && $arg->getBody()->getContents() === $expectedRequest->getBody()->getContents();
            }))
            ->willReturn($responseMock);

        $this->subject->sendAction('deliveryExecutionId', $acsControl);
    }

    public function testSendActionFAils(): void
    {
        $acsControl = new AcsControl(
            new LtiResourceLink('identifier'),
            'userIdentifier',
            'terminate',
            new DateTime(),
            2,
            'issuerIdentifier',
            4,
            0.5,
            'reasonCode',
            'reasonMessage',
        );

        $expectedRequest = new Request(
            'POST',
            'https://base.uri/proctoring/acs/deliveryExecutionId/action',
            ['Content-Type' => 'application/json'],
            json_encode($acsControl),
        );

        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('{"message":"reason"}');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(500);
        $responseMock
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock);

        $this->clientMock
            ->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function($arg) use ($expectedRequest) {
                return
                    $arg instanceof Request
                    && $arg->getBody()->getContents() === $expectedRequest->getBody()->getContents();
            }))
            ->willReturn($responseMock);

        $this->expectException(ProctoringAcsSendActionFailedException::class);
        $this->expectExceptionMessage('reason');

        $this->subject->sendAction('deliveryExecutionId', $acsControl);
    }
}
