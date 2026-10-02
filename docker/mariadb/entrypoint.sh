#!/bin/sh

# infra-10032026-Maurice
# Modification type: generate persisted local credentials without printing their values.
set -eu

printf '%s\n' 'event=startup component=mariadb-secrets status=begin' >&2
DB_SECRET_DIR=/run/ojt-secrets
APP_SECRET_DIR=/run/ojt-app-secrets
mkdir -p "$DB_SECRET_DIR" "$APP_SECRET_DIR"
chmod 0700 "$DB_SECRET_DIR" "$APP_SECRET_DIR"

if [ ! -s "$APP_SECRET_DIR/app.key" ]; then
    printf 'base64:%s\n' "$(openssl rand -base64 32 | tr -d '\n')" > "$APP_SECRET_DIR/app.key"
fi
if [ ! -s "$DB_SECRET_DIR/db.root.password" ]; then
    openssl rand -hex 32 > "$DB_SECRET_DIR/db.root.password"
fi
if [ ! -s "$DB_SECRET_DIR/db.app.password" ]; then
    openssl rand -hex 32 > "$DB_SECRET_DIR/db.app.password"
fi
if [ ! -s "$APP_SECRET_DIR/db.app.password" ]; then
    cp "$DB_SECRET_DIR/db.app.password" "$APP_SECRET_DIR/db.app.password"
fi
if ! cmp -s "$DB_SECRET_DIR/db.app.password" "$APP_SECRET_DIR/db.app.password"; then
    printf '%s\n' 'event=failure component=mariadb-secrets status=app-password-mismatch' >&2
    exit 78
fi
chown 1000:1000 "$APP_SECRET_DIR"
chown 1000:1000 "$APP_SECRET_DIR/app.key" "$APP_SECRET_DIR/db.app.password"
chmod 0400 "$APP_SECRET_DIR/app.key" "$APP_SECRET_DIR/db.app.password"
chmod 0400 "$DB_SECRET_DIR/db.root.password" "$DB_SECRET_DIR/db.app.password"

printf '%s\n' 'event=startup component=mariadb-secrets status=ready' >&2
exec /usr/local/bin/docker-entrypoint.sh "$@"
