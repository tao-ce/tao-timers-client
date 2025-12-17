<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2024 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\DataMapper\Validator;

use OAT\Library\TaoTimerClient\DataMapper\Exception\TimerDetailMinTimeGreaterThanMaxTime;
use OAT\Library\TaoTimerClient\Model\Contract\TimerDetailInterface;


class TimerDetailValidator
{
    public static function validate(TimerDetailInterface $timerDetail): void
    {
        $minTime = $timerDetail->getMinTime();
        $maxTime = $timerDetail->getMaxTime();

        if ($minTime > $maxTime) {
            throw new TimerDetailMinTimeGreaterThanMaxTime('Min time cannot be greater than max time');
        }
    }
}
