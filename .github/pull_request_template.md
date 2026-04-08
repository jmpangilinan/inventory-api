## What
<!-- Brief description of what this PR does -->

## Why
<!-- Context and motivation. Link related issues if any. Closes #xxx -->

## Changes
- 
- 

## How to Test
```bash
# Commands to test this PR locally
make migrate-fresh
make test
```

## Type of change
- [ ] Bug fix
- [ ] New feature
- [ ] Refactor / chore
- [ ] Documentation

## Pre-push checklist
- [ ] `make lint-check` passes (Pint — PSR-12)
- [ ] `make analyse` passes (Larastan level 6)
- [ ] `make test-coverage` passes (PHPUnit — ≥80% coverage)
- [ ] New code has corresponding tests
- [ ] Migrations included (if schema changed)
- [ ] `.env.example` updated (if new env vars added)
- [ ] No hardcoded secrets or `http://` URLs
