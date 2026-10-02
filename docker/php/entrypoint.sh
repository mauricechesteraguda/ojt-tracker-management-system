#!/bin/sh

# infra-10032026-Maurice
# Modification type: load generated local secrets and prove Laravel readiness before PHP-FPM.
set -eu

printf '%s\n' 'event=startup component=php-fpm status=begin' >&2

if [ "${PROVIDER_MODE:-fake}" != "fake" ] && [ "${PROVIDER_MODE:-fake}" != "real" ]; then
    printf '%s\n' 'event=failure component=php-fpm status=invalid-provider-mode' >&2
    exit 64
fi

if [ ! -s /run/ojt-secrets/app.key ] || [ ! -s /run/ojt-secrets/db.app.password ]; then
    printf '%s\n' 'event=failure component=php-fpm status=generated-secrets-missing' >&2
    exit 78
fi

export APP_KEY=$(cat /run/ojt-secrets/app.key)
export DB_PASSWORD=$(cat /run/ojt-secrets/db.app.password)

printf '%s\n' "event=operation component=laravel status=readiness-check provider_mode=${PROVIDER_MODE:-fake}" >&2
if ! php artisan --version >/dev/null; then
    printf '%s\n' 'event=failure component=laravel status=readiness-check-failed' >&2
    exit 1
fi

printf '%s\n' "event=startup component=php-fpm status=ready provider_mode=${PROVIDER_MODE:-fake}" >&2
exec "$@"
