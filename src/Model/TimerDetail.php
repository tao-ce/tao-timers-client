<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2022-2025 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Model;

use OAT\Library\TaoTimerClient\Model\Contract\TimerDetailInterface;

class TimerDetail extends Timer implements TimerDetailInterface
{
    private string $id;
    private bool $isStarted = false;
    private int $minTime = 0;
    private int $maxTime = 0;
    private ?int $maxTimeRemaining = null;
    private ?int $initialValue = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $value): void
    {
        $this->id = $value;
    }

    public function getMinTime(): int
    {
        return $this->minTime;
    }

    public function setMinTime(int $minTime): void
    {
        $this->minTime = $minTime;
    }

    public function getMaxTime(): int
    {
        return $this->maxTime;
    }

    public function setMaxTime(int $maxTime): void
    {
        $this->maxTime = $maxTime;
    }

    public function setMinTimeInSeconds(int $minTimeInSeconds): void
    {
        $this->minTime = 1000 * $minTimeInSeconds;
    }

    public function setMaxTimeInSeconds(int $maxTimeInSeconds): void
    {
        $this->maxTime = 1000 * $maxTimeInSeconds;
    }

    public function getMaxTimeRemaining(): int
    {
        return $this->maxTimeRemaining ?? $this->maxTime;
    }

    public function setMaxTimeRemaining(int $maxTime)
    {
        $this->maxTimeRemaining = $maxTime;
    }

    public function getInitialValue(): ?int
    {
        return $this->initialValue;
    }

    public function setInitialValue(?int $initialValue): void
    {
        $this->initialValue = $initialValue;
    }

    public function setIsStarted(bool $flag): void
    {
        $this->isStarted = $flag;
    }

    public function isStarted():bool
    {
        return $this->isStarted;
    }

    public function __toString(): string
    {
        return json_encode($this);
    }

    public function isTimeout(): bool
    {
        return isset($this->maxTimeRemaining) && $this->maxTimeRemaining <= 0;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'started' => $this->isStarted(),
            'minTime' => $this->getMinTime(),
            'maxTime' => $this->getMaxTime(),
            'maxTimeRemaining' => $this->getMaxTimeRemaining(),
            'initialValue' => $this->getInitialValue(),
        ];
    }
}
