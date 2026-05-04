# opensalestax-php

> PHP SDK for the [OpenSalesTax](https://github.com/ejosterberg/open-sales-tax) engine — open-source US sales tax calculation API.

**Status:** v0.1 alpha. API is stable but the engine pin moves quickly. Not yet on Packagist.

## Install

```bash
composer require ejosterberg/opensalestax
```

(Once the package is on Packagist. While the repo is private, install from the Git remote: `composer config repositories.opensalestax vcs git@github.com:ejosterberg/opensalestax-php.git && composer require ejosterberg/opensalestax:dev-main`.)

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
echo $result->taxTotal;  // "0.1500"

foreach ($result->lines as $line) {
    echo "{$line->category}: \${$line->tax}\n";
    if ($line->note !== null) {
        echo "  → {$line->note}\n";
    }
}
```

## API

```php
$client = new Client(
    baseUrl: 'http://your-engine:8080',
    apiKey: 'optional-x-api-key',  // null/omitted if engine doesn't require auth
    timeoutSeconds: 10.0,
    httpClient: null,              // optional PSR-18 override
);

$client->health();                          // HealthResponse{status, version, databaseConnected}
$client->states();                          // StatesResponse{states[StateInfo], total}
$client->rates(zip5: '55401');              // RatesResponse{input, jurisdictions[], combinedRatePct, disclaimer}
$client->calculate($address, $lineItems);   // CalculateResponse{subtotal, taxTotal, lines[], disclaimer}
```

## Tax categories

Standard categories per engine v0.14: `general` (default), `clothing`, `groceries`, `prescription_drugs`, `prepared_food`, `digital_goods`. Per-state taxability rules apply.

## Amounts

Amounts are **decimal strings**, not integers (cents) or floats. Strings preserve the engine's exact precision; the engine quantizes per-jurisdiction in fixed-point. Convert from cents in your own code if needed:

```php
$cents = 9999;
$amount = number_format($cents / 100, 2, '.', '');  // "99.99"
```

## Errors

All errors extend `OpenSalesTax\Exceptions\OpenSalesTaxException`:

- `OpenSalesTaxApiException` — non-2xx HTTP from the engine; carries `statusCode`, `rawBody`, `errorBody`.
- `OpenSalesTaxNetworkException` — transport failure (timeout, DNS); wraps the underlying PSR-18 exception.
- `OpenSalesTaxValidationException` — client-side input rejected before the request was sent (bad ZIP, negative amount, etc.).

## Engine compatibility

This SDK targets the OpenSalesTax v1 HTTP API. Tested against engine **v0.14.0**. Pin both the SDK and the engine in production:

```
ejosterberg/opensalestax: ^0.1
opensalestax engine:      v0.14.x
```

## Disclaimer

> Tax calculations are provided as-is for convenience. The merchant is solely responsible for tax-collection accuracy and remittance to the appropriate jurisdictions. Verify against your state Department of Revenue before remitting.

## Contributing

DCO sign-off (`git commit -s`) required on every commit. See `CONTRIBUTING.md`.

## License

[Apache 2.0](LICENSE).
