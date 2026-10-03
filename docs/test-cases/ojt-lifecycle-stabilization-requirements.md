# OJT lifecycle stabilization — Stage 1 requirements

## Boundary and evidence posture

This is a Ticket 1 documentation baseline for Stage 1 stabilization. It records the approved portfolio boundary and current, observable repository facts; it does not assert that remediation has happened. Existing behavior is characterized where the repository exposes it. A requirement is not permission to invent a status enum, report-hour rule, provider response, or happy-flow artifact.

The approved Stage 1 flow is: authenticate a seeded/synthetic student; obtain a deterministic academic profile without a default real-provider call; create a placement with its required owner and company; inspect the automatically created requirements for active categories; add descriptions and reports; submit for coordinator review; coordinator approves only when the approved review boundary is satisfied; then view an authorized filtered result and its PDF projection. The repository does not currently prove that this flow is implemented end to end.

**RED-test gate:** open questions below must be user-acknowledged and resolved before RED tests are authored or run. These cases are documentation/characterization targets only; this ticket does not add tests or application code.

## Sequential requirements and acceptance boundaries

### REQ-01 — One-command Compose demonstration

Acceptance boundary: a supported macOS/Linux developer can start the Stage 1 demonstration with one documented Docker Compose command; Nginx, PHP-FPM, and MariaDB LTS become healthy and the application is reachable without undocumented manual reconstruction.

### REQ-02 — Authentication and preserved roles

Acceptance boundary: the demo accepts the existing `sr_code`/password login shape and preserves guest, student, coordinator, and superuser roles. Role-specific access is explicit, unsupported/unknown roles fail closed, and every protected API action for an authenticated unknown-role principal returns **403** safe JSON; no new status or role enum is invented.

### REQ-03 — Deterministic academic provider boundary

Acceptance boundary: registration credential verification uses only an unauthenticated, rate-limited `POST /api/academic/profile` with `sr_code` and `password` in the request body; any `sr_code` or `password` query parameter causes rejection, even when a valid POST body also contains both credentials, and this rejection occurs before any provider invocation. Credentials are never accepted through GET/query parameters. Request validation, including body-only credential and schema validation, completes before any provider invocation. The browser registration flow preserves redirect and field-level errors in HTML responses, while JSON requests use the API statuses/contracts below and validation remains **422**. One request `correlation_id` is created at the controller boundary and is identical in controller, manager, and provider logs/traces and in the response, including failures; logs/traces never contain raw request or provider payloads.

The default adapter is a deterministic fake with no network transport: approved synthetic credentials return **200** JSON containing only the normalized safe profile, placeholder photo, and enrollment fields plus `correlation_id`; provider tokens, passwords, arbitrary forbidden fields, and raw PII are never returned, logged, or persisted. Unknown credentials return **422** `{error:{code:academic_credentials_invalid,message:safe generic},correlation_id}` and may be logged only at the expected WARN/INFO level. Local fake-only deterministic timeout/unavailable scenarios return **503** with codes `academic_provider_timeout`/`academic_provider_unavailable`, and fake responses complete under one second.

Real mode is explicit environment-only opt-in via `ACADEMIC_PROVIDER_MODE`, `ACADEMIC_PROVIDER_BASE_URL`, `ACADEMIC_PROVIDER_KEY`, `ACADEMIC_PROVIDER_CONNECT_TIMEOUT_SECONDS=2`, and `ACADEMIC_PROVIDER_TIMEOUT_SECONDS=5`; a real base URL must be `https://`, except for an explicit loopback (`127.0.0.1`, `::1`, or `localhost`) sentinel allowed only when `APP_ENV` is `local` or `testing`. cURL TLS peer and host verification remain enabled. Upstream non-2xx responses map to safe **503** code `academic_provider_unavailable`. Real response normalization uses the exact fake schema and a strict whitelist: arbitrary forbidden fields/tokens/passwords/raw PII are rejected or omitted, never passed through. Malformed payloads and forbidden-field payloads return **503** with code `academic_provider_malformed`; missing configuration fails closed with **503**. Each local real-provider sentinel scenario must prove transport occurred by asserting the sentinel hit count increases by exactly one for that scenario, alongside the exact safe error code; a pre-transport configuration failure is insufficient. Unexpected failures emit a structured ERROR containing exception class, message-safe category, stack, cause, and `correlation_id` (without raw payloads); expected invalid credentials are the permitted WARN/INFO exception. Fake tests use a local sentinel/isolated endpoint to assert zero hits, and real tests may use only a local test sentinel, never the live provider. Credentials never source from, appear in, or leak through logs/responses.

