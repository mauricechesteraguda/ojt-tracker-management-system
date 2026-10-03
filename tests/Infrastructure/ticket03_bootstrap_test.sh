#!/bin/sh

# test-10032026-Maurice
# Ticket 03 RED contract: exercise the supported Compose bootstrap/reset
# boundary without exposing credentials, secrets, token material, or fixture
# payloads.  This is intentionally fail-fast (max-failures=1) and has no
# function definitions so every assertion remains visible in its case section.

set -eu
umask 077

REPO_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)
COMPOSE_FILE=$REPO_ROOT/compose.yaml
TEST_NAME=ojt-ticket03-$$
PROJECT_NAME=${OJT_TEST_COMPOSE_PROJECT:-$TEST_NAME}
APP_PORT=${OJT_TEST_APP_PORT:-}
TMP_ROOT=${TMPDIR:-/tmp}/ojt-ticket03-$$
STUDENT_SR_CODE=DEMO-STUDENT-001
COORDINATOR_SR_CODE=DEMO-COORD-001
SUPERUSER_SR_CODE=DEMO-ADMIN-001
STUDENT_PASSWORD='DemoOnly-Student-001!'
COORDINATOR_PASSWORD='DemoOnly-Coord-001!'
SUPERUSER_PASSWORD='DemoOnly-Admin-001!'
mkdir -p "$TMP_ROOT"
trap 'docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" down --volumes --remove-orphans >/dev/null 2>&1 || :; rm -rf -- "$TMP_ROOT"' EXIT HUP INT TERM

logger -t ojt-ticket03 'event=startup status=begin test=ticket03_bootstrap_test max_failures=1' || :

# RED gate: fail before Docker config resolution, image pulls, or builds when
# either named container executable has not been added to the PHP image.
logger -t ojt-ticket03 'event=major-operation status=check artifact=demo-bootstrap' || :
if ! grep -Eq 'demo-bootstrap' "$REPO_ROOT/docker/php/Dockerfile" "$REPO_ROOT/docker/php/entrypoint.sh"; then
    logger -t ojt-ticket03 'event=failure status=red assertion=missing-demo-bootstrap-artifact' || :
    printf '%s\n' 'FAIL [TC-OJT-0034]: missing named container executable artifact demo-bootstrap (checked Dockerfile and PHP entrypoint)' >&2
    exit 1
fi
logger -t ojt-ticket03 'event=pass assertion=demo-bootstrap-artifact-declared' || :

logger -t ojt-ticket03 'event=major-operation status=check artifact=demo-reset' || :
if ! grep -Eq 'demo-reset' "$REPO_ROOT/docker/php/Dockerfile" "$REPO_ROOT/docker/php/entrypoint.sh"; then
    logger -t ojt-ticket03 'event=failure status=red assertion=missing-demo-reset-artifact' || :
    printf '%s\n' 'FAIL [TC-OJT-0038]: missing named container executable artifact demo-reset (checked Dockerfile and PHP entrypoint)' >&2
    exit 1
fi
logger -t ojt-ticket03 'event=pass assertion=demo-reset-artifact-declared' || :

# TC-OJT-0033: local-only fake mode, exact synthetic identities, and reset
# contract are prerequisites for every bootstrap assertion below.
logger -t ojt-ticket03 'event=major-operation status=check supporting-case=TC-OJT-0033' || :
[ -f "$COMPOSE_FILE" ] || { logger -t ojt-ticket03 'event=failure assertion=compose-file'; printf '%s\n' 'FAIL [TC-OJT-0033]: compose.yaml is required' >&2; exit 1; }
[ -f "$REPO_ROOT/.env.example" ] || { logger -t ojt-ticket03 'event=failure assertion=env-template'; printf '%s\n' 'FAIL [TC-OJT-0033]: .env.example is required' >&2; exit 1; }
grep -Eq '^PROVIDER_MODE=fake[[:space:]]*$' "$REPO_ROOT/.env.example" || { logger -t ojt-ticket03 'event=failure assertion=fake-provider-default'; printf '%s\n' 'FAIL [TC-OJT-0033]: fake provider must be the default' >&2; exit 1; }
if grep -Eiq 'https?://|curl[[:space:]]|wget[[:space:]]|nc[[:space:]]' "$REPO_ROOT/docker/php/entrypoint.sh" "$REPO_ROOT/docker/mariadb/entrypoint.sh" "$REPO_ROOT/.env.example"; then
    logger -t ojt-ticket03 'event=failure assertion=no-live-provider-or-network-call'; printf '%s\n' 'FAIL [TC-OJT-0033]: bootstrap/reset support files must not contain live provider/network calls' >&2; exit 1
