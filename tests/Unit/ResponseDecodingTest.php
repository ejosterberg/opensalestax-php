<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Tests\Unit;

use OpenSalesTax\Responses\CalculateResponse;
use OpenSalesTax\Responses\HealthResponse;
use OpenSalesTax\Responses\RatesResponse;
use OpenSalesTax\Responses\StatesResponse;
use PHPUnit\Framework\TestCase;

/**
 * Decode JSON fixtures captured from the live engine on 2026-05-03 (engine v0.14.0)
 * into typed DTOs and assert key invariants. No network.
 */
final class ResponseDecodingTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/fixtures';

    /** @return array<string, mixed> */
    private static function load(string $name): array
    {
        $raw = file_get_contents(self::FIXTURE_DIR . '/' . $name);
        if ($raw === false) {
            self::fail("Fixture missing: {$name}");
        }
        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            self::fail("Fixture {$name} is not valid JSON: " . $e->getMessage());
        }
        if (!is_array($decoded)) {
            self::fail("Fixture {$name} is not a JSON object.");
        }
        return $decoded;
    }

    public function testHealthFixture(): void
    {
        $r = HealthResponse::fromArray(self::load('health.json'));
        self::assertContains($r->status, ['ok', 'degraded']);
        self::assertNotSame('', $r->version);
    }

    public function testStatesFixture(): void
    {
        $r = StatesResponse::fromArray(self::load('states.json'));
        self::assertGreaterThanOrEqual(52, $r->total);
        self::assertGreaterThanOrEqual(52, count($r->states));
        self::assertNotEmpty($r->states);
        self::assertSame(2, strlen($r->states[0]->abbrev));
    }

    public function testRatesFixture(): void
    {
        $r = RatesResponse::fromArray(self::load('rates.json'));
        self::assertNotEmpty($r->jurisdictions, 'MN ZIP 55401 should resolve to a non-empty rate stack.');
        self::assertNotSame('', $r->combinedRatePct);
        self::assertNotSame('', $r->disclaimer);

        // /v1/rates jurisdictions never carry tax (no line amounts to apply against)
        foreach ($r->jurisdictions as $j) {
            self::assertNull($j->tax);
        }
    }

    public function testCalculateFixture(): void
    {
        $r = CalculateResponse::fromArray(self::load('calculate-mn-mixed.json'));
        self::assertSame('150.00', $r->subtotal);
        self::assertNotSame('', $r->taxTotal);
        self::assertCount(2, $r->lines);

        // Clothing in Minnesota is non-taxable; expect tax="0" + an explanatory note.
        $clothingLines = array_values(array_filter(
            $r->lines,
            static fn ($l) => $l->category === 'clothing',
        ));
        self::assertCount(1, $clothingLines);
        self::assertSame('0', $clothingLines[0]->tax);
        self::assertNotNull($clothingLines[0]->note);
    }
}
