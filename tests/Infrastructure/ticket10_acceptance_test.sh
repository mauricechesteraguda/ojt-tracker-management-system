#!/bin/sh
# test-10042026-Maurice: Ticket10 green acceptance and evidence contract.
# Deliberately top-level POSIX shell: no shell functions and no repository output.
set -eu
umask 077

REPO_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)
PROJECT_NAME=${OJT_TEST_COMPOSE_PROJECT:-ojt-ticket10-$$}
APP_PORT=${OJT_TEST_APP_PORT:-$((18400 + ($$ % 500)))}
SESSION_ID=${OJT_TICKET10_SESSION_ID:-ticket10-$$-$(date +%s)}
TMP_ROOT=${TMPDIR:-/tmp}/ojt-ticket10-$$
EVIDENCE_DIR=$TMP_ROOT/evidence
COMPOSE_FILE=$REPO_ROOT/compose.yaml
TIMEOUT_CMD=timeout
command -v timeout >/dev/null 2>&1 || TIMEOUT_CMD=gtimeout
command -v "$TIMEOUT_CMD" >/dev/null 2>&1 || { printf '%s\n' 'ticket10: timeout/gtimeout is required' >&2; exit 1; }
mkdir -p "$EVIDENCE_DIR"
COMPOSE_STARTED=0
trap 'if [ "$COMPOSE_STARTED" = 1 ]; then docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" down --volumes --remove-orphans >/dev/null 2>&1 || :; fi; rm -rf -- "$TMP_ROOT"' EXIT HUP INT TERM

