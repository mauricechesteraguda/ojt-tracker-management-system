#!/bin/sh

set -eu

logger -t ojt-compose-contract 'event=startup status=begin test=compose_contract_test' || :

# test-10022026-Maurice
# Adds the Ticket 02 RED contract test for the portable Compose runtime,
# readiness/health boundaries, persistence, safe environment defaults, and
# non-live-provider operation before any runtime implementation is added.

REPO_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)
COMPOSE_FILE=$REPO_ROOT/compose.yaml
ENV_TEMPLATE=$REPO_ROOT/.env.example

logger -t ojt-compose-contract 'event=major-operation status=check artifact=compose.yaml' || :
# TC-OJT-0001 / TC-OJT-0033: the approved local runtime must have a Compose entrypoint.
if [ ! -f "$COMPOSE_FILE" ]; then
    logger -t ojt-compose-contract 'event=failure status=missing artifact=compose.yaml' || :
    printf '%s\n' 'FAIL [TC-OJT-0001, TC-OJT-0033]: expected compose.yaml to exist' >&2
    exit 1
fi
logger -t ojt-compose-contract 'event=pass assertion=compose-file-present' || :

logger -t ojt-compose-contract 'event=major-operation status=check services=nginx/php-fpm/mariadb' || :
# TC-OJT-0001: Compose declares the approved Nginx, PHP-FPM, and MariaDB services.
grep -Eq '^  (nginx|php-fpm|mariadb):[[:space:]]*$' "$COMPOSE_FILE" || {
    logger -t ojt-compose-contract 'event=failure assertion=required-services' || :
    printf '%s\n' 'FAIL [TC-OJT-0001]: compose.yaml must declare nginx, php-fpm, and mariadb services' >&2
    exit 1
}

# TC-OJT-0001: images/builds are explicit and image references are pinned, not floating latest tags.
grep -Eq '(^|[[:space:]])(image:|build:)' "$COMPOSE_FILE" || {
    logger -t ojt-compose-contract 'event=failure assertion=image-build-intent' || :
    printf '%s\n' 'FAIL [TC-OJT-0001]: services must declare image/build intent' >&2
    exit 1
}
if grep -Eq 'image:[[:space:]]*[^[:space:]]+:latest([[:space:]]|$)' "$COMPOSE_FILE"; then
    logger -t ojt-compose-contract 'event=failure assertion=pinned-images' || :
    printf '%s\n' 'FAIL [TC-OJT-0001]: floating latest image tags are not allowed' >&2
    exit 1
fi

# TC-OJT-0001: service healthchecks and readiness dependency conditions are explicit.
grep -q 'healthcheck:' "$COMPOSE_FILE" || {
    logger -t ojt-compose-contract 'event=failure assertion=healthchecks' || :
    printf '%s\n' 'FAIL [TC-OJT-0001]: service healthchecks are required' >&2
    exit 1
}
grep -Eq 'condition:[[:space:]]*service_healthy' "$COMPOSE_FILE" || {
    logger -t ojt-compose-contract 'event=failure assertion=readiness-dependency' || :
    printf '%s\n' 'FAIL [TC-OJT-0001]: readiness must depend on healthy services' >&2
    exit 1
}

# TC-OJT-0001 / TC-OJT-0033: named volumes preserve database/runtime state across restart/reset.
grep -q '^volumes:' "$COMPOSE_FILE" || {
    logger -t ojt-compose-contract 'event=failure assertion=named-volumes' || :
    printf '%s\n' 'FAIL [TC-OJT-0001, TC-OJT-0033]: named volumes are required' >&2
    exit 1
}

