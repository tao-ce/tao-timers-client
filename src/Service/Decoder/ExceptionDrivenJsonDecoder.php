<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2023 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Service\Decoder;

use JsonException;
use OAT\Library\TaoTimerClient\Service\Exception\UnexpectedJsonFormatException;
use stdClass;

class ExceptionDrivenJsonDecoder
{
    /**
     * @throws UnexpectedJsonFormatException
     * @throws JsonException
     */
    public function decode(string $value, bool $assoc = true, int $depth = 512, ?int $flags = null): array|stdClass
    {
        try {
            return json_decode($value, $assoc, $depth, $this->applyFlags($flags));
        } catch (JsonException $e) {
            if ($e->getCode() === JSON_ERROR_SYNTAX) {
                throw UnexpectedJsonFormatException::fromJsonSyntaxError($e, $value);
            }
            throw $e;
        }
    }

    private function applyFlags(?int $flags = null): int
    {
        $usedFlags = JSON_THROW_ON_ERROR;
        if ($flags !== null) {
            $usedFlags |= $flags;
        }

        return $usedFlags;
    }
}
