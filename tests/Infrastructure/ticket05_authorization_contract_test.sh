#!/bin/sh

# test-10032026-Maurice
# Ticket 05 RED contract: backend authorization, ownership, safe errors, and
# cluster detachment. Mapped to TC-OJT-0008, 0013, 0019, 0020, 0028, 0030,
# 0032, and 0039. This is deliberately a top-level POSIX shell script with no
# shell functions. Static gates must fail before Docker is touched.
set -eu
umask 077
MAX_FAILURES=1
REPO_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)
PROJECT_NAME=${OJT_TEST_COMPOSE_PROJECT:-ojt-ticket05-$$}
APP_PORT=${OJT_TEST_APP_PORT:-$((18100 + ($$ % 700)))}
TMP_ROOT=${TMPDIR:-/tmp}/ojt-ticket05-$$
COMPOSE_FILE=$REPO_ROOT/compose.yaml
COMPOSE_STARTED=0
mkdir -p "$TMP_ROOT"
trap 'if [ "$COMPOSE_STARTED" = 1 ]; then docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" down --volumes --remove-orphans >/dev/null 2>&1 || :; fi; rm -rf -- "$TMP_ROOT"' EXIT HUP INT TERM
logger -t ojt-ticket05 "event=startup status=begin test=ticket05_authorization_contract_test max_failures=$MAX_FAILURES" || :

# TC-OJT-0008/0028/0030/0039: authorization must be a backend, fail-closed
# contract. These artifact gates intentionally precede all runtime prerequisites.
logger -t ojt-ticket05 'event=major-operation status=check cases=TC-OJT-0008,TC-OJT-0028,TC-OJT-0030,TC-OJT-0039 assertion=central-authorization' || :
[ -f "$REPO_ROOT/app/Http/Middleware/RoleAuthorization.php" ] || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0008 assertion=role-middleware' || :; printf '%s\n' 'FAIL [TC-OJT-0008]: missing central backend role authorization middleware (Docker not started)' >&2; exit 1; }
[ -d "$REPO_ROOT/app/Policies" ] && find "$REPO_ROOT/app/Policies" -type f -name '*.php' -print -quit | grep -q . || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0028 assertion=policies' || :; printf '%s\n' 'FAIL [TC-OJT-0028]: missing backend policy artifacts' >&2; exit 1; }
[ -f "$REPO_ROOT/app/Support/ApiErrorNormalizer.php" ] || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0039 assertion=error-normalizer' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: missing centralized safe API error normalizer' >&2; exit 1; }
grep -Eiq 'student|coordinator|superuser|unknown|fail.?closed' "$REPO_ROOT/app/Http/Middleware/RoleAuthorization.php" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0008 assertion=role-contract' || :; printf '%s\n' 'FAIL [TC-OJT-0008]: role middleware does not prove fail-closed role handling' >&2; exit 1; }
grep -Eiq 'correlation_id|error.*code|error.*message' "$REPO_ROOT/app/Support/ApiErrorNormalizer.php" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0039 assertion=safe-schema' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: error normalizer does not prove the exact safe schema' >&2; exit 1; }

# TC-OJT-0008/0019/0020/0028/0030/0032/0039: every protected API route must
# carry backend role/policy coverage, rather than relying on frontend RBAC.
# The route table is checked from the booted application below; source grep is
# deliberately not used as a substitute for Laravel's compiled route table.
logger -t ojt-ticket05 'event=major-operation status=check cases=TC-OJT-0008,TC-OJT-0019,TC-OJT-0020,TC-OJT-0028,TC-OJT-0030,TC-OJT-0032,TC-OJT-0039 assertion=route-coverage' || :
grep -Eiq '401|unauthenticated|authentication' "$REPO_ROOT/app/Http/Middleware/RoleAuthorization.php" "$REPO_ROOT/app/Support/ApiErrorNormalizer.php" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0019 assertion=guest-contract' || :; printf '%s\n' 'FAIL [TC-OJT-0019]: guest 401 safe-JSON contract is not statically proven' >&2; exit 1; }
[ -f "$REPO_ROOT/docker/nginx/default.conf" ] && grep -Eq 'fastcgi_param[[:space:]]+HTTP_AUTHORIZATION[[:space:]]+\$http_authorization;' "$REPO_ROOT/docker/nginx/default.conf" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0008 assertion=authorization-forwarding' || :; printf '%s\n' 'FAIL [TC-OJT-0008]: Nginx does not explicitly forward HTTP Authorization to PHP-FPM' >&2; exit 1; }

# TC-OJT-0020/0032: required search and cluster regression seams remain
# explicit. The test must catch the known Internship search role-condition and
# Cluster `$cluster->i` defects even when a route superficially has auth.
logger -t ojt-ticket05 'event=major-operation status=check cases=TC-OJT-0020,TC-OJT-0032 assertion=regressions' || :
grep -Eiq 'search|student|coordinator|superuser|user_id' "$REPO_ROOT/app/Http/Controllers/InternshipController.php" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0020 assertion=internship-search-role' || :; printf '%s\n' 'FAIL [TC-OJT-0020]: Internship search ownership/role condition is not present' >&2; exit 1; }
if grep -Fq '$cluster->i' "$REPO_ROOT/app/Http/Controllers/ClusterController.php"; then logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0020 assertion=cluster-property-regression' || :; printf '%s\n' 'FAIL [TC-OJT-0020]: Cluster controller contains the known $cluster->i defect' >&2; exit 1; fi

