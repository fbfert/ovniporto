# syntax=docker/dockerfile:1.7
#
# OVNIPORTO application image (php-fpm, PHP_VERSION build arg).
#
# Targets:
#   production  final runtime image, no dev dependencies (default, last stage)
#   dev         same runtime, keeps composer dev deps (Pest, Pint, PHPStan)
#   web         nginx:1.27-alpine serving the compiled public/ directory
#
# Build context: repository root.

# composer.lock pins Symfony 8.1 / Pest 5 / PHPUnit 13, which require PHP >= 8.4.1,
# so the runtime uses 8.4 (composer.json still declares php ^8.3).
ARG PHP_VERSION=8.4
ARG NODE_VERSION=22

# ---------------------------------------------------------------------------
# 1. Front-end assets: client bundle (public/build) + SSR bundle (bootstrap/ssr)
# ---------------------------------------------------------------------------
FROM node:${NODE_VERSION}-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm ci
COPY vite.config.js tsconfig.json ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---------------------------------------------------------------------------
# 2. Composer dependencies (no scripts; autoloader is dumped in the PHP stage)
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev --no-scripts --no-autoloader --no-interaction --no-progress \
        --prefer-dist --ignore-platform-reqs

FROM composer:2 AS vendor-dev
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-scripts --no-autoloader --no-interaction --no-progress \
        --prefer-dist --ignore-platform-reqs

# ---------------------------------------------------------------------------
# 3. PHP runtime base (extensions + config, no application code)
# ---------------------------------------------------------------------------
FROM php:${PHP_VERSION}-fpm-alpine AS php-base

RUN set -eux; \
    apk add --no-cache \
        icu-libs libzip libpng libjpeg-turbo libwebp libavif freetype oniguruma; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS icu-dev libzip-dev libpng-dev libjpeg-turbo-dev \
        libwebp-dev libavif-dev freetype-dev oniguruma-dev linux-headers; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-avif; \
    docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mbstring intl gd zip bcmath opcache pcntl; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    apk del .build-deps; \
    rm -rf /tmp/pear ~/.pearrc

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini

ENV OPCACHE_VALIDATE_TIMESTAMPS=0 \
    COMPOSER_HOME=/tmp/composer

WORKDIR /var/www/html

# ---------------------------------------------------------------------------
# 4. dev: runtime with dev dependencies (tests, lint) and timestamp validation
# ---------------------------------------------------------------------------
FROM php-base AS dev
ENV OPCACHE_VALIDATE_TIMESTAMPS=1
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor-dev /app/vendor ./vendor
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build
COPY --chown=www-data:www-data --from=assets /app/bootstrap/ssr ./bootstrap/ssr

RUN set -eux; \
    composer dump-autoload --optimize --no-scripts; \
    mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache; \
    php artisan package:discover --ansi; \
    chown -R www-data:www-data storage bootstrap/cache vendor/composer

USER www-data
EXPOSE 9000
CMD ["php-fpm"]

# ---------------------------------------------------------------------------
# 5. web: nginx with the compiled public/ directory baked in
# ---------------------------------------------------------------------------
FROM nginx:1.27-alpine AS web
# Real log files (the image links them to stdout) rotated by logrotate: 6 months at most.
RUN apk add --no-cache logrotate \
    && rm -f /var/log/nginx/access.log /var/log/nginx/error.log \
    && touch /var/log/nginx/access.log /var/log/nginx/error.log
COPY --chmod=644 docker/nginx/logrotate.conf /etc/logrotate.d/nginx
COPY --chmod=755 docker/nginx/40-start-crond.sh /docker-entrypoint.d/40-start-crond.sh
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY public /var/www/html/public
COPY --from=assets /app/public/build /var/www/html/public/build
# Mirrors `php artisan storage:link`; the storage volume is mounted read-only here.
RUN ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage

# ---------------------------------------------------------------------------
# 6. production: final runtime image without dev dependencies (default target)
# ---------------------------------------------------------------------------
FROM php-base AS production
ENV APP_ENV=production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=vendor /app/vendor ./vendor
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build
COPY --chown=www-data:www-data --from=assets /app/bootstrap/ssr ./bootstrap/ssr

RUN set -eux; \
    composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts; \
    mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache; \
    php artisan package:discover --ansi; \
    chown -R www-data:www-data storage bootstrap/cache vendor/composer

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
