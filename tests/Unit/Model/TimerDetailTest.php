<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2025 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Tests\Unit\Model;

use OAT\Library\TaoTimerClient\Model\TimerDetail;
use PHPUnit\Framework\TestCase;

class TimerDetailTest extends TestCase
{
    public function testSetAndGetId(): void
    {
        $timer = new TimerDetail();
        $timer->setId('testId');
        $this->assertSame('testId', $timer->getId());
    }

    public function testSetAndGetMinTime(): void
    {
        $timer = new TimerDetail();
        $timer->setMinTime(5000);
        $this->assertSame(5000, $timer->getMinTime());
    }

    public function testSetAndGetMaxTime(): void
    {
        $timer = new TimerDetail();
        $timer->setMaxTime(10000);
        $this->assertSame(10000, $timer->getMaxTime());
    }

    public function testSetMinTimeInSeconds(): void
    {
        $timer = new TimerDetail();
        $timer->setMinTimeInSeconds(5);
        $this->assertSame(5000, $timer->getMinTime());
    }

    public function testSetMaxTimeInSeconds(): void
    {
        $timer = new TimerDetail();
        $timer->setMaxTimeInSeconds(10);
        $this->assertSame(10000, $timer->getMaxTime());
    }

    public function testSetAndGetMaxTimeRemaining(): void
    {
        $timer = new TimerDetail();
        $timer->setMaxTime(15000);
        $this->assertSame(15000, $timer->getMaxTimeRemaining());

        $timer->setMaxTimeRemaining(12000);
        $this->assertSame(12000, $timer->getMaxTimeRemaining());
    }

    public function testSetAndGetInitialValue(): void
    {
        $timer = new TimerDetail();
        $timer->setInitialValue(2000);
        $this->assertSame(2000, $timer->getInitialValue());
    }

    public function testSetAndIsStarted(): void
    {
        $timer = new TimerDetail();
        $this->assertFalse($timer->isStarted());

        $timer->setIsStarted(true);
        $this->assertTrue($timer->isStarted());
    }

    public function testIsTimeout(): void
    {
        $timer = new TimerDetail();
        $timer->setMaxTimeRemaining(0);
        $this->assertTrue($timer->isTimeout());

        $timer->setMaxTimeRemaining(10);
        $this->assertFalse($timer->isTimeout());
    }

    public function testJsonSerialization(): void
    {
        $timer = new TimerDetail();
        $timer->setId('timer1');
        $timer->setIsStarted(true);
        $timer->setMinTime(5000);
        $timer->setMaxTime(10000);
        $timer->setMaxTimeRemaining(8000);
        $timer->setInitialValue(3000);

        $expected = [
            'id' => 'timer1',
            'started' => true,
            'minTime' => 5000,
            'maxTime' => 10000,
            'maxTimeRemaining' => 8000,
            'initialValue' => 3000,
        ];

        $this->assertSame($expected, $timer->jsonSerialize());
    }

    public function testJsonSerializationWithMissingInitialValue(): void
    {
        $timer = new TimerDetail();
        $timer->setId('timer1');
        $timer->setIsStarted(true);
        $timer->setMinTime(5000);
        $timer->setMaxTime(10000);
        $timer->setMaxTimeRemaining(8000);

        $expected = [
            'id' => 'timer1',
            'started' => true,
            'minTime' => 5000,
            'maxTime' => 10000,
            'maxTimeRemaining' => 8000,
            'initialValue' => null,
        ];

        $this->assertSame($expected, $timer->jsonSerialize());
    }

    public function testToString(): void
    {
        $timer = new TimerDetail();
        $timer->setId('timer1');
        $timer->setIsStarted(true);
        $timer->setMinTime(5000);
        $timer->setMaxTime(10000);
        $timer->setMaxTimeRemaining(8000);
        $timer->setInitialValue(3000);

        $expectedJson = json_encode($timer);
        $this->assertSame($expectedJson, (string) $timer);
    }
}