fi
logger -t ojt-ticket03 'event=pass assertion=supporting-local-fake-contract' || :

[ -n "$APP_PORT" ] || { logger -t ojt-ticket03 'event=failure assertion=test-port-required'; printf '%s\n' 'FAIL [TC-OJT-0033]: set OJT_TEST_APP_PORT to an isolated host port' >&2; exit 1; }
if command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 "$APP_PORT" >/dev/null 2>&1; then
    logger -t ojt-ticket03 'event=failure assertion=host-port-conflict'; printf '%s\n' 'FAIL [TC-OJT-0033]: OJT_TEST_APP_PORT is already in use' >&2; exit 1
fi
if ! command -v timeout >/dev/null 2>&1; then
    logger -t ojt-ticket03 'event=failure assertion=bounded-timeout-tool'; printf '%s\n' 'FAIL [TC-OJT-0033]: timeout command is required for bounded integration operations' >&2; exit 1
fi

export COMPOSE_PROJECT_NAME=$PROJECT_NAME
export APP_PORT
export PROVIDER_MODE=fake

# TC-OJT-0034: normal clean Compose up must migrate/bootstrap and expose the
# exact three synthetic role identities plus their deterministic fixture graph.
logger -t ojt-ticket03 'event=major-operation status=compose-up case=TC-OJT-0034' || :
# fix-10032026-Maurice
# CI/offline verification may reuse separately verified local service images;
# default behavior remains the full Compose build path.
if [ "${OJT_TEST_SKIP_BUILD:-0}" = 1 ]; then
    if ! timeout 120 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" up -d --no-build >"$TMP_ROOT/up.log" 2>&1; then
        logger -t ojt-ticket03 'event=failure case=TC-OJT-0034 assertion=clean-compose-up'; printf '%s\n' 'FAIL [TC-OJT-0034]: clean Compose up did not complete' >&2; exit 1
    fi
else
    if ! timeout 120 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" up -d >"$TMP_ROOT/up.log" 2>&1; then
        logger -t ojt-ticket03 'event=failure case=TC-OJT-0034 assertion=clean-compose-up'; printf '%s\n' 'FAIL [TC-OJT-0034]: clean Compose up did not complete' >&2; exit 1
    fi
fi
timeout 90 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" ps --status running >"$TMP_ROOT/ps.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0034 assertion=service-readiness'; printf '%s\n' 'FAIL [TC-OJT-0034]: Compose services did not become ready' >&2; exit 1; }
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm sh -c 'command -v demo-bootstrap && command -v demo-reset' >"$TMP_ROOT/executables.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0034 assertion=named-executables'; printf '%s\n' 'FAIL [TC-OJT-0034]: named demo-bootstrap/demo-reset executables are unavailable in the container' >&2; exit 1; }

