FROM php:8.3 AS builder

ENV TIME_ZONE Europe/Moscow
ENV PHP_ENABLE_XDEBUG 1

WORKDIR /app

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions pgsql pdo_pgsql intl opcache soap bcmath pcntl zip xdebug-3.3.2 tidy sockets gd
RUN  apt-get update \
  && apt-get install -y wget \
  && rm -rf /var/lib/apt/lists/*
RUN apt-get update && apt-get install -y git
RUN install-php-extensions imap

RUN apt-get update && apt-get -y install git wget libpq-dev
RUN docker-php-ext-install pcntl sockets pdo pdo_pgsql pgsql

RUN wget "http://browscap.org/stream?q=Lite_PHP_BrowsCapINI" -O /usr/local/etc/php/php_browscap.ini

COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
ADD ./docker/php/output.conf /usr/local/etc/php/conf.d/output.conf
ADD ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
ADD ./docker/php/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

RUN rm /usr/local/etc/php/conf.d/base.ini -f

ENTRYPOINT exec php /app/bin/console messenger:consume async --time-limit=3600 -vv >> /app/var/log/messenger-$(date +%Y-%m-%d).log 2>&1
