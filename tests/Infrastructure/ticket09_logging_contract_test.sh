#!/bin/sh
set -eu
umask 077

# test-10042026-Maurice: TC0040--TC0042 logging/trace contract.
REPO_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)
COMPOSE_FILE=$REPO_ROOT/compose.yaml
PROJECT_NAME=${OJT_TEST_COMPOSE_PROJECT:-ojt-ticket09-$$}
APP_PORT=${OJT_TEST_APP_PORT:-$((18040 + ($$ % 1000)))}
TMP_ROOT=${TMPDIR:-/tmp}/ojt-ticket09-$$
AGENT_SESSION_ID=${AGENT_SESSION_ID:-test-10042026-Maurice-$$}
mkdir -p "$TMP_ROOT"
trap 'docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" down --volumes --remove-orphans >/dev/null 2>&1 || :; rm -rf -- "$TMP_ROOT"' EXIT HUP INT TERM

printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"info","event":"startup","operation":"ticket09","component":"contract","status":"begin"}' >&2

# TC0040: JSON Laravel logging, registered correlation middleware, and the
# SessionTracer schema/redaction/error boundary are static prerequisites.
[ -f "$REPO_ROOT/config/logging.php" ] || { printf '%s\n' 'FAIL [TC0040]: logging config missing' >&2; exit 1; }
grep -Eiq 'JsonFormatter|json.*formatter|formatter.*json|tap.*json' "$REPO_ROOT/config/logging.php" || { printf '%s\n' 'FAIL [TC0040]: JSON logging formatter missing' >&2; exit 1; }
grep -ERiq 'correlation|X-Correlation-ID' "$REPO_ROOT/app/Http/Kernel.php" "$REPO_ROOT/app/Http/Middleware" "$REPO_ROOT/routes" || { printf '%s\n' 'FAIL [TC0040]: correlation middleware registration missing' >&2; exit 1; }
[ "$(grep -ERio 'Log::channel[[:space:]]*([("'"'"'](single|daily)["'"'"']))' "$REPO_ROOT/app" | wc -l | tr -d ' ')" -eq 0 ] || { printf '%s\n' 'FAIL [TC0040]: explicit single/daily channel in app' >&2; exit 1; }
# Every configured, enabled channel must opt into the structured formatter; the
# contract intentionally rejects Laravel's legacy single/daily/syslog defaults.
grep -Eq "'channels'[[:space:]]*=>" "$REPO_ROOT/config/logging.php" || { printf '%s\n' 'FAIL [TC0040]: logging channels missing' >&2; exit 1; }
grep -Eiq "JsonFormatter|formatter.*json|tap.*json" "$REPO_ROOT/config/logging.php" || { printf '%s\n' 'FAIL [TC0040]: JSON formatter is not configured for enabled channels' >&2; exit 1; }
if grep -Eq "'driver'[[:space:]]*=>[[:space:]]*'(single|daily|syslog|errorlog)'" "$REPO_ROOT/config/logging.php"; then printf '%s\n' 'FAIL [TC0040]: non-JSON enabled logging driver' >&2; exit 1; fi
[ -f "$REPO_ROOT/app/Support/SessionTracer.php" ] || { printf '%s\n' 'FAIL [TC0040]: SessionTracer missing' >&2; exit 1; }
grep -Eq 'function (id|enter|leave|exception|write|safeStack)' "$REPO_ROOT/app/Support/SessionTracer.php" || { printf '%s\n' 'FAIL [TC0040]: SessionTracer methods incomplete' >&2; exit 1; }
grep -Eq 'exception_class|error_category|safe_message|stack|cause|correlation_id' "$REPO_ROOT/app/Support/SessionTracer.php" || { printf '%s\n' 'FAIL [TC0040]: SessionTracer error fields incomplete' >&2; exit 1; }
grep -Eiq 'password|token|api[_-]?key|email|phone|address|payload|request' "$REPO_ROOT/app/Support/SessionTracer.php" && grep -Eiq 'redact|allow|safe|bounded|deny' "$REPO_ROOT/app/Support/SessionTracer.php" || { printf '%s\n' 'FAIL [TC0040]: SessionTracer redaction boundary missing' >&2; exit 1; }
grep -ERiq 'academic_credentials_invalid|Log::warning|level.*warning|invalid.*credential' "$REPO_ROOT/app" || { printf '%s\n' 'FAIL [TC0040]: invalid-credential warning/info contract missing' >&2; exit 1; }
grep -ERiq 'printPDF|pdf|view' "$REPO_ROOT/app/Http/Controllers" && grep -Eiq 'exception_class|error_category|safe_message|stack|cause' "$REPO_ROOT/app/Support/SessionTracer.php" || { printf '%s\n' 'FAIL [TC0040]: controlled PDF/view error contract missing' >&2; exit 1; }
grep -ERiq 'SessionTracer::exception' "$REPO_ROOT/app/Http/Controllers" "$REPO_ROOT/app/Services" || { printf '%s\n' 'FAIL [TC0040]: unexpected PDF/view failure is not traced' >&2; exit 1; }

