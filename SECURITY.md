# Security Policy

## Reporting a vulnerability

Email **ejosterberg@gmail.com** with subject line starting
`[opensalestax-php] security:`. Include affected version,
reproduction steps, and impact. Do not open a public GitHub
issue for security reports.

Acknowledgement target: 7 days. Critical issues
(tax-correctness or sensitive-data access): mark `[critical]`
in subject, expect faster turnaround.

Disclosure window: 90 days from acknowledgement, or sooner
once a fix ships.

## Supported versions

Latest minor on `main`. Older releases are not back-patched.

## Threat model

This SDK is a thin HTTP client that lives in the merchant's
own PHP process and talks to a self-hosted OpenSalesTax
engine instance the merchant runs. There is no inbound HTTP
surface, no webhook receiver, no JWT to manage. The trust
boundary is the merchant's PHP host.

Configuration (engine base URL, optional API key, timeout)
comes from the merchant's own code at SDK construction time.
The URL is not validated for SSRF here — that's the
connector's responsibility (Bagisto, WooCommerce, Magento,
etc. all implement an SSRF allowlist). If you're using the
SDK directly, validate the URL yourself.

The SDK does NOT log request bodies or response bodies. It
DOES throw structured exceptions carrying HTTP status + a
short raw-body excerpt for diagnostics. If you log those
exceptions, sanitize first if your engine returns
customer-derived data.

## Out of scope

- The engine itself — report engine bugs to
  https://github.com/ejosterberg/open-sales-tax/issues
- The merchant's PHP application code that calls this SDK —
  out of scope; that's the merchant's responsibility
