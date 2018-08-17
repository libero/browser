ARG image_tag=latest

FROM libero/browser_composer:${image_tag} AS composer
FROM php:7.2.8-fpm-alpine3.7

RUN mkdir -p build var && \
    chown --recursive www-data:www-data var

COPY LICENSE .
COPY bin/ bin/
COPY public/ public/
COPY config/ config/
COPY --from=composer /app/vendor/ vendor/
COPY src/ src/
COPY vendor-extra/ vendor-extra/
