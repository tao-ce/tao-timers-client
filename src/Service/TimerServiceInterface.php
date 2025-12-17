<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2022 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Service;

use OAT\Library\TaoTimerClient\Model\Contract\{
    InboundMsgInterface,
    TimerDefinitionInterface
};
use OAT\Library\TaoTimerClient\Client\{
    CreateTimerException,
    DeleteTimerException,
    GetTimerException,
    StartTimerException,
    StopTimerException
};

interface TimerServiceInterface
{
    /**
     * @throws CreateTimerException
     */
    public function createTimer(string $deliveryExecutionId, TimerDefinitionInterface $timerDefinition): void;

    /**
     * @throws GetTimerException
     */
    public function getTimer(string $deliveryExecutionId): TimerDefinitionInterface;

    /**
     * @throws DeleteTimerException
     */
    public function deleteTimer(string $deliveryExecutionId): void;

    /**
     * @throws StopTimerException
     */
    public function stopTimer(string $deliveryExecutionId): void;

    /**
     * @throws StartTimerException
     */
    public function startTimer(string $deliveryExecutionId, InboundMsgInterface $inboundMsg): void;
}
