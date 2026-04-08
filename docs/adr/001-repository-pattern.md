# ADR-001: Repository Pattern for Data Access

## Status
Accepted

## Context
The application needs a clear separation between business logic and data access.
Controllers calling Eloquent models directly couples HTTP logic to persistence,
making unit testing and future database changes difficult.

## Decision
Adopt the Repository pattern with explicit interfaces bound via a `RepositoryServiceProvider`.
All database queries live in `app/Repositories/Eloquent/`.
Services depend on interfaces, not concrete implementations.

## Consequences
- Services are testable in isolation by mocking the repository interface
- Swapping the underlying ORM or database requires only a new implementation class
- More boilerplate per model, acceptable for a business-critical API
- Eloquent-specific features (scopes, relationships) are encapsulated inside repository methods