# TC-OJT-0018 / TC-OJT-0021 / TC-OJT-0033: environment is templated, safe, and never embeds credentials.
[ -f "$ENV_TEMPLATE" ] || {
    logger -t ojt-compose-contract 'event=failure assertion=environment-template' || :
    printf '%s\n' 'FAIL [TC-OJT-0018, TC-OJT-0021, TC-OJT-0033]: .env.example is required' >&2
    exit 1
}
grep -Eq 'PROVIDER_MODE[[:space:]]*=[[:space:]]*fake|PROVIDER_MODE=fake' "$ENV_TEMPLATE" || {
    logger -t ojt-compose-contract 'event=failure assertion=fake-provider-default' || :
    printf '%s\n' 'FAIL [TC-OJT-0018]: fake provider must be the environment default' >&2
    exit 1
}
if grep -Eiq '(^|[[:space:]])(password|secret|token|api[_-]?key)[[:space:]]*=[[:space:]]*[^$[:space:]#]+' "$ENV_TEMPLATE"; then
    logger -t ojt-compose-contract 'event=failure assertion=no-live-credential' || :
    printf '%s\n' 'FAIL [TC-OJT-0018, TC-OJT-0021]: environment template contains a credential value' >&2
    exit 1
fi

# TC-OJT-0001 / TC-OJT-0017: readiness must not be implemented with arbitrary sleeps.
if grep -Eiq '(^|[[:space:]])sleep[[:space:]]+[0-9]+' "$COMPOSE_FILE"; then
    logger -t ojt-compose-contract 'event=failure assertion=no-arbitrary-sleep' || :
    printf '%s\n' 'FAIL [TC-OJT-0001, TC-OJT-0017]: arbitrary startup sleeps are forbidden' >&2
    exit 1
fi

# TC-OJT-0001 / TC-OJT-0018: the checked-in Compose document must be syntactically valid.
docker compose -f "$COMPOSE_FILE" config >/dev/null || {
    logger -t ojt-compose-contract 'event=failure assertion=compose-config' || :
    printf '%s\n' 'FAIL [TC-OJT-0001, TC-OJT-0018]: docker compose config rejected compose.yaml' >&2
    exit 1
}

# test-10032026-Maurice
# Ticket 02 RED hardening: assert the resolved contract, not shallow source text.
# Every failure below is intentionally fail-fast and is mapped to an existing
# OJT case so an implementation cannot satisfy the contract accidentally.
logger -t ojt-compose-contract 'event=major-operation status=check resolved-compose-contract' || :
CONFIG_FILE=${TMPDIR:-/tmp}/ojt-compose-contract.$$
umask 077
trap 'rm -f -- "$CONFIG_FILE"' EXIT HUP INT TERM
if ! docker compose -f "$COMPOSE_FILE" config >"$CONFIG_FILE"; then
    logger -t ojt-compose-contract 'event=failure assertion=resolved-compose-config' || :
    printf '%s\n' "FAIL [TC-OJT-0001, TC-OJT-0018]: could not resolve Compose config; warning: cleanup required for $CONFIG_FILE" >&2
    exit 1
fi

# TC-OJT-0001: Composer dependencies must be installed deterministically from
# the lockfile in the application image, rather than relying on a host vendor.
if [ ! -f "$REPO_ROOT/composer.json" ] || [ ! -f "$REPO_ROOT/composer.lock" ] || ! grep -Eq 'composer install' "$REPO_ROOT/docker/php/Dockerfile" || ! grep -Eq '[[:space:]]--no-interaction([[:space:]]|$)' "$REPO_ROOT/docker/php/Dockerfile" || ! grep -Eq '[[:space:]]--prefer-dist([[:space:]]|$)' "$REPO_ROOT/docker/php/Dockerfile" || ! grep -Eq 'COPY[^#]*composer\.json' "$REPO_ROOT/docker/php/Dockerfile" || ! grep -Eq 'COPY[^#]*composer\.lock' "$REPO_ROOT/docker/php/Dockerfile"; then
    logger -t ojt-compose-contract 'event=failure assertion=deterministic-composer-vendor' || :
    printf '%s\n' 'FAIL [TC-OJT-0001]: PHP image must copy composer manifests and run deterministic composer install' >&2
    printf '%s\n' "WARNING: explicit cleanup required for $CONFIG_FILE" >&2
    exit 1
fi

