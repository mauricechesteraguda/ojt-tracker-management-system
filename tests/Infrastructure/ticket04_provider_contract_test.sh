#!/bin/sh

# test-10032026-Maurice
# Ticket 04 RED contract: REQ-03, mapped exactly to TC-OJT-0015–TC-OJT-0018.
# POSIX shell; no function definitions; fail-fast/max-failures=1. Static checks
# run before Docker and therefore expose a specific immediate RED artifact.
set -eu
umask 077
REPO_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)
COMPOSE_FILE=$REPO_ROOT/compose.yaml
PROJECT_NAME=${OJT_TEST_COMPOSE_PROJECT:-ojt-ticket04-$$}
# fix-10032026-Maurice: default to per-process isolated ports; callers may still
# provide explicitly reserved ports for CI.
APP_PORT=${OJT_TEST_APP_PORT:-$((18000 + ($$ % 1000)))}
SENTINEL_PORT=${OJT_TEST_SENTINEL_PORT:-$((19000 + ($$ % 1000)))}
TMP_ROOT=${TMPDIR:-/tmp}/ojt-ticket04-$$
SENTINEL_PID=
COMPOSE_STARTED=0
mkdir -p "$TMP_ROOT"
trap 'if [ -n "${SENTINEL_PID:-}" ]; then kill "$SENTINEL_PID" >/dev/null 2>&1 || :; fi; if [ "$COMPOSE_STARTED" = 1 ]; then docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" down --volumes --remove-orphans >/dev/null 2>&1 || :; fi; rm -rf -- "$TMP_ROOT"' EXIT HUP INT TERM
logger -t ojt-ticket04 'event=startup status=begin test=ticket04_provider_contract_test max_failures=1' || :

# TC-OJT-0015: interface/fake/real provider, binding/config/controller, and no
# hard-coded legacy key/base URL. This is the first gate: no Docker before it.
logger -t ojt-ticket04 'event=major-operation status=check case=TC-OJT-0015 artifact=provider-boundary' || :
[ -f "$REPO_ROOT/app/Contracts/AcademicProvider.php" ] || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0015 assertion=provider-interface' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: missing provider boundary artifact app/Contracts/AcademicProvider.php (Docker not started)' >&2; exit 1; }
[ -f "$REPO_ROOT/app/Services/Academic/FakeAcademicProvider.php" ] || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0015 assertion=fake-provider' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: missing fake academic provider adapter' >&2; exit 1; }
[ -f "$REPO_ROOT/app/Services/Academic/RealAcademicProvider.php" ] || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0015 assertion=real-provider' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: missing real academic provider adapter' >&2; exit 1; }
[ -f "$REPO_ROOT/app/Providers/AcademicProviderServiceProvider.php" ] || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0015 assertion=binding' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: missing academic provider binding' >&2; exit 1; }
[ -f "$REPO_ROOT/config/academic.php" ] || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0015 assertion=config' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: missing config/academic.php' >&2; exit 1; }
[ -f "$REPO_ROOT/app/Http/Controllers/AcademicProfileController.php" ] || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0015 assertion=controller' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: missing academic profile controller' >&2; exit 1; }
grep -Eq 'interface[[:space:]]+AcademicProvider|implements[[:space:]]+AcademicProvider' "$REPO_ROOT/app/Contracts/AcademicProvider.php" "$REPO_ROOT/app/Services/Academic/FakeAcademicProvider.php" "$REPO_ROOT/app/Services/Academic/RealAcademicProvider.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0015 assertion=interface-shape' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: adapters do not implement AcademicProvider' >&2; exit 1; }
if grep -R -Eiq 'bulsu|bsu\.edu|api[_-]?key[[:space:]]*[:=][[:space:]]*[A-Za-z0-9]' "$REPO_ROOT/app" "$REPO_ROOT/config" "$REPO_ROOT/routes" "$REPO_ROOT/resources"; then logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0015 assertion=legacy-provider' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: hard-coded legacy provider key/base URL detected' >&2; exit 1; fi

# TC-OJT-0015/0016: POST-only route, body validation, and rate limiting.
logger -t ojt-ticket04 'event=major-operation status=check cases=TC-OJT-0015,TC-OJT-0016 assertion=route' || :
grep -Eq "Route::post[[:space:]]*\([^;]*academic/profile|academic/profile[^;]*Route::post" "$REPO_ROOT/routes/api.php" "$REPO_ROOT/routes/web.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0015 assertion=post-route' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: missing POST /api/academic/profile route' >&2; exit 1; }
if grep -Eiq 'Route::(get|any|match)[^;]*academic/profile|academic/profile[^;]*Route::(get|any|match)' "$REPO_ROOT/routes/api.php" "$REPO_ROOT/routes/web.php"; then logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0015 assertion=post-only' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: academic profile route accepts a non-POST method' >&2; exit 1; fi
grep -Eiq 'sr_code|password' "$REPO_ROOT/app/Http/Controllers/AcademicProfileController.php" "$REPO_ROOT/app/Services/Academic/FakeAcademicProvider.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0016 assertion=validation' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: sr_code/password body validation is missing' >&2; exit 1; }
grep -Eiq 'throttle|rate.?limit|RateLimiter' "$REPO_ROOT/routes/api.php" "$REPO_ROOT/app/Http/Controllers/AcademicProfileController.php" "$REPO_ROOT/app/Providers/RouteServiceProvider.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0016 assertion=rate-limit' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: academic profile route has no explicit rate limit' >&2; exit 1; }

