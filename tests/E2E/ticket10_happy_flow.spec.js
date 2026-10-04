/* test-10042026-Maurice — Ticket 10 synthetic browser acceptance. */
const fs = require('fs');
const os = require('os');
const path = require('path');
const crypto = require('crypto');
const childProcess = require('child_process');
const { test, expect } = require('@playwright/test');

const REPO_ROOT = path.resolve(__dirname, '../..');
const REPO_HASH = crypto.createHash('sha256').update(REPO_ROOT).digest('hex').slice(0, 16);
const SESSION_ID = process.env.OJT_TICKET10_SESSION_ID || `${process.pid}-${Date.now()}`;
const TRACE_DIR = path.join(os.homedir(), '.cache', 'agent-trace', REPO_HASH);
const TRACE_FILE = path.join(TRACE_DIR, `${SESSION_ID}.jsonl`);
const EVIDENCE_DIR = process.env.OJT_EVIDENCE_DIR || path.join(os.tmpdir(), `ojt-ticket10-${SESSION_ID}`);
const BASE_URL = process.env.OJT_BASE_URL || 'http://127.0.0.1:8088';
const STUDENT = Object.freeze({ srCode: 'DEMO-STUDENT-001', password: 'DemoOnly-Student-001!' });
const COORDINATOR = Object.freeze({ srCode: 'DEMO-COORD-001', password: 'DemoOnly-Coord-001!' });
const REVISION = process.env.OJT_TICKET10_REVISION || 'unknown';
const COMMAND = process.env.OJT_TICKET10_COMMAND || 'sh tests/Infrastructure/ticket10_acceptance_test.sh';
const LIFECYCLE_METHOD_REJECTION_CORRELATION_ID = 'ticket10-method-rejection';
const ORDERING_FIXTURE_IDS = Object.freeze({ alphaZulu: 920001, alphaAlphaFirst: 920002, betaEcho: 920003, alphaAlphaSecond: 920004 });

function traceEvent(event, operation, details) {
  fs.mkdirSync(TRACE_DIR, { recursive: true, mode: 0o700 });
  const safeDetails = details && Object.keys(details).reduce(function namedSafeDetails(result, key) {
    if (!/password|token|secret|credential|email|phone|address|pii/i.test(key)) result[key] = details[key];
    return result;
  }, {});
  fs.appendFileSync(TRACE_FILE, `${JSON.stringify({ timestamp: new Date().toISOString(), level: event === 'exception' ? 'ERROR' : 'INFO', event, operation, status: event === 'exception' ? 'error' : 'ok', details: safeDetails })}\n`, { mode: 0o600 });
}

async function withTrace(operation, callback) {
  traceEvent('enter', operation);
  try {
    const result = await callback();
    if (fs.existsSync(TRACE_FILE)) traceEvent('exit', operation);
    return result;
  } catch (error) {
    const message = error && error.message ? String(error.message).replace(/DemoOnly-[^\s]+|Bearer\s+[^\s]+/gi, '[redacted]').slice(0, 120) : 'unknown';
    traceEvent('exception', operation, { error_category: error && error.constructor ? error.constructor.name : 'unknown', message_category: message });
    throw error;
  }
}

function artifactHash(filePath) {
  return crypto.createHash('sha256').update(fs.readFileSync(filePath)).digest('hex');
}

function appendManifest(caseId, artifactPath, source) {
  return withTrace('appendManifest', async function namedAppendManifest() {
    const manifestPath = path.join(EVIDENCE_DIR, 'manifest.jsonl');
    const record = { case: caseId, artifact: artifactPath, sha256: artifactHash(artifactPath), source, contains_secrets: false, revision: REVISION, command: COMMAND, timestamp: new Date().toISOString() };
    fs.appendFileSync(manifestPath, `${JSON.stringify(record)}\n`, { mode: 0o600 });
    return record;
  });
}

function writeArtifact(fileName, content) {
  return withTrace('writeArtifact', async function namedWriteArtifact() {
    fs.mkdirSync(EVIDENCE_DIR, { recursive: true, mode: 0o700 });
    const artifactPath = path.join(EVIDENCE_DIR, fileName);
    fs.writeFileSync(artifactPath, content, { mode: 0o600 });
    return artifactPath;
  });
}

