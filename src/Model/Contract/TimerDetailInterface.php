<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2022-2024 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Model\Contract;

interface TimerDetailInterface extends TimerInterface
{
    public function getId(): string;
    public function getMinTime(): int;
    public function getMaxTime(): int;
    public function getMaxTimeRemaining(): int;
    public function getInitialValue(): ?int;
    public function isStarted(): bool;
}