# TC-OJT-0018: exact environment names and bounded connect/request timeouts.
logger -t ojt-ticket04 'event=major-operation status=check case=TC-OJT-0018 assertion=environment' || :
grep -Eq 'ACADEMIC_PROVIDER_MODE|ACADEMIC_PROVIDER_BASE_URL|ACADEMIC_PROVIDER_KEY|ACADEMIC_PROVIDER_CONNECT_TIMEOUT_SECONDS|ACADEMIC_PROVIDER_TIMEOUT_SECONDS' "$REPO_ROOT/config/academic.php" "$REPO_ROOT/.env.example" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0018 assertion=environment-names' || :; printf '%s\n' 'FAIL [TC-OJT-0018]: required academic provider environment names are missing' >&2; exit 1; }
grep -Eq 'ACADEMIC_PROVIDER_CONNECT_TIMEOUT_SECONDS[^0-9]*2|connect_timeout[^0-9]*2' "$REPO_ROOT/config/academic.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0018 assertion=connect-timeout' || :; printf '%s\n' 'FAIL [TC-OJT-0018]: connect timeout must be 2 seconds' >&2; exit 1; }
grep -Eq 'ACADEMIC_PROVIDER_TIMEOUT_SECONDS[^0-9]*5|timeout[^0-9]*5' "$REPO_ROOT/config/academic.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0018 assertion=request-timeout' || :; printf '%s\n' 'FAIL [TC-OJT-0018]: provider timeout must be 5 seconds' >&2; exit 1; }

# TC-OJT-0016: normalized safe fake/error fields and deterministic failures.
logger -t ojt-ticket04 'event=major-operation status=check case=TC-OJT-0016 assertion=safe-contract' || :
grep -Eiq 'correlation_id|academic_credentials_invalid|academic_provider_timeout|academic_provider_unavailable' "$REPO_ROOT/app/Services/Academic/FakeAcademicProvider.php" "$REPO_ROOT/app/Http/Controllers/AcademicProfileController.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0016 assertion=fake-errors' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: fake success/error correlation contract is incomplete' >&2; exit 1; }
if grep -R -Eiq 'return[^;]*(password|token|api[_-]?key|raw_payload)|log[^;]*(password|token|api[_-]?key)' "$REPO_ROOT/app/Services/Academic" "$REPO_ROOT/app/Http/Controllers/AcademicProfileController.php"; then logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0016 assertion=sensitive-output' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: provider boundary returns or logs sensitive fields' >&2; exit 1; fi

# TC-OJT-0017: malformed real payload and missing config fail closed, raw body absent.
logger -t ojt-ticket04 'event=major-operation status=check case=TC-OJT-0017 assertion=real-boundary' || :
grep -Eiq 'academic_provider_malformed|ACADEMIC_PROVIDER_MODE|ACADEMIC_PROVIDER_BASE_URL|ACADEMIC_PROVIDER_KEY' "$REPO_ROOT/app/Services/Academic/RealAcademicProvider.php" "$REPO_ROOT/config/academic.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0017 assertion=real-contract' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: real malformed/fail-closed contract is missing' >&2; exit 1; }
if grep -Eiq 'echo[^;]*(body|payload)|dd[[:space:]]*\(|var_dump|print_r|raw_payload' "$REPO_ROOT/app/Services/Academic/RealAcademicProvider.php"; then logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0017 assertion=raw-body' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: real provider raw payload can reach output' >&2; exit 1; fi

