# Обвязка над docker compose. На Windows тот же набор команд — в dev.ps1.
SHELL := /bin/bash
DC    := docker compose
ARGS  ?=

.DEFAULT_GOAL := help

.PHONY: help init build up down restart ps logs sh artisan composer \
        npm front test migrate fresh seed psql clean prod-build

help: ## Список команд
	@grep -hE '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) \
		| awk 'BEGIN{FS=":.*?## "}{printf "  \033[36m%-16s\033[0m %s\n", $$1, $$2}'

init: ## Первый запуск или свежий клон: .env, сборка, зависимости, ключ, миграции
	@test -f .env || (cp .env.example .env && echo "создан .env из .env.example")
	@mkdir -p backend
	$(DC) build
	@if [ ! -f backend/artisan ]; then \
		echo "Laravel не найден — ставлю скелет в backend/"; \
		$(DC) run --rm --no-deps app \
			bash -lc 'composer create-project --prefer-dist --no-interaction laravel/laravel:^13.0 /tmp/skeleton \
			  && cp -rn /tmp/skeleton/. /var/www/html/ \
			  && rm -rf /tmp/skeleton'; \
	fi
	@# Свежий клон: vendor/ и backend/.env в git не лежат.
	@test -f backend/.env || (cp backend/.env.example backend/.env && echo "создан backend/.env из backend/.env.example")
	@if [ ! -f backend/vendor/autoload.php ]; then \
		echo "vendor/ пуст — ставлю зависимости"; \
		$(DC) run --rm --no-deps app composer install --no-interaction --prefer-dist; \
	fi
	@if ! grep -qE '^APP_KEY=.+' backend/.env; then \
		echo "генерирую APP_KEY"; \
		$(DC) run --rm --no-deps app php artisan key:generate --force; \
	fi
	$(DC) up -d
	@echo "жду готовности приложения…" && sleep 10
	$(MAKE) migrate
	@echo
	@echo "  приложение   http://localhost:$${APP_PORT:-8080}"
	@echo "  фронтенд     make front  →  http://localhost:$${VITE_PORT:-5173}"
	@echo "  PostgreSQL   localhost:$${DB_PORT_FORWARD:-5432}"

build: ## Пересобрать образы
	$(DC) build $(ARGS)

up: ## Поднять окружение
	$(DC) up -d

down: ## Остановить окружение
	$(DC) down

restart: ## Перезапустить
	$(DC) restart

ps: ## Состояние сервисов
	$(DC) ps

logs: ## Логи (ARGS=app|nginx|postgres…)
	$(DC) logs -f --tail=100 $(ARGS)

sh: ## Шелл в контейнере приложения
	$(DC) exec app bash

artisan: ## make artisan ARGS="migrate:status"
	$(DC) exec app php artisan $(ARGS)

composer: ## make composer ARGS="require foo/bar"
	$(DC) exec app composer $(ARGS)

npm: ## make npm ARGS="install three"
	$(DC) run --rm --no-deps node npm $(ARGS)

front: ## Поднять Vite dev server
	$(DC) --profile frontend up -d node
	$(DC) --profile frontend logs -f node

test: ## Тесты (домен + фичевые)
	$(DC) exec -e APP_ENV=testing -e DB_DATABASE=$${DB_DATABASE:-fiberglass}_test app php artisan test $(ARGS)

migrate: ## Миграции
	$(DC) exec app php artisan migrate --force

fresh: ## Пересоздать схему со сидами
	$(DC) exec app php artisan migrate:fresh --seed

seed: ## Сиды (материалы, процессы, схемы укладки)
	$(DC) exec app php artisan db:seed

psql: ## Консоль PostgreSQL
	$(DC) exec postgres psql -U $${DB_USERNAME:-fiberglass} -d $${DB_DATABASE:-fiberglass}

prod-build: ## Собрать боевой образ
	PHP_TARGET=prod $(DC) build app

clean: ## Снести контейнеры вместе с томами (данные БД пропадут)
	$(DC) down -v --remove-orphans
