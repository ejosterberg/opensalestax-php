# opensalestax-php

> PHP SDK for the [OpenSalesTax](https://github.com/ejosterberg/open-sales-tax) engine — the open-source, self-hostable US sales tax calculation API.

[![License](https://img.shields.io/badge/license-Apache%202.0-blue)](LICENSE) [![PHP](https://img.shields.io/badge/php-%E2%89%A58.2-777bb4)](composer.json)

**Status:** v0.1 alpha. API surface stable. Tested against engine v0.14 — v0.24.

## Why this exists

US sales tax is a mess: ~10,000 jurisdictions, ~50,000 ZIPs, rates change quarterly, taxability rules vary per state per category. The commercial APIs (Avalara, TaxJar, Stripe Tax) charge $0.50–$10+ per transaction or 0.5% of revenue.

[**OpenSalesTax**](https://github.com/ejosterberg/open-sales-tax) is the open-source self-hostable engine. **This SDK** is the PHP wrapper around its v1 HTTP API — `composer require`, point at your engine, get tax.

```php
$client = new OpenSalesTaxClient(baseUrl: 'http://your-engine:8080');

$result = $client->calculate(
    address: new Address(zip5: '55401'),
    lineItems: [new LineItem(amount: '100.00', category: 'general')],
);

echo $result->taxTotal;  // "8.025"
```

That's it. ~200 LOC of stateless wrapper code — no business logic, no caching, no surprise dependencies. The complexity lives in the engine; this SDK just calls it.

## Install

```bash
composer require ejosterberg/opensalestax
```

Requires PHP 8.2+ (uses class-level `readonly` syntax for DTOs) and a reachable OpenSalesTax engine (self-host via the [engine's docker-compose](https://github.com/ejosterberg/open-sales-tax)).

## Quickstart

```php
use OpenSalesTax\Client;
use OpenSalesTax\Address;
use OpenSalesTax\LineItem;

$client = new Client(baseUrl: 'http://localhost:8080');

$result = $client->calculate(
    address: new Address(zip5: '55401'),
    lineItems: [
        new LineItem(amount: '100.00', category: 'general'),
        new LineItem(amount: '50.00', category: 'clothing'),
    ],
);

echo $result->subtotal;  // "150.00"
echo $result->taxTotal;  // "8.025"

foreach ($result->lines as $line) {
    echo "{$line->category}: \${$line->tax}\n";
    if ($line->note !== null) {
        echo "  → {$line->note}\n";   // e.g. "Clothing is non-taxable in Minnesota..."
    }
}
```

## API surface

```php
$client = new Client(
    baseUrl: 'http://your-engine:8080',
    apiKey: 'optional-x-api-key',  // null if engine doesn't require auth
    timeoutSeconds: 10.0,
    httpClient: null,              // optional PSR-18 override (Guzzle 7 default)
);

$client->health();                          // HealthResponse{status, version, databaseConnected}
$client->states();                          // StatesResponse{states[StateInfo], total}
$client->rates(zip5: '55401');              // RatesResponse{input, jurisdictions[], combinedRatePct, disclaimer}
$client->calculate($address, $lineItems);   // CalculateResponse{subtotal, taxTotal, lines[], disclaimer}
```

Each line in a `CalculateResponse` carries the per-jurisdiction breakdown:

```php
foreach ($result->lines as $line) {
    foreach ($line->jurisdictions as $j) {
        echo "  {$j->type:9} {$j->name:50} {$j->ratePct}% \${$j->tax}\n";
        // state     Minnesota                                    6.875% $6.8750
        // county    Hennepin County                              0.15%  $0.1500
        // city      Minneapolis                                  0.5%   $0.5000
        // district  Hennepin County Transit Sales Tax            0.5%   $0.5000
    }
}
```

Sums reconcile exactly: `$line->tax === sum($line->jurisdictions[*]->tax)`. Use the breakdown for accounting (state/county/city splits); use `$line->tax` for the customer-facing total.

## Tax categories

Standard categories the engine recognizes: `general` (default), `clothing`, `groceries`, `prescription_drugs`, `prepared_food`, `digital_goods`. Per-state taxability rules apply (e.g. clothing is non-taxable in Minnesota; groceries in most states).

## Amounts are decimal strings

Amounts are **strings**, not integers (cents) or floats. Strings preserve the engine's exact precision; the engine quantizes per-jurisdiction in fixed-point. Convert from cents in your own code if you need to:

```php
$cents = 9999;
$amount = number_format($cents / 100, 2, '.', '');  // "99.99"
new LineItem(amount: $amount, category: 'general');
```

## Errors

Flat hierarchy. All errors extend `OpenSalesTax\Exceptions\OpenSalesTaxException`:

- `OpenSalesTaxApiException` — non-2xx HTTP from the engine; carries `statusCode`, `rawBody`, `errorBody`.
- `OpenSalesTaxNetworkException` — transport failure (timeout, DNS); wraps the underlying PSR-18 exception via `getPrevious()`.
- `OpenSalesTaxValidationException` — client-side input rejected before sending (bad ZIP regex, negative amount).

```php
try {
    $result = $client->calculate(...);
} catch (OpenSalesTaxApiException $e) {
    error_log("Engine returned {$e->statusCode}: {$e->rawBody}");
} catch (OpenSalesTaxNetworkException $e) {
    error_log("Cannot reach engine: " . $e->getMessage());
}
```

## Quality bar

- **PHPStan level=max** — zero suppressed errors
- **PHP-CS-Fixer** with PSR-12 + risky rules — zero violations
- **PHPUnit** — 21 unit + integration tests, 54 assertions, all passing
- **GitHub Actions CI** matrix on PHP 8.2 / 8.3 / 8.4
- **DCO sign-off** required on every commit

## Engine compatibility

This SDK targets the OpenSalesTax v1 HTTP API. Tested against engine **v0.14 — v0.24**. The v1 API surface has been stable across that range. Pin both in production:

```
ejosterberg/opensalestax: ^0.1
opensalestax engine:      v0.20+ (recommended; older versions had a state-bleed bug fixed in v0.22)
```

## What this SDK is NOT

- **Not the engine.** See [open-sales-tax](https://github.com/ejosterberg/open-sales-tax) for the calculator itself.
- **Not Stripe-aware.** For a Stripe Tax replacement, layer [opensalestax-stripe-php](https://github.com/ejosterberg/opensalestax-stripe-php) on top.
- **Not a tax-filing service** — calculation only. The merchant remits.
- **Not a caching layer.** Caching is the consumer's job because cache-invalidation policy is platform-specific.

## Disclaimer

> Tax calculations are provided as-is for convenience. The merchant is solely responsible for tax-collection accuracy and remittance to the appropriate jurisdictions. Verify against your state Department of Revenue before remitting.

## Contributing

DCO sign-off (`git commit -s`) required on every commit. See [CONTRIBUTING.md](CONTRIBUTING.md). Apache 2.0 + SPDX header on every source file.

## License

[Apache 2.0](LICENSE).
