# ADR-003: Spatie Activitylog over Custom Audit Table

## Status
Accepted

## Context
The application requires a full audit trail of who changed what and when,
particularly for stock transactions. Options were a custom `audit_logs` table
or the Spatie Laravel Activitylog package.

## Decision
Use `spatie/laravel-activitylog`.

## Consequences
- Battle-tested package maintained by Spatie, reduces custom code
- Supports causer tracking (authenticated user), subject polymorphism, and event types
- `activity_log` table schema is standardised and well-documented
- Custom audit table would offer more control but requires significant maintenance
- For highly specialised audit needs (e.g., field-level diffs for compliance), the package
  already provides `withProperties()` for custom payloads
