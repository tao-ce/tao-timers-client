<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2022-2024 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License
declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\DataMapper;

use OAT\Library\TaoTimerClient\DataMapper\Contract\TimerResponseDataMapperInterface;
use OAT\Library\TaoTimerClient\DataMapper\Validator\TimerDetailValidator;
use OAT\Library\TaoTimerClient\Model\Contract\TimerDefinitionInterface;
use OAT\Library\TaoTimerClient\Model\Contract\TimerInterface;
use OAT\Library\TaoTimerClient\Model\TimerDefinition;
use OAT\Library\TaoTimerClient\Model\TimerDetail;
use OAT\Library\TaoTimerClient\Service\Decoder\ExceptionDrivenJsonDecoder;
use OAT\Library\TaoTimerClient\Service\Exception\UnexpectedJsonFormatException;
use Psr\Http\Message\ResponseInterface;
use JsonException;

class TimerResponseDataMapper implements TimerResponseDataMapperInterface
{
    private static array $responsePropertyToSetMethod = [
        'id' => 'setId',
        'minTime' => 'setMinTime',
        'maxTime' => 'setMaxTime',
        'maxTimeRemaining' => 'setMaxTimeRemaining',
        'initialValue' => 'setInitialValue',
        'started' => 'setIsStarted',
        'test' => 'setTest',
        'testParts' => 'setTestParts',
        'sections' => 'setSections',
        'items' => 'setItems',
        'extra' => 'setExtra'
    ];

    public function __construct(private ExceptionDrivenJsonDecoder $exceptionDrivenJsonDecoder)
    {
    }

    /**
     * @throws JsonException
     * @throws UnexpectedJsonFormatException
     */
    public function createTimerDefinitionFromResponse(ResponseInterface $response): TimerDefinitionInterface
    {
        $responseData = $this->exceptionDrivenJsonDecoder->decode($response->getBody()->getContents());
        $result = new TimerDefinition();
        $this->fillFromData($result, $responseData);

        return $result;
    }

    private function fillFromData(TimerInterface $object, array $responseData): void
    {
        foreach ($responseData as $key => $value) {
            if (!isset(self::$responsePropertyToSetMethod[$key])) {
                continue;
            }
            $method = self::$responsePropertyToSetMethod[$key];

            if (is_array($value)) {
                $list = [];
                foreach ($value as $subValue) {
                    $subResult = new TimerDetail();
                    $list[] = $subResult;

                    if (!is_array($subValue)) {
                        $this->fillFromData($subResult, $value);
                        TimerDetailValidator::validate($subResult);
                        break;
                    }

                    $this->fillFromData($subResult, $subValue);
                    TimerDetailValidator::validate($subResult);
                }

                $object->$method(...$list);
                continue;
            }

            $object->$method($value);
        }
    }
}