# TC-OJT-0008: user reads are self-only for student/coordinator; only a
# superuser may enumerate/search users or read the global requirement-user list.
grep -Eq 'role.*student|role.*coordinator' "$REPO_ROOT/app/Http/Controllers/UserController.php" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0008 assertion=user-read-roles' || :; printf '%s\n' 'FAIL [TC-OJT-0008]: user read ownership roles are not statically proven' >&2; exit 1; }
grep -Eq '422|in_array.*student.*coordinator.*superuser|allowed.*role' "$REPO_ROOT/app/Http/Controllers/UserController.php" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0008 assertion=assigned-role-validation' || :; printf '%s\n' 'FAIL [TC-OJT-0008]: assigned user roles are not fail-closed validated' >&2; exit 1; }

# TC-OJT-0013: approval must serialize the internship, all active requirements,
# and candidate reports in one transaction before checking the invariant.
cat >"$TMP_ROOT/approval_gate.php" <<'PHP'
<?php
$source = file_get_contents($argv[1]);
if (!preg_match('/public function approve\(\$id\)(.*?)(?=\n\s*public function )/s', $source, $match)) { fwrite(STDERR, "APPROVAL_GATE_FAIL missing approve method\n"); exit(1); }
$body = $match[1];
$transaction = strpos($body, 'DB::transaction');
$approved = strpos($body, 'is_approved = 1');
if (substr_count($body, 'DB::transaction') !== 1 || $transaction === false || $approved === false || $transaction > $approved || substr_count($body, 'lockForUpdate') < 3 || !preg_match('/Internship::.*lockForUpdate/s', $body) || !preg_match('/Requirement::.*lockForUpdate/s', $body) || !preg_match('/Report::.*lockForUpdate/s', $body) || strpos($body, "Requirement::where('internship_id'") === false || strpos($body, "Report::where('internship_id'") === false || substr_count($body, "'is_deleted', '0'") < 2 || strpos($body, "'is_valid', '1'") === false || strpos($body, 'whereBetween') === false) { fwrite(STDERR, "APPROVAL_GATE_FAIL transaction/locks/prerequisite scope\n"); exit(1); }
PHP
php "$TMP_ROOT/approval_gate.php" "$REPO_ROOT/app/Http/Controllers/InternshipController.php" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0013 assertion=approval-transaction-locks' || :; printf '%s\n' 'FAIL [TC-OJT-0013]: approval is not one locked transaction over internship, active requirements, and candidate reports' >&2; exit 1; }

# TC-OJT-0039: InternshipController::store has one trace envelope around
# validation, provider calls, persistence, and requirement creation.
cat >"$TMP_ROOT/store_trace_gate.php" <<'PHP'
<?php
$source = file_get_contents($argv[1]);
if (!preg_match('/public function store\(Request \$request\)(.*?)(?=\n\s*public function )/s', $source, $match)) { fwrite(STDERR, "STORE_TRACE_GATE_FAIL missing store method\n"); exit(1); }
$body = $match[1];
$enter = strpos($body, 'SessionTracer::enter');
$validate = strpos($body, '$request->validate');
$create = strpos($body, 'Requirement::create');
if (substr_count($body, 'SessionTracer::enter') !== 1 || $enter === false || $validate === false || $create === false || $enter > $validate || strpos($body, 'SessionTracer::leave') === false || strpos($body, 'SessionTracer::exception') === false || !preg_match('/catch\s*\(\\\\?Throwable\s+\$/', $body) || strrpos($body, 'catch') < $create) { fwrite(STDERR, "STORE_TRACE_GATE_FAIL incomplete envelope\n"); exit(1); }
PHP
php "$TMP_ROOT/store_trace_gate.php" "$REPO_ROOT/app/Http/Controllers/InternshipController.php" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0039 assertion=internship-store-trace-envelope' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: Internship store validation/provider/DB/requirements are not inside one trace envelope' >&2; exit 1; }

# test-10032026-Maurice: changed-method trace gate for every changed
# controller/policy/handler/service method. It maps diff hunks to PHP method
# bodies; file-level keywords are deliberately insufficient.
cat >"$TMP_ROOT/trace_gate.php" <<'PHP'
<?php
$root = $argv[1]; chdir($root);
$files = array_filter(array_merge(
    explode("\n", trim(shell_exec('git diff --name-only -- app/Http/Controllers app/Policies app/Services app/Exceptions/Handler.php app/Support/PaginatedJson.php') ?: '')),
    explode("\n", trim(shell_exec('git ls-files --others --exclude-standard -- app/Http/Controllers app/Policies app/Services app/Exceptions/Handler.php app/Support/PaginatedJson.php') ?: ''))
));
foreach ($files as $file) {
    if (!is_file($file) || substr($file, -4) !== '.php') continue;
    $source = file_get_contents($file);
    $diff = shell_exec('git diff --unified=0 -- ' . escapeshellarg($file));
    $ranges = array();
    if (trim($diff ?: '') === '') $ranges[] = array(1, substr_count($source, "\n") + 1);
    foreach (preg_split('/\n/', $diff ?: '') as $line) {
        if (preg_match('/^@@ .* \+(\d+)(?:,(\d+))? @@/', $line, $match)) $ranges[] = array((int) $match[1], (int) $match[1] + (isset($match[2]) ? (int) $match[2] : 1) - 1);
    }
    $tokens = token_get_all($source); $line = 1; $method = null; $brace = 0; $body = ''; $methodStart = 0;
    foreach ($tokens as $token) {
        $text = is_array($token) ? $token[1] : $token;
        $tokenLine = is_array($token) ? $token[2] : $line;
        if (is_array($token) && $token[0] === T_FUNCTION && $method === null) { $method = ''; $methodStart = $tokenLine; $body = ''; }
        elseif ($method !== null && is_array($token) && $method === '' && $token[0] === T_STRING) $method = $token[1];
        if ($method !== null) $body .= $text;
        if ($method !== null && $text === '{') $brace++;
        if ($method !== null && $text === '}') {
            $brace--;
            if ($brace === 0 && $method !== '') {
                $changed = false; foreach ($ranges as $range) if ($methodStart <= $range[1] && $line >= $range[0]) $changed = true;
                if ($changed && (!strpos($body, 'SessionTracer::enter') || !strpos($body, 'SessionTracer::leave') || !strpos($body, 'SessionTracer::exception') || !preg_match('/catch\s*\(\\\\?Throwable\s+\$/', $body))) {
                    fwrite(STDERR, "TRACE_GATE_FAIL $file::$method\n"); exit(1);
                }
                $method = null; $body = '';
            }
        }
        $line += substr_count($text, "\n");
    }
}
PHP
command -v php >/dev/null 2>&1 || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0039 assertion=trace-gate-runtime' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: PHP is required for the changed-method trace gate' >&2; exit 1; }
php "$TMP_ROOT/trace_gate.php" "$REPO_ROOT" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0039 assertion=changed-method-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: every changed controller/policy/handler/service method must trace enter/exit/Throwable' >&2; exit 1; }
grep -Eq 'DB::transaction|DB::beginTransaction|lockForUpdate' "$REPO_ROOT/app/Http/Controllers/ClusterController.php" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0032 assertion=cluster-row-locks' || :; printf '%s\n' 'FAIL [TC-OJT-0032]: cluster mutation does not assert transaction row locks' >&2; exit 1; }

