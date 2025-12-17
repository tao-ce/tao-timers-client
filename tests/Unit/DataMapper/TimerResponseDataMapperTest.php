<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2022-2024 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Tests\Unit\DataMapper;

use OAT\Library\TaoTimerClient\DataMapper\Exception\TimerDetailMinTimeGreaterThanMaxTime;
use OAT\Library\TaoTimerClient\DataMapper\TimerResponseDataMapper;
use OAT\Library\TaoTimerClient\Model\Contract\TimerDefinitionInterface;
use OAT\Library\TaoTimerClient\Model\Contract\TimerDetailInterface;
use OAT\Library\TaoTimerClient\Service\Decoder\ExceptionDrivenJsonDecoder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class TimerResponseDataMapperTest extends TestCase
{
    private TimerResponseDataMapper $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new TimerResponseDataMapper(new ExceptionDrivenJsonDecoder());
    }

    public function testCreateTimerDefinitionFromResponse(): void
    {
        $responseBody = [
            'testParts' => [
                [
                    'id' => 'testId',
                    'minTime' => 0,
                    'maxTime' => 10
                ]
            ],
            'sections' => [
                [
                    'id' => 'testId',
                    'minTime' => 1,
                    'maxTime' => 10
                ]
            ],
            'items' => [
                [
                    'id' => 'testId',
                    'minTime' => 5,
                    'maxTime' => 10
                ]
            ],
            'extra' =>
                [
                    'id' => 'extraTime',
                    'minTime' => 10,
                    'maxTime' => 10,
                    'initialValue' => 11,
                    'maxTimeRemaining' => 12,
                ]
            ,
        ];

        $timerDefinition = $this->subject->createTimerDefinitionFromResponse($this->getResponseMock($responseBody));

        $this->assertInstanceOf(TimerDefinitionInterface::class, $timerDefinition);
        $this->assertInstanceOf(TimerDetailInterface::class, $timerDefinition->getItems()[0]);
        $this->assertInstanceOf(TimerDetailInterface::class, $timerDefinition->getSections()[0]);
        $this->assertInstanceOf(TimerDetailInterface::class, $timerDefinition->getTestParts()[0]);
        $this->assertInstanceOf(TimerDetailInterface::class, $timerDefinition->getExtra());

        $this->assertEquals($responseBody['testParts'][0]['id'], $timerDefinition->getTestParts()[0]->getId());
        $this->assertEquals($responseBody['sections'][0]['id'], $timerDefinition->getSections()[0]->getId());
        $this->assertEquals($responseBody['items'][0]['id'], $timerDefinition->getItems()[0]->getId());
        $this->assertEquals($responseBody['extra']['id'], $timerDefinition->getExtra()->getId());

        $this->assertEquals(10, $timerDefinition->getTestParts()[0]->getMaxTime());
        $this->assertEquals(10, $timerDefinition->getSections()[0]->getMaxTime());
        $this->assertEquals(10, $timerDefinition->getItems()[0]->getMaxTime());
        $this->assertEquals(10, $timerDefinition->getExtra()->getMaxTime());

        $this->assertEquals(0, $timerDefinition->getTestParts()[0]->getMinTime());
        $this->assertEquals(1, $timerDefinition->getSections()[0]->getMinTime());
        $this->assertEquals(5, $timerDefinition->getItems()[0]->getMinTime());
        $this->assertEquals(10, $timerDefinition->getExtra()->getMinTime());

        $this->assertSame(11, $timerDefinition->getExtra()->getInitialValue());
        $this->assertSame(12, $timerDefinition->getExtra()->getMaxTimeRemaining());
    }

    public function testCreateTimerDefinitionFromResponseThrowsExceptionForInvalidMinTime(): void
    {
        $this->expectException(TimerDetailMinTimeGreaterThanMaxTime::class);

        $responseBody = [
            'testParts' => [
                [
                    'id' => 'testId',
                    'minTime' => 100,
                    'maxTime' => 10
                ]
            ],
        ];

        $this->subject->createTimerDefinitionFromResponse($this->getResponseMock($responseBody));
    }

    private function getResponseMock(array $body): ResponseInterface
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $messageMock = $this->createMock(StreamInterface::class);

        $messageMock
            ->expects($this->once())
            ->method('getContents')
            ->willReturn(json_encode($body));

        $responseMock
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($messageMock);

        return $responseMock;
    }
}
