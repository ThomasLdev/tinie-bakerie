# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec php

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP) bin/console

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build up start down logs sh composer vendor sf cc test fixtures quality phpmd phpcs phpstan cache-warmup cache-warmup-clear

## —— 🎵 🐳 The Symfony Docker Makefile 🐳 🎵 ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

start: up assets-install

build: build up ## Build and start the containers

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the FrankenPHP container
	@$(PHP_CONT) sh

bash: ## Connect to the FrankenPHP container via bash so up and down arrows go to previous commands
	@$(PHP_CONT) bash

test: ## Start tests with phpunit, pass the parameter "c=" to add options to phpunit, example: make test c="--group e2e --stop-on-failure"
	@$(eval c ?=)
	@$(DOCKER_COMP) exec -e APP_ENV=test php bin/phpunit $(c)

create-upload-dirs: ## Create upload directories
	@$(PHP_CONT) bin/console app:create-upload-dirs --clear

cache-warmup: ## Warm up entity caches (posts, categories, headers)
	@$(PHP_CONT) bin/console app:cache:warm

cache-warmup-clear: ## Clear and warm up entity caches
	@$(PHP_CONT) bin/console app:cache:warm --clear-first

fixtures: create-upload-dirs
	@$(PHP_CONT) bin/console c:c
	@$(PHP_CONT) bin/console doctrine:fixtures:load --no-interaction

fixtures-test: create-upload-dirs
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

assets-install:
	@$(PHP_CONT) bin/console assets:install

tailwind:
	@$(PHP_CONT) bin/console tailwind:build

tailwind-watch:
	@$(PHP_CONT) bin/console tailwind:build --watch

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

quality: rector phpcs phpstan twig-linter

doctrine-validate-schema:
	@$(PHP_CONT) bin/console -e app doctrine:schema:validate

rector:
	@$(PHP_CONT) vendor/bin/rector process

## —— Tests 🎵 ———————————————————————————————————————————————————————————————

cache:
	@$(PHP_CONT) bin/console cache:pool:clear --all

tests: cache
	@$(PHP_CONT) vendor/bin/phpunit --testsuite All

coverage: cache
	@$(PHP_CONT) vendor/bin/phpunit --configuration phpunit.xml --testsuite All --coverage-html coverage

unit: cache
	@$(PHP_CONT) vendor/bin/phpunit --testsuite UnitTests

functional: cache
	@$(PHP_CONT) vendor/bin/phpunit --testsuite FunctionalTests

## —— E2E Tests (Playwright) 🎭 ——————————————————————————————————————————————

e2e-install: ## Install Playwright dependencies (run once after setup)
	@$(DOCKER_COMP) run --rm playwright npm install

e2e: ## Run E2E tests with Playwright
	@if [ ! -d "node_modules" ]; then \
		echo "⚠️  node_modules not found. Running 'make e2e-install' first..."; \
		$(DOCKER_COMP) run --rm playwright npm install; \
	fi
	@$(DOCKER_COMP) run --rm playwright npm run test:e2e

e2e-headed: ## Run E2E tests with visible browser (requires X11)
	@echo "Note: This requires X11 forwarding. For UI debugging, use 'make e2e-report' instead."
	@$(DOCKER_COMP) run --rm -e DISPLAY=$(DISPLAY) -v /tmp/.X11-unix:/tmp/.X11-unix playwright npm run test:e2e:headed

e2e-debug: ## Run E2E tests in debug mode with inspector
	@echo "Opening Playwright Inspector (headless mode)..."
	@$(DOCKER_COMP) run --rm playwright npm run test:e2e:debug

e2e-report: ## Show the last E2E test report (BEST for visual debugging)
	@echo "Opening HTML report on http://localhost:9323..."
	@$(DOCKER_COMP) run --rm -p 9323:9323 playwright npx playwright show-report --host 0.0.0.0 --port 9323

e2e-show-trace: ## Show trace for last failed test
	@echo "Opening trace viewer on http://localhost:9323..."
	@$(DOCKER_COMP) run --rm -p 9323:9323 playwright npx playwright show-trace test-results/**/trace.zip --host 0.0.0.0 --port 9323

test-all: phpunit e2e ## Run all tests (PHPUnit + E2E)