# Runtime prerequisites are intentionally unreachable until all static RED
# gates pass. Keep every operation bounded and isolated.
[ -f "$COMPOSE_FILE" ] || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0039 assertion=compose-file' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: compose.yaml is required after static gates' >&2; exit 1; }
command -v timeout >/dev/null 2>&1 || { logger -t ojt-ticket05 'event=failure assertion=timeout' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: timeout command is required' >&2; exit 1; }
command -v curl >/dev/null 2>&1 || { logger -t ojt-ticket05 'event=failure assertion=curl' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: curl is required' >&2; exit 1; }
if command -v nc >/dev/null 2>&1 && nc -z 127.0.0.1 "$APP_PORT" >/dev/null 2>&1; then logger -t ojt-ticket05 'event=failure assertion=app-port-conflict' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: selected app port is already in use' >&2; exit 1; fi

# The route audit is temporary, top-level PHP with no function declarations.
# It inspects Laravel's actual runtime RouteCollection, including inherited
# middleware, rather than comments or route-source text.
cat >"$TMP_ROOT/route_enumerator.php" <<'PHP'
<?php
require "/var/www/html/vendor/autoload.php";
$app = require "/var/www/html/bootstrap/app.php";
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$routes = app('router')->getRoutes();
$required = [
    'POST /api/users' => 'role:superuser', 'POST /api/users/{id}' => 'role:superuser', 'DELETE /api/users/{id}' => 'role:superuser',
    'POST /api/companies' => 'role:coordinator,superuser', 'POST /api/companies/{id}' => 'role:coordinator,superuser', 'DELETE /api/companies/{id}' => 'role:coordinator,superuser',
    'POST /api/requirements/categories' => 'role:coordinator,superuser', 'POST /api/requirements/categories/{id}' => 'role:coordinator,superuser', 'DELETE /api/requirements/categories/{id}' => 'role:coordinator,superuser',
    'POST /api/requirements/{id}' => 'role:coordinator,superuser', 'POST /api/internships/{id}/approve' => 'role:coordinator,superuser',
    'POST /api/clusters' => 'role:coordinator,superuser', 'POST /api/clusters/{id}' => 'role:coordinator,superuser', 'DELETE /api/clusters/{id}' => 'role:coordinator,superuser',
];
$seen = [];
foreach ($routes as $route) {
    $uri = '/' . ltrim($route->uri(), '/');
    if (strpos($uri, '/api/') !== 0 || $uri === '/api/academic/profile') continue;
    foreach ($route->methods() as $method) {
        if ($method === 'HEAD') continue;
        $key = $method . ' ' . $uri;
    $middleware = $route->gatherMiddleware();
        if (!in_array('auth:api', $middleware, true)) throw new \RuntimeException('protected route missing auth:api');
        $roles = array_values(array_filter($middleware, function ($entry) { return strpos($entry, 'role:') === 0; }));
        if (!in_array('role:student,coordinator,superuser', $roles, true)) throw new \RuntimeException('protected route missing fail-closed outer role');
        if (isset($required[$key]) && !in_array($required[$key], $roles, true)) throw new \RuntimeException('action-specific role middleware missing');
        $seen[$key] = ['middleware' => $middleware];
    }
}
foreach ($required as $key => $role) if (!isset($seen[$key])) throw new \RuntimeException('required protected route missing from runtime table');
foreach (array('POST /api/clusters', 'POST /api/clusters/{id}', 'DELETE /api/clusters/{id}') as $key) {
    if (!isset($seen[$key]) || !in_array('role:coordinator,superuser', $seen[$key]['middleware'], true)) throw new \RuntimeException('cluster mutation is not coordinator/superuser-only');
}
file_put_contents('/tmp/ticket05-runtime-routes.json', json_encode($seen, JSON_UNESCAPED_SLASHES));
PHP

# The probe is temporary, top-level PHP with no function declarations. It
# creates Passport tokens in the container and never prints or logs token
# material. Fixture identifiers are deterministic but contain no real PII.
cat >"$TMP_ROOT/authorization_probe.php" <<'PHP'
<?php
$base = rtrim(getenv('OJT_PROBE_BASE_URL'), '/');
$correlation = 'ticket-correlation';
$users = ['student_a', 'student_b', 'coordinator', 'superuser', 'unknown_role'];
$company = \App\Company::query()->firstOrFail();
$student_a = \App\User::firstOrCreate(['sr_code' => 'T05-STUDENT_A'], ['name' => 'Ticket05 Student A', 'email' => 'ticket05-a@example.invalid', 'password' => bcrypt('ticket05-a'), 'role' => 'student']);
$student_b = \App\User::firstOrCreate(['sr_code' => 'T05-STUDENT_B'], ['name' => 'Ticket05 Student B', 'email' => 'ticket05-b@example.invalid', 'password' => bcrypt('ticket05-b'), 'role' => 'student']);
$unknown = \App\User::firstOrCreate(['sr_code' => 'T05-UNKNOWN_ROLE'], ['name' => 'Ticket05 Unknown Role', 'email' => 'ticket05-unknown@example.invalid', 'password' => bcrypt('ticket05-unknown'), 'role' => 'unknown']);
$coordinator = \App\User::firstOrCreate(['sr_code' => 'T05-COORDINATOR'], ['name' => 'Ticket05 Coordinator', 'email' => 'ticket05-coordinator@example.invalid', 'password' => bcrypt('ticket05-coordinator'), 'role' => 'coordinator']);
$superuser = \App\User::firstOrCreate(['sr_code' => 'T05-SUPERUSER'], ['name' => 'Ticket05 Superuser', 'email' => 'ticket05-superuser@example.invalid', 'password' => bcrypt('ticket05-superuser'), 'role' => 'superuser']);
$personalClient = \Illuminate\Support\Facades\DB::table('oauth_personal_access_clients')->first();
if (!$personalClient) {
    $clientId = \Illuminate\Support\Facades\DB::table('oauth_clients')->insertGetId(['user_id' => null, 'name' => 'Ticket05 Probe Client', 'secret' => hash('sha256', 'ticket05-probe-client'), 'redirect' => 'http://localhost', 'personal_access_client' => 1, 'password_client' => 0, 'revoked' => 0]);
    \Illuminate\Support\Facades\DB::table('oauth_personal_access_clients')->insert(['client_id' => $clientId]);
}
$cluster = \App\Cluster::firstOrCreate(['year' => 2099], ['is_deleted' => 0]);
$owned = \App\Internship::firstOrCreate(['user_id' => $student_a->id, 'company_id' => $company->id], ['start_date' => '2099-01-01', 'end_date' => '2099-12-31', 'is_approved' => 0, 'status' => 'pending']);
$cross_owner = \App\Internship::firstOrCreate(['user_id' => $student_b->id, 'company_id' => $company->id], ['start_date' => '2099-01-01', 'end_date' => '2099-12-31', 'is_approved' => 0, 'status' => 'pending']);
// Approval prerequisites are deliberately explicit: user, company, dates,
// representative, position, every active requirement, and an in-range
// quarter-hour report with valid=1.
$owned->start_date = '2099-01-01'; $owned->end_date = '2099-12-31'; $owned->representative = 'T05 Representative'; $owned->student_position = 'T05 Student'; $owned->is_approved = 0; $owned->status = 'pending'; $owned->save();
if ((int) $owned->cluster_id !== (int) $cluster->id) { $owned->cluster_id = $cluster->id; $owned->save(); }
$tokens = [];
foreach ($users as $key) {
    $user = \App\User::where('sr_code', 'T05-' . strtoupper($key))->firstOrFail();
    $tokens[$key] = app(\Laravel\Passport\PersonalAccessTokenFactory::class)->make($user->id, 'ticket05');
    $accessToken = (string) $tokens[$key]->accessToken;
    if (substr_count($accessToken, '.') !== 2 || preg_match('/^\s|\s$/', $accessToken)) throw new \RuntimeException('clean bearer token contract failed');
}
$description = \App\Description::firstOrCreate(['internship_id' => $owned->id, 'description' => 'T05 description'], ['is_deleted' => 0]);
$report = \App\Report::firstOrCreate(['internship_id' => $owned->id, 'description' => 'T05 report'], ['date' => '2099-02-01', 'hours' => '8', 'is_valid' => '1', 'is_deleted' => '0', 'updated_by' => $student_a->id]);
$report->date = '2099-02-01'; $report->hours = '8'; $report->is_valid = '1'; $report->is_deleted = '0'; $report->updated_by = $coordinator->id; $report->save();
$mutableDescription = \App\Description::firstOrCreate(['internship_id' => $owned->id, 'description' => 'T05 mutable description'], ['is_deleted' => 0]);
$mutableReport = \App\Report::firstOrCreate(['internship_id' => $owned->id, 'description' => 'T05 mutable report'], ['date' => '2099-02-02', 'hours' => '8', 'is_valid' => '0', 'is_deleted' => '0', 'updated_by' => $student_a->id]);
$mutableReport->date = '2099-02-02'; $mutableReport->hours = '8'; $mutableReport->is_valid = '0'; $mutableReport->is_deleted = '0'; $mutableReport->save();
$category = \App\RequirementCategory::firstOrCreate(['name' => 'T05 category'], ['is_deleted' => 0, 'updated_by' => $student_a->id]);
$categories = \App\RequirementCategory::where('is_deleted', '0')->get();
$requirement = null;
foreach ($categories as $activeCategory) {
    $requirement = \App\Requirement::firstOrCreate(['internship_id' => $owned->id, 'requirement_category_id' => $activeCategory->id], ['is_approved' => '1', 'is_deleted' => '0', 'updated_by' => $coordinator->id]);
    $requirement->is_approved = '1'; $requirement->is_deleted = '0'; $requirement->updated_by = $coordinator->id; $requirement->save();
}
$cross_owner->start_date = '';
$cross_owner->representative = '';
$cross_owner->student_position = '';
$cross_owner->save();
$crossDescription = \App\Description::firstOrCreate(['internship_id' => $cross_owner->id, 'description' => 'T05 cross description'], ['is_deleted' => 0]);
$crossReport = \App\Report::firstOrCreate(['internship_id' => $cross_owner->id, 'description' => 'T05 cross report'], ['date' => '2099-02-03', 'hours' => '8', 'is_valid' => '0', 'is_deleted' => '0', 'updated_by' => $student_b->id]);
$superCluster = \App\Cluster::firstOrCreate(['year' => 2100], ['is_deleted' => 0]);
$activeRequirements = \App\Requirement::where('internship_id', $owned->id)->where('is_deleted', '0')->count();
$unverifiedRequirements = \App\Requirement::where('internship_id', $owned->id)->where('is_deleted', '0')->where('is_approved', '!=', '1')->count();
$validInRangeReport = \App\Report::where('internship_id', $owned->id)->where('is_deleted', '0')->where('is_valid', '1')->get()->first(function ($candidate) use ($owned) {
    $hours = (float) $candidate->hours;
    return $candidate->date >= $owned->start_date && $candidate->date <= $owned->end_date && $hours >= 0.25 && $hours <= 24 && fmod($hours * 4, 1.0) === 0.0;
});
if (!$owned->user_id || !$owned->company_id || !$owned->start_date || !$owned->end_date || !$owned->representative || !$owned->student_position || !$activeRequirements || $unverifiedRequirements || !$validInRangeReport) throw new \RuntimeException('approval prerequisite fixture is incomplete');
$requests = [
    ['guest', 'GET', '/api/internships/1', '', 401],
    ['unknown_role', 'GET', '/api/internships/1', '', 403],
    ['student_a', 'GET', '/api/internships/' . $owned->id, '', 200],
    ['student_a', 'GET', '/api/internships/' . $cross_owner->id, '', 403],
    ['student_a', 'POST', '/api/internships/' . $cross_owner->id, '{}', 403],
    ['student_a', 'DELETE', '/api/internships/' . $cross_owner->id, '', 403],
    ['student_a', 'GET', '/api/internships/999999', '', 404],
    ['student_a', 'GET', '/api/users/' . $student_a->id, '', 200],
    ['student_a', 'GET', '/api/users/' . $student_b->id, '', 403],
    ['student_a', 'GET', '/api/users/search/T05', '', 403],
    ['student_a', 'GET', '/api/users/internship/requirement', '', 403],
    ['coordinator', 'GET', '/api/users/' . $coordinator->id, '', 200],
    ['coordinator', 'GET', '/api/users/' . $student_a->id, '', 403],
    ['coordinator', 'GET', '/api/users/search/T05', '', 403],
    ['coordinator', 'GET', '/api/users/internship/requirement', '', 403],
    ['superuser', 'GET', '/api/users', '', 200],
    ['superuser', 'GET', '/api/users/search/T05', '', 200],
    ['superuser', 'GET', '/api/users/internship/requirement', '', 200],
    ['student_a', 'GET', '/api/descriptions/internship/' . $owned->id, '', 200],
    ['student_a', 'POST', '/api/descriptions', json_encode(['internship_id' => $cross_owner->id, 'description' => 'cross-owner']), 403],
    ['student_a', 'POST', '/api/descriptions/' . $description->id, json_encode(['description' => 'T05 owned description edit']), 200],
    ['student_a', 'POST', '/api/descriptions/' . $mutableDescription->id, json_encode(['description' => 'T05 owned mutable edit']), 200],
    ['student_a', 'DELETE', '/api/descriptions/' . $mutableDescription->id, '', 204],
    ['student_a', 'POST', '/api/descriptions/' . $crossDescription->id, json_encode(['description' => 'cross-owner']), 403],
    ['student_a', 'DELETE', '/api/descriptions/' . $crossDescription->id, '', 403],
    ['student_a', 'DELETE', '/api/descriptions/999999', '', 404],
    ['student_a', 'POST', '/api/reports', json_encode(['internship_id' => $owned->id, 'description' => 'bad date', 'date' => '2098-01-01', 'hours' => '8']), 422],
    ['student_a', 'POST', '/api/reports', json_encode(['internship_id' => $owned->id, 'description' => 'bad hours', 'date' => '2099-02-01', 'hours' => '0.3']), 422],
    ['student_a', 'POST', '/api/reports/' . $report->id, json_encode(['description' => 'escalate', 'date' => '2099-02-01', 'hours' => '8', 'is_valid' => 1, 'updated_by' => $superuser->id]), 403],
    ['student_a', 'POST', '/api/reports/' . $mutableReport->id, json_encode(['description' => 'T05 owned mutable edit', 'date' => '2099-02-02', 'hours' => '8']), 200],
    ['student_a', 'DELETE', '/api/reports/' . $mutableReport->id, '', 204],
    ['student_a', 'POST', '/api/reports/' . $crossReport->id, json_encode(['description' => 'cross-owner', 'date' => '2099-02-03', 'hours' => '8']), 403],
    ['student_a', 'DELETE', '/api/reports/' . $crossReport->id, '', 403],
    ['student_a', 'DELETE', '/api/reports/' . $report->id, '', 403],
    ['student_a', 'DELETE', '/api/reports/999999', '', 404],
    ['student_a', 'POST', '/api/requirements/' . $requirement->id, json_encode(['is_approved' => 1, 'updated_by' => $student_a->id]), 403],
    ['coordinator', 'POST', '/api/requirements/' . $requirement->id, json_encode(['is_approved' => 1]), 200],
    ['superuser', 'POST', '/api/reports/' . $report->id, json_encode(['description' => 'T05 reviewed', 'date' => '2099-02-01', 'hours' => '8', 'is_valid' => 1]), 200],
    ['student_a', 'POST', '/api/requirements/categories', json_encode(['name' => 'student-write']), 403],
    ['student_a', 'DELETE', '/api/requirements/categories/' . $category->id, '', 403],
    ['unknown_role', 'POST', '/api/companies', json_encode(['name' => 'unknown-write']), 403],
    ['coordinator', 'POST', '/api/companies/' . $company->id, json_encode(['name' => 'coord-write', 'country' => 'PH', 'city' => 'Batangas', 'address' => 'T05', 'location_map' => 'T05', 'main_branch' => '1']), 200],
    ['coordinator', 'POST', '/api/users/' . $student_a->id, json_encode(['role' => 'superuser']), 403],
    ['superuser', 'POST', '/api/users/' . $student_a->id, json_encode(['role' => 'student']), 200],
    ['superuser', 'POST', '/api/users/' . $student_b->id, json_encode(['role' => 'unknown-assigned-role']), 422],
    ['superuser', 'POST', '/api/users', json_encode(['first_name' => 'Ticket05', 'last_name' => 'InvalidRole', 'name' => 'Ticket05 InvalidRole', 'sr_code' => 'T05-INVALID-ROLE', 'role' => 'unknown-assigned-role', 'password' => 'ticket05-invalid']), 422],
    ['student_a', 'POST', '/api/internships/' . $owned->id . '/approve', '{}', 403],
    ['coordinator', 'POST', '/api/internships/' . $cross_owner->id . '/approve', '{}', 422],
    ['coordinator', 'POST', '/api/internships/' . $owned->id . '/approve', '{}', 200],
    ['student_a', 'POST', '/api/descriptions/' . $description->id, json_encode(['description' => 'approved-edit']), 403],
    ['coordinator', 'POST', '/api/descriptions/' . $description->id, json_encode(['description' => 'approved-coordinator-edit']), 403],
    ['superuser', 'POST', '/api/descriptions/' . $description->id, json_encode(['description' => 'approved-superuser-edit']), 403],
    ['student_a', 'DELETE', '/api/descriptions/' . $description->id, '', 403],
    ['coordinator', 'DELETE', '/api/descriptions/' . $description->id, '', 403],
    ['superuser', 'DELETE', '/api/descriptions/' . $description->id, '', 403],
    ['student_a', 'POST', '/api/reports/' . $report->id, json_encode(['description' => 'approved-edit', 'date' => '2099-02-01', 'hours' => '8']), 403],
    ['coordinator', 'POST', '/api/reports/' . $report->id, json_encode(['description' => 'approved-coordinator-edit', 'date' => '2099-02-01', 'hours' => '8']), 403],
    ['superuser', 'POST', '/api/reports/' . $report->id, json_encode(['description' => 'approved-superuser-edit', 'date' => '2099-02-01', 'hours' => '8']), 403],
    ['student_a', 'DELETE', '/api/reports/' . $report->id, '', 403],
    ['coordinator', 'DELETE', '/api/reports/' . $report->id, '', 403],
    ['superuser', 'DELETE', '/api/reports/' . $report->id, '', 403],
    ['student_a', 'GET', '/api/internships/search/never-match-t05', '', 200],
    ['coordinator', 'GET', '/api/internships/search/T05', '', 200],
    ['superuser', 'GET', '/api/internships/search/T05', '', 200],
    ['student_a', 'POST', '/api/internships/1/approve', '{}', 403],
    ['student_a', 'POST', '/api/clusters', json_encode(['year' => 2099]), 403],
    ['student_a', 'POST', '/api/clusters/' . $cluster->id, json_encode(['name' => 'student-write', 'year' => 2099]), 403],
    ['student_a', 'DELETE', '/api/clusters/' . $cluster->id, '', 403],
    ['coordinator', 'POST', '/api/clusters', json_encode(['year' => 2099]), 201],
    ['coordinator', 'POST', '/api/clusters/' . $cluster->id, json_encode(['name' => 'coord-write', 'year' => 2099]), 200],
    ['superuser', 'POST', '/api/clusters/' . $cluster->id, json_encode(['name' => 'super-write', 'year' => 2099]), 200],
    ['coordinator', 'DELETE', '/api/clusters/' . $cluster->id, '', 204],
    ['superuser', 'DELETE', '/api/clusters/' . $superCluster->id, '', 204],
    ['superuser', 'DELETE', '/api/clusters/999999', '', 404]
];
foreach ($requests as $request) {
    $headers = ['Accept: application/json', 'X-Correlation-ID: ' . $correlation];
    if ($request[3] !== '') $headers[] = 'Content-Type: application/json';
    if ($request[0] !== 'guest') $headers[] = 'Authorization: Bearer ' . $tokens[$request[0]]->accessToken;
    $handle = curl_init($base . $request[2]);
    curl_setopt_array($handle, [CURLOPT_CUSTOMREQUEST => $request[1], CURLOPT_POSTFIELDS => $request[3], CURLOPT_HTTPHEADER => $headers, CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_CONNECTTIMEOUT => 2, CURLOPT_TIMEOUT => 5]);
    $response = curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);
    if ($status !== $request[4]) throw new \RuntimeException('authorization status contract failed');
    if ($status >= 401 && $status <= 499) {
        $body = substr($response, strpos($response, "\r\n\r\n") + 4);
        $decoded = json_decode($body, true);
        if (!is_array($decoded) || array_keys($decoded) !== ['error', 'correlation_id'] || !is_array($decoded['error']) || array_keys($decoded['error']) !== ['code', 'message'] || $decoded['correlation_id'] !== $correlation) throw new \RuntimeException('unsafe error schema');
        if (preg_match('/password|token|email|phone|address|T05-/i', $body)) throw new \RuntimeException('PII or secret in error');
    }
}
$paginationRequests = [
    ['superuser', 'GET', '/api/users?page=2', 200],
    ['superuser', 'GET', '/api/users/search/T05?page=2', 200],
    ['superuser', 'GET', '/api/users/internship/requirement?page=2', 200],
    ['superuser', 'GET', '/api/clusters/all?page=2', 200],
    ['superuser', 'GET', '/api/clusters?page=2', 200],
    ['superuser', 'GET', '/api/clusters/search/2099?page=2', 200],
    ['student_a', 'GET', '/api/descriptions?page=2', 200],
    ['student_a', 'GET', '/api/descriptions/search/T05/internship/' . $owned->id . '?page=2', 200],
    ['student_a', 'GET', '/api/internships?page=2', 200],
    ['student_a', 'GET', '/api/internships/search/T05?page=2', 200],
    ['student_a', 'GET', '/api/requirements/internship/' . $owned->id . '?page=1', 200],
    ['student_a', 'GET', '/api/requirements/search/T05/internship/' . $owned->id . '?page=1', 200]
];
foreach ($paginationRequests as $request) {
    $headers = ['Accept: application/json', 'X-Correlation-ID: ' . $correlation, 'Authorization: Bearer ' . $tokens[$request[0]]->accessToken];
    $handle = curl_init($base . $request[2]);
    curl_setopt_array($handle, [CURLOPT_CUSTOMREQUEST => $request[1], CURLOPT_HTTPHEADER => $headers, CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_CONNECTTIMEOUT => 2, CURLOPT_TIMEOUT => 5]);
    $response = curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);
    if ($status !== $request[3]) throw new \RuntimeException('paginated endpoint status contract failed');
    $body = substr($response, strpos($response, "\r\n\r\n") + 4);
    $decoded = json_decode($body, true);
    $expectedPage = strpos($request[2], '?page=1') !== false ? 1 : 2;
    if (!is_array($decoded) || !array_key_exists('data', $decoded) || !array_key_exists('links', $decoded) || !array_key_exists('meta', $decoded) || !is_array($decoded['meta']) || !array_key_exists('current_page', $decoded['meta']) || !array_key_exists('per_page', $decoded['meta']) || (int) $decoded['meta']['current_page'] !== $expectedPage || (int) $decoded['meta']['per_page'] < 1) throw new \RuntimeException('paginated response shape/current-page contract failed');
    if (strpos($request[2], '/requirements/') !== false) {
        if (!class_exists('App\\Http\\Resources\\Requirement') || class_exists('App\\Http\\Resources\\RequirementResource')) throw new \RuntimeException('requirement resource class contract failed');
        $requirementController = file_get_contents('/var/www/html/app/Http/Controllers/RequirementController.php');
        if (strpos($requirementController, '\\App\\Http\\Resources\\Requirement::class') === false || strpos($requirementController, 'RequirementResource') !== false) throw new \RuntimeException('requirement endpoint uses the wrong resource class');
        if (count($decoded['data']) < 1 || !array_key_exists('id', $decoded['data'][0]) || !array_key_exists('requirement_category', $decoded['data'][0]) || !array_key_exists('internship', $decoded['data'][0])) throw new \RuntimeException('requirement resource serialization contract failed');
    }
    if (preg_match('/Paginator|ResourceCollection|legacy paginator/i', $body)) throw new \RuntimeException('legacy paginator-resource deprecation contract failed');
}
$cluster = \App\Cluster::findOrFail($cluster->id);
if ((int) $cluster->is_deleted !== 1 || \App\Internship::where('cluster_id', $cluster->id)->exists()) throw new \RuntimeException('cluster detach/soft-delete contract failed');
$clusterId = (int) $cluster->id;
$clusterRequests = [
    ['GET', '/api/clusters/' . $clusterId, 404],
    ['GET', '/api/clusters?per_page=100', 200],
    ['GET', '/api/clusters/search/2099?per_page=100', 200]
];
foreach ($clusterRequests as $request) {
    $handle = curl_init($base . $request[1]);
    curl_setopt_array($handle, [CURLOPT_CUSTOMREQUEST => $request[0], CURLOPT_HTTPHEADER => ['Accept: application/json', 'X-Correlation-ID: ' . $correlation, 'Authorization: Bearer ' . $tokens['superuser']->accessToken], CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_CONNECTTIMEOUT => 2, CURLOPT_TIMEOUT => 5]);
    $response = curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);
    if ($status !== $request[2]) throw new \RuntimeException('soft-deleted cluster status contract failed');
    if ($request[0] === 'GET' && $request[1] !== '/api/clusters/' . $clusterId) {
        $body = substr($response, strpos($response, "\r\n\r\n") + 4);
        $decoded = json_decode($body, true);
        foreach (($decoded['data'] ?? array()) as $item) if ((int) ($item['id'] ?? 0) === $clusterId) throw new \RuntimeException('soft-deleted cluster leaked from list/search');
    }
}
PHP

