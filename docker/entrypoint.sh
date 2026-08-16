#!/bin/sh
set -e

echo "[entrypoint] Bootstrapping LalaTaxi container..."

# Refresh the shared code volume from the fresh image copy. The image ships the
# source at /src; /var/www/html is the app_code named volume shared with nginx.
# Syncing on boot (guarded by a build marker) keeps the volume in lockstep with
# the deployed image, so future image updates propagate automatically.
if [ "${SYNC_CODE:-true}" = "true" ] && [ -d /src ] && [ -d /src/vendor ]; then
    mkdir -p /var/www/html
    if [ ! -f /var/www/html/.src-build ] || \
       [ "$(cat /var/www/html/.src-build 2>/dev/null || true)" != "$(cat /src/.src-build 2>/dev/null || true)" ]; then
        echo "[entrypoint] Syncing application code into shared volume..."
        cp -a /src/. /var/www/html/
    fi
fi

cd /var/www/html

# Ensure Laravel runtime directories exist and are writable.
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    public/trip_file

touch storage/logs/laravel.log

# The web installer is bypassed: we provision the database from the CLI instead.
touch storage/installed

# Give the FPM user ownership of writable paths.
chown -R www-data:www-data storage bootstrap/cache public/trip_file 2>/dev/null || true
chmod -R 775 storage bootstrap/cache public/trip_file 2>/dev/null || true

# Link storage -> public/storage for uploaded files.
if [ ! -L public/storage ] && [ -d storage/app/public ]; then
    php artisan storage:link --no-interaction >/dev/null 2>&1 || true
fi

# Run migrations once per deploy (guarded by env, safe for a single app node).
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "[entrypoint] Running database migrations..."
    php artisan migrate --force --no-interaction
fi

echo "[entrypoint] Ready. Executing: $*"
exec "$@"
