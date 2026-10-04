#!/bin/sh
set -u
umask 077

# test-10042026-Maurice: bounded sequential local quality gate.
REPO_ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
TMP_ROOT=${TMPDIR:-/tmp}/ojt-quality-gate-$$
PROJECT_BASE=${OJT_TEST_COMPOSE_PROJECT:-ojt-quality-$$}
FORCE_FAIL=${OJT_QUALITY_GATE_FORCE_FAIL:-}
FAST_PROBE=${OJT_QUALITY_GATE_FAST_PROBE:-0}
START=$(date +%s)
mkdir -p "$TMP_ROOT"
(sleep 600; kill -TERM $$) >/dev/null 2>&1 & WATCHDOG_PID=$!
trap 'kill "${WATCHDOG_PID:-}" >/dev/null 2>&1 || :; rm -rf -- "$TMP_ROOT"' EXIT HUP INT TERM

TIMEOUT_CMD=timeout
command -v timeout >/dev/null 2>&1 || TIMEOUT_CMD=gtimeout
command -v "$TIMEOUT_CMD" >/dev/null 2>&1 || { printf '%s\n' 'quality-gate: timeout/gtimeout unavailable' >&2; exit 1; }

set -- \
    "compose-config|15|docker compose -f $REPO_ROOT/compose.yaml config" \
    "compose-contract|20|sh $REPO_ROOT/tests/Infrastructure/compose_contract_test.sh" \
    "ticket03|60|sh $REPO_ROOT/tests/Infrastructure/ticket03_bootstrap_test.sh" \
    "ticket04|60|sh $REPO_ROOT/tests/Infrastructure/ticket04_provider_contract_test.sh" \
    "ticket05|50|sh $REPO_ROOT/tests/Infrastructure/ticket05_authorization_contract_test.sh" \
    "ticket06|50|sh $REPO_ROOT/tests/Infrastructure/ticket06_placement_evidence_test.sh" \
    "ticket07|55|sh $REPO_ROOT/tests/Infrastructure/ticket07_reporting_pdf_test.sh" \
    "ticket08|55|sh $REPO_ROOT/tests/Infrastructure/ticket08_lifecycle_test.sh" \
    "ticket09|120|sh $REPO_ROOT/tests/Infrastructure/ticket09_logging_contract_test.sh" \
    "ticket10|75|sh $REPO_ROOT/tests/Infrastructure/ticket10_acceptance_test.sh"

# 560s of child budgets leaves 40s for process startup, cleanup, and summary;
# this assertion must remain true before the independent hard 600s watchdog.
BUDGET_TOTAL=0
for CASE in "$@"; do
    SPEC=${CASE#*|}; BUDGET=${SPEC%%|*}; BUDGET_TOTAL=$((BUDGET_TOTAL + BUDGET))
done
[ "$BUDGET_TOTAL" -le 570 ] || { printf '%s\n' 'quality-gate: per-suite budgets exceed 570s' >&2; exit 1; }

INDEX=0
OVERALL=0
for CASE in "$@"; do
    INDEX=$((INDEX + 1))
    NAME=${CASE%%|*}
    SPEC=${CASE#*|}
    BUDGET=${SPEC%%|*}
    COMMAND=${SPEC#*|}
    LOG="$TMP_ROOT/$INDEX-$NAME.log"
    PORT=$((18000 + (($$ + INDEX) % 1000)))
    PROJECT="$PROJECT_BASE-$INDEX"
    CASE_START=$(date +%s)
    # Each child is fail-fast and isolated by project/port; aggregate continues.
    if [ "$FAST_PROBE" = 1 ]; then
        COMMAND="printf '%s\\n' 'quality-gate probe suite=$NAME'; exit 0"
    fi
    if [ "$NAME" = "$FORCE_FAIL" ]; then
        COMMAND="printf '%s\\n' 'quality-gate forced failure suite=$NAME'; exit 97"
    fi
    export OJT_TEST_COMPOSE_PROJECT="$PROJECT" OJT_TEST_APP_PORT="$PORT" COMPOSE_PROJECT_NAME="$PROJECT"
    "$TIMEOUT_CMD" "$BUDGET" sh -c "$COMMAND" >"$LOG" 2>&1
    CODE=$?
    ELAPSED=$(( $(date +%s) - CASE_START ))
    if [ "$CODE" -eq 0 ]; then STATUS=PASS; else STATUS=FAIL; OVERALL=1; fi
    if [ ! -s "$LOG" ]; then STATUS=FAIL; OVERALL=1; fi
    printf '%s\n' "$NAME|$STATUS|${ELAPSED}s|$LOG"
done

TOTAL=$(( $(date +%s) - START ))
kill "$WATCHDOG_PID" >/dev/null 2>&1 || :
printf '%s\n' "summary|elapsed=${TOTAL}s|logs=$TMP_ROOT"
[ "$TOTAL" -le 600 ] || OVERALL=1
exit "$OVERALL"