### REQ-04 — Placement and evidence lifecycle

Acceptance boundary: an authorized student can create only an owned placement for an existing company, with required `company_id`, internship start/end dates where start <= end, representative, and `student_position`; owner, updater, and approval fields are server-authoritative. The create transaction creates exactly one placement-linked requirement for each active requirement category and no inactive-category requirements; placement and automatic requirements commit or roll back together. The student can manage their own placement-linked descriptions and reports through the approved lifecycle. Before approval, the student may create, read, edit, and soft-delete their own placement/evidence; approved records are immutable. Authenticated `GET /api/descriptions/{id}` and `GET /api/reports/{id}` return 200 for the owner and authorized staff, 403 cross-owner, and 404 for missing/deleted resources. Description/report lists and searches remain paginated `data`/`links`/`meta` responses and exclude soft-deleted records. Stage 1 lifecycle/completion derives from the approval prerequisites and `is_approved`; the free-string internship `status` is display/backward-compatibility only and must not drive authorization or completion. A report requires a real date within the inclusive internship start/end dates and numeric hours from **0.25 through 24 inclusive** in **0.25-hour increments**. Existing 0/1 flags and the current backend/UI report-field mismatch remain compatibility facts.

### REQ-05 — Coordinator approval and reportable output

Acceptance boundary: a student can submit eligible evidence for coordinator review but cannot verify, validate, or approve it, and only an authorized coordinator or superuser (with superuser inheriting coordinator permissions) can verify/validate/approve when core placement fields are complete, every active requirement is verified, and at least one non-deleted valid report exists. That report must have a real date within the inclusive internship start/end dates and numeric hours from 0.25 through 24 inclusive in 0.25-hour increments. A successful approval sets/reflects `is_approved` and drives Stage 1 completion; approved records are immutable. An authorized coordinator may explicitly reopen a placement only with a required audit reason, returning it to the unapproved state so the student can make corrections; the same ownership and validation rules then apply. Descriptions are optional narrative and do not gate approval. The free-string internship `status` must not drive authorization or completion. The filtered UI and PDF use identical authorized filter semantics and stable ordering; the PDF contains exactly the same records and key values visible to the requester. No-result UI shows a clear empty state and the valid PDF states `No matching records`; unauthorized records never appear in either output.

### REQ-06 — Authorization and ownership

Acceptance boundary: backend authorization mirrors the existing frontend RBAC for every protected placement/evidence/cluster/search read and mutation. Unauthenticated API requests return **401**; authenticated-but-forbidden and cross-owner requests, including edits, soft-deletes, reopen attempts, and student cluster deletion, return **403**; nonexistent resources return **404** without existence disclosure; validation failures return **422** safe JSON errors. Each error response is exactly shaped as `{error:{code,message},correlation_id}` (with safe, non-sensitive values) and does not disclose record existence or PII. Student search returns **200** with own records only; coordinator and superuser may perform authorized global search; no matches return **200** with an empty data collection. A student cannot access another student’s placement or self-approve, approved records cannot be changed without the explicit coordinator reopen flow, and superuser inherits coordinator permissions.

### REQ-07 — Safe validation, errors, and failure behavior

Acceptance boundary: malformed placement/evidence/filter inputs return **422** safe JSON errors; unauthenticated API access returns **401**; authenticated forbidden/cross-owner access returns **403**; missing resources return **404**. Errors use `{error:{code,message},correlation_id}` and never reveal record existence details or PII. Provider failures, partial failures, concurrency conflicts, empty results, loading states, and session expiry remain bounded, actionable, and non-sensitive. The server derives owner, updater, and role-sensitive fields and rejects or ignores client attempts to escalate them. Persistence does not silently create cross-owner or partial unrelated records.

