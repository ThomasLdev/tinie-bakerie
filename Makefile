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
.PHONY        : help build up up-dev up-ci start down logs verify-prod sh bash node-sh composer vendor sf cc test fixtures quality phpmd phpcs phpstan assets-compile tailwind tailwind-watch watch node-install lint lint-fix format format-check type-check

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

assets-compile: tailwind ## Compile all assets (Tailwind) for production
	@$(PHP_CONT) bin/console asset-map:compile

tailwind: ## Build Tailwind CSS
	@$(PHP_CONT) bin/console tailwind:build

tailwind-watch: ## Watch and rebuild Tailwind CSS on changes
	@$(PHP_CONT) bin/console tailwind:build --watch

## —— Node.js 📦 ———————————————————————————————————————————————————————————————
node-install: ## Install Node.js dependencies
	@$(NPM) install

lint: ## Lint JS files with ESLint
	@$(NPM) run lint

lint-fix: ## Lint and auto-fix JS files
	@$(NPM) run lint:fix

format: ## Format code with Prettier
	@$(NPM) run format

format-check: ## Check code formatting with Prettier
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

## —— E2E Tests (Playwright) 🎭 ——————————————————————————————————————————————

e2e-install: ## Install Playwright dependencies (included in node-install)
	@echo "Installing Playwright browsers..."
	@$(NODE_CONT) npx playwright install --with-deps

test.e2e: ## Run E2E tests with Playwright
	@$(NPM) run test:e2e

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
