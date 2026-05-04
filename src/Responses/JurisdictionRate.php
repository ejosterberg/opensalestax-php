<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Responses;

use OpenSalesTax\Internal\Decode;

/**
 * One jurisdiction's contribution to the rate stack.
 *
 * `tax` is populated only when this object appears inside a /v1/calculate
 * response (where a line amount exists). The sum of per-jurisdiction `tax`
 * values equals the line's tax exactly — the engine quantizes per-jurisdiction
 * first, then sums.
 */
final readonly class JurisdictionRate
{
    public function __construct(
        public string $name,
        public string $type,
        public string $ratePct,
        public ?string $tax = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: Decode::str($data, 'name'),
            type: Decode::str($data, 'type'),
            ratePct: Decode::str($data, 'rate_pct', '0'),
            tax: Decode::nullableStr($data, 'tax'),
        );
    }
}