# Safe machine-readable inspection is required from the independent inspection
# adapter before any manual bootstrap call.  It must expose concrete stable
# identifiers and relationships, not only fixture labels or aggregate claims.
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-inspect >"$TMP_ROOT/fixture-1.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0034 assertion=fixture-graph'; printf '%s\n' 'FAIL [TC-OJT-0034]: deterministic fixture graph inspection failed' >&2; exit 1; }
grep -Eq 'users=3 user_student=DEMO-STUDENT-001 role_student=student user_coordinator=DEMO-COORD-001 role_coordinator=coordinator user_superuser=DEMO-ADMIN-001 role_superuser=superuser' "$TMP_ROOT/fixture-1.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0034 assertion=exact-user-identities-and-roles'; printf '%s\n' 'FAIL [TC-OJT-0034]: inspect output lacks exactly three demo users with their exact roles and stable identifiers' >&2; exit 1; }
grep -Eq 'companies=1 company=Demo Local Company' "$TMP_ROOT/fixture-1.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0034 assertion=company-identity'; printf '%s\n' 'FAIL [TC-OJT-0034]: inspect output lacks the single stable demo company identifier' >&2; exit 1; }
grep -Eq 'internships=1 internship_student=DEMO-STUDENT-001 internship_company=Demo Local Company internship_status=active' "$TMP_ROOT/fixture-1.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0034 assertion=internship-relationship'; printf '%s\n' 'FAIL [TC-OJT-0034]: inspect output lacks the active internship-to-student/company relationship' >&2; exit 1; }
grep -Eq 'active_categories=1 category=Demo Requirement requirements=1 requirement_category=Demo Requirement requirement_internship_student=DEMO-STUDENT-001 requirement_internship_company=Demo Local Company' "$TMP_ROOT/fixture-1.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0034 assertion=requirement-mapping'; printf '%s\n' 'FAIL [TC-OJT-0034]: inspect output lacks the active category-to-requirement mapping' >&2; exit 1; }
grep -Eq 'descriptions=1 description=Demo placement description description_internship_student=DEMO-STUDENT-001 reports=1 valid_reports=1 report=Demo valid report report_internship_student=DEMO-STUDENT-001' "$TMP_ROOT/fixture-1.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0034 assertion=description-and-report-relationships'; printf '%s\n' 'FAIL [TC-OJT-0034]: inspect output lacks one description and one valid report linked to the synthetic internship' >&2; exit 1; }
grep -Eq 'natural_keys=unique' "$TMP_ROOT/fixture-1.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0034 assertion=natural-key-uniqueness'; printf '%s\n' 'FAIL [TC-OJT-0034]: inspect output does not prove demo natural keys are unique' >&2; exit 1; }
grep -Eq 'fixture_graph=deterministic oauth_clients=[1-9][0-9]* passport_public_key_sha256=[[:xdigit:]]+ passport_private_key_sha256=[[:xdigit:]]+' "$TMP_ROOT/fixture-1.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0034 assertion=fixture-oauth-graph-and-keys'; printf '%s\n' 'FAIL [TC-OJT-0034]: deterministic fixture graph, OAuth clients, or Passport key hashes are missing' >&2; exit 1; }

# TC-OJT-0035: only after the automatic bootstrap has been inspected, invoke
# demo-bootstrap twice and require the independent inspection contract to stay
# byte-for-byte stable.
logger -t ojt-ticket03 'event=major-operation status=bootstrap-rerun case=TC-OJT-0035' || :
cp "$TMP_ROOT/fixture-1.log" "$TMP_ROOT/fixture-2.before"
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-bootstrap >"$TMP_ROOT/bootstrap-1.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0035 assertion=bootstrap-idempotence-first'; printf '%s\n' 'FAIL [TC-OJT-0035]: first manual demo-bootstrap did not complete' >&2; exit 1; }
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-bootstrap >"$TMP_ROOT/bootstrap-2.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0035 assertion=bootstrap-idempotence-second'; printf '%s\n' 'FAIL [TC-OJT-0035]: second manual demo-bootstrap did not complete' >&2; exit 1; }
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-inspect >"$TMP_ROOT/fixture-2.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0035 assertion=repeat-inspection'; printf '%s\n' 'FAIL [TC-OJT-0035]: repeated fixture inspection failed' >&2; exit 1; }
cmp -s "$TMP_ROOT/fixture-1.log" "$TMP_ROOT/fixture-2.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0035 assertion=stable-fixtures-keys-clients'; printf '%s\n' 'FAIL [TC-OJT-0035]: bootstrap changed fixture, OAuth, or key contract' >&2; exit 1; }

