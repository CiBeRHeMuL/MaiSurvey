FROM php:8.3-fpm AS builder

ENV TIME_ZONE Europe/Moscow
ENV PHP_ENABLE_XDEBUG 1

WORKDIR /app

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions pgsql pdo_pgsql intl opcache soap bcmath pcntl zip xdebug-3.3.2 tidy sockets gd imap pcntl sockets pdo pdo_pgsql pgsql
RUN  apt-get update \
  && apt-get install -y  git wget libpq-dev \
  && rm -rf /var/lib/apt/lists/*

RUN wget "http://browscap.org/stream?q=Lite_PHP_BrowsCapINI" -O /usr/local/etc/php/php_browscap.ini

COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
ADD ./docker/php/output.conf /usr/local/etc/php/conf.d/output.conf
ADD ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
ADD ./docker/php/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

RUN rm /usr/local/etc/php/conf.d/base.ini -f

RUN echo "Install Composer"
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version