logger -t ojt-ticket05 'event=major-operation status=compose-up cases=TC-OJT-0008,TC-OJT-0013,TC-OJT-0019,TC-OJT-0020,TC-OJT-0028,TC-OJT-0030,TC-OJT-0032,TC-OJT-0039' || :
export COMPOSE_PROJECT_NAME=$PROJECT_NAME APP_PORT OJT_PROBE_BASE_URL=${OJT_PROBE_BASE_URL:-http://nginx:8080} OJT_TEST_NO_PROVIDER_CALLS=1
export AGENT_SESSION_ID=${AGENT_SESSION_ID:-ticket05-$$}
timeout "${OJT_TEST_BUILD_TIMEOUT_SECONDS:-180}" docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" up -d --build >"$TMP_ROOT/compose.log" 2>&1
COMPOSE_STARTED=1
chmod 0644 "$TMP_ROOT/route_enumerator.php"
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp "$TMP_ROOT/route_enumerator.php" php-fpm:/tmp/route_enumerator.php >"$TMP_ROOT/route-copy.log" 2>&1
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm sh -c 'export APP_KEY=$(cat /run/ojt-secrets/app.key); export DB_PASSWORD=$(cat /run/ojt-secrets/db.app.password); php /tmp/route_enumerator.php' >"$TMP_ROOT/runtime-routes.log" 2>&1 || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0008 assertion=runtime-route-table' || :; printf '%s\n' 'FAIL [TC-OJT-0008]: runtime route table authorization contract failed' >&2; exit 1; }
chmod 0644 "$TMP_ROOT/authorization_probe.php"
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp "$TMP_ROOT/authorization_probe.php" php-fpm:/tmp/authorization_probe.php >"$TMP_ROOT/probe-copy.log" 2>&1
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T -e OJT_PROBE_BASE_URL="$OJT_PROBE_BASE_URL" php-fpm sh -c 'export APP_KEY=$(cat /run/ojt-secrets/app.key); export DB_PASSWORD=$(cat /run/ojt-secrets/db.app.password); php -r '\''require "/var/www/html/vendor/autoload.php"; $app = require "/var/www/html/bootstrap/app.php"; $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class); $kernel->bootstrap(); require "/tmp/authorization_probe.php";'\''' >"$TMP_ROOT/probe.log" 2>&1 || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0039 assertion=runtime-probe' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: runtime authorization probe failed (see captured first RED in probe.log)' >&2; sed -n '1,40p' "$TMP_ROOT/probe.log" >&2; exit 1; }
timeout 20 docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" cp "php-fpm:/home/app/.cache/agent-trace/ojt-tracker-management-system/$AGENT_SESSION_ID.jsonl" "$TMP_ROOT/session-trace.jsonl" >"$TMP_ROOT/trace-copy.log" 2>&1 || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0039 assertion=trace-copy' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: authorization trace was not captured' >&2; exit 1; }
grep -q '"operation":"authorization.role"' "$TMP_ROOT/session-trace.jsonl" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0008 assertion=role-entry-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0008]: authorization role entry trace is missing' >&2; exit 1; }
grep -q '"operation":"authorization.role".*"role":"unknown"' "$TMP_ROOT/session-trace.jsonl" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0008 assertion=unknown-role-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0008]: unknown authenticated role did not reach role middleware trace' >&2; exit 1; }
grep -q '"operation":"authorization.role".*"outcome":"allowed"' "$TMP_ROOT/session-trace.jsonl" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0008 assertion=allowed-path-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0008]: sampled allowed path lacks runtime exit trace' >&2; exit 1; }
grep -q '"operation":"authorization.role".*"outcome":"denied"' "$TMP_ROOT/session-trace.jsonl" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0008 assertion=denied-path-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0008]: sampled denial path lacks runtime exit trace' >&2; exit 1; }
grep -q '"operation":"internship.approve".*"outcome":"success"' "$TMP_ROOT/session-trace.jsonl" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0013 assertion=approval-success-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0013]: approval success path lacks runtime trace evidence' >&2; exit 1; }
grep -q '"operation":"internship.approve".*"outcome":"exception"' "$TMP_ROOT/session-trace.jsonl" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0013 assertion=approval-validation-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0013]: approval validation path lacks runtime exception trace evidence' >&2; exit 1; }
grep -Eq '"operation":"(internship\.show|description\.(update|delete)|report\.(update|delete))".*"outcome":"exception"' "$TMP_ROOT/session-trace.jsonl" || { logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0039 assertion=missing-or-denial-trace' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: sampled missing/denial controller path lacks runtime exception trace evidence' >&2; exit 1; }
if grep -Eiq 'token|password|email|phone|address|T05-' "$TMP_ROOT/probe.log"; then logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0039 assertion=probe-redaction' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: runtime probe output contains a secret or PII' >&2; exit 1; fi
if grep -Eiq 'provider|academic|bulsu|bsu\.edu|api[_-]?key' "$TMP_ROOT/probe.log"; then logger -t ojt-ticket05 'event=failure status=red case=TC-OJT-0039 assertion=no-provider-calls' || :; printf '%s\n' 'FAIL [TC-OJT-0039]: authorization test made or reported a provider call' >&2; exit 1; fi
logger -t ojt-ticket05 'event=pass status=complete test=ticket05_authorization_contract_test' || :
printf '%s\n' 'PASS: Ticket 05 authorization contract'
