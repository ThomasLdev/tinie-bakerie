# Executables (local)
DOCKER_COMP = docker compose
DOCKER_COMP_DEV = docker compose --profile dev

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php
NODE_CONT = $(DOCKER_COMP_DEV) exec node

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console
NPM      = $(NODE_CONT) npm

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build up up-dev up-ci up-prod-local start down logs verify-prod sh bash node-sh composer vendor sf cc refresh-prod test fixtures quality phpmd phpcs phpstan assets-compile watch node-install lint lint-fix format format-check type-check build-css watch-css

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start production services (no Node.js)
	@$(DOCKER_COMP) up --detach

up-dev: ## Start all services including Node.js for development
	@$(DOCKER_COMP_DEV) up --detach

up-ci: ## Start services for CI environment (includes Node.js for testing)
	@echo "🔧 Starting services for CI environment..."
	@$(DOCKER_COMP_DEV) up --detach
	@echo "✅ CI environment ready (Node.js available for linting/testing)"

up-prod-local: ## Recreate the PHP container with APP_ENV=prod + FrankenPHP worker mode (debug prod locally)
	@APP_ENV=prod APP_DEBUG=0 FRANKENPHP_CONFIG="import worker.Caddyfile" $(DOCKER_COMP) up --detach --force-recreate php
	@$(PHP_CONT) bin/console cache:clear --env=prod
	@$(PHP_CONT) bin/console asset-map:compile --env=prod

start: up-dev assets-install ## Start development environment

build: build up ## Build and start the containers

down: ## Stop all containers including dev profile
	@$(DOCKER_COMP) --profile dev down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

verify-prod: ## Verify production setup (no Node.js container)
	@echo "🔍 Checking production setup..."
	@if docker compose ps | grep -q node; then \
		echo "❌ ERROR: Node.js container is running in production mode!"; \
		echo "   This should not happen. Use 'make up' for production."; \
		exit 1; \
	else \
		echo "✅ SUCCESS: Node.js container is NOT running (production mode)"; \
		echo "   Running containers:"; \
		docker compose ps --format "table {{.Name}}\t{{.Service}}\t{{.Status}}"; \
	fi

sh: ## Connect to the FrankenPHP container
	@$(PHP_CONT) sh

bash: ## Connect to the FrankenPHP container via bash so up and down arrows go to previous commands
	@$(PHP_CONT) bash

node-sh: ## Connect to the Node container
	@$(NODE_CONT) sh