function extractPdfReport(pdfPath) {
  return withTrace('extractPdfReport', async function namedExtractPdfReport() {
    const textPath = `${pdfPath}.txt`;
    const result = childProcess.spawnSync('pdftotext', [pdfPath, textPath], { encoding: 'utf8' });
    if (result.status !== 0) throw new Error('pdf text extraction failed');
    const text = fs.readFileSync(textPath, 'utf8');
    fs.unlinkSync(textPath);
    const labels = Object.freeze({
      id: 'Internship ID', first_name: 'First Name', last_name: 'Last Name', sr_code: 'SR Code',
      company: 'Company', start_date: 'Start Date', end_date: 'End Date', approval: 'Approval',
      status: 'Status', campus: 'Campus', schoolyear: 'School Year', semester: 'Semester',
      college: 'College', course: 'Course',
    });
    const records = text.split(/(?=Internship ID:)/).filter(function namedHasPdfRecord(block) { return block.indexOf('Internship ID:') === 0; }).map(function namedParsePdfRecord(block) {
      const record = {};
      Object.keys(labels).forEach(function namedParsePdfField(key) {
        const match = block.match(new RegExp(`${labels[key]}:[ \\t]*([^\\r\\n]*)`));
        record[key] = match ? match[1].trim() : '';
      });
      return record;
    });
    return { text, records };
  });
}

function normalizeJsonRecord(record) {
  return withTrace('normalizeJsonRecord', async function namedNormalizeJsonRecord() {
    return {
      id: String(record.id == null ? '' : record.id), first_name: String(record.first_name == null ? '' : record.first_name), last_name: String(record.last_name == null ? '' : record.last_name), sr_code: String(record.sr_code == null ? '' : record.sr_code),
      company: String(record.company == null ? '' : record.company), start_date: String(record.start_date == null ? '' : record.start_date), end_date: String(record.end_date == null ? '' : record.end_date), approval: String(record.approval == null ? '' : record.approval),
      status: String(record.status == null ? '' : record.status), campus: String(record.campus == null ? '' : record.campus), schoolyear: String(record.schoolyear == null ? '' : record.schoolyear), semester: String(record.semester == null ? '' : record.semester),
      college: String(record.college == null ? '' : record.college), course: String(record.course == null ? '' : record.course),
    };
  });
}

function assertLifecycleEvents(events, internshipId) {
  return withTrace('assertLifecycleEvents', async function namedAssertLifecycleEvents() {
    // Lifecycle timestamp order is asserted chronologically below; rows are immutable append-only evidence.
    const EVENT_KEYS = ['internship_id', 'actor_id', 'event_type', 'from_is_approved', 'to_is_approved', 'reason', 'timestamp'];
    let previousTimestamp = 0;
    for (const event of events) {
      expect(Object.keys(event).sort()).toEqual(EVENT_KEYS.slice().sort());
      expect(event.internship_id).toBe(Number(internshipId));
      expect(Number.isInteger(event.actor_id)).toBeTruthy();
      expect(event.actor_id).toBeGreaterThan(0);
      expect(['approved', 'reopened']).toContain(event.event_type);
      expect(typeof event.from_is_approved).toBe('boolean');
      expect(typeof event.to_is_approved).toBe('boolean');
      expect(event.timestamp).toMatch(/^\d{4}-\d{2}-\d{2}T/);
      const timestamp = Date.parse(event.timestamp);
      expect(Number.isNaN(timestamp)).toBeFalsy();
      expect(timestamp).toBeGreaterThanOrEqual(previousTimestamp);
      previousTimestamp = timestamp;
      if (event.event_type === 'approved') {
        expect(event.from_is_approved).toBe(false);
        expect(event.to_is_approved).toBe(true);
        expect(event.reason).toBeNull();
      }
      if (event.event_type === 'reopened') {
        expect(event.from_is_approved).toBe(true);
        expect(event.to_is_approved).toBe(false);
        expect(event.reason).toBe(event.reason.trim());
        expect(event.reason.length).toBeGreaterThanOrEqual(10);
        expect(event.reason.length).toBeLessThanOrEqual(500);
      }
    }
    return events;
  });
}

function expectExactStatus(response, status) {
  return withTrace('expectExactStatus', async function namedExpectExactStatus() {
    const expectedStatuses = Array.isArray(status) ? status : [status];
    traceEvent('response', 'expectExactStatus', { actual_status: response.status(), expected_status: expectedStatuses.join(',') });
    expect(expectedStatuses).toContain(response.status());
    return response;
  });
}

async function captureMilestone(page, caseId, milestone) {
  return withTrace('captureMilestone', async function namedCaptureMilestone() {
    fs.mkdirSync(EVIDENCE_DIR, { recursive: true, mode: 0o700 });
    const artifactPath = path.join(EVIDENCE_DIR, `${caseId}-${milestone}.png`);
    await page.screenshot({ path: artifactPath, fullPage: true, animations: 'disabled', mask: [page.locator('input'), page.locator('[data-testid="pii"]')] });
    await appendManifest(caseId, artifactPath, `ticket10:${milestone}`);
  });
}

