#!/bin/sh
set -eu
cd "$(dirname "$0")"
if [ "$#" -gt 1 ] || { [ "$#" -eq 1 ] && [ "$1" != '--allow-bookings' ]; }; then
    echo 'Usage: sh release.sh [--allow-bookings]' >&2
    exit 2
fi
: "${BACKUP_REFERENCE:?Supply the verified current database and persistent upload backup reference}"
: "${RESTORE_EVIDENCE:?Supply the successful staging restore verification reference}"
sh ./preflight.sh --config-only
# This upgrades application code, not database/cache infrastructure. Pull only
# application images and never let `up` implicitly recreate their dependencies.
docker compose pull app queue reverb scheduler
# No old readers may execute while the single app writer replaces shared code.
docker compose stop nginx scheduler queue reverb app
docker compose up -d --no-deps --wait --wait-timeout 600 app
# Inspect upgraded schema/config before any delivery worker is allowed to run.
docker compose exec -T app php artisan corporate:release-check --no-interaction "$@" < /dev/null
docker compose up -d --no-deps --wait --wait-timeout 180 nginx queue reverb scheduler
sh ./preflight.sh "$@"
echo 'Release started. Record authenticated staging/device and observed job/delivery acceptance under the approved booking policy.'