fixtures:
	@$(PHP_CONT) rm -rf public/media/original/fixtures/*
	@$(PHP_CONT) bin/console c:c
	@$(PHP_CONT) bin/console doctrine:fixtures:load --no-interaction

fixtures-test:
	@$(PHP_CONT) rm -rf public/media/original/fixtures/*
	@$(PHP_CONT) bin/console c:c --env=test
	@$(PHP_CONT) bin/console doctrine:fixtures:load --no-interaction --env=test

doctrine-diff:
	@$(PHP_CONT) bin/console doctrine:migrations:diff

doctrine-migrate:
	@$(PHP_CONT) bin/console c:c
	@$(PHP_CONT) bin/console doctrine:migrations:migrate --no-interaction

doctrine-migrate-test:
	@$(PHP_CONT) bin/console c:c --env=test
	@$(PHP_CONT) bin/console doctrine:migrations:migrate --no-interaction --env=test

doctrine-db-create:
	@$(PHP_CONT) bin/console doctrine:database:create --if-not-exists

doctrine-db-test-create:
	@$(PHP_CONT) bin/console doctrine:database:create --if-not-exists --env=test

## —— Assets 🎨 ————————————————————————————————————————————————————————————————
assets-install: ## Install assets in the public directory
	@$(PHP_CONT) bin/console assets:install

build-css: ## Compile CSS bundle via Lightning CSS (sources -> assets/styles/app.css)
	@$(NPM) run build:css

watch-css: ## Watch CSS sources and rebuild bundle on change (dev)
	@$(NPM) run watch:css

assets-compile: build-css ## Compile all assets for production (CSS bundle + AssetMapper)
	@$(PHP_CONT) bin/console asset-map:compile

## —— Node.js 📦 ———————————————————————————————————————————————————————————————
node-install: ## Install Node.js dependencies
	@$(NPM) install

lint: ## Lint JS + CSS with Biome
	@$(NPM) run lint

lint-fix: ## Lint + auto-fix JS + CSS with Biome
	@$(NPM) run lint:fix

format: ## Format JS + CSS with Biome
	@$(NPM) run format

format-check: ## Check JS + CSS formatting with Biome
	@$(NPM) run format:check

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— Symfony 🎵 ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf

refresh-prod: ## Clear prod cache and restart the FrankenPHP worker (needed after PHP/Twig/translation changes)
	@$(PHP_CONT) bin/console cache:clear --env=prod
	@$(DOCKER_COMP) restart php

## —— Tools 🎵 ———————————————————————————————————————————————————————————————

phpstan:
	@$(PHP_CONT) vendor/bin/phpstan analyse src --level=9

phpcs:
	@$(PHP_CONT) vendor/bin/php-cs-fixer fix

phpcs-dry:
	@$(PHP_CONT) vendor/bin/php-cs-fixer fix --dry-run

twig-linter:
	@$(PHP_CONT) bin/console lint:twig templates

quality: rector phpcs phpstan twig-linter lint format-check ## Run all quality checks (PHP + JS)

doctrine-validate-schema:
	@$(PHP_CONT) bin/console -e app doctrine:schema:validate

rector:
	@$(PHP_CONT) vendor/bin/rector process

## —— Tests 🎵 ———————————————————————————————————————————————————————————————

test:
	@$(PHP_CONT) vendor/bin/phpunit

test.coverage:
	@$(PHP_CONT) vendor/bin/phpunit --coverage-html coverage

test.unit:
	@$(PHP_CONT) vendor/bin/phpunit --testsuite UnitTests --no-results --testdox

test.functional:
	@$(PHP_CONT) vendor/bin/phpunit --testsuite FunctionalTests --no-results --testdox

test.js: ## Run JS unit tests with Vitest
	@$(NPM) run test:js

test.js.coverage: ## Run JS unit tests with coverage report (HTML in coverage-js/)
	@$(NPM) run test:js:coverage

typecheck: ## Run TypeScript type checking on tests
	@$(NPM) run typecheck

## —— E2E Tests (Playwright) 🎭 ——————————————————————————————————————————————

e2e-install: ## Install Playwright dependencies (included in node-install)
	@echo "Installing Playwright browsers..."
	@$(NODE_CONT) npx playwright install --with-deps

e2e-up-test: ## Recreate php in APP_ENV=test (call e2e-up-dev to switch back)
	@APP_ENV=test $(DOCKER_COMP) up --detach --force-recreate --no-deps php

e2e-up-dev: ## Recreate php in APP_ENV=dev (used to restore dev after e2e debugging)
	@APP_ENV=dev $(DOCKER_COMP) up --detach --force-recreate --no-deps php

e2e-reset: ## Drop, migrate and seed app_test for the Playwright suite (assumes php is in APP_ENV=test)
	@$(PHP_CONT) bin/console app:e2e:reset --env=test

args ?=

test.e2e: ## Run E2E tests with Playwright (switches php to APP_ENV=test for the run, restores dev on exit). Pass `args="--repeat-each=5"` to forward flags.
	@set -e; \
	current_env=$$($(DOCKER_COMP) exec -T php sh -c 'printf "%s" "$$APP_ENV"' 2>/dev/null || true); \
	if [ "$$current_env" = "test" ]; then \
		echo "🧪 php already in APP_ENV=test (CI override or manual e2e-up-test) — skipping recreate."; \
	else \
		trap 'echo "♻️  Restoring php to APP_ENV=dev..."; APP_ENV=dev $(DOCKER_COMP) up --detach --force-recreate --no-deps php >/dev/null 2>&1' EXIT INT TERM; \
		echo "🧪 Booting php in APP_ENV=test..."; \
		APP_ENV=test $(DOCKER_COMP) up --detach --force-recreate --no-deps php >/dev/null; \
	fi; \
	$(PHP_CONT) bin/console app:e2e:reset --env=test; \
	$(NPM) run test:e2e -- $(args)

e2e-headed: ## Run E2E tests with visible browser (requires X11)
	@echo "Note: This requires X11 forwarding. For UI debugging, use 'make e2e-report' instead."
	@$(NODE_CONT) sh -c "DISPLAY=${DISPLAY} npm run test:e2e:headed"

e2e-debug: ## Run E2E tests in debug mode with inspector
	@echo "Opening Playwright Inspector (headless mode)..."
	@$(NPM) run test:e2e:debug

e2e-report: ## Show the last E2E test report (BEST for visual debugging)
	@echo "Opening HTML report on http://localhost:9323..."
	@$(NODE_CONT) npx playwright show-report --host 0.0.0.0 --port 9323

e2e-show-trace: ## Show trace for last failed test
	@echo "Opening trace viewer on http://localhost:9323..."
	@$(NODE_CONT) npx playwright show-trace test-results/**/trace.zip --host 0.0.0.0 --port 9323

test-all: tests test.e2e ## Run all tests (PHPUnit + E2E)
