<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2022 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Model;

use OAT\Library\TaoTimerClient\Model\Contract\InboundMsgInterface;
use OAT\Library\TaoTimerClient\Model\Contract\ItemInterface;
use OAT\Library\TaoTimerClient\Model\Contract\SectionInterface;
use OAT\Library\TaoTimerClient\Model\Contract\TestPartInterface;

class InboundMsg extends Identifiable implements InboundMsgInterface
{
    public function __construct(
        string $id,
        private TestPartInterface $testPart,
        private SectionInterface $section,
        private ItemInterface $item,
    ) {
        parent::__construct($id);
    }

    public function getTestPart(): TestPartInterface
    {
        return $this->testPart;
    }

    public function getSection(): SectionInterface
    {
        return $this->section;
    }

    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    public function __toString(): string
    {
        return json_encode($this);
    }

    public function jsonSerialize(): array
    {
        return [
            'testPart' => $this->getTestPart(),
            'section' => $this->getSection(),
            'item' => $this->getItem(),
        ] + parent::jsonSerialize();
    }
}
