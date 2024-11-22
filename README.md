# Сервис опросов МАИ

## Оглавление

1. [Общее описание сервиса](#общее-описание-сервиса)
2. [Quickstart](#quickstart)
    - [С докером](#с-докером)

## Общее описание сервиса

ОНО ТУТ БУДЕТ

## Quickstart

Инструкция о развертывании сервиса

### С докером

**1.** Настраиваем окружение

Для корректной работы сервиса необходимо заполнить все переменные окружения.

```shell
# Копируем .env
cp .env .env.local

# заменяем APP_SECRET в .env.local. Сгенерировать можно с помощью команды:
openssl rand -base64 32
```

**2.** Настраиваем бд

1. Создаем пользователя и базу данных, владельцем которой является пользователь.

```sql
CREATE USER "ms" WITH PASSWORD '123456';
ALTER USER "ms" WITH LOGIN;
CREATE DATABASE "ms" OWNER "ms";
```

**3.** Прописываем в `.env.local` коннект к базе

Прописываем в `.env.local` переменную `DATABASE_URL="postgresql://ms:123456@mai-survey-postgresql:5432/ms?serverVersion=17&charset=utf8"`

**4.** Добавляем локально хосты в hosts файл

```
127.0.0.1 mai-survey.loc
```

**5.** Так как вся работа теперь связана с PHP контейнером, удобно будет добавить в систему алиас, для удобства работы с докером.

```shell
alias mphp='docker exec -it mai-survey-php-container'
```

**6.** Разворачиваем докер

```shell
make docker-install
```

**7.** Выполняем команды для настройки сервиса

- Устанавливаем composer зависимости

```shell
mphp composer i
```

- Накатываем миграции

```shell
mphp make migrate
```