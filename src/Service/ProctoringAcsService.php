<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2022-2023 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Service;

use GuzzleHttp\Psr7\Request;
use JsonException;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlInterface;
use OAT\Library\TaoTimerClient\Service\Decoder\ExceptionDrivenJsonDecoder;
use OAT\Library\TaoTimerClient\Service\Exception\ProctoringAcsGetExtraTimeFailedException;
use OAT\Library\TaoTimerClient\Service\Exception\ProctoringAcsSendActionFailedException;
use OAT\Library\TaoTimerClient\Service\Exception\UnexpectedJsonFormatException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

class ProctoringAcsService
{
    public function __construct(
        private ClientInterface $client,
        private string $baseUri,
        private ExceptionDrivenJsonDecoder $exceptionDrivenJsonDecoder
    ) {
    }

    /**
     * @throws ProctoringAcsGetExtraTimeFailedException
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws UnexpectedJsonFormatException
     */
    public function getExtraTime(string $deliveryExecutionId): int
    {
        $request = new Request(
            'GET',
            sprintf('%s/proctoring/acs/%s/extra-time', $this->baseUri, rawurlencode($deliveryExecutionId)),
            ['Content-Type' => 'application/json'],
        );

        $response = $this->client->sendRequest($request);

        $parsedBody = $this->exceptionDrivenJsonDecoder->decode($response->getBody()->getContents());
        if ($response->getStatusCode() === 200) {
            return $parsedBody['extraTime'];
        }

        throw new ProctoringAcsGetExtraTimeFailedException($parsedBody['message']);
    }

    /**
     * @throws ProctoringAcsSendActionFailedException
     * @throws ClientExceptionInterface
     * @throws JsonException
     * @throws UnexpectedJsonFormatException
     */
    public function sendAction(string $deliveryExecutionId, AcsControlInterface $acsControl): void
    {
        $request = new Request(
            'POST',
            sprintf('%s/proctoring/acs/%s/action', $this->baseUri, rawurlencode($deliveryExecutionId)),
            ['Content-Type' => 'application/json'],
            json_encode($acsControl),
        );

        $response = $this->client->sendRequest($request);

        if ($response->getStatusCode() !== 201) {
            $parsedBody = $this->exceptionDrivenJsonDecoder->decode($response->getBody()->getContents());

            throw new ProctoringAcsSendActionFailedException($parsedBody['message']);
        }
    }
}
