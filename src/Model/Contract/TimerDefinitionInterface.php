<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2022 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Model\Contract;

use OAT\Library\TaoTimerClient\Model\TimerDetail;

interface TimerDefinitionInterface extends TimerInterface
{
    public function getExtra(): ?TimerDetail;

    public function getTest(): ?TimerDetail;

    /** @return TimerDetail[] */
    public function getTestParts(): array;

    /** @return TimerDetail[] */
    public function getSections(): array;

    /** @return TimerDetail[] */
    public function getItems(): array;
}
