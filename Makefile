DOCKER_COMPOSE_FILE=./docker-compose.yml
DOCKER_COMPOSE_DIR=./.docker
DOCKER_COMPOSE=docker-compose --env-file $(DOCKER_COMPOSE_DIR)/.env -f $(DOCKER_COMPOSE_FILE)

.PHONY: setup
setup: docker-init
	$(DOCKER_COMPOSE) run workspace composer install

build: docker-init
	$(DOCKER_COMPOSE) build

workspace: docker-init
	$(DOCKER_COMPOSE) run workspace sh

.PHONY: analyse
analyse:
	$(DOCKER_COMPOSE) run workspace psalm

.PHONY: test
test:
	$(DOCKER_COMPOSE) run workspace phpunit

release-major:
	$(DOCKER_COMPOSE) run workspace monorepo-builder release major

release-minor:
	$(DOCKER_COMPOSE) run workspace monorepo-builder release minor

release-patch:
	$(DOCKER_COMPOSE) run workspace monorepo-builder release patch

.docker/.env:
	cp $(DOCKER_COMPOSE_DIR)/.env.example $(DOCKER_COMPOSE_DIR)/.env

.PHONY: docker-init
docker-init: .docker/.env