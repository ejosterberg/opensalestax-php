# Changelog

All notable changes to this project are documented here.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Versioning: [SemVer](https://semver.org).

## [Unreleased]

## [0.1.0] — 2026-05-03

### Added
- Initial v0.1 alpha against engine v0.14.0
- `OpenSalesTax\Client` wrapping `/v1/health`, `/v1/states`, `/v1/rates`, `/v1/calculate`
- Request DTOs: `Address`, `LineItem` (PHP 8.1 readonly classes)
- Response DTOs: `HealthResponse`, `StatesResponse`, `StateInfo`, `RatesResponse`, `CalculateResponse`, `CalculatedLine`, `JurisdictionRate`
- Exceptions: `OpenSalesTaxException` (base), `OpenSalesTaxApiException`, `OpenSalesTaxNetworkException`, `OpenSalesTaxValidationException`
- PSR-18 HTTP client interface (default Guzzle 7; injectable)
- `X-API-Key` header auth (optional)
- Decimal-string amount handling (no float / cents conversion)
- PHPUnit test suite with fixture-based unit tests + gated live-engine integration tests
- GitHub Actions CI on PHP 8.2 / 8.3 / 8.4