# TC0041: owned shell output must be JSONL, and Nginx must declare structured
# access/error logging where supported.
for owned in "$REPO_ROOT/docker/php/entrypoint.sh" "$REPO_ROOT/docker/php/demo-bootstrap" "$REPO_ROOT/docker/php/demo-reset" "$REPO_ROOT/docker/mariadb/entrypoint.sh"; do
    [ -f "$owned" ] || { printf '%s\n' "FAIL [TC0041]: missing owned entrypoint $owned" >&2; exit 1; }
    grep -Eq 'printf|echo' "$owned" || { printf '%s\n' "FAIL [TC0041]: no emitter in $owned" >&2; exit 1; }
    if grep -Eq 'event=[A-Za-z_-]+|status=[A-Za-z_-]+|component=[A-Za-z_-]+|operation=[A-Za-z_.-]+' "$owned"; then
        printf '%s\n' "FAIL [TC0041]: key=value shell log in $owned" >&2
        exit 1
    fi
done
[ "$(grep -ERil 'status.*failure|status.*failed|level.*error' "$REPO_ROOT/docker/php/entrypoint.sh" "$REPO_ROOT/docker/php/demo-bootstrap" "$REPO_ROOT/docker/php/demo-reset" | wc -l | tr -d ' ')" -gt 0 ] || { printf '%s\n' 'FAIL [TC0041]: startup failure capture missing' >&2; exit 1; }
grep -ERiq 'remediation|error[-_]class|category|stack|cause' "$REPO_ROOT/docker/php/entrypoint.sh" "$REPO_ROOT/docker/php/demo-bootstrap" "$REPO_ROOT/docker/php/demo-reset" || { printf '%s\n' 'FAIL [TC0041]: startup failure fields/remediation missing' >&2; exit 1; }
[ -f "$REPO_ROOT/docker/nginx/default.conf" ] || { printf '%s\n' 'FAIL [TC0041]: Nginx config missing' >&2; exit 1; }
grep -Eiq 'log_format|access_log.*json|error_log' "$REPO_ROOT/docker/nginx/default.conf" || { printf '%s\n' 'FAIL [TC0041]: Nginx structured logs missing' >&2; exit 1; }
grep -Eiq 'invalid-provider-mode|generated-secrets-missing|provider-not-fake|nonlocal|db-readiness-timeout|readiness-check-failed' "$REPO_ROOT/docker/php/entrypoint.sh" "$REPO_ROOT/docker/php/demo-bootstrap" "$REPO_ROOT/docker/php/demo-reset" || { printf '%s\n' 'FAIL [TC0041]: bounded startup/provider/reset failure events missing' >&2; exit 1; }
grep -Eiq '"status":"(ready|complete|begin)"' "$REPO_ROOT/docker/php/entrypoint.sh" "$REPO_ROOT/docker/php/demo-bootstrap" "$REPO_ROOT/docker/php/demo-reset" "$REPO_ROOT/docker/mariadb/entrypoint.sh" || { printf '%s\n' 'FAIL [TC0041]: startup/init/completion events missing' >&2; exit 1; }

