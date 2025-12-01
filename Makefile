#!/usr/bin/make -f
ifneq (,$(wildcard ./.env))
	include .env
	export
endif

build:
	docker compose up -d --build
	docker compose exec app composer i
	yarn && yarn build

serve:
	docker compose up -d
	docker compose exec app bin/console migration:migrate --no-interaction
	docker compose exec app bin/console orm:generate-proxies
	docker compose exec app bin/console app:auth:resources:update
	docker compose exec app bin/console app:user:role:assign
	docker compose exec -t documan /documan/bin/documan import

sh:
	docker compose exec -it app /bin/bash

format:
	docker compose exec app composer format
	yarn format
	docker compose exec -t documan /documan/bin/documan lint

format-check:
	docker compose exec app composer format:check
	yarn lint

test:
	docker compose exec app rm -rf temp/di/* temp/cache/*
	yarn lint
	yarn typecheck
	yarn mail
	docker compose exec app composer analyse
	docker compose exec app vendor/bin/phpunit --colors=always
	docker compose exec -t documan /documan/bin/documan lint

db-restore:
	docker compose exec postgres gunzip /var/lib/postgresql/temp/dump.sql.gz
	docker compose exec postgres psql -U "$(DB_USERNAME)" -d "postgres" -c "DROP DATABASE IF EXISTS \"$(DB_DATABASE)\";"
	docker compose exec postgres psql -U "$(DB_USERNAME)" -d "postgres" -c "CREATE DATABASE \"$(DB_DATABASE)\";"
	docker compose exec postgres pg_restore --no-owner --no-privileges -U "$(DB_USERNAME)" -d "$(DB_DATABASE)" /var/lib/postgresql/temp/dump.sql
	docker compose exec postgres rm /var/lib/postgresql/temp/dump.sql

documan-import:
	docker compose exec -t documan /documan/bin/documan import

documan-lint:
	docker compose exec -t documan /documan/bin/documan lint

documan-fix:
	docker compose exec -t documan /documan/bin/documan fix
