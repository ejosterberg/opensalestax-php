<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use OpenSalesTax\Exceptions\OpenSalesTaxApiException;
use OpenSalesTax\Exceptions\OpenSalesTaxNetworkException;
use OpenSalesTax\Internal\HttpClientFactory;
use OpenSalesTax\Responses\CalculateResponse;
use OpenSalesTax\Responses\HealthResponse;
use OpenSalesTax\Responses\RatesResponse;
use OpenSalesTax\Responses\StatesResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

final class Client
{
    private readonly string $baseUrl;
    private readonly ClientInterface $http;

    public function __construct(
        string $baseUrl,
        private readonly ?string $apiKey = null,
        float $timeoutSeconds = 10.0,
        ?ClientInterface $httpClient = null,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->http = $httpClient ?? HttpClientFactory::default($timeoutSeconds);
    }

    public function health(): HealthResponse
    {
        return HealthResponse::fromArray($this->request('GET', '/v1/health'));
    }

    public function states(): StatesResponse
    {
        return StatesResponse::fromArray($this->request('GET', '/v1/states'));
    }

    public function rates(string $zip5, ?string $zip4 = null): RatesResponse
    {
        $query = ['zip5' => $zip5];
        if ($zip4 !== null) {
            $query['zip4'] = $zip4;
        }
        return RatesResponse::fromArray($this->request('GET', '/v1/rates', $query));
    }

    /**
     * @param LineItem[] $lineItems
     */
    public function calculate(Address $address, array $lineItems): CalculateResponse
    {
        $body = [
            'address' => $address->toArray(),
            'line_items' => array_map(static fn (LineItem $li) => $li->toArray(), $lineItems),
        ];
        return CalculateResponse::fromArray($this->request('POST', '/v1/calculate', null, $body));
    }

    /**
     * @param array<string, string>|null $query
     * @param array<string, mixed>|null  $jsonBody
     * @return array<string, mixed>
     *
     * @throws OpenSalesTaxNetworkException on transport failure.
     * @throws OpenSalesTaxApiException     on non-2xx HTTP, non-JSON 2xx body, or non-object 2xx body.
     */
    private function request(
        string $method,
        string $path,
        ?array $query = null,
        ?array $jsonBody = null,
    ): array {
        $url = $this->baseUrl . $path;
        if ($query !== null && $query !== []) {
            $url .= '?' . http_build_query($query);
        }

        $headers = ['Accept' => 'application/json'];
        if ($jsonBody !== null) {
            $headers['Content-Type'] = 'application/json';
        }
        if ($this->apiKey !== null) {
            $headers['X-API-Key'] = $this->apiKey;
        }

        $bodyStream = null;
        if ($jsonBody !== null) {
            $encoded = json_encode($jsonBody, JSON_THROW_ON_ERROR);
            $bodyStream = Utils::streamFor($encoded);
        }

        $request = new Request($method, $url, $headers, $bodyStream);

        try {
            $response = $this->http->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new OpenSalesTaxNetworkException(
                "Network error contacting OpenSalesTax engine at {$this->baseUrl}: " . $e->getMessage(),
                0,
                $e,
            );
        }

        $status = $response->getStatusCode();
        $rawBody = (string) $response->getBody();

        if ($status < 200 || $status >= 300) {
            $errorBody = null;
            if ($rawBody !== '') {
                try {
                    $decoded = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
                    if (is_array($decoded)) {
                        $errorBody = $decoded;
                    }
                } catch (\JsonException) {
                    // non-JSON error body — leave $errorBody as null
                }
            }
            throw new OpenSalesTaxApiException(
                message: "OpenSalesTax engine returned HTTP {$status}",
                statusCode: $status,
                rawBody: $rawBody,
                errorBody: $errorBody,
            );
        }

        try {
            $decoded = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new OpenSalesTaxApiException(
                message: 'OpenSalesTax engine returned non-JSON 2xx body: ' . $e->getMessage(),
                statusCode: $status,
                rawBody: $rawBody,
                errorBody: null,
            );
        }

        if (!is_array($decoded)) {
            throw new OpenSalesTaxApiException(
                message: "OpenSalesTax engine returned JSON 2xx body that wasn't an object",
                statusCode: $status,
                rawBody: $rawBody,
                errorBody: null,
            );
        }

        return $decoded;
    }
}