# TC-OJT-0018 / TC-OJT-0021: resolved application and database credentials
# must be non-empty; empty-root/passwordless local shortcuts are forbidden.
PHP_SERVICE=$(awk '/^  php-fpm:/{section=1; next} /^  [A-Za-z0-9_-]+:/{section=0} section{print}' "$CONFIG_FILE")
DB_SERVICE=$(awk '/^  mariadb:/{section=1; next} /^  [A-Za-z0-9_-]+:/{section=0} section{print}' "$CONFIG_FILE")
NGINX_SERVICE=$(awk '/^  nginx:/{section=1; next} /^  [A-Za-z0-9_-]+:/{section=0} section{print}' "$CONFIG_FILE")

# test-10032026-Maurice
# TC-OJT-0018 / TC-OJT-0021 / security: resolved secret mounts are scoped to
# their consumer. PHP-FPM may receive app secrets only; the MariaDB root secret
# and its volume must remain DB-scoped, and generated app files must not be
# world-readable.
DB_SECRET_SOURCE=$(printf '%s\n' "$DB_SERVICE" | awk '/source:/{source=$2} /target: \/run\/ojt-secrets$/{print source; exit}')
PHP_SECRET_SOURCE=$(printf '%s\n' "$PHP_SERVICE" | awk '/source:/{source=$2} /target: \/run\/ojt-secrets$/{print source; exit}')
if ! printf '%s\n' "$DB_SERVICE" | grep -Eq 'MARIADB_ROOT_PASSWORD_FILE:[[:space:]]*/run/ojt-secrets/db\.root\.password' || [ -z "$DB_SECRET_SOURCE" ] || ! printf '%s\n' "$DB_SECRET_SOURCE" | grep -Eq '(^|[-_])(db|mariadb)[-_]?secrets$' || [ -z "$PHP_SECRET_SOURCE" ] || ! printf '%s\n' "$PHP_SECRET_SOURCE" | grep -Eq '(^|[-_])app[-_]?secrets$' || [ "$DB_SECRET_SOURCE" = "$PHP_SECRET_SOURCE" ] || printf '%s\n' "$PHP_SERVICE" | grep -Eq 'db\.root\.password|MARIADB_ROOT_PASSWORD_FILE'; then
    logger -t ojt-compose-contract 'event=failure assertion=scoped-secret-mounts' || :
    printf '%s\n' 'FAIL [TC-OJT-0018, TC-OJT-0021, security]: resolved PHP-FPM mounts must contain app-scoped secrets only; MariaDB root secret must remain DB-scoped' >&2
    printf '%s\n' "WARNING: explicit cleanup required for $CONFIG_FILE" >&2
    exit 1
fi
if ! grep -Eq 'chmod[[:space:]]+0[46][0-7]0[[:space:]].*app\.key' "$REPO_ROOT/docker/mariadb/entrypoint.sh" || ! grep -Eq 'chmod[[:space:]]+0[46][0-7]0[[:space:]].*db\.app\.password' "$REPO_ROOT/docker/mariadb/entrypoint.sh"; then
    logger -t ojt-compose-contract 'event=failure assertion=restrictive-app-secret-permissions' || :
    printf '%s\n' 'FAIL [TC-OJT-0021, security]: generated app secret files must use restrictive non-world-readable permissions' >&2
    printf '%s\n' "WARNING: explicit cleanup required for $CONFIG_FILE" >&2
    exit 1
fi
if printf '%s\n' "$PHP_SERVICE" | grep -Eq 'APP_KEY:[[:space:]]*(""|$)' || ! printf '%s\n' "$PHP_SERVICE" | grep -Eq 'APP_KEY:[[:space:]]*[^"[:space:]][^[:space:]]*'; then
    logger -t ojt-compose-contract 'event=failure assertion=nonempty-app-key' || :
    printf '%s\n' 'FAIL [TC-OJT-0018]: resolved php-fpm APP_KEY must be non-empty' >&2
    printf '%s\n' "WARNING: explicit cleanup required for $CONFIG_FILE" >&2
    exit 1
