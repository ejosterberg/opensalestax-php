<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Tests\Unit;

use OpenSalesTax\Address;
use OpenSalesTax\Exceptions\OpenSalesTaxValidationException;
use PHPUnit\Framework\TestCase;

final class AddressTest extends TestCase
{
    public function testHappyPathZip5Only(): void
    {
        $a = new Address(zip5: '55401');
        self::assertSame('55401', $a->zip5);
        self::assertNull($a->zip4);
        self::assertSame(['zip5' => '55401'], $a->toArray());
    }

    public function testHappyPathZip5AndZip4(): void
    {
        $a = new Address(zip5: '55401', zip4: '1234');
        self::assertSame('55401', $a->zip5);
        self::assertSame('1234', $a->zip4);
        self::assertSame(['zip5' => '55401', 'zip4' => '1234'], $a->toArray());
    }

    public function testRejectsZip5WrongLength(): void
    {
        $this->expectException(OpenSalesTaxValidationException::class);
        new Address(zip5: '5540');
    }

    public function testRejectsZip5TooLong(): void
    {
        $this->expectException(OpenSalesTaxValidationException::class);
        new Address(zip5: '554010');
    }

    public function testRejectsZip5NonNumeric(): void
    {
        $this->expectException(OpenSalesTaxValidationException::class);
        new Address(zip5: '5540A');
    }

    public function testRejectsZip4WrongLength(): void
    {
        $this->expectException(OpenSalesTaxValidationException::class);
        new Address(zip5: '55401', zip4: '12345');
    }

    public function testRejectsZip4NonNumeric(): void
    {
        $this->expectException(OpenSalesTaxValidationException::class);
        new Address(zip5: '55401', zip4: 'abcd');
    }
}