async function loginAs(page, identity) {
  return withTrace('loginAs', async function namedLoginAs() {
    await page.goto('/login');
    await expect(page.locator('#sr_code')).toBeVisible();
    await captureMilestone(page, 'TC-OJT-0024', 'login-before-credentials');
    await page.locator('#sr_code').fill(identity.srCode);
    await page.locator('#password').fill(identity.password);
    await page.locator('#password').evaluate(function namedClearPassword(input) { input.setAttribute('data-testid', 'pii'); });
    await page.locator('form input[type="submit"], form button[type="submit"]').first().click();
    await page.waitForURL(/\/home|\/dashboard/);
    await page.getByText('Internship', { exact: true }).first().click();
    await page.waitForURL(/\/internships/);
    await page.waitForLoadState('networkidle');
  });
}

async function jsonResponse(response, expectedStatuses) {
  return withTrace('jsonResponse', async function namedJsonResponse() {
    traceEvent('response', 'jsonResponse', { status: response.status() });
    expect(expectedStatuses).toContain(response.status());
    expect(response.headers()['content-type'] || '').toContain('json');
    return response.json();
  });
}

async function expectMethodNotAllowed(response, correlationId) {
  return withTrace('expectMethodNotAllowed', async function namedExpectMethodNotAllowed() {
    const body = await jsonResponse(response, [405]);
    expect(body.error).toEqual({ code: 'method_not_allowed', message: 'Method not allowed.' });
    expect(body.correlation_id).toBe(correlationId);
    expect(response.headers()['x-correlation-id']).toBe(correlationId);
    expect(JSON.stringify(body)).not.toMatch(/exception|trace|password|token|email|phone|address/i);
    return body;
  });
}

function parseBackendJsonLines(raw) {
  return raw.split(/\r?\n/).filter(function namedBackendNonEmptyLine(line) { return Boolean(line.trim()); }).map(function namedParseBackendJsonLine(line) {
    try { return JSON.parse(line); } catch (error) { return null; }
  }).filter(function namedKeepBackendJsonRecord(record) { return Boolean(record && typeof record === 'object' && !Array.isArray(record)); }).map(function namedNormalizeBackendRecord(record) {
    const context = record.context && typeof record.context === 'object' ? record.context : {};
    return {
      timestamp: record.timestamp || record.datetime || context.timestamp || context.datetime,
      level: String(record.level || context.level || '').toLowerCase(),
      event: record.event || context.event || record.message,
      operation: record.operation || context.operation || record.channel,
      status: record.status == null ? context.status : record.status,
      category: record.category || context.category,
      phase: record.phase || context.phase,
      correlation_id: record.correlation_id || context.correlation_id,
    };
  });
}

async function inspectTc0043BackendEvidence(windows) {
  return withTrace('inspectTc0043BackendEvidence', async function namedInspectTc0043BackendEvidence() {
    const projectName = process.env.COMPOSE_PROJECT_NAME;
    const args = ['compose', '-p', projectName, '-f', path.join(REPO_ROOT, 'compose.yaml'), 'exec', '-T', 'php-fpm', 'sh', '-c', 'find /home/app/.cache/agent-trace -type f -name "*.jsonl" -exec cat {} \\; 2>/dev/null; find /var/www/html/storage/logs -type f -exec cat {} \\; 2>/dev/null'];
    const traceResult = childProcess.spawnSync('docker', args, { encoding: 'utf8', env: process.env });
    const stderrResult = childProcess.spawnSync('docker', ['compose', '-p', projectName, '-f', path.join(REPO_ROOT, 'compose.yaml'), 'logs', '--no-color', '--no-log-prefix', 'php-fpm'], { encoding: 'utf8', env: process.env });
    if (traceResult.status !== 0 || stderrResult.status !== 0) throw new Error('TC0043 backend evidence collection failed');
    const raw = `${traceResult.stdout || ''}\n${stderrResult.stdout || ''}`;
    const artifactPath = await writeArtifact('tc0043-backend-observability.jsonl', raw);
    await appendManifest('TC-OJT-0043', artifactPath, 'ticket10:backend-session-trace-and-container-logs');
    const records = parseBackendJsonLines(raw);
    const correlated = records.filter(function namedCorrelatedBackendRecord(record) { return record.correlation_id === LIFECYCLE_METHOD_REJECTION_CORRELATION_ID; });
    expect(correlated.length).toBeGreaterThanOrEqual(3);
    for (const record of correlated) {
      expect(record.timestamp).toMatch(/^\d{4}-\d\d-\d\dT/);
      expect(Number.isNaN(Date.parse(record.timestamp))).toBeFalsy();
      expect(record.level).toMatch(/^(debug|info|notice|warning|warn|error|critical|alert|emergency|fatal)$/);
      expect(record.event || record.operation).toBeTruthy();
      expect(record.status).not.toBeUndefined();
      if (['error', 'critical', 'alert', 'emergency', 'fatal'].includes(record.level)) expect(false).toBeTruthy();
      if (record.level === 'error' && record.phase === 'exception') expect(false).toBeTruthy();
    }
    for (const window of windows) {
      const inWindow = correlated.filter(function namedRecordInCallWindow(record) { const timestamp = Date.parse(record.timestamp); return timestamp >= window.startedAt && timestamp <= window.finishedAt; });
      expect(inWindow.some(function namedExpected405Warning(record) { return ['warning', 'warn'].includes(record.level) && Number(record.status) === 405 && (record.category === 'expected_http_405' || record.event === 'http_method_not_allowed' || record.operation === 'exception.handler.render'); })).toBeTruthy();
    }
    return records;
  });
}

