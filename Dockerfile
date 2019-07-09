FROM php:7.2.11-fpm-alpine AS php

ENV PHP_EXTENSION_DIR=/usr/local/lib/php/extensions/no-debug-non-zts-20170718



#
# Stage: Composer install for production
#
FROM composer:1.7.3 AS composer

COPY composer.json \
    composer.lock \
    symfony.lock \
    ./
COPY vendor-extra/ vendor-extra/

RUN composer --no-interaction install --no-dev --ignore-platform-reqs --no-autoloader --no-suggest --prefer-dist

COPY src/ src/

RUN composer --no-interaction dump-autoload --classmap-authoritative



#
# Stage: Production environment
#
FROM php AS prod

WORKDIR /app

ENV APP_ENV=prod

RUN mkdir data var && \
    chown www-data:www-data var

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS && \
    apk add --no-cache \
        icu-dev \
    && \
    docker-php-ext-install \
        intl \
        opcache \
    && \
    apk del .build-deps && \
    rm -rf /var/cache/apk/

COPY LICENSE .
COPY .docker/php.ini ${PHP_INI_DIR}/conf.d/00-app.ini
COPY bin/ bin/
COPY src/ src/
COPY public/ public/
COPY templates/ templates/
COPY config/ config/
COPY --from=composer /app/vendor/ vendor/
COPY vendor-extra/ vendor-extra/

RUN bin/console assets:install && \
    rm -rf var/*

USER www-data
HEALTHCHECK --interval=5s CMD sh -c 'nc -z localhost 9000'



#
# Stage: Composer install for development
#
FROM composer AS composer-dev

RUN composer --no-interaction install --ignore-platform-reqs --no-suggest --prefer-dist



#
# Stage: Test environment
#
FROM prod AS test

ENV APP_ENV=test

USER root

COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN touch .phpcs-cache && \
    chown www-data:www-data .phpcs-cache
COPY tests/ tests/
COPY .docker/php-test.ini ${PHP_INI_DIR}/conf.d/01-app.ini
COPY composer.json \
    composer.lock \
    phpcs.xml.dist \
    phpstan.neon.dist \
    phpunit.xml.dist \
    symfony.lock \
    ./
COPY --from=composer-dev /app/vendor/ vendor/

RUN bin/console assets:install && \
    rm -rf var/*

USER www-data



#
# Stage: Development environment
#
FROM test AS dev

ENV APP_ENV=dev

USER root
ENV COMPOSER_ALLOW_SUPERUSER=true

COPY .docker/php-dev.ini ${PHP_INI_DIR}/conf.d/02-app.ini

RUN bin/console assets:install && \
    rm -rf var/*



#
# Stage: Install Xdebug
#
FROM php AS xdebug

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS && \
    pecl install \
        xdebug-2.7.0 \
    && \
    docker-php-ext-enable \
        xdebug \
    && \
    apk del .build-deps && \
    rm -rf /var/cache/apk/



#
# Stage: Debug environment
#
FROM dev AS debug

COPY --from=xdebug ${PHP_EXTENSION_DIR}/*.so ${PHP_EXTENSION_DIR}/
COPY --from=xdebug ${PHP_INI_DIR}/conf.d/*.ini ${PHP_INI_DIR}/conf.d/

COPY .docker/php-debug.ini ${PHP_INI_DIR}/conf.d/03-app.ini
