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

# Build dependencies live in a virtual package so they can be removed without
# taking the runtime shared libraries (libpng, libicu, libzip, ...) that the
# compiled extensions need to load at runtime.
RUN apk add --no-cache --virtual .build-deps \
        freetype-dev \
        icu-dev \
        jpeg-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        oniguruma-dev \
        linux-headers \
    && apk add --no-cache \
        freetype \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libwebp \
        libzip \
        oniguruma \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        sockets \
        zip \
    && apk del .build-deps \
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