fi
if printf '%s\n' "$DB_SERVICE" | grep -Eq 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD:[[:space:]]*"?yes|MARIADB_ROOT_PASSWORD:[[:space:]]*(""|$)|MARIADB_PASSWORD:[[:space:]]*(""|$)'; then
    logger -t ojt-compose-contract 'event=failure assertion=database-credentials' || :
    printf '%s\n' 'FAIL [TC-OJT-0018, TC-OJT-0021]: resolved MariaDB root and app credentials must be non-empty and passworded' >&2
    printf '%s\n' "WARNING: explicit cleanup required for $CONFIG_FILE" >&2
    exit 1
fi

# TC-OJT-0001: service detection must enumerate exactly the approved services
# from resolved config; substring/one-regex detection is not sufficient.
SERVICE_COUNT=$(awk '/^services:/{section=1; next} section && /^  [A-Za-z0-9_-]+:$/ {count++} /^networks:/{section=0} END{print count+0}' "$CONFIG_FILE")
if [ "$SERVICE_COUNT" -ne 3 ] || ! grep -Eq '^  nginx:$' "$CONFIG_FILE" || ! grep -Eq '^  php-fpm:$' "$CONFIG_FILE" || ! grep -Eq '^  mariadb:$' "$CONFIG_FILE"; then
    logger -t ojt-compose-contract 'event=failure assertion=explicit-resolved-services' || :
    printf '%s\n' 'FAIL [TC-OJT-0001]: resolved Compose config must contain exactly nginx, php-fpm, and mariadb services' >&2
    printf '%s\n' "WARNING: explicit cleanup required for $CONFIG_FILE" >&2
    exit 1
fi

# TC-OJT-0001 / TC-OJT-0033: frontend and backend networks, and all required
# named state volumes, must be explicit in resolved config.
# test-10032026-Maurice
# The generated secret boundary is intentionally split by consumer scope.
if ! grep -Eq '^networks:$' "$CONFIG_FILE" || ! grep -Eq '^  frontend:$' "$CONFIG_FILE" || ! grep -Eq '^  backend:$' "$CONFIG_FILE" || ! grep -Eq '^volumes:$' "$CONFIG_FILE" || ! grep -Eq '^  app_secrets:' "$CONFIG_FILE" || ! grep -Eq '^  db_secrets:' "$CONFIG_FILE" || ! grep -Eq '^  runtime_storage:' "$CONFIG_FILE" || ! grep -Eq '^  mariadb_data:' "$CONFIG_FILE" || ! printf '%s\n' "$PHP_SERVICE" | grep -Eq 'app_secrets|runtime_storage' || ! printf '%s\n' "$NGINX_SERVICE" | grep -Eq 'runtime_storage' || ! printf '%s\n' "$DB_SERVICE" | grep -Eq 'db_secrets|mariadb_data'; then
    logger -t ojt-compose-contract 'event=failure assertion=separate-named-state-boundaries' || :
    printf '%s\n' 'FAIL [TC-OJT-0001, TC-OJT-0033]: resolved config must define frontend/backend networks and generated-secrets/runtime/database named volumes' >&2
    printf '%s\n' "WARNING: explicit cleanup required for $CONFIG_FILE" >&2
    exit 1
fi

# test-10032026-Maurice
# Named volumes resolve to type: volume/source: pairs; reject only bind mounts
# and host paths so required named state volumes remain testable.
# TC-OJT-0001 / TC-OJT-0033: database is backend-only and never exposed to a
# host port or the web network; source binds are not portable runtime state.
if printf '%s\n' "$DB_SERVICE" | grep -Eq 'frontend|^[[:space:]]+ports:|type:[[:space:]]*bind|source:[[:space:]]*\.?/' || printf '%s\n' "$PHP_SERVICE" | grep -Eq 'type:[[:space:]]*bind|source:[[:space:]]*\.?/' || grep -Eq 'type:[[:space:]]*bind|source:[[:space:]]*\.?/' "$CONFIG_FILE"; then
    logger -t ojt-compose-contract 'event=failure assertion=no-db-web-exposure-or-source-bind' || :
    printf '%s\n' 'FAIL [TC-OJT-0001, TC-OJT-0033]: database must be backend-only and runtime images must not use source bind mounts' >&2
    printf '%s\n' "WARNING: explicit cleanup required for $CONFIG_FILE" >&2
    exit 1
