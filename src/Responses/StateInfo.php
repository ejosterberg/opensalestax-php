<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Responses;

use OpenSalesTax\Internal\Decode;

/**
 * One entry in the /v1/states coverage list.
 *
 * Tier semantics:
 * - 0 = unsupported (catalog entry only; calculate returns zero)
 * - 1 = fully maintained (taxability matrix + tests)
 * - 2 = rate-only via SST data with default taxability
 */
final readonly class StateInfo
{
    public function __construct(
        public string $abbrev,
        public string $name,
        public bool $hasSalesTax,
        public bool $sstMember,
        public int $tier,
        public string $notes = '',
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            abbrev: Decode::str($data, 'abbrev'),
            name: Decode::str($data, 'name'),
            hasSalesTax: Decode::bool($data, 'has_sales_tax'),
            sstMember: Decode::bool($data, 'sst_member'),
            tier: Decode::int($data, 'tier'),
            notes: Decode::str($data, 'notes'),
        );
    }
}
