# Local acceptance extends a previously tested production runtime image. This
# preserves the exact PHP extensions/vendor versions and avoids rebuilding them.
# Use the standard Dockerfile whenever composer.lock/runtime dependencies change.
ARG ACCEPTANCE_BASE_IMAGE
FROM ${ACCEPTANCE_BASE_IMAGE}

WORKDIR /src
ARG RELEASE_SHA
RUN sha256sum composer.lock > /tmp/acceptance-composer.sha256
COPY . .
RUN sha256sum -c /tmp/acceptance-composer.sha256 \
    && composer dump-autoload --no-dev --optimize --no-scripts --no-interaction \
    && printf '%s\n' "$RELEASE_SHA" > .src-build \
    && find app bootstrap config database Modules routes resources public -type f \
        ! -path 'bootstrap/cache/*' ! -path 'public/images/*' ! -path 'public/trip_file/*' \
        | sort > .src-files
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint
