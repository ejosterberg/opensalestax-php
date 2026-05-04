<?php

// SPDX-License-Identifier: Apache-2.0

declare(strict_types=1);

namespace OpenSalesTax\Tests\Integration;

use OpenSalesTax\Address;
use OpenSalesTax\Client;
use OpenSalesTax\LineItem;
use PHPUnit\Framework\TestCase;

/**
 * Hits a real OpenSalesTax engine. Skipped unless OPENSALESTAX_BASE_URL is set.
 *
 * Locally:
 *   OPENSALESTAX_BASE_URL=http://10.32.161.126:8080 vendor/bin/phpunit
 *
 * In CI, set OPENSALESTAX_BASE_URL only on jobs you want to exercise the
 * live engine; unit tests cover every code path that doesn't need network.
 */
final class ClientLiveTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $base = getenv('OPENSALESTAX_BASE_URL');
        if ($base === false || $base === '') {
            self::markTestSkipped('OPENSALESTAX_BASE_URL not set; skipping live-engine tests.');
        }

        $apiKey = getenv('OPENSALESTAX_API_KEY');
        $apiKey = ($apiKey !== false && $apiKey !== '') ? $apiKey : null;

        $this->client = new Client(baseUrl: $base, apiKey: $apiKey);
    }

    public function testHealth(): void
    {
        $r = $this->client->health();
        self::assertContains($r->status, ['ok', 'degraded']);
        self::assertNotSame('', $r->version);
    }

    public function testStatesReturnsAtLeastFiftyTwoEntries(): void
    {
        $r = $this->client->states();
        self::assertGreaterThanOrEqual(52, $r->total);
        self::assertNotEmpty($r->states);
    }

    public function testRatesForMinneapolisZipReturnsJurisdictions(): void
    {
        $r = $this->client->rates(zip5: '55401');
        self::assertNotEmpty($r->jurisdictions);
        self::assertNotSame('', $r->combinedRatePct);
        self::assertNotSame('', $r->disclaimer);
    }

    public function testCalculateMinneapolisMixedCart(): void
    {
        $r = $this->client->calculate(
            address: new Address(zip5: '55401'),
            lineItems: [
                new LineItem(amount: '100.00', category: 'general'),
                new LineItem(amount: '50.00', category: 'clothing'),
            ],
        );
        self::assertSame('150.00', $r->subtotal);
        self::assertCount(2, $r->lines);

        // Clothing in MN is non-taxable per engine state-law module.
        $clothingLines = array_values(array_filter(
            $r->lines,
            static fn ($l) => $l->category === 'clothing',
        ));
        self::assertCount(1, $clothingLines);
        self::assertSame('0', $clothingLines[0]->tax);
    }
}
