FROM php:8.5-cli-alpine
RUN apk update && \
  apk add postgresql-dev && \
  docker-php-ext-install pdo_pgsql
