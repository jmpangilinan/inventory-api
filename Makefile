# Inventory API - Docker command shortcuts

PHP = docker-compose exec app php
COMPOSER = docker-compose exec app composer
ARTISAN = docker-compose exec app php artisan

# ── Docker ────────────────────────────────────────────────
up:
	docker-compose up -d

down:
	docker-compose down

build:
	docker-compose up -d --build

logs:
	docker-compose logs -f

shell:
	docker-compose exec app bash

# ── Laravel setup ─────────────────────────────────────────
install:
	$(COMPOSER) install

setup: install
	cp .env.example .env
	$(ARTISAN) key:generate
	$(ARTISAN) migrate --seed

# ── Database ──────────────────────────────────────────────
migrate:
	$(ARTISAN) migrate

migrate-fresh:
	$(ARTISAN) migrate:fresh --seed

seed:
	$(ARTISAN) db:seed

# ── Testing ───────────────────────────────────────────────
test:
	$(ARTISAN) test

test-coverage:
	$(ARTISAN) test --coverage --min=80

# ── Code quality ──────────────────────────────────────────
lint:
	docker-compose exec app ./vendor/bin/pint

lint-check:
	docker-compose exec app ./vendor/bin/pint --test

# ── API Docs ──────────────────────────────────────────────
docs:
	$(ARTISAN) l5-swagger:generate

# ── Composer ─────────────────────────────────────────────
require:
	$(COMPOSER) require $(pkg)

require-dev:
	$(COMPOSER) require --dev $(pkg)

.PHONY: up down build logs shell install setup migrate migrate-fresh seed test test-coverage lint lint-check docs require require-dev
