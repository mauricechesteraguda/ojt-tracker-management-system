#!/bin/sh

# infra-10032026-Maurice
# Modification type: load generated local secrets and prove Laravel readiness before PHP-FPM.
set -eu

printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"info","event":"startup","component":"php-fpm","operation":"entrypoint","status":"begin"}' >&2

if [ "${PROVIDER_MODE:-fake}" != "fake" ] && [ "${PROVIDER_MODE:-fake}" != "real" ]; then
    printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"php-fpm","operation":"entrypoint","status":"invalid-provider-mode","correlation_id":null,"exception_class":"RuntimeException","error_class":"RuntimeException","error_category":"startup_failure","message_category":"configuration","stack":"redacted","cause":null,"remediation":"check_provider_mode"}' >&2
    exit 64
fi

if [ ! -s /run/ojt-secrets/app.key ] || [ ! -s /run/ojt-secrets/db.app.password ]; then
    printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"php-fpm","operation":"entrypoint","status":"generated-secrets-missing","correlation_id":null,"exception_class":"RuntimeException","error_class":"RuntimeException","error_category":"startup_failure","message_category":"secret_configuration","stack":"redacted","cause":null,"remediation":"check_secret_volume"}' >&2
    exit 78
fi

export APP_KEY=$(cat /run/ojt-secrets/app.key)
export DB_PASSWORD=$(cat /run/ojt-secrets/db.app.password)

printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"info","event":"operation","component":"laravel","operation":"readiness-check","status":"begin"}' >&2
if ! php artisan --version >/dev/null 2>&1; then
    printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"laravel","operation":"readiness-check","status":"readiness-check-failed","correlation_id":null,"exception_class":"RuntimeException","error_class":"RuntimeException","error_category":"readiness_failure","message_category":"runtime_unavailable","stack":"redacted","cause":null,"remediation":"inspect_database_readiness"}' >&2
    exit 1
fi

printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"info","event":"operation","component":"demo-bootstrap","operation":"bootstrap","status":"begin"}' >&2
BOOTSTRAP_LOG=$(mktemp)
if ! demo-bootstrap >"$BOOTSTRAP_LOG" 2>&1; then
    cat "$BOOTSTRAP_LOG" >&2
    rm -f "$BOOTSTRAP_LOG"
    printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"php-fpm","operation":"bootstrap","status":"bootstrap-failed","correlation_id":null,"exception_class":"RuntimeException","error_class":"RuntimeException","error_category":"bootstrap_failure","message_category":"bootstrap_failed","stack":"redacted","cause":null,"remediation":"inspect_database_health"}' >&2
    exit 1
fi
cat "$BOOTSTRAP_LOG" >&2
rm -f "$BOOTSTRAP_LOG"

printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"info","event":"startup","component":"php-fpm","operation":"entrypoint","status":"ready"}' >&2
if [ "${1:-}" = php-fpm ]; then
    # observability-10042026-Maurice: retain only structured worker records;
    # suppress native FPM notices/access lines without exposing raw output.
    "$@" 2>&1 | while IFS= read -r line; do
        case "$line" in
            *'{'*) json=${line#*\{}; printf '%s\n' "{$json" >&2 ;;
        esac
    done
    exit 0
fi
exec "$@"
