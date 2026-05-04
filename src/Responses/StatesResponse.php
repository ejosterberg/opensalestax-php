<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Responses;

use OpenSalesTax\Internal\Decode;

final readonly class StatesResponse
{
    /**
     * @param StateInfo[] $states
     */
    public function __construct(
        public array $states,
        public int $total,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $statesList = [];
        foreach (Decode::listOfArrays($data, 'states') as $entry) {
            $statesList[] = StateInfo::fromArray($entry);
        }
        return new self(
            states: $statesList,
            total: Decode::int($data, 'total'),
        );
    }
}
