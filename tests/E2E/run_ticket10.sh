#!/bin/sh
# test-10042026-Maurice: isolated browser runner; token material is never logged.
set -eu
REPO_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)
PROJECT_NAME=${COMPOSE_PROJECT_NAME:?COMPOSE_PROJECT_NAME is required}
COMPOSE_FILE=$REPO_ROOT/compose.yaml
APP_PORT=${APP_PORT:?APP_PORT is required}
BASE_URL=${OJT_BASE_URL:-http://127.0.0.1:$APP_PORT}
EVIDENCE_DIR=${OJT_EVIDENCE_DIR:?OJT_EVIDENCE_DIR is required}
SESSION_ID=${OJT_TICKET10_SESSION_ID:?OJT_TICKET10_SESSION_ID is required}

TOKEN_FILE=$EVIDENCE_DIR/token.php
cat >"$TOKEN_FILE" <<'PHP'
<?php
require '/var/www/html/vendor/autoload.php';
$app = require '/var/www/html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$client = DB::table('oauth_clients')->where('personal_access_client', 1)->first();
if (!$client) { exit(1); }
Laravel\Passport\Passport::personalAccessClientId($client->id);
$user = App\User::where('sr_code', getenv('DEMO_TOKEN_SR_CODE'))->first();
if (!$user && getenv('DEMO_TOKEN_SR_CODE') === 'DEMO-STUDENT-002') {
    $user = App\User::create(array('sr_code' => 'DEMO-STUDENT-002', 'name' => 'Synthetic Second Student', 'first_name' => 'Synthetic', 'last_name' => 'Second', 'email' => 'synthetic.second.student@test.example', 'password' => Illuminate\Support\Facades\Hash::make('Synthetic-Second-Student-Only!'), 'role' => 'student'));
}
if (!$user) { exit(1); }
echo $user->createToken('ticket10')->accessToken;
PHP
docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm sh -c 'cat > /tmp/ticket10-token.php' <"$TOKEN_FILE" 2>"$EVIDENCE_DIR/token-copy-error.log" || { cat "$EVIDENCE_DIR/token-copy-error.log" >&2; exit 1; }
STUDENT_TOKEN=$(docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T -e DEMO_TOKEN_SR_CODE=DEMO-STUDENT-001 php-fpm sh -c 'export APP_KEY=$(cat /run/ojt-secrets/app.key); export DB_PASSWORD=$(cat /run/ojt-secrets/db.app.password); php /tmp/ticket10-token.php' 2>"$EVIDENCE_DIR/student-token-error.log") || { cat "$EVIDENCE_DIR/student-token-error.log" >&2; docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T php-fpm php -l /tmp/ticket10-token.php >&2 || :; printf '%s\n' 'ticket10: student token preparation failed' >&2; exit 1; }
STUDENT_TOKEN=$(printf '%s' "$STUDENT_TOKEN" | tr -d '\r\n')
COORDINATOR_TOKEN=$(docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T -e DEMO_TOKEN_SR_CODE=DEMO-COORD-001 php-fpm sh -c 'export APP_KEY=$(cat /run/ojt-secrets/app.key); export DB_PASSWORD=$(cat /run/ojt-secrets/db.app.password); php /tmp/ticket10-token.php' 2>"$EVIDENCE_DIR/coordinator-token-error.log") || { cat "$EVIDENCE_DIR/coordinator-token-error.log" >&2; printf '%s\n' 'ticket10: coordinator token preparation failed' >&2; exit 1; }
COORDINATOR_TOKEN=$(printf '%s' "$COORDINATOR_TOKEN" | tr -d '\r\n')
CROSS_OWNER_TOKEN=$(docker compose -p "$PROJECT_NAME" -f "$COMPOSE_FILE" exec -T -e DEMO_TOKEN_SR_CODE=DEMO-STUDENT-002 php-fpm sh -c 'export APP_KEY=$(cat /run/ojt-secrets/app.key); export DB_PASSWORD=$(cat /run/ojt-secrets/db.app.password); php /tmp/ticket10-token.php' 2>"$EVIDENCE_DIR/cross-owner-token-error.log") || { cat "$EVIDENCE_DIR/cross-owner-token-error.log" >&2; printf '%s\n' 'ticket10: cross-owner token preparation failed' >&2; exit 1; }
CROSS_OWNER_TOKEN=$(printf '%s' "$CROSS_OWNER_TOKEN" | tr -d '\r\n')
[ -n "$STUDENT_TOKEN" ] && [ -n "$COORDINATOR_TOKEN" ] && [ -n "$CROSS_OWNER_TOKEN" ] || { printf '%s\n' 'ticket10: token preparation failed' >&2; exit 1; }
rm -f "$TOKEN_FILE"

OJT_BASE_URL="$BASE_URL" OJT_EVIDENCE_DIR="$EVIDENCE_DIR" OJT_TICKET10_SESSION_ID="$SESSION_ID" \
  OJT_TICKET10_REVISION="${OJT_TICKET10_REVISION:?OJT_TICKET10_REVISION is required}" OJT_TICKET10_COMMAND="${OJT_TICKET10_COMMAND:?OJT_TICKET10_COMMAND is required}" \
  T10_STUDENT_TOKEN="$STUDENT_TOKEN" T10_COORDINATOR_TOKEN="$COORDINATOR_TOKEN" T10_CROSS_OWNER_TOKEN="$CROSS_OWNER_TOKEN" \
  npx --no-install playwright test --config="$REPO_ROOT/tests/E2E/playwright.config.cjs" "$REPO_ROOT/tests/E2E/ticket10_happy_flow.spec.js" --workers=1 --max-failures=1
