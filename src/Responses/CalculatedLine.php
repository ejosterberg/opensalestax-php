<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Responses;

use OpenSalesTax\Internal\Decode;

/**
 * One calculated line in a /v1/calculate response.
 *
 * Invariant: `tax == sum(j.tax for j in jurisdictions)` — the per-jurisdiction
 * breakdown reconciles exactly with the line total. Use the breakdown for
 * accounting (state/county/city splits); use `tax` for the customer-facing total.
 *
 * `note` is populated when the line carries an explanatory footnote — typically
 * because the category is non-taxable in the resolved state, or the ZIP isn't
 * covered by any loaded state module.
 */
final readonly class CalculatedLine
{
    /**
     * @param JurisdictionRate[] $jurisdictions
     */
    public function __construct(
        public string $amount,
        public string $category,
        public string $tax,
        public string $ratePct,
        public array $jurisdictions = [],
        public ?string $note = null,
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
            amount: Decode::str($data, 'amount', '0'),
            category: Decode::str($data, 'category', 'general'),
            tax: Decode::str($data, 'tax', '0'),
            ratePct: Decode::str($data, 'rate_pct', '0'),
            jurisdictions: $jl,
            note: Decode::nullableStr($data, 'note'),
        );
    }
}