async function apiJson(request, method, endpoint, data, statuses) {
  return withTrace('apiJson', async function namedApiJson() {
    const response = await request[method](endpoint, { data });
    return jsonResponse(response, statuses || [200]);
  });
}

async function makeApiContext(browser, token) {
  return withTrace('makeApiContext', async function namedMakeApiContext() {
    return browser.newContext({ baseURL: BASE_URL, extraHTTPHeaders: { Authorization: `Bearer ${token}`, Accept: 'application/json' } });
  });
}

async function seedOrderingFixtures(filters, companyId, coordinatorId) {
  return withTrace('seedOrderingFixtures', async function namedSeedOrderingFixtures() {
    const projectName = process.env.COMPOSE_PROJECT_NAME;
    expect(projectName).toBeTruthy();
    const php = String.raw`<?php
require '/var/www/html/vendor/autoload.php';
$app = require '/var/www/html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$now = date('Y-m-d H:i:s');
$createdField = 'created'.'_at';
$updatedField = 'updated'.'_at';
$filterValue = function ($key) { $value = getenv($key); return $value === '__T10_NULL__' ? null : $value; };
$emails = array('ticket10-order-alpha-zulu@test.example', 'ticket10-order-alpha-alpha@test.example', 'ticket10-order-beta-echo@test.example', 'ticket10-order-alpha-alpha-tie@test.example');
DB::table('internships')->whereIn('user_id', DB::table('users')->whereIn('email', $emails)->pluck('id'))->delete();
DB::table('users')->whereIn('email', $emails)->delete();
$users = array(
    array('id' => 910001, 'email' => $emails[0], 'sr_code' => 'T10-ORDER-001', 'first_name' => 'Alpha', 'last_name' => 'Zulu', 'internship_id' => 920001),
    array('id' => 910002, 'email' => $emails[1], 'sr_code' => 'T10-ORDER-002', 'first_name' => 'Alpha', 'last_name' => 'Alpha', 'internship_id' => 920002),
    array('id' => 910003, 'email' => $emails[2], 'sr_code' => 'T10-ORDER-003', 'first_name' => 'Beta', 'last_name' => 'Echo', 'internship_id' => 920003),
    array('id' => 910004, 'email' => $emails[3], 'sr_code' => 'T10-ORDER-004', 'first_name' => 'Alpha', 'last_name' => 'Alpha', 'internship_id' => 920004),
);
foreach ($users as $user) {
    DB::table('users')->insert(array('id' => $user['id'], 'name' => $user['first_name'].' '.$user['last_name'], 'email' => $user['email'], 'sr_code' => $user['sr_code'], 'first_name' => $user['first_name'], 'last_name' => $user['last_name'], 'password' => Hash::make('Ticket10 synthetic only!'), 'role' => 'student', $createdField => $now, $updatedField => $now));
}
foreach (array($users[3], $users[1], $users[0], $users[2]) as $user) DB::table('internships')->insert(array('id' => $user['internship_id'], 'user_id' => $user['id'], 'company_id' => (int) getenv('T10_COMPANY_ID'), 'start_date' => '2026-10-01', 'end_date' => '2026-12-31', 'representative' => 'Ticket10 synthetic', 'student_position' => 'Ticket10 synthetic', 'is_approved' => 1, 'status' => 'approved', 'comment' => 'Ticket10 ordering fixture', 'updated_by' => (int) getenv('T10_COORDINATOR_ID'), 'schoolyear' => $filterValue('T10_SCHOOLYEAR'), 'course_code' => $filterValue('T10_COURSE'), 'semester' => $filterValue('T10_SEMESTER'), 'campus' => $filterValue('T10_CAMPUS'), 'college_code' => $filterValue('T10_COLLEGE'), 'is_deleted' => '0', $createdField => $now, $updatedField => $now));
echo 'ticket10-ordering-fixtures='.DB::table('internships')->where('comment', 'Ticket10 ordering fixture')->count();
`;
    const env = {
      ...process.env,
      T10_COMPANY_ID: String(companyId), T10_COORDINATOR_ID: String(coordinatorId),
      T10_CAMPUS: filters.campus == null ? '__T10_NULL__' : String(filters.campus), T10_SCHOOLYEAR: filters.schoolyear == null ? '__T10_NULL__' : String(filters.schoolyear),
      T10_SEMESTER: filters.semester == null ? '__T10_NULL__' : String(filters.semester), T10_COLLEGE: filters.college == null ? '__T10_NULL__' : String(filters.college), T10_COURSE: filters.course == null ? '__T10_NULL__' : String(filters.course),
    };
    const result = childProcess.spawnSync('docker', ['compose', '-p', projectName, '-f', path.join(REPO_ROOT, 'compose.yaml'), 'exec', '-T', 'php-fpm', 'php'], { input: php, encoding: 'utf8', env });
    if (result.status !== 0 || !/ticket10-ordering-fixtures=4/.test(result.stdout || '')) throw new Error('ordering fixture setup failed');
  });
}