# Strengthened TC-OJT-0015–TC-OJT-0018 static contract: credentials are body
# only; real transport is TLS-verified except an explicit loopback test seam;
# upstream status, strict response normalization, and structured failures are
# safe before any runtime probe is attempted.
logger -t ojt-ticket04 'event=major-operation status=check cases=TC-OJT-0015,TC-OJT-0016,TC-OJT-0017,TC-OJT-0018 assertion=strengthened-contract' || :
grep -Eiq 'query\(|query.*sr_code|sr_code.*query|body.*only|input\(' "$REPO_ROOT/app/Http/Controllers/AcademicProfileController.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0015 assertion=query-credentials' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: query-string credentials are not explicitly rejected' >&2; exit 1; }
grep -Eiq 'required.*before|validator.*fails|validation.*provider|validate' "$REPO_ROOT/app/Http/Controllers/AcademicProfileController.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0016 assertion=validation-order' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: required-field validation is not proven before provider invocation' >&2; exit 1; }
grep -Eiq 'parse_url|https|127\.0\.0\.1|localhost|loopback' "$REPO_ROOT/app/Services/Academic/RealAcademicProvider.php" "$REPO_ROOT/config/academic.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0018 assertion=https-policy' || :; printf '%s\n' 'FAIL [TC-OJT-0018]: real provider URL policy does not reject non-HTTPS outside loopback' >&2; exit 1; }
grep -Eiq 'CURLOPT_SSL_VERIFYPEER[^;]*(true|1)|CURLOPT_SSL_VERIFYHOST[^;]*[2468]' "$REPO_ROOT/app/Services/Academic/RealAcademicProvider.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0018 assertion=tls-verification' || :; printf '%s\n' 'FAIL [TC-OJT-0018]: TLS peer/host verification is not enabled statically' >&2; exit 1; }
grep -Eiq 'CURLINFO_HTTP_CODE|http.?code|non.?2xx|status.*503|503.*status' "$REPO_ROOT/app/Services/Academic/RealAcademicProvider.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0017 assertion=upstream-status' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: upstream non-2xx response is not mapped to safe 503' >&2; exit 1; }
grep -Eiq 'array_intersect_key|whitelist|allowed.*profile|profile.*allowed|strict.*normal' "$REPO_ROOT/app/Services/Academic/RealAcademicProvider.php" "$REPO_ROOT/app/Http/Controllers/AcademicProfileController.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0017 assertion=payload-whitelist' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: real profile payload has no strict normalized whitelist/rejection' >&2; exit 1; }
grep -Eiq 'error_class|exception_class|stack|cause' "$REPO_ROOT/app/Support/SessionTracer.php" "$REPO_ROOT/app/Exceptions/AcademicProviderException.php" "$REPO_ROOT/app/Services/Academic/RealAcademicProvider.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0018 assertion=structured-failure' || :; printf '%s\n' 'FAIL [TC-OJT-0018]: unexpected failures lack structured class/stack/cause evidence' >&2; exit 1; }
grep -Eiq 'probe.*correlation|correlation.*probe|correlation_id' "$REPO_ROOT/app/Console/Commands/AcademicProviderProbe.php" "$REPO_ROOT/app/Support/SessionTracer.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0017 assertion=probe-correlation' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: provider probe correlation is not asserted' >&2; exit 1; }
grep -Eiq 'expected|credentials_invalid|error.*category|ERROR|error_level' "$REPO_ROOT/app/Support/SessionTracer.php" "$REPO_ROOT/app/Services/Academic/FakeAcademicProvider.php" || { logger -t ojt-ticket04 'event=failure status=red case=TC-OJT-0016 assertion=expected-error-level' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: expected invalid credentials are not distinguished from ERROR failures' >&2; exit 1; }

# Runtime prerequisites occur only after all static RED gates.
[ -f "$COMPOSE_FILE" ] || { logger -t ojt-ticket04 'event=failure assertion=compose-file' || :; printf '%s\n' 'FAIL [TC-OJT-0018]: compose.yaml is required' >&2; exit 1; }
if command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 "$APP_PORT" >/dev/null 2>&1; then logger -t ojt-ticket04 'event=failure assertion=app-port-conflict' || :; printf '%s\n' 'FAIL [TC-OJT-0018]: selected app port is already in use' >&2; exit 1; fi
if command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 "$SENTINEL_PORT" >/dev/null 2>&1; then logger -t ojt-ticket04 'event=failure assertion=sentinel-port-conflict' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: selected sentinel port is already in use' >&2; exit 1; fi
command -v timeout >/dev/null 2>&1 || { logger -t ojt-ticket04 'event=failure assertion=timeout' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: timeout command is required' >&2; exit 1; }
command -v curl >/dev/null 2>&1 || { logger -t ojt-ticket04 'event=failure assertion=curl' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: curl is required' >&2; exit 1; }
command -v php >/dev/null 2>&1 || { logger -t ojt-ticket04 'event=failure assertion=php' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: PHP is required for local sentinel' >&2; exit 1; }

# Local sentinel router is top-level PHP, no functions; persisted evidence is count only.
cat >"$TMP_ROOT/sentinel.php" <<'PHP'
<?php
$count_file = getenv('OJT_SENTINEL_COUNT_FILE');
$count = is_file($count_file) ? (int) trim(file_get_contents($count_file)) : 0;
file_put_contents($count_file, (string) ($count + 1), LOCK_EX);
if (isset($_GET['mode']) && $_GET['mode'] === 'non2xx') {
    http_response_code(502);
    header('Content-Type: application/json');
    echo '{"error":"upstream-failure","password":"must-not-leak"}';
    exit;
}
http_response_code(200);
header('Content-Type: application/json');
if (isset($_GET['mode']) && $_GET['mode'] === 'forbidden') {
    echo '{"profile":{"sr_code":"DEMO-STUDENT-001","first_name":"Demo","last_name":"Student","token":"forbidden","password":"forbidden","raw_extra":"forbidden"},"token":"forbidden","password":"forbidden"}';
    exit;
}
echo '{"provider_raw_marker":"sentinel-malformed-body"}';
PHP
printf '%s\n' 0 >"$TMP_ROOT/sentinel.count"
OJT_SENTINEL_COUNT_FILE="$TMP_ROOT/sentinel.count" php -S "127.0.0.1:$SENTINEL_PORT" -t "$TMP_ROOT" >"$TMP_ROOT/sentinel.log" 2>&1 & SENTINEL_PID=$!
SENTINEL_READY=0
for ATTEMPT in 1 2 3 4 5 6 7 8 9 10; do
    if timeout 1 curl -fsS "http://127.0.0.1:$SENTINEL_PORT/sentinel.php" >/dev/null 2>&1; then SENTINEL_READY=1; break; fi
    sleep 0.1
done
[ "$SENTINEL_READY" = 1 ] || { logger -t ojt-ticket04 'event=failure assertion=sentinel-ready' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: local sentinel did not become ready' >&2; exit 1; }
printf '%s\n' 0 >"$TMP_ROOT/sentinel.count"
export COMPOSE_PROJECT_NAME=$PROJECT_NAME APP_PORT PROVIDER_MODE=fake ACADEMIC_PROVIDER_MODE=fake
export AGENT_SESSION_ID=${AGENT_SESSION_ID:-ticket04-$$}
export ACADEMIC_PROVIDER_BASE_URL="http://host.docker.internal:$SENTINEL_PORT" ACADEMIC_PROVIDER_KEY=

# TC-OJT-0018: clean isolated Compose fake mode. Build the current default
# Compose images in this same test so image names can never drift to a stale
# project-specific tag.
logger -t ojt-ticket04 'event=major-operation status=compose-up case=TC-OJT-0018' || :
timeout "${OJT_TEST_BUILD_TIMEOUT_SECONDS:-180}" docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" build >"$TMP_ROOT/compose-build.log" 2>&1 || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0018 assertion=compose-build' || :; printf '%s\n' 'FAIL [TC-OJT-0018]: current default Compose images did not build' >&2; exit 1; }
timeout 120 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" up -d --no-build >"$TMP_ROOT/compose-up.log" 2>&1 || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0018 assertion=compose-up' || :; printf '%s\n' 'FAIL [TC-OJT-0018]: isolated Compose fake mode did not start' >&2; exit 1; }
COMPOSE_STARTED=1
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" ps --status running >"$TMP_ROOT/compose-ps.log" 2>&1 || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0018 assertion=compose-ready' || :; printf '%s\n' 'FAIL [TC-OJT-0018]: Compose services are not running' >&2; exit 1; }

# Strengthened TC-OJT-0015/0016 fail-fast request-boundary probes. Query-string
# credentials and malformed/partial bodies must be rejected before the provider
# seam is entered; the local sentinel count and provider trace are both proof.
docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp "php-fpm:/home/app/.cache/agent-trace/ojt-tracker-management-system/$AGENT_SESSION_ID.jsonl" "$TMP_ROOT/boundary-before.trace" >/dev/null 2>&1 || :
BOUNDARY_BEFORE_LINES=0
[ -f "$TMP_ROOT/boundary-before.trace" ] && BOUNDARY_BEFORE_LINES=$(wc -l <"$TMP_ROOT/boundary-before.trace" | tr -d ' ')
timeout 5 curl -sS -o "$TMP_ROOT/query-credentials.json" -w '%{http_code}' -H 'X-Forwarded-For: 10.0.0.1' -H 'Accept: application/json' -H 'Content-Type: application/json' -X POST "http://127.0.0.1:$APP_PORT/api/academic/profile?sr_code=DEMO-STUDENT-001&password=DemoOnly-Student-001%21" --data '{}' >"$TMP_ROOT/query-credentials.status" || :
grep -Eq '^4[0-9][0-9]$' "$TMP_ROOT/query-credentials.status" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=query-credentials-status' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: query-string credentials were not rejected' >&2; exit 1; }
docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp "php-fpm:/home/app/.cache/agent-trace/ojt-tracker-management-system/$AGENT_SESSION_ID.jsonl" "$TMP_ROOT/query-credentials.trace" >/dev/null 2>&1 || :
[ -f "$TMP_ROOT/query-credentials.trace" ] || : >"$TMP_ROOT/query-credentials.trace"
tail -n +$((BOUNDARY_BEFORE_LINES + 1)) "$TMP_ROOT/query-credentials.trace" | grep -q '"operation":"academic.profile"' && { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=query-zero-provider' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: query-string credentials reached the provider' >&2; exit 1; }

BOUNDARY_BEFORE_LINES=$(wc -l <"$TMP_ROOT/query-credentials.trace" | tr -d ' ')
MIXED_BEFORE_SENTINEL=$(cat "$TMP_ROOT/sentinel.count")
timeout 5 curl -sS -o "$TMP_ROOT/mixed-credentials.json" -w '%{http_code}' -H 'X-Forwarded-For: 10.0.0.3' -H 'Accept: application/json' -H 'Content-Type: application/json' -X POST "http://127.0.0.1:$APP_PORT/api/academic/profile?sr_code=QUERY-STUDENT-001&password=QueryOnly-Student-001%21" --data '{"sr_code":"DEMO-STUDENT-001","password":"DemoOnly-Student-001!"}' >"$TMP_ROOT/mixed-credentials.status" || :
grep -Eq '^4[0-9][0-9]$' "$TMP_ROOT/mixed-credentials.status" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=mixed-credentials-status' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: mixed body/query credentials were not rejected' >&2; exit 1; }
docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp "php-fpm:/home/app/.cache/agent-trace/ojt-tracker-management-system/$AGENT_SESSION_ID.jsonl" "$TMP_ROOT/mixed-credentials.trace" >/dev/null 2>&1 || :
[ -f "$TMP_ROOT/mixed-credentials.trace" ] || : >"$TMP_ROOT/mixed-credentials.trace"
tail -n +$((BOUNDARY_BEFORE_LINES + 1)) "$TMP_ROOT/mixed-credentials.trace" | grep -q '"operation":"academic.profile"' && { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=mixed-zero-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: mixed body/query credentials invoked a provider trace' >&2; exit 1; }
[ "$(cat "$TMP_ROOT/sentinel.count")" = "$MIXED_BEFORE_SENTINEL" ] || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=mixed-zero-sentinel' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: mixed body/query credentials contacted local sentinel' >&2; exit 1; }
timeout 5 curl -sS -o "$TMP_ROOT/malformed-body.json" -w '%{http_code}' -H 'X-Forwarded-For: 10.0.0.2' -H 'Accept: application/json' -H 'Content-Type: application/json' -X POST "http://127.0.0.1:$APP_PORT/api/academic/profile" --data '{"sr_code":"DEMO-STUDENT-001"}' >"$TMP_ROOT/malformed-body.status" || :
grep -Eq '^4[0-9][0-9]$' "$TMP_ROOT/malformed-body.status" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=required-fields-status' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: missing required field was not rejected' >&2; exit 1; }
docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp "php-fpm:/home/app/.cache/agent-trace/ojt-tracker-management-system/$AGENT_SESSION_ID.jsonl" "$TMP_ROOT/malformed-body.trace" >/dev/null 2>&1 || :
[ -f "$TMP_ROOT/malformed-body.trace" ] || : >"$TMP_ROOT/malformed-body.trace"
tail -n +$((BOUNDARY_BEFORE_LINES + 1)) "$TMP_ROOT/malformed-body.trace" | grep -q '"operation":"academic.profile"' && { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=required-fields-zero-provider' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: malformed body reached the provider' >&2; exit 1; }
BOUNDARY_BEFORE_LINES=$(wc -l <"$TMP_ROOT/malformed-body.trace" | tr -d ' ')
[ "$(cat "$TMP_ROOT/sentinel.count")" = 0 ] || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=boundary-zero-sentinel' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: rejected request contacted local sentinel' >&2; exit 1; }

# TC-OJT-0015/0016 runtime: known 200 safe normalized response, unknown 422,
# fake timeout/unavailable 503 under one second, HTML-safe errors, rate-limit.
logger -t ojt-ticket04 'event=major-operation status=api-probes cases=TC-OJT-0015,TC-OJT-0016' || :
KNOWN_BEFORE_LINES=$BOUNDARY_BEFORE_LINES
if timeout 5 curl -sS -o "$TMP_ROOT/unknown.json" -w '%{http_code}' -H 'Accept: application/json' -H 'Content-Type: application/json' -X POST "http://127.0.0.1:$APP_PORT/api/academic/profile" --data '{"sr_code":"UNKNOWN","password":"wrong"}' >"$TMP_ROOT/unknown.status" && [ "$(cat "$TMP_ROOT/unknown.status")" = 422 ]; then :; else logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=unknown-422' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: unknown credentials must return 422' >&2; exit 1; fi
grep -Eq 'academic_credentials_invalid|correlation_id' "$TMP_ROOT/unknown.json" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=unknown-safe-error' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: unknown credentials lack safe error contract' >&2; exit 1; }
grep -Eq '^4[0-9][0-9]$' "$TMP_ROOT/query-credentials.status" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=query-only-rejected' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: query-only credentials were accepted or reached provider' >&2; exit 1; }
grep -Eq '^4[0-9][0-9]$' "$TMP_ROOT/malformed-body.status" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=validation-before-provider' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: invalid body was not rejected before provider invocation' >&2; exit 1; }
timeout 5 curl -fsS -D "$TMP_ROOT/known.headers" -H 'X-Forwarded-For: 10.0.0.5' -H 'Accept: application/json' -H 'Content-Type: application/json' -H 'X-Correlation-ID: ticket04-correlation' -X POST "http://127.0.0.1:$APP_PORT/api/academic/profile" --data '{"sr_code":"DEMO-STUDENT-001","password":"DemoOnly-Student-001!"}' >"$TMP_ROOT/known.json" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=known-200' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: known fake profile did not return 200' >&2; exit 1; }
grep -Eq '^HTTP/[^ ]+[[:space:]]+200' "$TMP_ROOT/known.headers" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=known-status' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: known fake profile status is not 200' >&2; exit 1; }
grep -q correlation_id "$TMP_ROOT/known.json" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=correlation' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: known profile lacks correlation_id' >&2; exit 1; }
grep -q 'ticket04-correlation' "$TMP_ROOT/known.json" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=correlation-propagation' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: correlation ID was not propagated unchanged' >&2; exit 1; }
if grep -Eiq 'password|token|api[_-]?key|email|phone|address|sentinel-malformed-body' "$TMP_ROOT/known.json"; then logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=safe-fields' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: known profile contains forbidden sensitive/PII fields' >&2; exit 1; fi
KNOWN_CORRELATION=$(sed -n 's/.*"correlation_id":"\([^"]*\)".*/\1/p' "$TMP_ROOT/known.json" | head -n 1)
docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp "php-fpm:/home/app/.cache/agent-trace/ojt-tracker-management-system/$AGENT_SESSION_ID.jsonl" "$TMP_ROOT/known.trace" >/dev/null 2>&1 || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=correlation-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: known request trace was not captured' >&2; exit 1; }
tail -n +$((KNOWN_BEFORE_LINES + 1)) "$TMP_ROOT/known.trace" >"$TMP_ROOT/known.trace.delta"
[ -n "$KNOWN_CORRELATION" ] || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=correlation-value' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: known response correlation_id is empty' >&2; exit 1; }
grep '"operation":"academic.profile.http".*"correlation_id":"'$KNOWN_CORRELATION'"' "$TMP_ROOT/known.trace" | sed -n 's/.*"correlation_id":"\([^"]*\)".*/\1/p' | sort -u >"$TMP_ROOT/http-correlation.ids"
[ "$(wc -l <"$TMP_ROOT/http-correlation.ids" | tr -d ' ')" = 1 ] && grep -qx "$KNOWN_CORRELATION" "$TMP_ROOT/http-correlation.ids" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=correlation-consistency' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: request/response correlation_id was regenerated across HTTP trace events' >&2; exit 1; }
grep '"operation":"academic.profile".*"correlation_id":"'$KNOWN_CORRELATION'"' "$TMP_ROOT/known.trace" | sed -n 's/.*"correlation_id":"\([^"]*\)".*/\1/p' | sort -u >"$TMP_ROOT/provider-correlation.ids"
[ "$(wc -l <"$TMP_ROOT/provider-correlation.ids" | tr -d ' ')" = 1 ] && grep -qx "$KNOWN_CORRELATION" "$TMP_ROOT/provider-correlation.ids" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0015 assertion=provider-correlation-consistency' || :; printf '%s\n' 'FAIL [TC-OJT-0015]: provider trace correlation_id differs from request/response' >&2; exit 1; }