fi

# TC-OJT-0001: app/web containers must not run as root, and image bases must be
# immutable digests rather than mutable tags.
if printf '%s\n' "$PHP_SERVICE" "$NGINX_SERVICE" | grep -Eq 'user:[[:space:]]*(0|root|"0"|"root")([[:space:]]|$)' || ! printf '%s\n' "$PHP_SERVICE" | grep -Eq 'user:' || ! printf '%s\n' "$NGINX_SERVICE" | grep -Eq 'user:' || ! grep -Eq '^FROM[^#]*@sha256:' "$REPO_ROOT/docker/php/Dockerfile" || ! grep -Eq '^FROM[^#]*@sha256:' "$REPO_ROOT/docker/nginx/Dockerfile" || grep -Eq '^    image:[[:space:]]*[^@[:space:]]+:[^@[:space:]]+' "$CONFIG_FILE"; then
    logger -t ojt-compose-contract 'event=failure assertion=nonroot-immutable-images' || :
    printf '%s\n' 'FAIL [TC-OJT-0001]: app/web must declare non-root users and all base images must be immutable digests' >&2
    printf '%s\n' "WARNING: explicit cleanup required for $CONFIG_FILE" >&2
    exit 1
fi

# TC-OJT-0001 / TC-OJT-0013: readiness must exercise the application, not only
# an FPM socket or static health body; arbitrary sleeps remain forbidden.
if ! grep -Eq '/login' "$CONFIG_FILE" || ! grep -Eq '/healthz' "$CONFIG_FILE" || grep -REiq '(^|[[:space:]])sleep[[:space:]]+[0-9]+' "$REPO_ROOT/docker"; then
    logger -t ojt-compose-contract 'event=failure assertion=application-readiness' || :
    printf '%s\n' 'FAIL [TC-OJT-0001, TC-OJT-0013]: readiness must verify application /login and /healthz without arbitrary sleep' >&2
    printf '%s\n' "WARNING: explicit cleanup required for $CONFIG_FILE" >&2
    exit 1
fi

rm -f "$CONFIG_FILE"
logger -t ojt-compose-contract 'event=pass assertion=resolved-compose-contract' || :

# TC-OJT-0001: paths use repository-relative/container-portable forms for macOS and Linux.
if grep -Eq '(^|[[:space:]])(/Users/|/home/|[A-Za-z]:\\)' "$COMPOSE_FILE"; then
    logger -t ojt-compose-contract 'event=failure assertion=portable-paths' || :
    printf '%s\n' 'FAIL [TC-OJT-0001]: host-specific absolute paths are forbidden' >&2
    exit 1
fi

# TC-OJT-0001 / TC-OJT-0013: Nginx health response is local, deterministic, and safe.
grep -Eq 'health(uri|_path|check)|/health' "$COMPOSE_FILE" || {
    logger -t ojt-compose-contract 'event=failure assertion=nginx-health-response' || :
    printf '%s\n' 'FAIL [TC-OJT-0001, TC-OJT-0013]: safe Nginx health response is required' >&2
    exit 1
}
if grep -Eiq 'health.*(password|secret|token|api[_-]?key|email|sr_code)|/health[^#]*(password|secret|token|email)' "$COMPOSE_FILE"; then
    logger -t ojt-compose-contract 'event=failure assertion=safe-health-output' || :
    printf '%s\n' 'FAIL [TC-OJT-0001, TC-OJT-0013]: health output must not expose secrets or PII' >&2
    exit 1
fi

logger -t ojt-compose-contract 'event=pass status=complete test=compose_contract_test' || :
printf '%s\n' 'PASS: Ticket 02 Compose contract'