# TC0041b: every owned failure boundary must be fail-fast and carry the same
# safe error envelope.  These are deliberately static assertions as well as
# runtime checks below: a set -e exit is not an observable failure contract.
for owned in "$REPO_ROOT/docker/php/entrypoint.sh" "$REPO_ROOT/docker/php/demo-bootstrap" "$REPO_ROOT/docker/php/demo-reset" "$REPO_ROOT/docker/mariadb/entrypoint.sh"; do
    grep -Eq 'exception_class' "$owned" || { printf '%s\n' "FAIL [TC0041b]: exception_class missing in $owned" >&2; exit 1; }
    grep -Eq 'error_category' "$owned" || { printf '%s\n' "FAIL [TC0041b]: error_category missing in $owned" >&2; exit 1; }
    grep -Eq 'message_category' "$owned" || { printf '%s\n' "FAIL [TC0041b]: message_category missing in $owned" >&2; exit 1; }
    grep -Eq 'stack' "$owned" || { printf '%s\n' "FAIL [TC0041b]: stack missing in $owned" >&2; exit 1; }
    grep -Eq 'cause' "$owned" || { printf '%s\n' "FAIL [TC0041b]: cause missing in $owned" >&2; exit 1; }
    grep -Eq 'remediation' "$owned" || { printf '%s\n' "FAIL [TC0041b]: remediation missing in $owned" >&2; exit 1; }
done
grep -Eiq 'migration_failure|migrate-fresh-failed|migration.*failure' "$REPO_ROOT/docker/php/demo-reset" || { printf '%s\n' 'FAIL [TC0041b]: demo-reset migration failure boundary missing' >&2; exit 1; }
grep -Eiq 'seed_failure|seed-failed|seed.*failure' "$REPO_ROOT/docker/php/demo-reset" || { printf '%s\n' 'FAIL [TC0041b]: demo-reset seed failure boundary missing' >&2; exit 1; }

# TC0042: quality-gate is a bounded, sequential, fail-fast-per-case aggregate.
[ -f "$REPO_ROOT/tests/quality-gate.sh" ] || { printf '%s\n' 'FAIL [TC0042]: tests/quality-gate.sh missing' >&2; exit 1; }
grep -q '^#!/bin/sh' "$REPO_ROOT/tests/quality-gate.sh" || { printf '%s\n' 'FAIL [TC0042]: quality gate is not POSIX shell' >&2; exit 1; }
if grep -Eq '^[[:space:]]*[A-Za-z_][A-Za-z0-9_]*[[:space:]]*\(\)[[:space:]]*\{' "$REPO_ROOT/tests/quality-gate.sh"; then printf '%s\n' 'FAIL [TC0042]: quality gate defines a function' >&2; exit 1; fi
for required in compose_contract_test ticket03_bootstrap_test ticket04_provider_contract_test ticket05_authorization_contract_test ticket06_placement_evidence_test ticket07_reporting_pdf_test ticket08_lifecycle_test ticket09_logging_contract_test; do
    grep -q "$required" "$REPO_ROOT/tests/quality-gate.sh" || { printf '%s\n' "FAIL [TC0042]: quality gate omits $required" >&2; exit 1; }
done
grep -Eiq 'timeout[[:space:]]+[0-9]+|600' "$REPO_ROOT/tests/quality-gate.sh" || { printf '%s\n' 'FAIL [TC0042]: quality gate lacks per-case timeout/600s ceiling' >&2; exit 1; }
grep -Eiq 'fail-fast|continue|status|TMPDIR|/tmp' "$REPO_ROOT/tests/quality-gate.sh" || { printf '%s\n' 'FAIL [TC0042]: quality gate lacks aggregate statuses/temp log semantics' >&2; exit 1; }

# Runtime is deliberately unreachable until every static gate passes.
command -v docker >/dev/null 2>&1 || { printf '%s\n' 'FAIL [TC0042]: Docker unavailable' >&2; exit 1; }
TIMEOUT_CMD=timeout
command -v timeout >/dev/null 2>&1 || TIMEOUT_CMD=gtimeout
command -v "$TIMEOUT_CMD" >/dev/null 2>&1 || { printf '%s\n' 'FAIL [TC0042]: timeout/gtimeout unavailable' >&2; exit 1; }
command -v curl >/dev/null 2>&1 || { printf '%s\n' 'FAIL [TC0042]: curl unavailable' >&2; exit 1; }
[ -f "$COMPOSE_FILE" ] || { printf '%s\n' 'FAIL [TC0042]: compose.yaml missing' >&2; exit 1; }
if command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 "$APP_PORT" >/dev/null 2>&1; then printf '%s\n' 'FAIL [TC0042]: application port is in use' >&2; exit 1; fi
export COMPOSE_PROJECT_NAME=$PROJECT_NAME APP_PORT AGENT_SESSION_ID PROVIDER_MODE=fake
"$TIMEOUT_CMD" 120 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" up -d --build >"$TMP_ROOT/up.log" 2>&1 || { printf '%s\n' 'FAIL [TC0042]: startup failed' >&2; exit 1; }
"$TIMEOUT_CMD" 90 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" ps --status running >"$TMP_ROOT/ps.log" 2>&1 || { printf '%s\n' 'FAIL [TC0042]: readiness failed' >&2; exit 1; }

# Runtime fail-fast probes use only this test's disposable Compose project.
# They intentionally corrupt no host or shared database: the project is
# removed by the trap above, and all inputs are synthetic/invalid.
for mariadb_secret_case in missing invalid; do
    set +e
    "$TIMEOUT_CMD" 10 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T -e TICKET09_SECRET_CASE="$mariadb_secret_case" mariadb sh -c 'backup=$(mktemp); cp /run/ojt-app-secrets/db.app.password "$backup"; trap '\''cp "$backup" /run/ojt-app-secrets/db.app.password; rm -f "$backup"'\'' EXIT; if [ "$TICKET09_SECRET_CASE" = missing ]; then rm -f /run/ojt-app-secrets/db.app.password; else printf %s ticket09-invalid-secret > /run/ojt-app-secrets/db.app.password; fi; /usr/local/bin/ojt-mariadb-entrypoint --help' >"$TMP_ROOT/mariadb-$mariadb_secret_case.log" 2>&1
    MARIADB_RC=$?
    set -e
    [ "$MARIADB_RC" -ne 0 ] || { printf '%s\n' "FAIL [TC0041b]: $mariadb_secret_case MariaDB secret unexpectedly succeeded" >&2; exit 1; }
python3 - "$TMP_ROOT/mariadb-$mariadb_secret_case.log" 1 <<'PY'
import json, re, sys
path, minimum_errors = sys.argv[1], int(sys.argv[2])
lines = [line for line in open(path, encoding='utf-8', errors='replace').read().splitlines() if line.strip()]
errors = []
for line in lines:
    try: item = json.loads(line)
    except Exception as exc: raise SystemExit('MariaDB failure emitted non-JSON output: %s' % exc)
    if item.get('level') == 'error': errors.append(item)
if len(errors) != minimum_errors: raise SystemExit('MariaDB invalid-secret must emit exactly one JSON ERROR')
for item in errors:
    for key in ('exception_class','error_category','message_category','stack','cause','remediation'):
        if key not in item or item[key] in (None, ''): raise SystemExit('MariaDB error lacks '+key)
    if re.search(r'ticket09-invalid', json.dumps(item, sort_keys=True), re.I):
        raise SystemExit('MariaDB error leaked secret material')
PY
done

set +e
"$TIMEOUT_CMD" 10 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm sh -c 'rm -f /tmp/ojt-demo-bootstrap-complete; DB_HOST=ticket09-invalid-db DB_READINESS_TIMEOUT_SECONDS=2 demo-bootstrap' >"$TMP_ROOT/bootstrap-timeout.log" 2>&1
BOOTSTRAP_RC=$?
set -e
[ "$BOOTSTRAP_RC" -ne 0 ] || { printf '%s\n' 'FAIL [TC0041b]: demo-bootstrap invalid DB unexpectedly succeeded' >&2; exit 1; }
python3 - "$TMP_ROOT/bootstrap-timeout.log" <<'PY'
import json, re, sys
lines = [line for line in open(sys.argv[1], encoding='utf-8', errors='replace').read().splitlines() if line.strip()]
errors = []
for line in lines:
    try: item = json.loads(line)
    except Exception as exc: raise SystemExit('demo-bootstrap failure emitted non-JSON output: %s' % exc)
    if item.get('level') == 'error': errors.append(item)
if not errors: raise SystemExit('demo-bootstrap timeout emitted no JSON ERROR')
for item in errors:
    for key in ('exception_class','error_category','message_category','stack','cause','remediation'):
        if key not in item or item[key] in (None, ''): raise SystemExit('demo-bootstrap error lacks '+key)
    if re.search(r'ticket09-invalid', json.dumps(item, sort_keys=True), re.I): raise SystemExit('demo-bootstrap error leaked secret material')
PY

for reset_case in migration seed; do
    set +e
    if [ "$reset_case" = migration ]; then
        "$TIMEOUT_CMD" 10 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm sh -c 'DB_HOST=ticket09-invalid-db demo-reset' >"$TMP_ROOT/reset-migration.log" 2>&1
    else
        "$TIMEOUT_CMD" 10 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm sh -c 'd=/tmp/ticket09-bin; mkdir -p "$d"; cat > "$d/php" <<"SH"
#!/bin/sh
case "$*" in
  *migrate:fresh*) exit 0 ;;
  *scripts/demo-seed.php*) exit 42 ;;
  *) exec /usr/bin/php "$@" ;;
esac
SH
chmod 700 "$d/php"; PATH="$d:$PATH" demo-reset' >"$TMP_ROOT/reset-seed.log" 2>&1
    fi
    RESET_RC=$?
    set -e
    [ "$RESET_RC" -ne 0 ] || { printf '%s\n' "FAIL [TC0041b]: demo-reset $reset_case failure unexpectedly succeeded" >&2; exit 1; }
    python3 - "$TMP_ROOT/reset-$reset_case.log" "$reset_case" <<'PY'
import json, re, sys
path, case = sys.argv[1:]
lines = [line for line in open(path, encoding='utf-8', errors='replace').read().splitlines() if line.strip()]
errors = []
for line in lines:
    try: item = json.loads(line)
    except Exception as exc: raise SystemExit('demo-reset %s emitted non-JSON output: %s' % (case, exc))
    if item.get('level') == 'error': errors.append(item)
if not errors: raise SystemExit('demo-reset %s emitted no JSON ERROR' % case)
for item in errors:
    for key in ('exception_class','error_category','message_category','stack','cause','remediation'):
        if key not in item or item[key] in (None, ''): raise SystemExit('demo-reset %s error lacks %s' % (case, key))
    if re.search(r'ticket09-invalid', json.dumps(item, sort_keys=True), re.I): raise SystemExit('demo-reset error leaked secret material')
PY
done

CID=test-10042026-Maurice
"$TIMEOUT_CMD" 10 curl -fsS -H "X-Correlation-ID: $CID" -o "$TMP_ROOT/health.body" "http://127.0.0.1:$APP_PORT/healthz" >/dev/null || { printf '%s\n' 'FAIL [TC0042]: health request failed' >&2; exit 1; }
grep -q '"status":"ok"' "$TMP_ROOT/health.body" || { printf '%s\n' 'FAIL [TC0042]: health response is not JSON' >&2; exit 1; }
"$TIMEOUT_CMD" 10 curl -sS -D "$TMP_ROOT/generated.headers" -o "$TMP_ROOT/generated.body" -H 'Accept: application/json' "http://127.0.0.1:$APP_PORT/api/academic/profile" --data 'sr_code=invalid&password=invalid' >/dev/null || :
"$TIMEOUT_CMD" 10 curl -sS -D "$TMP_ROOT/credentials.headers" -o "$TMP_ROOT/credentials.body" -H 'Accept: application/json' -H "X-Correlation-ID: $CID" --data 'sr_code=invalid&password=invalid' "http://127.0.0.1:$APP_PORT/api/academic/profile" >/dev/null || :
grep -Eq 'HTTP/[0-9.]+ (401|422)' "$TMP_ROOT/credentials.headers" || { printf '%s\n' 'FAIL [TC0042]: invalid credentials status missing' >&2; exit 1; }
"$TIMEOUT_CMD" 10 curl -sS -D "$TMP_ROOT/unauth.headers" -o "$TMP_ROOT/unauth.body" -H 'Accept: application/json' -H "X-Correlation-ID: $CID" "http://127.0.0.1:$APP_PORT/api/internships" >/dev/null || :
grep -Eq 'HTTP/[0-9.]+ (401|403|422|503)' "$TMP_ROOT/unauth.headers" || { printf '%s\n' 'FAIL [TC0042]: mapped response status missing' >&2; exit 1; }
"$TIMEOUT_CMD" 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" logs --no-color --timestamps php-fpm nginx mariadb >"$TMP_ROOT/owned.log" 2>&1 || { printf '%s\n' 'FAIL [TC0042]: owned logs unavailable' >&2; exit 1; }
"$TIMEOUT_CMD" 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp "php-fpm:/home/app/.cache/agent-trace/ojt-tracker-management-system/$AGENT_SESSION_ID.jsonl" "$TMP_ROOT/trace.jsonl" >"$TMP_ROOT/cp.log" 2>&1 || { printf '%s\n' 'FAIL [TC0042]: trace JSONL unavailable' >&2; exit 1; }
grep -Eiq 'academic_credentials_invalid|invalid.*credential|"level":"(warning|info)"' "$TMP_ROOT/owned.log" || { printf '%s\n' 'FAIL [TC0042]: invalid credentials are not warning/info' >&2; exit 1; }
python3 - "$TMP_ROOT/generated.headers" "$TMP_ROOT/generated.body" "$TMP_ROOT/owned.log" "$TMP_ROOT/trace.jsonl" <<'PY'
import json, re, sys
headers, body, owned, trace = sys.argv[1:]
ids = re.findall(r'(?im)^x-correlation-id:\s*([^\r\n ]+)', open(headers, encoding='utf-8', errors='replace').read())
if len(ids) != 1: raise SystemExit('generated response must contain exactly one correlation id')
try: value = json.loads(open(body, encoding='utf-8', errors='replace').read()).get('correlation_id')
except Exception: value = None
if value and value != ids[0]: raise SystemExit('response correlation id mismatch')
for path in (owned, trace):
    matched = 0
    for line in open(path, encoding='utf-8', errors='replace').read().splitlines():
        marker = line.find('{')
        if marker < 0: raise SystemExit('non-JSON owned line')
        try: item = json.loads(line[marker:])
        except Exception as exc: raise SystemExit('non-JSON owned line: %s' % exc)
        if item.get('correlation_id'):
            if not re.match(r'^[A-Za-z0-9._-]{1,64}$', str(item['correlation_id'])): raise SystemExit('unsafe correlation id')
            if item['correlation_id'] == ids[0]: matched += 1
    if not matched: raise SystemExit('generated correlation id missing from owned artifact')
