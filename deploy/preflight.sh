#!/bin/sh
set -eu
cd "$(dirname "$0")"
if [ "$#" -gt 1 ] || { [ "$#" -eq 1 ] && [ "$1" != '--config-only' ] && [ "$1" != '--allow-bookings' ]; }; then
    echo 'Usage: sh preflight.sh [--config-only|--allow-bookings]' >&2
    exit 2
fi
docker compose config --quiet
application_images=$(docker compose config --images | awk '/lala-taxi-backend/ {print}')
if [ -z "$application_images" ]; then
    echo 'Missing application image.' >&2
    exit 1
fi
first_application_image=''
for application_image in $application_images; do
    case "$application_image" in
        ghcr.io/*/lala-taxi-backend@sha256:*)
            digest=${application_image##*@sha256:}
            case "$digest" in *[!0-9a-f]*) echo 'Invalid immutable image digest.' >&2; exit 1;; esac
            [ "${#digest}" = 64 ] || { echo 'Invalid immutable image digest length.' >&2; exit 1; }
            ;;
        *) echo 'Application image must be pinned to the tested GHCR sha256 digest.' >&2; exit 1;;
    esac
    if [ -n "$first_application_image" ] && [ "$application_image" != "$first_application_image" ]; then
        echo 'All application services must use the same immutable image.' >&2
        exit 1
    fi
    first_application_image=$application_image
done
if [ "${1:-}" = '--config-only' ]; then
    echo 'Compose configuration and immutable application image validated.'
    exit 0
fi
docker compose exec -T app php artisan corporate:release-check --no-interaction "$@" < /dev/null
docker compose ps
echo 'Read-only preflight complete; this does not establish observed SMTP, scheduler or device acceptance.'
