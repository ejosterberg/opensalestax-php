<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Responses;

use OpenSalesTax\Internal\Decode;

final readonly class CalculateResponse
{
    /**
     * @param CalculatedLine[] $lines
     */
    public function __construct(
        public string $subtotal,
        public string $taxTotal,
        public array $lines,
        public string $disclaimer,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $ll = [];
        foreach (Decode::listOfArrays($data, 'lines') as $line) {
            $ll[] = CalculatedLine::fromArray($line);
        }
        return new self(
            subtotal: Decode::str($data, 'subtotal', '0'),
            taxTotal: Decode::str($data, 'tax_total', '0'),
            lines: $ll,
            disclaimer: Decode::str($data, 'disclaimer'),
        );
    }
}
