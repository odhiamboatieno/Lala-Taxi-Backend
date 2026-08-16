# syntax=docker/dockerfile:1

# ============================================================================
# Stage 1: Composer dependencies (build only)
# Uses the official composer image — never shipped in the final image.
# ============================================================================
FROM composer:2 AS vendor

WORKDIR /app

# Full source is required: composer.json's classmap references database/seeds
# and database/factories, which must exist for composer to resolve them.
COPY . .

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --ignore-platform-reqs

# ============================================================================
# Stage 2: Runtime — tiny Alpine FPM image
# Only the app, its dependencies and the PHP extensions it needs are shipped.
# ============================================================================
FROM php:8.5-fpm-alpine AS runtime

# Minimal system packages required to compile the PHP extensions we need.
RUN apk add --no-cache \
        freetype-dev \
        icu-dev \
        jpeg-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        sockets \
        zip \
    && docker-php-ext-enable opcache \
    && apk del --no-cache \
        freetype-dev \
        icu-dev \
        jpeg-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
    && rm -rf /var/cache/apk/* /tmp/*

# Copy the composer binary so we can regenerate the optimized autoloader.
COPY --from=vendor /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# Copy application source.
COPY . .

# Copy the already-installed production dependencies (no dev packages).
COPY --from=vendor /app/vendor ./vendor

# Rebuild the optimized autoloader so App\ + Modules\ namespaces resolve.
RUN composer dump-autoload --no-dev --optimize --no-scripts --no-interaction \
    && chmod -R 755 storage bootstrap/cache

# Runtime entrypoint (storage bootstrap, migrations, permissions).
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint"]

CMD ["php-fpm", "--nodaemonize"]
