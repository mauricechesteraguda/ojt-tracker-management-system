#!/bin/sh

# infra-10032026-Maurice
# observability/fix-10042026-Maurice: every secret/daemon command has a safe boundary.
# Modification type: generate persisted local credentials without printing their values.
set -eu

printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"info","event":"startup","component":"mariadb-secrets","operation":"entrypoint","status":"begin"}' >&2
DB_SECRET_DIR=/run/ojt-secrets
APP_SECRET_DIR=/run/ojt-app-secrets
if ! mkdir -p "$DB_SECRET_DIR" "$APP_SECRET_DIR"; then
    printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"mariadb-secrets","operation":"prepare","status":"directory-failed","correlation_id":null,"exception_class":"RuntimeException","error_category":"secret_storage_failure","message_category":"directory_failed","stack":"redacted","cause":"none","remediation":"inspect_secret_volume"}' >&2
    exit 1
fi
if ! chmod 0700 "$DB_SECRET_DIR" "$APP_SECRET_DIR"; then
    printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"mariadb-secrets","operation":"prepare","status":"permission-failed","correlation_id":null,"exception_class":"RuntimeException","error_category":"secret_storage_failure","message_category":"permission_failed","stack":"redacted","cause":"none","remediation":"inspect_secret_volume"}' >&2
    exit 1
fi
if { [ -s "$APP_SECRET_DIR/app.key" ] || [ -s "$APP_SECRET_DIR/db.app.password" ]; } && { [ ! -s "$APP_SECRET_DIR/app.key" ] || [ ! -s "$APP_SECRET_DIR/db.app.password" ]; }; then
    printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"mariadb-secrets","operation":"validate","status":"generated-secrets-missing","correlation_id":null,"exception_class":"RuntimeException","error_category":"secret_integrity_failure","message_category":"generated_secret_missing","stack":"redacted","cause":"none","remediation":"recreate_secret_volume"}' >&2
    exit 78
fi

if [ ! -s "$APP_SECRET_DIR/app.key" ]; then
    if ! openssl rand -base64 32 > "$APP_SECRET_DIR/app.key"; then
        printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"mariadb-secrets","operation":"generate","status":"app-key-failed","correlation_id":null,"exception_class":"RuntimeException","error_category":"secret_generation_failure","message_category":"app_key_generation_failed","stack":"redacted","cause":"none","remediation":"inspect_random_source"}' >&2
        exit 1
    fi
    if ! sed -i '1s/^/base64:/' "$APP_SECRET_DIR/app.key"; then
        printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"mariadb-secrets","operation":"format","status":"app-key-format-failed","correlation_id":null,"exception_class":"RuntimeException","error_category":"secret_generation_failure","message_category":"app_key_format_failed","stack":"redacted","cause":"none","remediation":"inspect_random_source"}' >&2
        exit 1
    fi
fi
if [ ! -s "$DB_SECRET_DIR/db.root.password" ]; then
    if ! openssl rand -hex 32 > "$DB_SECRET_DIR/db.root.password"; then
        printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"mariadb-secrets","operation":"generate","status":"root-password-failed","correlation_id":null,"exception_class":"RuntimeException","error_category":"secret_generation_failure","message_category":"root_password_generation_failed","stack":"redacted","cause":"none","remediation":"inspect_random_source"}' >&2
        exit 1
    fi
fi
if [ ! -s "$DB_SECRET_DIR/db.app.password" ]; then
    if ! openssl rand -hex 32 > "$DB_SECRET_DIR/db.app.password"; then
        printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"mariadb-secrets","operation":"generate","status":"app-password-failed","correlation_id":null,"exception_class":"RuntimeException","error_category":"secret_generation_failure","message_category":"app_password_generation_failed","stack":"redacted","cause":"none","remediation":"inspect_random_source"}' >&2
        exit 1
    fi
fi
if [ ! -s "$APP_SECRET_DIR/db.app.password" ]; then
    if ! cp "$DB_SECRET_DIR/db.app.password" "$APP_SECRET_DIR/db.app.password"; then
        printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"mariadb-secrets","operation":"sync","status":"app-password-copy-failed","correlation_id":null,"exception_class":"RuntimeException","error_category":"secret_storage_failure","message_category":"app_password_copy_failed","stack":"redacted","cause":"none","remediation":"inspect_secret_volume"}' >&2
        exit 1
    fi
fi
if ! cmp -s "$DB_SECRET_DIR/db.app.password" "$APP_SECRET_DIR/db.app.password"; then
    printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"mariadb-secrets","operation":"entrypoint","status":"app-secret-mismatch","correlation_id":null,"exception_class":"RuntimeException","error_category":"secret_integrity_failure","message_category":"app_secret_mismatch","stack":"redacted","cause":"none","remediation":"recreate_secret_volume"}' >&2
    exit 78
fi
if ! chown 1000:1000 "$APP_SECRET_DIR" || ! chown 1000:1000 "$APP_SECRET_DIR/app.key" "$APP_SECRET_DIR/db.app.password" || ! chmod 0400 "$APP_SECRET_DIR/app.key" "$APP_SECRET_DIR/db.app.password" || ! chmod 0400 "$DB_SECRET_DIR/db.root.password" "$DB_SECRET_DIR/db.app.password"; then
    printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"mariadb-secrets","operation":"permissions","status":"permission-failed","correlation_id":null,"exception_class":"RuntimeException","error_category":"secret_storage_failure","message_category":"permission_failed","stack":"redacted","cause":"none","remediation":"inspect_secret_volume"}' >&2
    exit 1
fi

printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"info","event":"startup","component":"mariadb-secrets","operation":"entrypoint","status":"ready"}' >&2
# observability-10042026-Maurice: capture native daemon diagnostics so the
# owned stream remains JSONL; expose only a safe failure envelope.
if ! DB_LOG=$(mktemp); then
    printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"mariadb","operation":"daemon","status":"capture-failed","correlation_id":null,"exception_class":"RuntimeException","error_category":"diagnostic_capture_failure","message_category":"temporary_file_failed","stack":"redacted","cause":"none","remediation":"inspect_runtime_storage"}' >&2
    exit 1
fi
if ! /usr/local/bin/docker-entrypoint.sh "$@" >"$DB_LOG" 2>&1; then
    rm -f "$DB_LOG"
    printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"error","event":"failure","component":"mariadb","operation":"daemon","status":"startup-failed","correlation_id":null,"exception_class":"RuntimeException","error_class":"RuntimeException","error_category":"database_startup_failure","message_category":"database_unavailable","stack":"redacted","cause":"none","remediation":"inspect_database_health"}' >&2
    exit 1
fi
rm -f "$DB_LOG"
