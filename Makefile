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

sh:
	docker compose exec -it app /bin/bash

test:
	docker compose exec app composer analyse
	yarn lint

db-restore:
	docker compose exec postgres gunzip /var/lib/postgresql/temp/dump.sql.gz
	docker compose exec postgres psql -U "$(DB_USERNAME)" -d "postgres" -c "DROP DATABASE IF EXISTS \"$(DB_DATABASE)\";"
	docker compose exec postgres psql -U "$(DB_USERNAME)" -d "postgres" -c "CREATE DATABASE \"$(DB_DATABASE)\";"
	docker compose exec postgres pg_restore --no-owner --no-privileges -U "$(DB_USERNAME)" -d "$(DB_DATABASE)" /var/lib/postgresql/temp/dump.sql
	docker compose exec postgres rm /var/lib/postgresql/temp/dump.sql