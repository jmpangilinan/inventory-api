# ADR-002: Laravel Sanctum over Passport for API Authentication

## Status
Accepted

## Context
The API requires token-based authentication. Laravel offers two first-party packages:
Passport (OAuth2 server) and Sanctum (lightweight API tokens).

## Decision
Use Laravel Sanctum.

## Consequences
- Sanctum is sufficient for SPA and mobile API token auth without OAuth2 overhead
- No need for authorization codes, refresh tokens, or client credentials for this use case
- Simpler setup, fewer moving parts, easier to maintain
- If OAuth2 is required in the future (third-party integrations), migrating to Passport
  is straightforward as Sanctum and Passport share compatible guard conventions
