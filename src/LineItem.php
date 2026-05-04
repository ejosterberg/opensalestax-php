<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax;

use OpenSalesTax\Exceptions\OpenSalesTaxValidationException;

/**
 * One taxable line in a calculate request.
 *
 * `amount` is a decimal string (engine schema regex: ^[+-]?0*\d*\.?\d*$).
 * Strings preserve exact precision; the engine quantizes per-jurisdiction
 * in fixed-point, then sums. Float inputs would lose precision.
 *
 * Standard categories per engine v0.14: general (default), clothing,
 * groceries, prescription_drugs, prepared_food, digital_goods.
 */
final readonly class LineItem
{
    public function __construct(
        public string $amount,
        public string $category = 'general',
    ) {
        if ($category === '') {
            throw new OpenSalesTaxValidationException('category cannot be empty');
        }
        if (preg_match('/^(?!^[-+.]*$)[+-]?0*\d*\.?\d*$/', $amount) !== 1) {
            throw new OpenSalesTaxValidationException(
                "amount must be a decimal string, got: {$amount}",
            );
        }
        if (str_starts_with($amount, '-')) {
            throw new OpenSalesTaxValidationException(
                "amount must be non-negative, got: {$amount}",
            );
        }
    }

    /** @return array{amount: string, category: string} */
    public function toArray(): array
    {
        return ['amount' => $this->amount, 'category' => $this->category];
    }
}
