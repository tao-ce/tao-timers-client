<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2023 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace Unit\Service;

use JsonException;
use OAT\Library\TaoTimerClient\Service\Decoder\ExceptionDrivenJsonDecoder;
use OAT\Library\TaoTimerClient\Service\Exception\UnexpectedJsonFormatException;
use PHPUnit\Framework\TestCase;
use stdClass;

class ExceptionDrivenJsonDecoderTest extends TestCase
{
    private const EXPECTED_EXCEPTION_MESSAGE_PATTERN = 'Unexpected response format, json expected, but %s';
    private ExceptionDrivenJsonDecoder $subject;

    protected function setUp(): void
    {
        $this->subject = new ExceptionDrivenJsonDecoder();
    }

    /**
     * @dataProvider provideNonJsonStrings
     */
    public function testDecodeWithSyntaxErrorWithDefaultArgs(string $content)
    {
        $this->expectException(UnexpectedJsonFormatException::class);
        $this->expectExceptionMessage(sprintf(
            self::EXPECTED_EXCEPTION_MESSAGE_PATTERN,
            mb_substr($content, 0, 20)
        ));
        $this->subject->decode($content);
    }

    /**
     * @dataProvider provideNonJsonStrings
     */
    public function testDecodeWithSyntaxErrorWithNotAssocArg(string $content)
    {
        $this->expectException(UnexpectedJsonFormatException::class);
        $this->expectExceptionMessage(sprintf(
            self::EXPECTED_EXCEPTION_MESSAGE_PATTERN,
            mb_substr($content, 0, 20)
        ));
        $this->subject->decode($content, false);
    }

    /**
     * @dataProvider provideNonJsonStrings
     */
    public function testDecodeWithSyntaxErrorWithAdditionalFlags(string $content)
    {
        $this->expectException(UnexpectedJsonFormatException::class);
        $this->expectExceptionMessage(sprintf(
            self::EXPECTED_EXCEPTION_MESSAGE_PATTERN,
            mb_substr($content, 0, 20)
        ));
        $this->subject->decode($content, flags: JSON_BIGINT_AS_STRING);
    }

    public function testDecodeWithNotSyntaxError()
    {
        $this->expectException(JsonException::class);
        $this->subject->decode('{"string": "𝘥𝘦𝘴𝘪𝘨𝘯𝘦\\ud835"}');
    }

    public function testSuccessfullyDecodeAssoc()
    {
        $result = $this->subject->decode('{"value": 1}');
        self::assertTrue(is_array($result));
        self::assertEquals(1, $result['value']);
    }

    public function testSuccessfullyDecodeStdClass()
    {
        $result = $this->subject->decode('{"value": 1}', false);
        self::assertInstanceOf(stdClass::class, $result);
        self::assertEquals(1, $result->value);
    }

    public function provideNonJsonStrings(): array
    {
        return [
            ['<html>'],
            ['no healthy upstream'],
            [''],
            ['abcdefghijklmnopqrstuvwxyz']
        ];
    }
}
