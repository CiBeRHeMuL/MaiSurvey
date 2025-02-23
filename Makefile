.DEFAULT_GOAL := help

CONSOLE = php bin/console
Command := $(firstword $(MAKECMDGOALS))
Arguments := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))

# Установка проекта с docker
docker-install:
	@if [ -z "$$(docker network ls | grep caddy)" ]; then \
		echo "Сеть caddy не найдена. Создание сети..."; \
		docker network create --driver bridge caddy; \
	else \
		echo "Сеть caddy уже существует."; \
	fi;
	@docker compose -f docker-compose.yml --env-file .env.local up --build -d --remove-orphans

# Запуск команд внутри php контейнера
dphp:
	@docker exec -it mai-survey-php-container $(cmd)

# Запуск тестов
test:
	php bin/console --env=test doctrine:database:create --if-not-exists -vv
	php bin/console --env=test doctrine:schema:drop --force -vv
	php bin/console --env=test doctrine:schema:create -vv
	php bin/phpunit --testdox --colors

# Очистка кэша Symfony
cache-clear:
	$(CONSOLE) cache:clear

# Применение миграций
migrate:
	$(CONSOLE) doctrine:migrations:migrate
	$(CONSOLE) cache:clear

# Применение миграций
docker-migrate:
	@docker exec -it mai-survey-php-container doctrine:migrations:migrate
	@docker exec -it mai-survey-php-container cache:clear

# Создание новой миграции
migration:
	$(CONSOLE) make:migration

# Помощь
help:
	@echo "Доступные команды:"
	@echo "  make docker-install - Установка проекта с docker"
	@echo "  make dphp			 - Запуск команд внутри php контейнера"
	@echo "  make test           - Запуск тестов"
	@echo "  make cache-clear    - Очистка кэша Symfony"
	@echo "  make migrate        - Применение миграций"
	@echo "  make docker-migrate - Применение миграций внутри докер контейнера"
	@echo "  make migration      - Создание новой миграции"
	@echo "  make help           - Помощь"

# Документация
openapi:
	touch ./doc/openapi.yaml && $(CONSOLE) nelmio:apidoc:dump --format=yaml > ./doc/openapi.yaml

dump:
	pg_dump --file=./dump/dump.sql --create --format=c --clean --if-exists