function compareReportRecords(left, right) {
  return left.first_name.localeCompare(right.first_name) || left.last_name.localeCompare(right.last_name) || Number(left.id) - Number(right.id);
}

async function expectCoherentPaginationMeta(meta, filters, dataLength) {
  return withTrace('expectCoherentPaginationMeta', async function namedExpectCoherentPaginationMeta() {
    expect(meta.current_page).toBe(filters.page);
    expect(meta.per_page).toBe(filters.per_page);
    expect(meta.total).toBeGreaterThanOrEqual(dataLength);
    expect(meta.from).toBe(dataLength ? ((filters.page - 1) * filters.per_page) + 1 : null);
    expect(meta.to).toBe(dataLength ? meta.from + dataLength - 1 : null);
    expect(meta.last_page).toBe(Math.max(1, Math.ceil(meta.total / filters.per_page)));
  });
}

async function auditTrace() {
  return withTrace('auditTrace', async function namedAuditTrace() {
    const lines = fs.readFileSync(TRACE_FILE, 'utf8').trim().split('\n').filter(function namedNonEmptyLine(line) { return Boolean(line); }).map(function namedParseTrace(line) { return JSON.parse(line); });
    expect(lines.some(function namedHasEnter(line) { return line.event === 'enter'; })).toBeTruthy();
    expect(lines.some(function namedHasExit(line) { return line.event === 'exit'; })).toBeTruthy();
    expect(lines.some(function namedHasException(line) { return line.event === 'exception'; })).toBeFalsy();
    for (const line of lines) expect(JSON.stringify(line)).not.toMatch(/DemoOnly-|password|Bearer|token/i);
    traceEvent('exit', 'auditTrace');
    const archivedTrace = path.join(EVIDENCE_DIR, 'session-trace.jsonl');
    fs.copyFileSync(TRACE_FILE, archivedTrace);
    await appendManifest('TC-OJT-0024', archivedTrace, 'ticket10:session-trace');
    fs.unlinkSync(TRACE_FILE);
  });
}