### REQ-08 — Security containment and remediation boundary

Acceptance boundary: runtime secrets are environment/config-backed, synthetic fixtures contain no real PII, logs and responses redact credentials/tokens/passwords, and the sensitive SQL dump and hard-coded provider key are contained from the distributable demo. The published local-only synthetic identities are `DEMO-STUDENT-001` (`demo.student.001@test.example`, password `DemoOnly-Student-001!`), `DEMO-COORD-001` (`demo.coord.001@test.example`, password `DemoOnly-Coord-001!`), and `DEMO-ADMIN-001` (`demo.admin.001@test.example`, password `DemoOnly-Admin-001!`). These credentials are non-secret demo fixtures, never production defaults, and must not contain real PII. Credential rotation and Git-history purge are documented with backup, dry-run, verification, coordination, and force-push gates; neither operation is executed in Stage 1 documentation.

### REQ-09 — Persistent, resettable, idempotent demo

Acceptance boundary: Compose-backed data persists across the supported restart path; migrations/auth initialization/seeders can be rerun without duplicate identities or broken relationships; seeded synthetic student, coordinator, and superuser fixtures are exactly the documented local-only identities `DEMO-STUDENT-001`, `DEMO-COORD-001`, and `DEMO-ADMIN-001`; an explicit local reset deterministically restores those identities and their safe synthetic fixture state without external-provider access. They are never production defaults.

Recommended supported interfaces and invariants for Stage 1 targets: automatic bootstrap on normal `docker compose up`; an explicit `docker compose exec php-fpm demo-reset` command (or equivalent named container command finalized by implementation); exactly three synthetic role identities and a deterministic fixture graph; Passport keys byte-identical across normal bootstrap/restart with a stable OAuth client count; and reset that deterministically recreates database data and OAuth clients without exposing key/client secrets or making provider network calls. If the executable name is not yet implemented, these cases specify the behavior contract while naming `demo-reset` as the preferred command.

### REQ-10 — Layered local quality gate and operational evidence

Acceptance boundary: the Stage 1 quality gate covers browser happy flow, HTTP/API contracts, domain/service invariants, operational health/reset/idempotence, security regression, structured correlation-safe logging, and the requirements/test/baseline evidence package, completing within ten minutes on a reasonable developer laptop. Future happy-flow documentation becomes sealed only after Compose startup and reset pass, the authoritative browser journey passes, linked API/domain/security/regression tests pass, and the local gate completes under ten minutes, followed by explicit user approval. This ticket documents the gate and does not add test code.

## Current-state facts used by this specification

- `composer.json:8-15` identifies PHP `^7.1.3`, Laravel `5.7.*`, and Passport `^7.5.1`; `package.json:27-29` identifies React 16.2.0 and React DOM 16.2.0.
- `phpunit.xml:11-18` discovers Unit and Feature suites; the repository contains only `tests/Unit/ExampleTest.php` and `tests/Feature/ExampleTest.php` beyond the harness.
- `database/seeds/DatabaseSeeder.php:12-15` leaves the seeder call commented out.
- `app/Http/Controllers/Auth/LoginController.php:40-43` uses `sr_code` as the username field; role vocabulary is present in `app/User.php:20` and current UI/controller paths.
- `app/Internship.php:15` lists `user_id`, `company_id`, `is_approved`, `status`, and `is_deleted`; `status` is not an enum in the inspected model.
- `app/RequirementController.php:20-40` scopes requirements to an internship and creates them from active categories; `app/DescriptionController.php:35-42` and `app/ReportController.php:35-42` show the backend validation boundary.
- `resources/coreui/src/views/Base/Internship/Report.js:36-40,109,451-455` shows the UI asks for date and hours; `app/Http/Controllers/ReportController.php:35-42,68-75` does not show the resolved date-range/0.25-hour validation in current implementation evidence.
- `app/Http/Controllers/InternshipController.php:226` contains the observed PDF/report filter dimensions; `app/Http/Controllers/InternshipController.php:65-86` is the search/role characterization seam.
- The repository has no Docker/Compose file in its top level, `ojt.sql` is present, and provider-key semantics are not resolved in the current evidence. README is stale and intentionally not modified.

