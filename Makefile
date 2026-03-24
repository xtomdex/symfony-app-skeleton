.DEFAULT_GOAL := help

ENV_FILE ?= .env.docker

# Load variables from .env.docker if it exists
ifneq ("$(wildcard $(ENV_FILE))","")
include $(ENV_FILE)
export
endif

.PHONY: help
help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-22s\033[0m %s\n", $$1, $$2}'

.PHONY: docker-start
docker-start: ## Start PostgreSQL container (dev/test local only)
	docker compose --env-file $(ENV_FILE) up -d

.PHONY: docker-stop
docker-stop: ## Stop containers (keeps volumes/data)
	docker compose --env-file $(ENV_FILE) down

.PHONY: docker-down
docker-down: ## Stop and remove containers AND volumes (DANGEROUS: wipes DB)
	docker compose --env-file $(ENV_FILE) down -v

.PHONY: docker-wait
docker-wait: ## Wait until PostgreSQL container is healthy
	@echo "Waiting for PostgreSQL to become ready..."
	@cid=$$(docker compose --env-file $(ENV_FILE) ps -q postgres); \
	if [ -z "$$cid" ]; then echo "PostgreSQL container not found. Run 'make docker-start' first."; exit 1; fi; \
	for i in 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30; do \
	  status=$$(docker inspect --format='{{.State.Health.Status}}' $$cid 2>/dev/null || echo "starting"); \
	  if [ "$$status" = "healthy" ]; then echo "PostgreSQL is healthy."; exit 0; fi; \
	  echo "  status=$$status (retry $$i/30)"; \
	  sleep 1; \
	done; \
	echo "PostgreSQL did not become healthy in time."; \
	docker compose --env-file $(ENV_FILE) logs postgres --tail=200; \
	exit 1

.PHONY: db-create
db-create: ## Create dev and test databases (idempotent)
	php bin/console doctrine:database:create --if-not-exists
	DATABASE_URL="postgresql://$(POSTGRES_USER):$(POSTGRES_PASSWORD)@127.0.0.1:$(POSTGRES_PORT)/app?serverVersion=17&charset=utf8" \
	APP_ENV=test php bin/console doctrine:database:create --if-not-exists

.PHONY: db-migrate
db-migrate: ## Run migrations in dev and test
	php bin/console doctrine:migrations:migrate -n
	APP_ENV=test php bin/console doctrine:migrations:migrate -n

.PHONY: db-grant-test
db-grant-test: ## Grant app user privileges on test DB
	@docker compose --env-file $(ENV_FILE) exec -T postgres psql \
		-U $(POSTGRES_USER) \
		-c "GRANT ALL PRIVILEGES ON DATABASE $(POSTGRES_DB)_test TO $(POSTGRES_USER);"

.PHONY: db-init
db-init: docker-start docker-wait db-create db-grant-test db-migrate ## First-time init: start docker + create DBs + migrate

.PHONY: test
test: ## Run tests
	php bin/phpunit

.PHONY: dev-restart
dev-restart: ## Restart docker (safe), ensure DB + migrations, run tests
	@echo "Stopping containers (without deleting volumes)..."
	docker compose --env-file $(ENV_FILE) down
	@echo "Starting containers..."
	docker compose --env-file $(ENV_FILE) up -d
	@$(MAKE) docker-wait
	@echo "Ensuring databases exist..."
	$(MAKE) db-create
	@echo "Running migrations..."
	$(MAKE) db-migrate
	@echo "Running test suite..."
	php bin/phpunit
