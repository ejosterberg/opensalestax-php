<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Internal;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Client\ClientInterface;

/**
 * Default PSR-18 client factory.
 *
 * Internal: callers that want a custom client inject it directly into
 * `OpenSalesTax\Client::__construct()` rather than using this factory.
 */
final class HttpClientFactory
{
    public static function default(float $timeoutSeconds): ClientInterface
    {
        return new GuzzleClient([
            'timeout' => $timeoutSeconds,
            'http_errors' => false,
            'connect_timeout' => $timeoutSeconds,
        ]);
    }
}
