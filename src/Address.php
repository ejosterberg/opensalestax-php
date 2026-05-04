<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax;

use OpenSalesTax\Exceptions\OpenSalesTaxValidationException;

/**
 * ZIP-based address for OpenSalesTax v1 calculations.
 *
 * Engine v0.14 derives state from ZIP — there is no state field.
 */
final readonly class Address
{
    public function __construct(
        public string $zip5,
        public ?string $zip4 = null,
    ) {
        if (preg_match('/^\d{5}$/', $zip5) !== 1) {
            throw new OpenSalesTaxValidationException(
                "zip5 must be exactly 5 digits, got: {$zip5}",
            );
        }
        if ($zip4 !== null && preg_match('/^\d{4}$/', $zip4) !== 1) {
            throw new OpenSalesTaxValidationException(
                "zip4 must be exactly 4 digits, got: {$zip4}",
            );
        }
    }

    /** @return array{zip5: string, zip4?: string} */
    public function toArray(): array
    {
        $out = ['zip5' => $this->zip5];
        if ($this->zip4 !== null) {
            $out['zip4'] = $this->zip4;
        }
        return $out;
    }
}