test('Ticket10 synthetic lifecycle, reporting, correction, and authorization', async function ticket10HappyFlow({ browser, page }) {
  const studentToken = process.env.T10_STUDENT_TOKEN;
  const coordinatorToken = process.env.T10_COORDINATOR_TOKEN;
  const crossOwnerToken = process.env.T10_CROSS_OWNER_TOKEN;
  expect(studentToken).toBeTruthy();
  expect(coordinatorToken).toBeTruthy();
  expect(crossOwnerToken).toBeTruthy();
  const studentContext = await makeApiContext(browser, studentToken);
  const coordinatorContext = await makeApiContext(browser, coordinatorToken);
  const crossOwnerContext = await makeApiContext(browser, crossOwnerToken);
  const coordinatorUiContext = await browser.newContext({ baseURL: BASE_URL });
  const studentApi = studentContext.request;
  const coordinatorApi = coordinatorContext.request;
  const crossOwnerApi = crossOwnerContext.request;
  const coordinatorPage = await coordinatorUiContext.newPage();
  let testFailure = null;
  try {
    await loginAs(page, STUDENT);
    await captureMilestone(page, 'TC-OJT-0024', 'student-internships');
    const profileResponse = await studentApi.post('/api/academic/profile', { data: { sr_code: STUDENT.srCode, password: STUDENT.password } });
    const profile = await jsonResponse(profileResponse, [200]);
    expect(profile).toHaveProperty('correlation_id');
    const companies = await apiJson(studentApi, 'get', '/api/companies/all', undefined, [200]);
    const company = (companies.data || companies).find(function namedFindCompany(item) { return item.name === 'Demo Local Company'; });
    expect(company).toBeTruthy();
    const placement = await apiJson(studentApi, 'post', '/api/internships', { company_id: company.id, start_date: '2026-10-01', end_date: '2026-12-31', representative: 'Synthetic Representative', student_position: 'Synthetic Intern', comment: 'Synthetic placement' }, [200, 201]);
    const internship = placement.data || placement;
    const internshipId = internship.id;
    const requirements = await apiJson(studentApi, 'get', `/api/requirements/internship/${internshipId}`, undefined, [200]);
    expect((requirements.data || requirements).length).toBeGreaterThan(0);
    const description = await apiJson(studentApi, 'post', '/api/descriptions', { internship_id: internshipId, description: 'Synthetic orientation description.' }, [200, 201]);
    const report = await apiJson(studentApi, 'post', '/api/reports', { internship_id: internshipId, description: 'Synthetic daily report.', date: '2026-10-02', hours: 8 }, [200, 201]);
    await withTrace('coordinatorReview', async function namedCoordinatorReview() {
      await loginAs(coordinatorPage, COORDINATOR);
      await captureMilestone(coordinatorPage, 'TC-OJT-0024', 'coordinator-review');
      for (const requirement of (requirements.data || requirements)) await apiJson(coordinatorApi, 'post', `/api/requirements/${requirement.id}`, { is_approved: '1' }, [200]);
      const reportId = (report.data || report).id;
      await apiJson(coordinatorApi, 'post', `/api/reports/${reportId}`, { is_valid: true }, [200]);
      await apiJson(coordinatorApi, 'post', `/api/internships/${internshipId}/approve`, {}, [200]);
    });
    const current = internship;
    const filters = { campus: current.campus, schoolyear: current.schoolyear, semester: current.semester, college: current.college_code, course: current.course_code, page: 1, per_page: 15 };
    const reportFilters = { page: 1, per_page: 15 };
    await seedOrderingFixtures(reportFilters, company.id, 1);
    const reportResponse = await coordinatorApi.post('/api/internships/report', { data: reportFilters });
    const reportBody = await jsonResponse(reportResponse, [200]);
    // Ticket07 covers default page=1, per_page=15/100, and invalid pagination variants; Ticket10 checks the requested page.
    traceEvent('contract', 'Ticket07 pagination variants retained', { requested_page: filters.page, per_page: filters.per_page, invalid_pagination: 'Ticket07' });
    const pdfResponse = await coordinatorApi.post('/api/internships/report/pdf', { data: reportFilters });
    await expectExactStatus(pdfResponse, 200);
    expect(pdfResponse.headers()['content-type'] || '').toContain('application/pdf');
    const jsonIds = (reportBody.data || []).map(function namedMapJsonId(item) { return Number(item.id); });
    const pdfPath = await writeArtifact('approved-report.pdf', await pdfResponse.body());
    const pdfReport = await extractPdfReport(pdfPath);
    const pdfRecords = pdfReport.records;
    const jsonRecords = [];
    for (const item of (reportBody.data || [])) jsonRecords.push(await normalizeJsonRecord(item));
    const jsonValues = jsonRecords;
    const pdfValues = pdfRecords;
    expect(pdfValues).toEqual(jsonValues);
    const pdfIds = pdfRecords.map(function namedMapPdfId(item) { return Number(item.id); });
    expect(pdfIds).toEqual(jsonIds);
    expect(jsonRecords.length).toBeGreaterThanOrEqual(4);
    expect(jsonRecords).toEqual(jsonRecords.slice().sort(compareReportRecords));
    expect(new Set(jsonRecords.map(function namedSyntheticNames(item) { return `${item.first_name}/${item.last_name}`; })).size).toBeGreaterThanOrEqual(3);
    expect(jsonRecords.filter(function namedTieBreakRecord(item) { return item.first_name === 'Alpha' && item.last_name === 'Alpha'; }).map(function namedTiedIds(item) { return Number(item.id); })).toEqual([ORDERING_FIXTURE_IDS.alphaAlphaFirst, ORDERING_FIXTURE_IDS.alphaAlphaSecond]);
    expect(jsonIds).toContain(Number(internshipId));
    expect(reportBody.meta.current_page).toBe(filters.page);
    expect(reportBody.meta.per_page).toBe(filters.per_page);
    await expectCoherentPaginationMeta(reportBody.meta, reportFilters, (reportBody.data || []).length);
    expect(pdfRecords.length).toBe(reportBody.meta.to - reportBody.meta.from + 1);
    expect((reportBody.data || []).every(function namedApprovedOnly(item) { return item.approval === 'approved'; })).toBeTruthy();
    await appendManifest('TC-OJT-0022', pdfPath, 'ticket10:approved-report-pdf');
    const jsonArtifact = await writeArtifact('approved-report-ids.json', JSON.stringify({ ids: jsonIds, filters: filters }));
    await appendManifest('TC-OJT-0022', jsonArtifact, 'ticket10:approved-report-json');
    const noResultsFilters = Object.assign({}, filters, { campus: 'NO_SUCH_SYNTHETIC_CAMPUS' });
    const noResultsJson = await apiJson(coordinatorApi, 'post', '/api/internships/report', noResultsFilters, [200]);
    expect(noResultsJson.data).toHaveLength(0);
    const noResultsPdfResponse = await coordinatorApi.post('/api/internships/report/pdf', { data: noResultsFilters });
    await expectExactStatus(noResultsPdfResponse, 200);
    const noResultsPdfPath = await writeArtifact('no-results-report.pdf', await noResultsPdfResponse.body());
    const noResultsReport = await extractPdfReport(noResultsPdfPath);
    expect(noResultsReport.records).toHaveLength(0);
    const emptyMarker = noResultsReport.text.replace(/\s+/g, ' ').trim().match(/No matching records/);
    expect(emptyMarker ? emptyMarker[0] : '').toBe('No matching records');
    expect(noResultsReport.text).not.toMatch(/Internship ID:/);
    await appendManifest('TC-OJT-0014', noResultsPdfPath, 'ticket10:no-results-pdf');
    await captureMilestone(coordinatorPage, 'TC-OJT-0024', 'approved-report');
    const firstApprovalEvents = await apiJson(coordinatorApi, 'get', `/api/internships/${internshipId}/lifecycle-events`, undefined, [200]);
    await assertLifecycleEvents(firstApprovalEvents, internshipId);
    const beforeRepeatApproval = await apiJson(coordinatorApi, 'get', `/api/internships/${internshipId}`, undefined, [200]);
    const repeatApproval = await coordinatorApi.post(`/api/internships/${internshipId}/approve`, { data: {} });
    await expectExactStatus(repeatApproval, 200);
    const afterRepeatEvents = await apiJson(coordinatorApi, 'get', `/api/internships/${internshipId}/lifecycle-events`, undefined, [200]);
    expect(afterRepeatEvents).toHaveLength(firstApprovalEvents.length);
    const afterRepeatApproval = await apiJson(coordinatorApi, 'get', `/api/internships/${internshipId}`, undefined, [200]);
    expect(afterRepeatApproval).toEqual(beforeRepeatApproval);
    const repeatApprovalState = { is_approved: afterRepeatApproval.is_approved, status: afterRepeatApproval.status };
    expect(repeatApprovalState).toEqual({ is_approved: beforeRepeatApproval.is_approved, status: beforeRepeatApproval.status });
    const approvedDescriptionMutation = await coordinatorApi.post(`/api/descriptions/${(description.data || description).id}`, { data: { description: 'Synthetic forbidden approved mutation.' } });
    await expectExactStatus(approvedDescriptionMutation, 403);
    const firstReopen = await coordinatorApi.post(`/api/internships/${internshipId}/reopen`, { data: { reason: 'Synthetic correction required.' } });
    await expectExactStatus(firstReopen, 200);
    const reopenedEvents = await apiJson(coordinatorApi, 'get', `/api/internships/${internshipId}/lifecycle-events`, undefined, [200]);
    await assertLifecycleEvents(reopenedEvents, internshipId);
    expect(reopenedEvents.length).toBe(afterRepeatEvents.length + 1);
    expect(reopenedEvents[reopenedEvents.length - 1].event_type).toBe('reopened');
    expect(reopenedEvents[reopenedEvents.length - 1].reason).toBe('Synthetic correction required.');
    const repeatReopen = await coordinatorApi.post(`/api/internships/${internshipId}/reopen`, { data: { reason: 'Synthetic second correction.' } });
    await expectExactStatus(repeatReopen, 409);
    const mutationConflict = await coordinatorApi.post(`/api/internships/${internshipId}/reopen`, { data: { reason: 'Synthetic conflict mutation.' } });
    await expectExactStatus(mutationConflict, 409);
    expect(mutationConflict.status()).toBe(409);
    await apiJson(studentApi, 'post', `/api/descriptions/${(description.data || description).id}`, { description: 'Synthetic corrected description.' }, [200]);
    for (const requirement of (requirements.data || requirements)) await apiJson(coordinatorApi, 'post', `/api/requirements/${requirement.id}`, { is_approved: '1' }, [200]);
    await apiJson(coordinatorApi, 'post', `/api/reports/${(report.data || report).id}`, { is_valid: true }, [200]);
    await apiJson(coordinatorApi, 'post', `/api/internships/${internshipId}/approve`, {}, [200]);
    const finalEvents = await apiJson(coordinatorApi, 'get', `/api/internships/${internshipId}/lifecycle-events`, undefined, [200]);
    await assertLifecycleEvents(finalEvents, internshipId);
    expect(finalEvents.length).toBe(reopenedEvents.length + 1);
    expect(finalEvents[finalEvents.length - 1].event_type).toBe('approved');
    const studentReopen = await studentApi.post(`/api/internships/${internshipId}/reopen`, { data: { reason: 'Student must not reopen.' } });
    await expectExactStatus(studentReopen, 403);
    expect(studentReopen.status()).toBe(403);
    const crossOwner = await crossOwnerApi.get(`/api/internships/${internshipId}`);
    await expectExactStatus(crossOwner, 403);
    expect(crossOwner.status()).toBe(403);
    const beforeLifecycleRoutes = finalEvents;
    // TC-OJT-0043: the read-only lifecycle-event collection rejects every mutation verb.
    const rejectionHeaders = { 'X-Correlation-ID': LIFECYCLE_METHOD_REJECTION_CORRELATION_ID };
    const rejectionWindows = [];
    let startedAt = Date.now();
    const lifecycleUpdate = await coordinatorApi.put(`/api/internships/${internshipId}/lifecycle-events`, { data: { event_type: 'approved' }, headers: rejectionHeaders });
    rejectionWindows.push({ method: 'PUT', startedAt: startedAt - 2000, finishedAt: Date.now() + 2000 });
    await expectMethodNotAllowed(lifecycleUpdate, LIFECYCLE_METHOD_REJECTION_CORRELATION_ID);
    startedAt = Date.now();
    const lifecyclePatch = await coordinatorApi.patch(`/api/internships/${internshipId}/lifecycle-events`, { data: { event_type: 'approved' }, headers: rejectionHeaders });
    rejectionWindows.push({ method: 'PATCH', startedAt: startedAt - 2000, finishedAt: Date.now() + 2000 });
    await expectMethodNotAllowed(lifecyclePatch, LIFECYCLE_METHOD_REJECTION_CORRELATION_ID);
    startedAt = Date.now();
    const lifecycleDelete = await coordinatorApi.delete(`/api/internships/${internshipId}/lifecycle-events`, { headers: rejectionHeaders });
    rejectionWindows.push({ method: 'DELETE', startedAt: startedAt - 2000, finishedAt: Date.now() + 2000 });
    await expectMethodNotAllowed(lifecycleDelete, LIFECYCLE_METHOD_REJECTION_CORRELATION_ID);
    await inspectTc0043BackendEvidence(rejectionWindows);
    const afterLifecycleRoute = await apiJson(coordinatorApi, 'get', `/api/internships/${internshipId}/lifecycle-events`, undefined, [200]);
    await assertLifecycleEvents(afterLifecycleRoute, internshipId);
    expect(afterLifecycleRoute).toEqual(beforeLifecycleRoutes);
    expect(beforeLifecycleRoutes).toEqual(finalEvents);
    await captureMilestone(page, 'TC-OJT-0024', 'reapproved-correction');
  } catch (error) {
    testFailure = error;
    throw error;
  } finally {
    await withTrace('closeContexts', async function namedCloseContexts() {
      await coordinatorPage.close();
      await coordinatorUiContext.close();
      await coordinatorContext.close();
      await crossOwnerContext.close();
      await studentContext.close();
    });
    if (!testFailure) await auditTrace();
  }
});