PY
python3 - "$TMP_ROOT/owned.log" "$TMP_ROOT/trace.jsonl" <<'PY'
import json, sys
for path in sys.argv[1:]:
    lines=open(path, encoding='utf-8', errors='replace').read().splitlines()
    if not lines: raise SystemExit('empty owned artifact')
    parsed = 0
    for line in lines:
        if not line.strip(): raise SystemExit('blank owned artifact line')
        marker=line.find('{')
        if marker < 0: raise SystemExit('non-JSON line')
        try: item=json.loads(line[marker:])
        except Exception as exc: raise SystemExit(f'non-JSON line: {exc}')
        parsed += 1
        for key in ('timestamp','level','event','status'):
            if key not in item: raise SystemExit(f'missing {key}')
        if not (item.get('operation') or item.get('component')): raise SystemExit('missing operation/component')
        if 'request' in str(item.get('event','')).lower() and not item.get('correlation_id'): raise SystemExit('request lacks correlation')
        if item.get('level') == 'error':
            for key in ('error_class','error_category','stack','cause'):
                if key not in item: raise SystemExit('error lacks '+key)
    if not parsed: raise SystemExit('no structured owned lines')
PY
grep -q "$CID" "$TMP_ROOT/unauth.body" "$TMP_ROOT/owned.log" "$TMP_ROOT/trace.jsonl" || { printf '%s\n' 'FAIL [TC0042]: correlation was not preserved' >&2; exit 1; }
for artifact in "$TMP_ROOT/owned.log" "$TMP_ROOT/trace.jsonl" "$TMP_ROOT/mariadb-missing.log" "$TMP_ROOT/mariadb-invalid.log" "$TMP_ROOT/bootstrap-timeout.log" "$TMP_ROOT/reset-migration.log" "$TMP_ROOT/reset-seed.log"; do
    if grep -Eiq 'password|token|api[_-]?key|raw[_ -]?provider|@example\.|phone|address|DemoOnly-|BEGIN .*PRIVATE KEY' "$artifact"; then printf '%s\n' "FAIL [TC0042]: secret/PII/provider leak in $artifact" >&2; exit 1; fi
done

printf '%s\n' '{"timestamp":"'"$(date -u +%Y-%m-%dT%H:%M:%SZ)"'","level":"info","event":"completion","operation":"ticket09","component":"contract","status":"pass"}' >&2
printf '%s\n' 'PASS: Ticket 09 logging and trace contract'
