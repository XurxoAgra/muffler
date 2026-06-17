current-dir := $(dir $(abspath $(lastword $(MAKEFILE_LIST))))
SHELL = /bin/sh
docker-container = muffler-api-webserver

#
# ❓ Help output
#
help: ## Show make targets
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\/]+:.*?## / {sub("\\\\n",sprintf("\n%22c"," "), $$2);printf " \033[36m%-24s\033[0m  %s\n", $$1, $$2}' $(MAKEFILE_LIST)

#
# 🐘 Build and run
#
start: ## Start and run project
	docker compose --env-file ./docker/develop/.env up -d

start/force: ## Start and run project
	docker compose --env-file ./docker/develop/.env up -d --force-recreate

stop: ## Stop project
	docker compose --env-file ./docker/develop/.env stop

down: ## Remove the project container
	docker compose --env-file ./docker/develop/.env down

ps: ## List containers
	docker compose --env-file ./docker/develop/.env ps

install: ## Install dependencies
	docker exec $(docker-container) composer install

init: ## Initialize project containers and install dependencies
	@make start
	@make install

build:
	docker compose --env-file ./docker/develop/.env build
rebuild: ## Reset/Reinstall project containers and install dependencies
	docker compose --env-file ./docker/develop/.env build --pull --force-rm --no-cache
	@make start
	@make install
	@make create-db
	@make migrate

bash: ## Start bash console inside the container
	docker exec -u www-data -it $(docker-container) /bin/bash
  ##docker exec -u www-data -it muffler-api-webserver /bin/bash

#
# Database
#
rebuild-db: drop-db create-db migrate rbac
create-db: create-db/dev create-db/test
create-db/dev:
	docker exec $(docker-container) php bin/console doctrine:database:create --env=dev --no-interaction --if-not-exists

create-db/test:
	docker exec $(docker-container) php bin/console doctrine:database:create --env=test --no-interaction --if-not-exists

migrate: migrate/dev migrate/test
migrate/dev:
	docker exec $(docker-container) php bin/console doctrine:migrations:migrate --env=dev

migrate/test:
	docker exec $(docker-container) php bin/console doctrine:migrations:migrate --env=test

migration/diff:
	docker exec $(docker-container) php bin/console doctrine:migrations:diff

migration/gen:
	docker exec $(docker-container) php bin/console doctrine:migrations:generate

drop-db: drop-db/dev  drop-db/test
drop-db/dev:
	docker exec $(docker-container) php bin/console doctrine:database:drop --force --env=dev --if-exists
drop-db/test:
	docker exec $(docker-container) php bin/console doctrine:database:drop --force --env=test --if-exists



#
# 🔬 Testing
#
test/all: ## Execute all tests
	docker exec $(docker-container) ./vendor/bin/phpunit

test/unit: ## Execute unit tests
	docker exec $(docker-container) ./vendor/bin/phpunit --testsuite unit

test/integration: ## Execute integration tests
	docker exec $(docker-container) ./vendor/bin/phpunit --testsuite Integration

test/functional: ## Execute functional tests
	docker exec $(docker-container) ./vendor/bin/behat

#
# 💅 Style and errors
#
style/all: style/fix style/static-analysis

style/code-style: ## Analyse code style
	docker exec $(docker-container) ./vendor/bin/php-cs-fixer fix --dry-run --diff --config .php-cs-fixer.dist.php

style/static-analysis: ## Find possible errors with static analysis
	docker exec $(docker-container) ./vendor/bin/phpstan analyse -c phpstan.neon.dist

style/fix: ## Fix code style
	docker exec $(docker-container) ./vendor/bin/php-cs-fixer fix --config .php-cs-fixer.dist.php


#
# :on: Queue consuming
#
queue/consume: ## Consume messages from queue transport make queue/consume <...receivers>
	docker exec $(docker-container) php bin/console messenger:consume $(filter-out $@,$(MAKECMDGOALS)) -vvv

queue/stop: ## Stop consuming messages from queue
	docker exec $(docker-container) php bin/console messenger:stop-workers


#
# Clear cache
#
clear: clear/dev clear/test
clear/dev:
	docker exec $(docker-container) php bin/console c:c --env=dev
clear/test:
	docker exec $(docker-container) php bin/console c:c --env=test
clear/pool/mail:
	docker exec $(docker-container) bin/console cache:pool:clear mail_cache_pool

#
# Messenger
#
consume/ticketing:
	docker exec -t $(docker-container) php bin/console messenger:consume ticketing

#
# Init Websocket
#
init/websocket:
	docker exec -t $(docker-container) php bin/console init:websocket-server:ticketing
#
# Capabilities
#
rbac: rbac/dev rbac/test
rbac/dev:
	docker exec $(docker-container) php bin/console rbac:install --env=dev
rbac/test:
	docker exec $(docker-container) php bin/console rbac:install --env=test

#
# Utils
#
uuid:
	docker exec $(docker-container) php bin/console generate:uuidv7
