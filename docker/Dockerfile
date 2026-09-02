# Parameterised PHP CLI image used for running the test suite, PHPStan and Pint.
# The PHP version is selected per service in compose.yaml via the PHP_VERSION build arg.
ARG PHP_VERSION=8.1
FROM php:${PHP_VERSION}-cli

# Composer needs git and unzip to fetch package dists; the official php images ship neither.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

WORKDIR /app
