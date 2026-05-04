<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Responses;

use OpenSalesTax\Internal\Decode;

final readonly class HealthResponse
{
    public function __construct(
        public string $status,
        public string $version,
        public bool $databaseConnected,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            status: Decode::str($data, 'status'),
            version: Decode::str($data, 'version'),
            databaseConnected: Decode::bool($data, 'database_connected'),
        );
    }
}