## Coverage summary

The CSV contains at least one case for every REQ above. Its `Test Type` vocabulary is intentionally limited to **Positive, Negative, Boundary, Permission, Regression, Security, Integration**. Checklist categories are encoded in case titles/descriptions and this mapping, not as additional Test Type values:

| Checklist category | Coverage mapping |
|---|---|
| Happy flow | TC-OJT-0005, TC-OJT-0022; Stage 1 target |
| Filtered UI/PDF parity and no-results output | TC-OJT-0014, TC-OJT-0022, TC-OJT-0032; Stage 1 target |
| Alternate flow | TC-OJT-0006; Stage 1 target |
| Validation | TC-OJT-0004, TC-OJT-0007, TC-OJT-0026; current characterization plus Stage 1 target |
| Preconditions/state | TC-OJT-0009, TC-OJT-0024; current characterization plus Stage 1 target |
| Report date/hour boundary | TC-OJT-0025–TC-OJT-0026; Stage 1 target |
| Edit/delete/undo/correction | TC-OJT-0010, TC-OJT-0027–TC-OJT-0031; Stage 1 target |
| Data integrity/concurrency/partial failure/rollback | TC-OJT-0011, TC-OJT-0012; Stage 1 target |
| Errors/messages and safe JSON contract | TC-OJT-0013, TC-OJT-0019, TC-OJT-0039; Stage 1 target |
| Empty/loading/no-results | TC-OJT-0014; Stage 1 target |
| Integration success/failure/timeout/bad payload | TC-OJT-0015–TC-OJT-0018; Stage 1 target |
| Roles/permissions and ownership | TC-OJT-0008, TC-OJT-0028–TC-OJT-0030, TC-OJT-0039; Stage 1 target |
| Security unauthorized/injection/session expiry and safe error shape | TC-OJT-0013, TC-OJT-0019, TC-OJT-0021, TC-OJT-0032, TC-OJT-0039; Stage 1 target |
| Search ownership/role scope and empty collection | TC-OJT-0020, TC-OJT-0032; Stage 1 target |
| Cluster deletion/detachment/soft-delete | TC-OJT-0020; Stage 1 target |
| Regression | TC-OJT-0020, TC-OJT-0023, TC-OJT-0039; current characterization plus Stage 1 quality target |
| Compose bootstrap, persistence, idempotence, Passport repeatability, and reset | TC-OJT-0034–TC-OJT-0038; Stage 1 target |
| Synthetic fixtures/reset | TC-OJT-0033–TC-OJT-0039; Stage 1 target |
| Happy-flow sealing gate | TC-OJT-0024; Stage 1 target |

Genuine N/A categories and reasons: **Automated Test Ref. IDs** are N/A because documentation tickets must not create tests; **real-provider success beyond explicit opt-in** is N/A for the default demo because only the environment-held opt-in boundary is approved. Compose health, cross-platform report date/hour validation, fake-provider success/failure/timeout/bad-payload cases, student/coordinator edit/reopen cases, search ownership/global scope, cluster detach/soft-delete, unknown-role fail-closed, filtered UI/PDF parity, no-result output, security cases, and the quality gate are applicable Stage 1 targets, not N/A merely because current implementation is absent.

## Out of scope

- Application, test, Docker, configuration, README, happy-flow, and existing sealed-document edits.
- Credential rotation execution; SQL removal; Git-history rewriting; force-push; provider calls; or production integration.
- Inventing status enums, report-hour limits, provider semantics, approval persistence, or correction/reopen rules.
- Stage 2 modernization, framework/runtime replacement, frontend rewrite, new features, and broad test coverage.

## Open Questions

None. All Ticket 1 open questions are resolved; no unacknowledged questions remain. Future happy-flow sealing still requires the explicit user approval defined by REQ-10.
