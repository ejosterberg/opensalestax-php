<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Tests\Unit;

use OpenSalesTax\Exceptions\OpenSalesTaxValidationException;
use OpenSalesTax\LineItem;
use PHPUnit\Framework\TestCase;

final class LineItemTest extends TestCase
{
    public function testDefaultsToGeneralCategory(): void
    {
        $li = new LineItem(amount: '100.00');
        self::assertSame('100.00', $li->amount);
        self::assertSame('general', $li->category);
        self::assertSame(['amount' => '100.00', 'category' => 'general'], $li->toArray());
    }

    public function testExplicitCategory(): void
    {
        $li = new LineItem(amount: '50.00', category: 'clothing');
        self::assertSame('clothing', $li->category);
    }

    public function testIntegerAmountString(): void
    {
        $li = new LineItem(amount: '100');
        self::assertSame('100', $li->amount);
    }

    public function testRejectsNegativeAmount(): void
    {
        $this->expectException(OpenSalesTaxValidationException::class);
        new LineItem(amount: '-10.00');
    }

    public function testRejectsNonDecimalAmount(): void
    {
        $this->expectException(OpenSalesTaxValidationException::class);
        new LineItem(amount: 'abc');
    }

    public function testRejectsEmptyCategory(): void
    {
        $this->expectException(OpenSalesTaxValidationException::class);
        new LineItem(amount: '100.00', category: '');
    }
}
