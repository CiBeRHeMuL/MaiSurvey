FROM php:8.3-fpm AS builder

ENV TIME_ZONE Europe/Moscow

WORKDIR /app

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions pgsql pdo_pgsql intl opcache soap bcmath pcntl zip tidy gd imap sockets pdo apcu
RUN apt update \
    && apt install -y git wget libpq-dev tzdata \
    && rm -rf /var/lib/apt/lists/*

RUN cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime \
    && echo "Europe/Moscow" > /etc/timezone

RUN wget "http://browscap.org/stream?q=Lite_PHP_BrowsCapINI" -O /usr/local/etc/php/php_browscap.ini

COPY ./docker/php/php.ini /usr/local/etc/php/php.ini
ADD ./docker/php/output.conf /usr/local/etc/php/conf.d/output.conf
ADD ./docker/php/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

RUN rm /usr/local/etc/php/conf.d/base.ini -f

RUN echo "Install Composer"
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version
