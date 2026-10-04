# OVNIPORTO developer commands.
# Docker targets use `docker compose`; `local-*` targets run natively (PHP 8.3 + Node 22).

COMPOSE ?= docker compose
APP     ?= app
NODE_IMAGE ?= ovniporto/node-build:dev

# Tests always run against in-memory SQLite / array drivers, overriding the
# MySQL/Redis variables the app service receives from docker-compose.yml.
TEST_ENV = -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: \
	-e CACHE_STORE=array -e SESSION_DRIVER=array -e QUEUE_CONNECTION=sync \
	-e MAIL_MAILER=array -e INERTIA_SSR_ENABLED=false

.PHONY: help up down sh test e2e lint build ssr-restart migrate seed-demo logs \
	local-test local-lint local-build

help: ## List available targets
	@grep -E '^[a-zA-Z_-]+:.*## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*## "}; {printf "  %-14s %s\n", $$1, $$2}'

up: ## Build (if needed) and start the stack in the background
	$(COMPOSE) up -d --build

down: ## Stop and remove containers (named volumes are kept)
	$(COMPOSE) down

sh: ## Open a shell in the app container
	$(COMPOSE) exec $(APP) sh

test: ## Pest in the app image, then the Vitest component tests in the node build image
	$(COMPOSE) run --rm --no-deps $(TEST_ENV) $(APP) php artisan test
	docker build --target build -f docker/ssr/Dockerfile -t $(NODE_IMAGE) .
	docker run --rm $(NODE_IMAGE) npm test

e2e: ## End-to-end suite (Playwright, axe-core, visual) run natively: PHP 8.3+, Node 22+, `npx playwright install chromium`
	npm run e2e

lint: ## Pint + PHPStan in the app image, ESLint/tsc/Prettier in the node build image
	$(COMPOSE) run --rm --no-deps $(APP) ./vendor/bin/pint --test
	$(COMPOSE) run --rm --no-deps $(APP) ./vendor/bin/phpstan analyse --memory-limit=1G
	docker build --target build -f docker/ssr/Dockerfile -t $(NODE_IMAGE) .
	docker run --rm $(NODE_IMAGE) npm run lint

build: ## Rebuild all images (assets are compiled inside the build)
	$(COMPOSE) build

ssr-restart: ## Restart the Inertia SSR server
	$(COMPOSE) restart ssr

migrate: ## Run database migrations
	$(COMPOSE) exec $(APP) php artisan migrate --force

seed-demo: ## Seed demo content
	$(COMPOSE) exec $(APP) php artisan dev:seed-demo

logs: ## Follow logs of all services
	$(COMPOSE) logs -f --tail=100

local-test: ## Run the Pest suite natively
	php artisan test

local-lint: ## Pint + PHPStan + ESLint/tsc/Prettier natively
	./vendor/bin/pint --test
	./vendor/bin/phpstan analyse --memory-limit=1G
	npm run lint

local-build: ## Build client + SSR bundles natively
	npm run build