# TC-OJT-0036: restart services without deleting named volumes and compare the
# same safe contract output.
logger -t ojt-ticket03 'event=major-operation status=service-restart case=TC-OJT-0036' || :
timeout 60 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" restart >"$TMP_ROOT/restart.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0036 assertion=restart'; printf '%s\n' 'FAIL [TC-OJT-0036]: normal service restart failed' >&2; exit 1; }
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-inspect >"$TMP_ROOT/fixture-3.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0036 assertion=persistent-state'; printf '%s\n' 'FAIL [TC-OJT-0036]: fixture state unavailable after restart' >&2; exit 1; }
cmp -s "$TMP_ROOT/fixture-1.log" "$TMP_ROOT/fixture-3.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0036 assertion=restart-preservation'; printf '%s\n' 'FAIL [TC-OJT-0036]: restart changed fixture, OAuth, or key contract' >&2; exit 1; }

# TC-OJT-0037: authentication by sr_code/password succeeds for all three
# synthetic identities without ever writing credential values to output.
logger -t ojt-ticket03 'event=major-operation status=authenticate case=TC-OJT-0037' || :
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-auth-contract --student-sr-code "$STUDENT_SR_CODE" --student-password "$STUDENT_PASSWORD" --coordinator-sr-code "$COORDINATOR_SR_CODE" --coordinator-password "$COORDINATOR_PASSWORD" --superuser-sr-code "$SUPERUSER_SR_CODE" --superuser-password "$SUPERUSER_PASSWORD" >"$TMP_ROOT/auth.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0037 assertion=sr-code-password-auth'; printf '%s\n' 'FAIL [TC-OJT-0037]: synthetic role authentication contract failed' >&2; exit 1; }
grep -Eq 'student=authenticated coordinator=authenticated superuser=authenticated' "$TMP_ROOT/auth.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0037 assertion=all-role-auth'; printf '%s\n' 'FAIL [TC-OJT-0037]: not all synthetic roles authenticated' >&2; exit 1; }
if grep -Eiq 'DemoOnly-|password=|secret=|token=|BEGIN .*PRIVATE KEY|@test\.example' "$TMP_ROOT"/*.log; then
    logger -t ojt-ticket03 'event=failure case=TC-OJT-0037 assertion=no-secret-or-pii-output'; printf '%s\n' 'FAIL [TC-OJT-0037]: credentials, token material, or PII appeared in captured output' >&2; exit 1
fi

# TC-OJT-0038: mutate, reset twice, and require exact fixture/client/key
# restoration while observing fake-mode/no-provider behavior.
logger -t ojt-ticket03 'event=major-operation status=reset case=TC-OJT-0038' || :
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-mutate >"$TMP_ROOT/mutate.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=mutation'; printf '%s\n' 'FAIL [TC-OJT-0038]: deterministic local mutation failed' >&2; exit 1; }
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-reset >"$TMP_ROOT/reset-1.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=reset'; printf '%s\n' 'FAIL [TC-OJT-0038]: demo-reset failed' >&2; exit 1; }
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-inspect >"$TMP_ROOT/fixture-reset-1.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=reset-fixtures'; printf '%s\n' 'FAIL [TC-OJT-0038]: reset fixture inspection failed' >&2; exit 1; }
cmp -s "$TMP_ROOT/fixture-1.log" "$TMP_ROOT/fixture-reset-1.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=exact-reset'; printf '%s\n' 'FAIL [TC-OJT-0038]: reset did not restore exact fixtures/clients/key hashes' >&2; exit 1; }
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-reset >"$TMP_ROOT/reset-2.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=repeat-reset'; printf '%s\n' 'FAIL [TC-OJT-0038]: repeated demo-reset failed' >&2; exit 1; }
cmp -s "$TMP_ROOT/fixture-1.log" "$TMP_ROOT/fixture-reset-1.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=repeat-reset-stability'; printf '%s\n' 'FAIL [TC-OJT-0038]: repeated reset changed deterministic contract' >&2; exit 1; }
# test-10032026-Maurice
# Scope provider/network assertions to runtime bootstrap/reset paths and their
# runtime events; Compose build logs may legitimately contain public dependency
# URLs and download tooling and are not provider-call evidence.
RUNTIME_LOGS="$TMP_ROOT/mutate.log $TMP_ROOT/reset-1.log $TMP_ROOT/reset-2.log"
if grep -Eiq 'bulsu|bsu\.edu|university|provider[_-]?(host|key|url)|api[_-]?key|curl[[:space:]]|wget[[:space:]]|nc[[:space:]]|provider.*(call|request)' \
    "$REPO_ROOT/docker/php/demo-bootstrap" "$REPO_ROOT/docker/php/demo-reset" "$REPO_ROOT/scripts/demo-seed.php" $RUNTIME_LOGS; then
    logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=runtime-provider-safe-output'; printf '%s\n' 'FAIL [TC-OJT-0038]: runtime bootstrap/reset paths or events indicate provider access' >&2; exit 1
fi

# TC-OJT-0038: a destructive reset must refuse both non-local and non-fake
# invocations, and each refusal must leave the independently inspected fixture
# unchanged.
logger -t ojt-ticket03 'event=major-operation status=reset-guard case=TC-OJT-0038' || :
cp "$TMP_ROOT/fixture-1.log" "$TMP_ROOT/fixture-guard.before"
if timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T -e APP_ENV=staging -e PROVIDER_MODE=fake php-fpm demo-reset >"$TMP_ROOT/reset-guard-nonlocal.log" 2>&1; then
    logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=nonlocal-reset-refusal'
    printf '%s\n' 'FAIL [TC-OJT-0038]: demo-reset accepted a non-local environment' >&2
    exit 1
fi
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-inspect >"$TMP_ROOT/fixture-guard-nonlocal.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=nonlocal-reset-no-mutation-inspection'; printf '%s\n' 'FAIL [TC-OJT-0038]: inspection after non-local reset refusal failed' >&2; exit 1; }
cmp -s "$TMP_ROOT/fixture-guard.before" "$TMP_ROOT/fixture-guard-nonlocal.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=nonlocal-reset-no-mutation'; printf '%s\n' 'FAIL [TC-OJT-0038]: non-local reset refusal mutated fixture data' >&2; exit 1; }
if timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T -e APP_ENV=local -e PROVIDER_MODE=real php-fpm demo-reset >"$TMP_ROOT/reset-guard-nonfake.log" 2>&1; then
    logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=nonfake-reset-refusal'
    printf '%s\n' 'FAIL [TC-OJT-0038]: demo-reset accepted a non-fake provider mode' >&2
    exit 1
fi
timeout 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-inspect >"$TMP_ROOT/fixture-guard-nonfake.log" 2>&1 || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=nonfake-reset-no-mutation-inspection'; printf '%s\n' 'FAIL [TC-OJT-0038]: inspection after non-fake reset refusal failed' >&2; exit 1; }
cmp -s "$TMP_ROOT/fixture-guard.before" "$TMP_ROOT/fixture-guard-nonfake.log" || { logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=nonfake-reset-no-mutation'; printf '%s\n' 'FAIL [TC-OJT-0038]: non-fake reset refusal mutated fixture data' >&2; exit 1; }
if grep -Eiq 'bulsu|bsu\.edu|university|provider[_-]?(host|key|url)|api[_-]?key|curl[[:space:]]|wget[[:space:]]|nc[[:space:]]|provider.*(call|request)' "$TMP_ROOT/reset-guard-nonlocal.log" "$TMP_ROOT/reset-guard-nonfake.log"; then
    logger -t ojt-ticket03 'event=failure case=TC-OJT-0038 assertion=runtime-reset-guard-safe-output'; printf '%s\n' 'FAIL [TC-OJT-0038]: reset guard events indicate provider access' >&2; exit 1
fi

logger -t ojt-ticket03 'event=pass status=complete test=ticket03_bootstrap_test' || :
printf '%s\n' 'PASS: Ticket 03 bootstrap/reset contract'
