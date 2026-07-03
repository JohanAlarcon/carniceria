# syntax=docker/dockerfile:1

###############################################################################
# Stage 1 — Build the frontend assets (Vite + React + PWA)
###############################################################################
FROM node:20-bookworm-slim AS assets
WORKDIR /app

# Install JS deps from the lockfile for reproducible builds
COPY package.json package-lock.json ./
RUN npm ci

# Copy the sources needed by Vite and build the production bundle
COPY . .
RUN npm run build


###############################################################################
# Stage 2 — Install PHP dependencies (Composer, no dev packages)
###############################################################################
FROM composer:2 AS vendor
WORKDIR /app

# Install vendor without running artisan scripts (no app/env yet)
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --optimize-autoloader

# Bring in the full source and regenerate an optimized, authoritative autoloader
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev


###############################################################################
# Stage 3 — Runtime image: PHP-FPM + Nginx + Supervisor
###############################################################################
FROM php:8.3-fpm-bookworm AS runtime

# System packages: web server, process manager, image/zip/intl libs, mysql client
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions via the maintained installer (handles configure flags for us)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        pdo_mysql \
        mbstring \
        bcmath \
        gd \
        zip \
        intl \
        exif \
        pcntl \
        opcache

WORKDIR /var/www/html

# Application code + vendor (stage 2) and compiled assets (stage 1)
COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

# Container configuration
COPY docker/php.ini            /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/nginx.conf         /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf   /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh      /usr/local/bin/entrypoint.sh

RUN rm -f /etc/nginx/sites-enabled/default \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p \
        /var/log/supervisor \
        /run \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/app/public \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# 80 = Nginx (web) · 8080 = Reverb (WebSocket, only used by the reverb service)
EXPOSE 80 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