for CASE in timeout unavailable; do
    if timeout 2 curl -sS -o "$TMP_ROOT/$CASE.json" -w '%{http_code}' -H "X-Forwarded-For: 10.0.0.$((7 + ${#CASE}))" -H 'Accept: application/json' -H 'Content-Type: application/json' -X POST "http://127.0.0.1:$APP_PORT/api/academic/profile" --data "{\"sr_code\":\"DEMO-FAKE-$CASE\",\"password\":\"fixture\"}" >"$TMP_ROOT/$CASE.status" && [ "$(cat "$TMP_ROOT/$CASE.status")" = 503 ] && grep -q "academic_provider_$CASE" "$TMP_ROOT/$CASE.json"; then :; else logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=fake-503' || :; printf '%s\n' "FAIL [TC-OJT-0016]: fake $CASE must return 503 under one second" >&2; exit 1; fi
done

REGISTER_PAGE=$(timeout 5 curl -fsS -c "$TMP_ROOT/register.cookies" -H 'Accept: text/html' "http://127.0.0.1:$APP_PORT/register") || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=html-form' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: registration form did not load' >&2; exit 1; }
CSRF_TOKEN=$(printf '%s' "$REGISTER_PAGE" | sed -n 's/.*name="_token" value="\([^"]*\)".*/\1/p' | head -n 1)
[ -n "$CSRF_TOKEN" ] || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=html-csrf' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: registration form did not expose a CSRF field' >&2; exit 1; }
timeout 5 curl -sS -b "$TMP_ROOT/register.cookies" -c "$TMP_ROOT/register.cookies" -o "$TMP_ROOT/html.html" -w '%{http_code}' -H 'Accept: text/html' -H 'Content-Type: application/x-www-form-urlencoded' -X POST "http://127.0.0.1:$APP_PORT/register" --data-urlencode "_token=$CSRF_TOKEN" --data 'sr_code=UNKNOWN&password=wrong' >"$TMP_ROOT/html.status" || :
rm -f "$TMP_ROOT/register.cookies"
grep -Eq '30[1278]|422' "$TMP_ROOT/html.status" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=html-safe' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: HTML registration error is not redirect/field-safe' >&2; exit 1; }
rm -f "$TMP_ROOT/html.html"
[ "$(cat "$TMP_ROOT/sentinel.count")" = 0 ] || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=fake-zero-network' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: fake mode contacted local sentinel' >&2; exit 1; }

