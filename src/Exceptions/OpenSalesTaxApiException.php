<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Exceptions;

final class OpenSalesTaxApiException extends OpenSalesTaxException
{
    /**
     * @param array<string, mixed>|null $errorBody Decoded error body if JSON; null if non-JSON or absent.
     */
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly string $rawBody = '',
        public readonly ?array $errorBody = null,
    ) {
        parent::__construct($message);
    }
}