if git -C "$REPO_ROOT" ls-files --error-unmatch ojt.sql >/dev/null 2>&1; then printf '%s\n' 'ticket10: tracked ojt.sql remains' >&2; exit 1; fi
if git -C "$REPO_ROOT" ls-files | grep -E '(^|/)public/uploads(/|$)' >/dev/null 2>&1; then printf '%s\n' 'ticket10: tracked public/uploads content remains' >&2; exit 1; fi
if git -C "$REPO_ROOT" grep -n -I -E '(ACADEMIC_PROVIDER_KEY|api[_-]?key)[[:space:]]*[:=][[:space:]]*["'"'][^"'"']+["'"']' -- ':!tests/**' ':!docs/**' >/dev/null 2>&1; then printf '%s\n' 'ticket10: hardcoded provider credential remains' >&2; exit 1; fi
grep -q '^/ojt.sql$' "$REPO_ROOT/.gitignore" || { printf '%s\n' 'ticket10: missing SQL ignore rule' >&2; exit 1; }
grep -q '^/public/uploads/$' "$REPO_ROOT/.gitignore" || { printf '%s\n' 'ticket10: missing upload ignore rule' >&2; exit 1; }

[ -f "$REPO_ROOT/docs/happy-flow.md" ] || { printf '%s\n' 'ticket10: candidate happy-flow document missing' >&2; exit 1; }
grep -q 'Candidate.*not sealed' "$REPO_ROOT/docs/happy-flow.md" || { printf '%s\n' 'ticket10: happy-flow is not explicitly unsealed' >&2; exit 1; }
grep -q 'manifest.jsonl\|contains_secrets' "$REPO_ROOT/docs/happy-flow.md" || { printf '%s\n' 'ticket10: evidence schema missing' >&2; exit 1; }
[ -f "$REPO_ROOT/docs/runbooks/local-demo.md" ] && [ -f "$REPO_ROOT/docs/runbooks/security-containment.md" ] || { printf '%s\n' 'ticket10: required runbooks missing' >&2; exit 1; }
grep -q 'docker compose up --build -d' "$REPO_ROOT/README.md" || { printf '%s\n' 'ticket10: README Compose command missing' >&2; exit 1; }
grep -q 'demo-reset' "$REPO_ROOT/README.md" || { printf '%s\n' 'ticket10: README reset command missing' >&2; exit 1; }
grep -q 'checked-in.*Playwright\|Playwright.*checked-in' "$REPO_ROOT/README.md" || { printf '%s\n' 'ticket10: README does not state checked-in Playwright truth' >&2; exit 1; }
if grep -q -E 'not a checked-in test stack|only to capture the README screenshots|not a checked-in Playwright' "$REPO_ROOT/README.md"; then
    printf '%s\n' 'ticket10: README contains stale Playwright denial' >&2
    exit 1
fi
python3 - "$REPO_ROOT" <<'PY'
import os, re, sys
root = sys.argv[1]
readme = open(os.path.join(root, 'README.md'), encoding='utf-8').read()
local_demo = open(os.path.join(root, 'docs/runbooks/local-demo.md'), encoding='utf-8').read()
requirements = open(os.path.join(root, 'docs/test-cases/ojt-lifecycle-stabilization-requirements.md'), encoding='utf-8').read()
timing = readme[readme.lower().find('last independently verified'):]
if not re.search(r'Last independently verified on 20\d\d-\d\d-\d\d', timing, re.I):
    raise SystemExit('ticket10: README timing claim is not date-stamped')
for marker in ('two successful runs', 'each under 600', 'ephemeral', 'clean(?:ed|up)', 'manifest', 'candidate.*not sealed|does not seal|unsealed'):
    if not re.search(marker, timing, re.I):
        raise SystemExit('ticket10: README timing claim missing explicit ' + marker)
if re.search(r'\bin\s+\d+(?:\.\d+)?\s*(?:s|sec|seconds)\b|\b\d+(?:\.\d+)?\s*seconds?\s*(?:each|and)', timing, re.I):
    raise SystemExit('ticket10: README timing claim includes exact run durations')
if re.search(r'permanent|retained|persistent evidence|evidence retained', timing, re.I):
    raise SystemExit('ticket10: README timing claim implies retained evidence')
if re.search(r'`hash`|"hash"', requirements, re.I) or not re.search(r'`sha256`|"sha256"', requirements, re.I):
    raise SystemExit('ticket10: requirements docs use obsolete hash schema')
if re.search(r'\bRED\b|planned|when its checked-in.*available', local_demo, re.I):
    raise SystemExit('ticket10: local-demo contains RED/planned language')
if not re.search(r'checked-in.*acceptance|acceptance.*checked-in', local_demo, re.I):
    raise SystemExit('ticket10: local-demo does not describe checked-in acceptance')
PY
[ "$(grep -ci 'Candidate.*not sealed' "$REPO_ROOT/docs/happy-flow.md")" -gt 0 ] || { printf '%s\n' 'ticket10: candidate is not explicitly unsealed' >&2; exit 1; }
if grep -q '"approval"[[:space:]]*:' "$REPO_ROOT/docs/happy-flow.md"; then
    printf '%s\n' 'ticket10: candidate contains an approval record' >&2
    exit 1
fi

python3 - "$REPO_ROOT" <<'PY'
import os, re, subprocess, sys
root = sys.argv[1]
source = open(os.path.join(root, 'tests/E2E/ticket10_happy_flow.spec.js'), encoding='utf-8').read()
ticket07 = open(os.path.join(root, 'tests/Infrastructure/ticket07_reporting_pdf_test.sh'), encoding='utf-8').read()
contracts = {
    'same-filter JSON/PDF parity by stable record IDs and order': [r'/api/internships/report', r'/api/internships/report/pdf', r'jsonIds', r'pdfIds', r'toEqual\(jsonIds\)', r'order|sort'],
    'approved-only and no-results filtering': [r'approved', r'no.?results|No matching records|length\).*toBe\(0\)', r'filter'],
    'existing cross-owner resource exact 403': [r'crossOwner', r'/api/(internships|descriptions|reports)/(?:[0-9]+|\$\{)', r'toBe\(403\)'],
    'student reopen exact 403': [r'studentReopen', r'/reopen', r'toBe\(403\)'],
    'immutable append-only lifecycle evidence': [r'lifecycle-events', r'events', r'immut|append', r'toEqual'],
    'idempotent repeated approval without duplicate event': [r'approve', r'approval.*approval|approve.*approve', r'event', r'length'],
    'approved description mutation exact 403': [r'approvedDescriptionMutation', r'/api/descriptions/', r'expectExactStatus\(approvedDescriptionMutation, 403\)'],
    'reopen reason audit followed by reapproval event': [r'reopen', r'reason', r'lifecycle-events', r'reapprov|approve.*event'],
    'TC-OJT-0043 exact read-only mutation rejection': [r'TC-OJT-0043', r'lifecyclePatch', r'expectMethodNotAllowed', r'put|patch|delete', r'405', r'correlation_id'],
}
for name, patterns in contracts.items():
    if not all(re.search(pattern, source, re.I | re.S) for pattern in patterns):
        raise SystemExit('ticket10: checked-in E2E missing exact contract: ' + name)
if re.search(r'crossOwner.*(?:999999|missing|unknown)|studentReopen.*\[[^]]*401', source, re.I | re.S):
    raise SystemExit('ticket10: E2E weakens existing-resource/student-reopen denial')
if re.search(r'expect\(\[[^]]*403[^]]*\]\)\.toContain', source):
    raise SystemExit('ticket10: E2E uses non-exact 403 assertion')
if not re.search(r'ticket.?07|Ticket.?07', source, re.I) or not all(re.search(pattern, ticket07, re.I) for pattern in (r'default', r'per_page[^\n]*100', r'page[^\n]*1', r'per_page[^\n]*1', r'invalid.*pagination|pagination.*invalid|page[^\n]*0|per_page[^\n]*101')):
    raise SystemExit('ticket10: Ticket07 pagination coverage is not explicitly linked')
if not re.search(r'(requested page|page parity|same page|page.*parity)', source, re.I):
    raise SystemExit('ticket10: Ticket10 does not assert requested-page parity')
repeat = source[source.find('repeatApproval'):source.find('repeatReopen')]
if not re.search(r'expectExactStatus\(repeatApproval, 200\)', repeat) or re.search(r'repeatApproval.*toContain|toContain.*repeatApproval', repeat, re.S):
    raise SystemExit('ticket10: repeated approval is not exact 200')
if not re.search(r'(afterRepeatEvents|repeatApprovalEvents).*length.*firstApprovalEvents|firstApprovalEvents.*length.*afterRepeatEvents', repeat, re.S) or not re.search(r'(afterRepeatState|repeatApprovalState|is_approved|status)', repeat, re.I):
    raise SystemExit('ticket10: repeated approval does not prove unchanged event count/state')
ordered_fields = ('id', 'first_name', 'last_name', 'sr_code', 'company', 'start_date', 'end_date', 'approval', 'status', 'campus', 'schoolyear', 'semester', 'college', 'course')
if not (re.search(r'first_name\s*,\s*last_name\s*,\s*id', source) or re.search(r'function\s+compareReportRecords[\s\S]*first_name[\s\S]*last_name[\s\S]*Number\([^)]*id', source)) or not all(re.search(r'\b' + field + r'\b', source) for field in ordered_fields):
    raise SystemExit('ticket10: E2E missing ordered 14-field JSON/PDF parity contract')
if not re.search(r'(jsonValues|jsonFields).*toEqual\((?:pdfValues|pdfFields)\)|(pdfValues|pdfFields).*toEqual\((?:jsonValues|jsonFields)\)', source, re.S):
    raise SystemExit('ticket10: PDF parity does not compare extracted values')
if not re.search(r'No matching records.*toBe\(["\']No matching records["\']\)', source, re.S):
    raise SystemExit('ticket10: empty PDF lacks exact No matching records assertion')
for field in ('internship_id', 'actor_id', 'event_type', 'from_is_approved', 'to_is_approved', 'reason', 'timestamp'):
    if not re.search(r'\b' + field + r'\b', source):
        raise SystemExit('ticket10: lifecycle event field assertion missing: ' + field)
if re.search(r'\b(created_at|updated_at)\b', source):
    raise SystemExit('ticket10: lifecycle API contract uses created_at/updated_at aliases')
if not re.search(r'(EVENT_KEYS|eventKeys).*internship_id.*actor_id.*event_type.*from_is_approved.*to_is_approved.*reason.*timestamp', source, re.I | re.S) or not re.search(r'Object\.keys.*(?:toEqual|equal).*EVENT_KEYS|EVENT_KEYS.*Object\.keys', source, re.I | re.S):
    raise SystemExit('ticket10: lifecycle rows do not assert the exact seven API keys')
if not re.search(r'(Number|typeof).*internship_id|actor_id.*(?:Number|number)', source, re.I | re.S) or not re.search(r'(approved|reopened).*allowed|toContain.*approved.*reopened', source, re.I | re.S):
    raise SystemExit('ticket10: lifecycle rows lack numeric IDs/allowed event type assertions')
if not re.search(r'(typeof|Boolean|boolean).*from_is_approved|from_is_approved.*(?:true|false)', source, re.I | re.S) or not re.search(r'ISO|toISOString|timestamp.*Date', source, re.I | re.S):
    raise SystemExit('ticket10: lifecycle rows lack boolean/ISO timestamp assertions')
if not re.search(r'from_is_approved.*false.*to_is_approved.*true.*reason.*null', source, re.I | re.S) or not re.search(r'from_is_approved.*true.*to_is_approved.*false.*reason.*(?:trim|Synthetic correction required)', source, re.I | re.S):
    raise SystemExit('ticket10: lifecycle approval/reopen transition assertions are incomplete')
if not re.search(r'(created_at|timestamp).*order|order.*(created_at|timestamp)', source, re.I | re.S) or not re.search(r'(immutable|append.?only)', source, re.I):
    raise SystemExit('ticket10: lifecycle events lack immutable chronological-order assertion')
collection_route = r'/api/internships/\$\{internshipId\}/lifecycle-events'
if re.search(r'/unsupported', source):
    raise SystemExit('ticket10: lifecycle route probe uses fake unsupported path')
if not re.search(collection_route + r'.*\.put|\.put.*' + collection_route, source, re.S) or not re.search(collection_route + r'.*\.delete|\.delete.*' + collection_route, source, re.S):
    raise SystemExit('ticket10: lifecycle update/delete probes do not use exact collection route')
if not re.search(r'lifecycleUpdate.*expectMethodNotAllowed', source, re.S) or not re.search(r'lifecyclePatch.*expectMethodNotAllowed', source, re.S) or not re.search(r'lifecycleDelete.*expectMethodNotAllowed', source, re.S) or not re.search(r'(afterLifecycleRoute|afterUnsupported|unchanged).*toEqual.*(?:beforeLifecycle|immutableEvents|firstApprovalEvents)', source, re.I | re.S):
    raise SystemExit('ticket10: TC-OJT-0043 exact PUT/PATCH/DELETE 405 routes are not denied unchanged')
if source.count('ticket10-method-rejection') < 1 or not re.search(r'rejectionHeaders\s*=\s*\{[^}]*X-Correlation-ID[^}]*LIFECYCLE_METHOD_REJECTION_CORRELATION_ID', source, re.S) or not re.search(r'correlation_id\)\.toBe\(correlationId\)', source) or not re.search(r'x-correlation-id.*toBe\(correlationId\)', source, re.I):
    raise SystemExit('ticket10: TC-OJT-0043 does not require fixed correlation ID in body and header')
ticket08 = open(os.path.join(root, 'tests/Infrastructure/ticket08_lifecycle_test.sh'), encoding='utf-8').read()
if not re.search(r'DB::table.*internship_lifecycle_events.*(?:update|delete)', ticket08, re.S):
    raise SystemExit('ticket10: Ticket08 DB mutation proof is missing')
if subprocess.run(['git', '-C', root, 'grep', '-n', '-I', '-E', r'batsu_api', '--', '.', ':!tests/Infrastructure/ticket10_acceptance_test.sh'], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL).returncode == 0:
    raise SystemExit('ticket10: obsolete batsu_api import/reference remains')
PY

git -C "$REPO_ROOT" status --porcelain | awk '{print $2}' | while IFS= read -r DIRTY; do
    case "$DIRTY" in
        .gitignore|README.md|app/Classes/api.php|app/Exceptions/Handler.php|app/Http/Controllers/UserController.php|app/Support/ApiErrorNormalizer.php|composer.json|ojt.sql|public/uploads/*|docs/changelogs/CHANGELOG-2026-10-02-Maurice.md|docs/test-cases/*|docs/happy-flow.md|docs/runbooks/*|package.json|package-lock.json|tests/quality-gate.sh|tests/E2E/*|tests/Infrastructure/ticket08_lifecycle_test.sh|tests/Infrastructure/ticket10_acceptance_test.sh) ;;
        *) printf '%s\n' "ticket10: unexpected dirty path: $DIRTY" >&2; exit 1 ;;
    esac
done

python3 - "$REPO_ROOT" "$REPO_ROOT/docs/test-cases/ojt-lifecycle-stabilization.csv" <<'PY'
import csv, os, sys
root, csv_path = sys.argv[1:]
with open(csv_path, newline='') as stream:
    rows = list(csv.DictReader(stream))
if len(rows) != 43:
    raise SystemExit('ticket10: expected 43 CSV cases')
for index, row in enumerate(rows):
    expected = 'TC-OJT-%04d' % (index + 1)
    if row.get('Test Case ID') != expected:
        raise SystemExit('ticket10: non-sequential case %s' % row.get('Test Case ID'))
    for reference in (row.get('Automated Test Ref.') or '').split(';'):
        path_name = reference.strip().split(':', 1)[0].strip()
        if path_name and not os.path.exists(os.path.join(root, path_name)):
            raise SystemExit('ticket10: invalid automated ref %s' % path_name)
PY
[ -f "$REPO_ROOT/tests/E2E/ticket10_happy_flow.spec.js" ] && [ -f "$REPO_ROOT/tests/E2E/playwright.config.cjs" ] && [ -x "$REPO_ROOT/tests/E2E/run_ticket10.sh" ] || { printf '%s\n' 'ticket10: checked-in Playwright test/config/runner missing' >&2; exit 1; }
grep -q 'ticket10' "$REPO_ROOT/tests/quality-gate.sh" || { printf '%s\n' 'ticket10: quality gate omits Ticket10' >&2; exit 1; }
grep -q 'OJT_QUALITY_GATE_FORCE_FAIL' "$REPO_ROOT/tests/quality-gate.sh" || { printf '%s\n' 'ticket10: quality gate force-failure interface missing' >&2; exit 1; }

export COMPOSE_PROJECT_NAME="$PROJECT_NAME" APP_PORT ACADEMIC_PROVIDER_MODE=fake PROVIDER_MODE=fake OJT_TICKET10_SESSION_ID="$SESSION_ID" OJT_EVIDENCE_DIR="$EVIDENCE_DIR" OJT_BASE_URL="http://127.0.0.1:$APP_PORT" OJT_TICKET10_REVISION="$(git -C "$REPO_ROOT" rev-parse HEAD 2>/dev/null || printf '%s' unknown)" OJT_TICKET10_COMMAND="sh tests/Infrastructure/ticket10_acceptance_test.sh"
START=$(date +%s)
"$TIMEOUT_CMD" 180 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" up --build -d --wait >"$TMP_ROOT/compose-up.log" 2>&1 || { cat "$TMP_ROOT/compose-up.log" >&2; exit 1; }
COMPOSE_STARTED=1
"$TIMEOUT_CMD" 60 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-reset >"$TMP_ROOT/reset-1.log" 2>&1 || { cat "$TMP_ROOT/reset-1.log" >&2; exit 1; }
"$TIMEOUT_CMD" 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-inspect >"$TMP_ROOT/inspect-1.log" 2>&1 || { cat "$TMP_ROOT/inspect-1.log" >&2; exit 1; }
grep -q 'natural_keys=unique' "$TMP_ROOT/inspect-1.log" || { printf '%s\n' 'ticket10: reset fixture graph invalid' >&2; exit 1; }
grep 'users=.*natural_keys=unique' "$TMP_ROOT/inspect-1.log" >"$TMP_ROOT/fingerprint-1.txt"
"$TIMEOUT_CMD" 45 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-bootstrap >"$TMP_ROOT/restart.log" 2>&1 || { cat "$TMP_ROOT/restart.log" >&2; exit 1; }
"$TIMEOUT_CMD" 45 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-inspect >"$TMP_ROOT/inspect-persisted.log" 2>&1 || { cat "$TMP_ROOT/inspect-persisted.log" >&2; exit 1; }
grep -q 'natural_keys=unique' "$TMP_ROOT/inspect-persisted.log" || { printf '%s\n' 'ticket10: persistence after restart failed' >&2; exit 1; }
"$TIMEOUT_CMD" 60 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-reset >"$TMP_ROOT/reset-2.log" 2>&1 || { cat "$TMP_ROOT/reset-2.log" >&2; exit 1; }
"$TIMEOUT_CMD" 30 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm demo-inspect >"$TMP_ROOT/inspect-reset.log" 2>&1 || { cat "$TMP_ROOT/inspect-reset.log" >&2; exit 1; }
grep -q 'natural_keys=unique' "$TMP_ROOT/inspect-reset.log" || { printf '%s\n' 'ticket10: deterministic reset failed' >&2; exit 1; }
grep 'users=.*natural_keys=unique' "$TMP_ROOT/inspect-reset.log" >"$TMP_ROOT/fingerprint-reset.txt"
cmp -s "$TMP_ROOT/fingerprint-1.txt" "$TMP_ROOT/fingerprint-reset.txt" || { printf '%s\n' 'ticket10: reset fingerprint is not deterministic' >&2; exit 1; }
"$TIMEOUT_CMD" 240 sh "$REPO_ROOT/tests/E2E/run_ticket10.sh" >"$TMP_ROOT/browser.log" 2>&1 || { cat "$TMP_ROOT/browser.log" >&2; exit 1; }
ELAPSED=$(( $(date +%s) - START ))
[ "$ELAPSED" -le 600 ] || { printf '%s\n' "ticket10: elapsed ${ELAPSED}s exceeds 600s" >&2; exit 1; }

REPO_HASH=$(printf '%s' "$REPO_ROOT" | shasum -a 256 | cut -c1-16)
python3 - "$REPO_ROOT" "$EVIDENCE_DIR" "$HOME/.cache/agent-trace/$REPO_HASH/$SESSION_ID.jsonl" <<'PY'
import hashlib, json, os, re, sys
root, evidence, trace_path = sys.argv[1:]
manifest = os.path.join(evidence, 'manifest.jsonl')
if not os.path.isfile(manifest): raise SystemExit('ticket10: evidence manifest missing')
secret = re.compile(rb'DemoOnly-(?:Student|Coord|Admin)-001!|Bearer\s+[A-Za-z0-9._-]+|"(?:password|api_key|token)"\s*:', re.I)
records = []
manifest_bytes = open(manifest, 'rb').read()
if secret.search(manifest_bytes): raise SystemExit('ticket10: manifest secret scan failed')
with open(manifest, 'rb') as stream:
    for line_number, line in enumerate(stream, 1):
        if not line.strip(): raise SystemExit('ticket10: blank manifest row')
        record = json.loads(line)
        required = ('case', 'artifact', 'sha256', 'source', 'contains_secrets', 'revision', 'command', 'timestamp')
        if set(required) - set(record): raise SystemExit('ticket10: incomplete manifest row %d' % line_number)
        if not isinstance(record['case'], str) or not record['case']: raise SystemExit('ticket10: invalid manifest case')
        if not isinstance(record['sha256'], str) or not re.fullmatch(r'[0-9a-f]{64}', record['sha256']): raise SystemExit('ticket10: invalid lowercase sha256')
        if not isinstance(record['source'], str) or not record['source']: raise SystemExit('ticket10: invalid manifest source')
        if record['contains_secrets'] is not False: raise SystemExit('ticket10: secret-bearing manifest record')
        if not isinstance(record['revision'], str) or not re.fullmatch(r'[0-9a-f]{7,64}', record['revision']): raise SystemExit('ticket10: invalid manifest revision')
        if not isinstance(record['command'], str) or not record['command']: raise SystemExit('ticket10: invalid manifest command')
        if not isinstance(record['timestamp'], str) or not re.fullmatch(r'\d{4}-\d\d-\d\dT.*Z', record['timestamp']): raise SystemExit('ticket10: invalid manifest timestamp')
        artifact = record['artifact']
        if os.path.realpath(artifact) == os.path.realpath(manifest) or not artifact or os.path.commonpath([os.path.realpath(artifact), os.path.realpath(evidence)]) != os.path.realpath(evidence): raise SystemExit('ticket10: artifact outside evidence directory')
        if not os.path.isfile(artifact): raise SystemExit('ticket10: missing artifact')
        data = open(artifact, 'rb').read()
        digest = hashlib.sha256(data).hexdigest()
        if record['sha256'] != digest: raise SystemExit('ticket10: artifact hash mismatch')
        if secret.search(data): raise SystemExit('ticket10: secret scan failed')
        records.append(record)
if not records: raise SystemExit('ticket10: empty evidence manifest')
traces = [os.path.join(dirpath, name) for dirpath, _, names in os.walk(evidence) for name in names if name.endswith('.jsonl') and name not in ('manifest.jsonl', 'tc0043-backend-observability.jsonl')]
for trace in traces:
    events = [json.loads(line) for line in open(trace) if line.strip()]
    if any(item.get('event') == 'exception' for item in events): raise SystemExit('ticket10: trace contains exception')
    if sum(item.get('event') == 'enter' for item in events) != sum(item.get('event') == 'exit' for item in events): raise SystemExit('ticket10: trace enter/exit imbalance')
if os.path.exists(trace_path): raise SystemExit('ticket10: session trace was not archived/deleted')
docs = open(os.path.join(root, 'docs/happy-flow.md'), encoding='utf-8').read()
for marker in ('manifest.jsonl', 'one object per artifact row', '"case"', '"artifact"', '"sha256"', '"source"', '"contains_secrets"', '"revision"', '"command"', '"timestamp"'):
    if marker not in docs: raise SystemExit('ticket10: docs schema does not match JSONL row semantics: ' + marker)
if re.search(r'"artifacts"\s*:', docs): raise SystemExit('ticket10: docs uses aggregate manifest schema instead of JSONL rows')
if not re.search(r'Candidate.*not sealed', docs, re.I): raise SystemExit('ticket10: candidate is not explicitly unsealed')
if re.search(r'"approval"\s*:', docs, re.I): raise SystemExit('ticket10: candidate contains an approval record')
PY
printf '%s\n' "ticket10|PASS|elapsed=${ELAPSED}s|evidence=$EVIDENCE_DIR"
