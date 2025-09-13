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
.PHONY        : help build up start down logs sh composer vendor sf cc test fixtures quality phpmd phpcs phpstan

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

assets-install: tailwind
	@$(PHP_CONT) bin/console assets:install

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

phpunit:
	@$(PHP_CONT) vendor/bin/phpunit --testsuite All

coverage:
	@$(PHP_CONT) vendor/bin/phpunit --configuration phpunit.xml --testsuite All --coverage-html public/coverage

phpunit-unit:
	@$(PHP_CONT) vendor/bin/phpunit --testsuite UnitTests

phpunit-functional:
	@$(PHP_CONT) vendor/bin/phpunit --testsuite FunctionalTests

tailwind:
	@$(PHP_CONT) bin/console tailwind:build

tailwind-watch:
	@$(PHP_CONT) bin/console tailwind:build --watch
