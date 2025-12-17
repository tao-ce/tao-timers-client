<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2022 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Model;

use OAT\Library\TaoTimerClient\Model\Contract\TimerDefinitionInterface;

class TimerDefinition extends Timer implements TimerDefinitionInterface
{
    private ?TimerDetail $extra = null;
    private ?TimerDetail $test = null;

    /** @var TimerDetail[] */
    private array $testParts = [];

    /** @var TimerDetail[] */
    private array $sections = [];

    /** @var TimerDetail[] */
    private array $items = [];

    public function getTest(): ?TimerDetail
    {
        return $this->test;
    }

    public function getTestParts(): array
    {
        return $this->testParts;
    }

    public function getSections(): array
    {
        return $this->sections;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function __toString(): string
    {
        return json_encode($this);
    }

    public function jsonSerialize(): array
    {
        return [
            'test' => $this->getTest(),
            'testParts' => $this->getTestParts(),
            'sections' => $this->getSections(),
            'items' => $this->getItems(),
            'extra' => $this->getExtra(),
        ];
    }

    public function setTest(?TimerDetail $test): void
    {
        $this->test = $test;
    }

    public function setTestParts(TimerDetail ...$value): void
    {
        $this->testParts = $value;
    }

    public function setSections(TimerDetail ...$value): void
    {
        $this->sections = $value;
    }

    public function setItems(TimerDetail ...$value): void
    {
        $this->items = $value;
    }

    public function getExtra(): ?TimerDetail
    {
        return $this->extra;
    }

    public function setExtra(?TimerDetail $extra): void
    {
        $this->extra = $extra;
    }
}
