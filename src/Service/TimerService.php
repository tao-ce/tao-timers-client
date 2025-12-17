<?php

// SPDX-FileCopyrightText: 2012-2026 Open Assessment Technologies S.A.
// Copyright (C) 2022-2023 (original work) Open Assessment Technologies SA;
//
// SPDX-License-Identifier: AGPL-3.0-only OR LicenseRef-TAO-Commercial-License

declare(strict_types=1);

namespace OAT\Library\TaoTimerClient\Service;

use GuzzleHttp\Psr7\Request;
use OAT\Library\TaoTimerClient\Client\{
    CreateTimerException,
    DeleteTimerException,
    GetTimerException,
    ResponseException,
    StartTimerException,
    StopTimerException
};
use OAT\Library\TaoTimerClient\DataMapper\Contract\TimerResponseDataMapperInterface;
use OAT\Library\TaoTimerClient\Model\Contract\{
    InboundMsgInterface,
    ResponseErrorMessageInterface,
    TimerDefinitionInterface
};
use OAT\Library\TaoTimerClient\Model\ResponseErrorMessage;
use OAT\Library\TaoTimerClient\Service\Decoder\ExceptionDrivenJsonDecoder;
use OAT\Library\TaoTimerClient\Service\Exception\UnexpectedJsonFormatException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientExceptionInterface;
use JsonException;
use RuntimeException;

class TimerService implements TimerServiceInterface
{
    private const RESPONSE_REASON_CREATED = 'Created';
    private const RESPONSE_REASON_DELETED = 'No Content';
    private const RESPONSE_REASON_NOT_FOUND = 'Not Found';

    public function __construct(
        private ClientInterface $client,
        private TimerResponseDataMapperInterface $responseTimerDefinitionDataMapper,
        private string $baseUri,
        private ExceptionDrivenJsonDecoder $exceptionDrivenJsonDecoder
    ) {
    }

    /**
     * @throws JsonException              - happens if incorrectJSON come from response, could be as unexpected error
     * @throws ClientExceptionInterface
     * @throws UnexpectedJsonFormatException
     */
    public function createTimer(string $deliveryExecutionId, TimerDefinitionInterface $timerDefinition): void
    {
        $request = $this->createRequest(
            'POST',
            $this->createUri($deliveryExecutionId),
            $timerDefinition
        );

        $response = $this->send($request);

        if ($response->getReasonPhrase() !== self::RESPONSE_REASON_CREATED) {
            $errorMessage = $this->parseErrorMessage($response);
            throw new CreateTimerException($errorMessage->getMessage());
        }
    }

    /**
     * @throws JsonException             - happens if incorrectJSON come from response, could be as unexpected error
     * @throws ClientExceptionInterface
     * @throws UnexpectedJsonFormatException
     */
    public function getTimer(string $deliveryExecutionId): TimerDefinitionInterface
    {
        $request = $this->createRequest('GET', $this->createUri($deliveryExecutionId));
        $response = $this->send($request);

        if ($response->getReasonPhrase() === self::RESPONSE_REASON_NOT_FOUND) {
            $errorMessage = $this->parseErrorMessage($response);
            throw new GetTimerException($errorMessage->getMessage());
        }

        return $this->responseTimerDefinitionDataMapper->createTimerDefinitionFromResponse($response);
    }

    /**
     * @throws JsonException             - happens if incorrect JSON come from response, could be as unexpected error
     * @throws ClientExceptionInterface
     * @throws UnexpectedJsonFormatException
     */
    public function deleteTimer(string $deliveryExecutionId): void
    {
        $request = $this->createRequest('DELETE', $this->createUri($deliveryExecutionId));
        $response = $this->send($request);

        if ($response->getReasonPhrase() !== self::RESPONSE_REASON_DELETED) {
            $errorMessage = $this->parseErrorMessage($response);
            throw new DeleteTimerException($errorMessage->getMessage());
        }
    }

    /**
     * @throws JsonException             - happens if incorrectJSON come from response, could be as unexpected error
     * @throws ClientExceptionInterface
     * @throws UnexpectedJsonFormatException
     */
    public function stopTimer(string $deliveryExecutionId): void
    {
        $request = $this->createRequest('POST', $this->createUri($deliveryExecutionId, '/stop'));
        $response = $this->send($request);

        if ($response->getReasonPhrase() !== self::RESPONSE_REASON_CREATED) {
            $errorMessage = $this->parseErrorMessage($response);
            throw new StopTimerException($errorMessage->getMessage());
        }
    }

    /**
     * @throws JsonException             - happens if incorrectJSON come from response, could be as unexpected error
     * @throws ClientExceptionInterface
     * @throws UnexpectedJsonFormatException
     */
    public function startTimer(string $deliveryExecutionId, InboundMsgInterface $inboundMsg): void
    {
        $request = $this->createRequest('POST', $this->createUri($deliveryExecutionId, '/start'), $inboundMsg);
        $response = $this->send($request);

        if ($response->getReasonPhrase() !== self::RESPONSE_REASON_CREATED) {
            $errorMessage = $this->parseErrorMessage($response);
            throw new StartTimerException($errorMessage->getMessage());
        }
    }

    private function createUri(string $deliveryExecutionId, ?string $pathSuffix = null): string
    {
        return sprintf(
            '%s/timers/%s%s',
            $this->baseUri,
            rawurlencode($deliveryExecutionId),
            $pathSuffix ?? ''
        );
    }

    private function createRequest(string $method, string $uri, object $body = null): Request
    {
        return new Request(
            $method,
            $uri,
            ['Content-Type' => 'application/json'],
            $body
        );
    }

    /**
     * @throws JsonException
     * @throws UnexpectedJsonFormatException
     */
    private function parseErrorMessage(ResponseInterface $response): ResponseErrorMessageInterface
    {
        $responseData = $this->exceptionDrivenJsonDecoder->decode($response->getBody()->getContents());
        if (empty($responseData['message'])) {
            throw new ResponseException(
                sprintf(
                    '%s Incorrect error message provided',
                    self::class,
                )
            );
        }

        return new ResponseErrorMessage($responseData['message'], $response->getStatusCode());
    }

    /**
     * @throws ClientExceptionInterface
     */
    private function send(RequestInterface $request): ResponseInterface
    {
        $response = $this->client->sendRequest($request);
        if ($response->getStatusCode() >= 500) {
            throw new RuntimeException(sprintf(
                'Timer Service exception: %s',
                $response->getBody()->getContents(),
            ));
        }
        return $response;
    }
}
