# Project Status App - local setup
#
# Usage on a freshly copied/cloned machine:
#   make setup   # installs dependencies, creates .env, generates key, migrates + seeds, builds assets
#   make dev     # starts the Laravel dev server + Vite together
#
# Requires PHP 8.3+, Composer, Node.js 18+, and a running MariaDB/MySQL server
# already on PATH. See README.md for how to install those if this is a brand
# new machine, and for the manual (non-make) equivalent of every target below.

.PHONY: help install env db-create migrate migrate-fresh seed fresh setup serve dev build watch test pint clean

help:
	@echo Available targets:
	@echo   make setup          Full first-time setup (install + env + fresh migrate + seed + build)
	@echo   make install        Install PHP and JS dependencies (composer + npm)
	@echo   make env            Copy .env.example to .env (if missing) and generate the app key
	@echo   make db-create      Create the MariaDB database defined in .env
	@echo   make migrate        Run pending migrations
	@echo   make migrate-fresh  Drop all tables and re-run every migration
	@echo   make seed           Seed the database (admin + staff users + sample projects)
	@echo   make fresh          migrate-fresh + seed in one step
	@echo   make serve          Start the PHP dev server on http://127.0.0.1:8000
	@echo   make dev            Start the dev server + Vite together (hot reload)
	@echo   make build          Build production frontend assets
	@echo   make test           Run the PHPUnit test suite
	@echo   make pint           Run Laravel Pint (code style fixer)
	@echo   make clean          Remove vendor/, node_modules/, and cached files

install:
	composer install
	npm install

env:
	php -r "file_exists('.env') || copy('.env.example', '.env');"
	php scripts/ensure-key.php

# Creates the database using the credentials in .env. See scripts/create-database.php.
db-create:
	php scripts/create-database.php

migrate:
	php artisan migrate

migrate-fresh:
	php artisan migrate:fresh

seed:
	php artisan db:seed

fresh: migrate-fresh seed

# NOTE: uses migrate-fresh (drops all tables first) rather than migrate, so
# `make setup` is always safe to re-run - re-running plain `migrate` + `seed`
# on a database that's already been seeded once would fail on duplicate rows.
# Only ever run this against a database whose data you don't need to keep.
setup: install env db-create migrate-fresh seed build
	@echo Setup complete. Run make serve (or make dev) and open http://127.0.0.1:8000
	@echo Admin login: admin@example.com / password

serve:
	php artisan serve

dev:
	php artisan dev

build:
	npm run build

watch:
	npm run dev

test:
	php artisan test

pint:
	php vendor/bin/pint

clean:
	php scripts/clean.php
