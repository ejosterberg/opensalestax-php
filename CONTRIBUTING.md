# Contributing

## Developer Certificate of Origin (DCO)

Every commit must be signed off by its author with the `--signoff` (`-s`) flag:

```bash
git commit -s -m "Your message"
```

This appends a `Signed-off-by: Your Name <your-email>` trailer that certifies you wrote the patch (or have the right to submit it under the project's license). See https://developercertificate.org for the full text.

CI in this repo enforces DCO on every PR. PRs without sign-off are blocked until you amend.

## License

By contributing, you agree your contribution is licensed under Apache 2.0 (the project's LICENSE).

Every source file must carry an `SPDX-License-Identifier: Apache-2.0` header.

## Running tests

Unit tests (no network):

```bash
composer test
# or
vendor/bin/phpunit --testsuite=unit
```

Integration tests against a live OpenSalesTax engine:

```bash
export OPENSALESTAX_BASE_URL=http://your-engine:8080
export OPENSALESTAX_API_KEY=optional-key  # only if your engine requires auth
vendor/bin/phpunit
```

Integration tests are **skipped** when `OPENSALESTAX_BASE_URL` is unset.

## Static analysis

```bash
composer stan      # phpstan --level=max
composer lint      # php-cs-fixer dry-run
composer lint-fix  # php-cs-fixer apply
```

CI runs all three.

## Reporting issues

Open an issue on GitHub. Include:

- The OpenSalesTax engine version (from `GET /v1/health`)
- The SDK version (`ejosterberg/opensalestax` version in your `composer.lock`)
- A minimal reproducer

For security issues: email ejosterberg@gmail.com directly rather than opening a public issue.
