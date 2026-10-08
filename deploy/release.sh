#!/bin/sh
set -eu
cd "$(dirname "$0")"
: "${BACKUP_REFERENCE:?Supply the verified current database and persistent upload backup reference}"
: "${RESTORE_EVIDENCE:?Supply the successful staging restore verification reference}"
sh ./preflight.sh --config-only
docker compose pull
# No old readers may execute while the single app writer replaces shared code.
docker compose stop nginx scheduler queue reverb app
docker compose up -d --wait --wait-timeout 600 app
# Inspect upgraded schema/config before any delivery worker is allowed to run.
docker compose exec -T app php artisan corporate:release-check --no-interaction
docker compose up -d --wait --wait-timeout 180 nginx queue reverb scheduler
sh ./preflight.sh
echo 'Release started. Record authenticated staging/device and observed job/delivery acceptance before enabling new corporate bookings.'
