<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Responses;

use OpenSalesTax\Internal\Decode;

final readonly class RatesResponse
{
    /**
     * @param array<string, mixed> $input
     * @param JurisdictionRate[] $jurisdictions
     */
    public function __construct(
        public array $input,
        public array $jurisdictions,
        public string $combinedRatePct,
        public string $disclaimer,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $jl = [];
        foreach (Decode::listOfArrays($data, 'jurisdictions') as $j) {
            $jl[] = JurisdictionRate::fromArray($j);
        }
        return new self(
            input: Decode::arr($data, 'input'),
            jurisdictions: $jl,
            combinedRatePct: Decode::str($data, 'combined_rate_pct', '0'),
            disclaimer: Decode::str($data, 'disclaimer'),
        );
    }
}