# TC-OJT-0017: one-off existing PHP image probes only local sentinel in real mode;
# malformed raw body is absent, and missing config returns 503 fail-closed.
logger -t ojt-ticket04 'event=major-operation status=real-probe case=TC-OJT-0017' || :
MALFORMED_BEFORE_SENTINEL=$(cat "$TMP_ROOT/sentinel.count")
timeout 5 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" run --name "${PROJECT_NAME}-real-malformed" --no-deps -T -e APP_ENV=local -e ACADEMIC_PROVIDER_MODE=real -e ACADEMIC_PROVIDER_ALLOW_LOCAL=1 -e ACADEMIC_PROVIDER_BASE_URL="http://host.docker.internal:$SENTINEL_PORT/sentinel.php" -e ACADEMIC_PROVIDER_KEY=sentinel-test-key -e AGENT_SESSION_ID="$AGENT_SESSION_ID" php-fpm php artisan academic:provider-probe --correlation-id "$AGENT_SESSION_ID-real-malformed" --sr-code DEMO-STUDENT-001 --password fixture >"$TMP_ROOT/real-malformed.log" 2>&1 || :
docker cp "${PROJECT_NAME}-real-malformed:/home/app/.cache/agent-trace/ojt-tracker-management-system/$AGENT_SESSION_ID.jsonl" "$TMP_ROOT/real-malformed.trace" >/dev/null 2>&1 || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=real-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: real malformed probe trace was not captured' >&2; exit 1; }
grep -q '"event":"entry"' "$TMP_ROOT/real-malformed.trace" && grep -q '"event":"exit"' "$TMP_ROOT/real-malformed.trace" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=real-trace-events' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: real malformed probe trace is incomplete' >&2; exit 1; }
if grep -Eiq 'sentinel-malformed-body|password|token|api[_-]?key|provider_raw_marker' "$TMP_ROOT/real-malformed.log"; then logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=malformed-redaction' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: malformed raw body or secret appeared in output/log' >&2; exit 1; fi
grep -Eq '(^|[^[:alnum:]_])academic_provider_malformed([^[:alnum:]_]|$)' "$TMP_ROOT/real-malformed.log" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=malformed-code' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: malformed local provider response must return exact academic_provider_malformed code' >&2; exit 1; }
[ "$(cat "$TMP_ROOT/sentinel.count")" = "$((MALFORMED_BEFORE_SENTINEL + 1))" ] || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=malformed-sentinel-hit' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: malformed real probe did not make exactly one sentinel request' >&2; exit 1; }
NON2XX_BEFORE_SENTINEL=$(cat "$TMP_ROOT/sentinel.count")
timeout 5 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" run --name "${PROJECT_NAME}-real-non2xx" --no-deps -T -e APP_ENV=local -e ACADEMIC_PROVIDER_MODE=real -e ACADEMIC_PROVIDER_ALLOW_LOCAL=1 -e ACADEMIC_PROVIDER_BASE_URL="http://host.docker.internal:$SENTINEL_PORT/sentinel.php?mode=non2xx" -e ACADEMIC_PROVIDER_KEY=sentinel-test-key -e AGENT_SESSION_ID="$AGENT_SESSION_ID" php-fpm php artisan academic:provider-probe --correlation-id "$AGENT_SESSION_ID-real-non2xx" --sr-code DEMO-STUDENT-001 --password fixture >"$TMP_ROOT/real-non2xx.log" 2>&1 || :
docker cp "${PROJECT_NAME}-real-non2xx:/home/app/.cache/agent-trace/ojt-tracker-management-system/$AGENT_SESSION_ID.jsonl" "$TMP_ROOT/real-non2xx.trace" >/dev/null 2>&1 || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=non2xx-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: non-2xx trace was not captured' >&2; exit 1; }
grep -Eq '(^|[^[:alnum:]_])academic_provider_unavailable([^[:alnum:]_]|$)' "$TMP_ROOT/real-non2xx.log" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=non2xx-code' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: local sentinel non-2xx response did not return exact academic_provider_unavailable code' >&2; exit 1; }
[ "$(cat "$TMP_ROOT/sentinel.count")" = "$((NON2XX_BEFORE_SENTINEL + 1))" ] || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=non2xx-sentinel-hit' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: non-2xx real probe did not make exactly one sentinel request' >&2; exit 1; }
if grep -Eiq 'upstream-failure|password|token|api[_-]?key' "$TMP_ROOT/real-non2xx.log"; then logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=non2xx-redaction' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: non-2xx upstream body leaked into probe output' >&2; exit 1; fi
FORBIDDEN_BEFORE_SENTINEL=$(cat "$TMP_ROOT/sentinel.count")
timeout 5 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" run --name "${PROJECT_NAME}-real-forbidden" --no-deps -T -e APP_ENV=local -e ACADEMIC_PROVIDER_MODE=real -e ACADEMIC_PROVIDER_ALLOW_LOCAL=1 -e ACADEMIC_PROVIDER_BASE_URL="http://host.docker.internal:$SENTINEL_PORT/sentinel.php?mode=forbidden" -e ACADEMIC_PROVIDER_KEY=sentinel-test-key -e AGENT_SESSION_ID="$AGENT_SESSION_ID" php-fpm php artisan academic:provider-probe --correlation-id "$AGENT_SESSION_ID-real-forbidden" --sr-code DEMO-STUDENT-001 --password fixture >"$TMP_ROOT/real-forbidden.log" 2>&1 || :
docker cp "${PROJECT_NAME}-real-forbidden:/home/app/.cache/agent-trace/ojt-tracker-management-system/$AGENT_SESSION_ID.jsonl" "$TMP_ROOT/real-forbidden.trace" >/dev/null 2>&1 || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=forbidden-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: forbidden-payload trace was not captured' >&2; exit 1; }
sed "s/$AGENT_SESSION_ID-real-forbidden//g" "$TMP_ROOT/real-forbidden.log" >"$TMP_ROOT/real-forbidden.safe.log"
if grep -Eiq 'forbidden|raw_extra|password|token|api[_-]?key' "$TMP_ROOT/real-forbidden.safe.log"; then logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=forbidden-payload-redaction' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: valid profile with forbidden fields leaked unsafe payload' >&2; exit 1; fi
grep -Eq '(^|[^[:alnum:]_])academic_provider_malformed([^[:alnum:]_]|$)' "$TMP_ROOT/real-forbidden.log" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=forbidden-code' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: forbidden-field profile did not return exact academic_provider_malformed code' >&2; exit 1; }
[ "$(cat "$TMP_ROOT/sentinel.count")" = "$((FORBIDDEN_BEFORE_SENTINEL + 1))" ] || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=forbidden-sentinel-hit' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: forbidden real probe did not make exactly one sentinel request' >&2; exit 1; }
timeout 5 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" run --name "${PROJECT_NAME}-real-missing" --no-deps -T -e ACADEMIC_PROVIDER_MODE=real -e ACADEMIC_PROVIDER_BASE_URL= -e ACADEMIC_PROVIDER_KEY= -e AGENT_SESSION_ID="$AGENT_SESSION_ID" php-fpm php artisan academic:provider-probe --correlation-id "$AGENT_SESSION_ID-real-missing" --sr-code DEMO-STUDENT-001 --password fixture >"$TMP_ROOT/real-missing.log" 2>&1 || :
docker cp "${PROJECT_NAME}-real-missing:/home/app/.cache/agent-trace/ojt-tracker-management-system/$AGENT_SESSION_ID.jsonl" "$TMP_ROOT/real-missing.trace" >/dev/null 2>&1 || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=real-missing-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: real missing-config probe trace was not captured' >&2; exit 1; }
grep -q '"event":"entry"' "$TMP_ROOT/real-missing.trace" && grep -q '"event":"exit"' "$TMP_ROOT/real-missing.trace" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=real-missing-trace-events' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: real missing-config probe trace is incomplete' >&2; exit 1; }
grep -Eq 'academic_provider_unavailable|503' "$TMP_ROOT/real-missing.log" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0017 assertion=missing-config' || :; printf '%s\n' 'FAIL [TC-OJT-0017]: missing real config must fail closed with 503' >&2; exit 1; }

