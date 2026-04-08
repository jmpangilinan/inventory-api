# ADR-004: HMAC-SHA256 Signature Verification for Device Webhooks

## Status
Accepted

## Context
External devices (barcode scanners, weighing scales, biometric terminals) push data
to the API via webhooks. Without verification, any actor could POST arbitrary payloads
to the webhook endpoint.

## Decision
Verify incoming device webhook requests using HMAC-SHA256 signatures.
Devices sign the raw request body with a shared secret (`DEVICE_WEBHOOK_SECRET`).
The API rejects requests with missing or invalid `X-Device-Signature` headers.

```
Signature = HMAC-SHA256(raw_body, DEVICE_WEBHOOK_SECRET)
```

## Consequences
- Only devices holding the shared secret can submit valid payloads
- Replay attacks are a residual risk — mitigated in future by adding a timestamp
  nonce to the signed payload and rejecting requests older than 5 minutes
- Secret rotation requires coordination with device firmware — documented in ops runbook
- This is the same mechanism used by GitHub, Stripe, and Shopify webhooks
