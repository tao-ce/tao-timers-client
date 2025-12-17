<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2023 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Service\Exception;

use Exception;
use JsonException;

class UnexpectedJsonFormatException extends Exception
{
    protected const EXCEPTION_MESSAGE_TEMPLATE = 'Unexpected response format, json expected, but %s';

    public static function fromJsonSyntaxError(JsonException $exception, string $causedContent): static
    {
        return new static(
            sprintf(
                static::EXCEPTION_MESSAGE_TEMPLATE,
                mb_substr($causedContent, 0, 20)
            ),
            $exception->getCode(),
            $exception
        );
    }
}