# TC-OJT-0016: repeated requests must yield 429; scan all runtime output/logs.
for ATTEMPT in 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21; do timeout 2 curl -sS -o "$TMP_ROOT/rate-$ATTEMPT.json" -w '%{http_code}' -H 'Accept: application/json' -H 'Content-Type: application/json' -X POST "http://127.0.0.1:$APP_PORT/api/academic/profile" --data '{"sr_code":"UNKNOWN","password":"wrong"}' >"$TMP_ROOT/rate-$ATTEMPT.status" || :; done
grep -q '^429$' "$TMP_ROOT"/rate-*.status || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=rate-limit-behavior' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: repeated profile requests were not rate limited' >&2; exit 1; }
timeout 10 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp "php-fpm:/home/app/.cache/agent-trace/ojt-tracker-management-system/$AGENT_SESSION_ID.jsonl" "$TMP_ROOT/session-trace.jsonl" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=session-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: session trace was not copied before cleanup' >&2; exit 1; }
grep -q '"event":"entry"' "$TMP_ROOT/session-trace.jsonl" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=trace-entry' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: session trace lacks entry events' >&2; exit 1; }
grep -q '"event":"exit"' "$TMP_ROOT/session-trace.jsonl" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=trace-exit' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: session trace lacks exit events' >&2; exit 1; }
grep -q 'ticket04-correlation' "$TMP_ROOT/session-trace.jsonl" || { logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=trace-correlation' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: trace correlation ID was not propagated' >&2; exit 1; }
if grep -Eiq 'password|token|api[_-]?key|raw_payload|provider_raw_marker|email|phone|address' "$TMP_ROOT/session-trace.jsonl"; then logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=trace-redaction' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: session trace contains sensitive data' >&2; exit 1; fi
rm -f "$TMP_ROOT/sentinel.php"
# Build output contains dependency names such as egulias/email-validator; scan runtime evidence only.
if find "$TMP_ROOT" -type f ! -name 'compose-build.log' -print0 | xargs -0 grep -Eiq 'password|token|api[_-]?key|sentinel-malformed-body|provider_raw_marker|email|phone|address'; then logger -t ojt-ticket04 'event=failure case=TC-OJT-0016 assertion=evidence-redaction' || :; printf '%s\n' 'FAIL [TC-OJT-0016]: runtime evidence contains secret/raw payload/PII' >&2; exit 1; fi
logger -t ojt-ticket04 'event=pass status=complete test=ticket04_provider_contract_test' || :
printf '%s\n' 'PASS: Ticket 04 academic provider contract'
