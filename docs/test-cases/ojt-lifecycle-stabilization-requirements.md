# OJT lifecycle stabilization — Stage 1 requirements

## Boundary and evidence posture

This is a Ticket 1 documentation baseline for Stage 1 stabilization. It records the approved portfolio boundary and current, observable repository facts; it does not assert that remediation has happened. Existing behavior is characterized where the repository exposes it. A requirement is not permission to invent a status enum, report-hour rule, provider response, or happy-flow artifact.

The approved Stage 1 flow is: authenticate a seeded/synthetic student; obtain a deterministic academic profile without a default real-provider call; create a placement with its required owner and company; inspect the automatically created requirements for active categories; add descriptions and reports; submit for coordinator review; coordinator approves only when the approved review boundary is satisfied; then view an authorized filtered result and its PDF projection. The repository does not currently prove that this flow is implemented end to end.

**RED-test gate:** open questions below must be user-acknowledged and resolved before RED tests are authored or run. These cases are documentation/characterization targets only; this ticket does not add tests or application code.

## Sequential requirements and acceptance boundaries

### REQ-01 — One-command Compose demonstration

Acceptance boundary: a supported macOS/Linux developer can start the Stage 1 demonstration with one documented Docker Compose command; Nginx, PHP-FPM, and MariaDB LTS become healthy and the application is reachable without undocumented manual reconstruction.

### REQ-02 — Authentication and preserved roles

Acceptance boundary: the demo accepts the existing `sr_code`/password login shape and preserves guest, student, coordinator, and superuser roles. Role-specific access is explicit and unsupported roles fail closed; no new status or role enum is invented.

### REQ-03 — Deterministic academic provider boundary

Acceptance boundary: the default academic-profile path uses an approved deterministic fake and makes no network call. Known synthetic SR credentials return a normalized profile, placeholder photo, and enrollment metadata; unknown credentials return safe **422** JSON errors. Explicit timeout or unavailable scenarios return safe **503** responses. The real provider remains opt-in only, reads credentials from environment-backed configuration, and never logs secrets or PII.

### REQ-04 — Placement and evidence lifecycle

Acceptance boundary: an authorized student can create an owned placement with required `user_id` and `company_id`, receive requirements for active categories, and manage their own placement-linked descriptions and reports through the approved lifecycle. Before approval, the student may edit or soft-delete their own descriptions/reports; approved records are immutable. Stage 1 lifecycle/completion derives from the approval prerequisites and `is_approved`; the free-string internship `status` is display/backward-compatibility only and must not drive authorization or completion. A report requires a real date within the inclusive internship start/end dates and numeric hours from **0.25 through 24 inclusive** in **0.25-hour increments**. Existing 0/1 flags and the current backend/UI report-field mismatch remain compatibility facts.

### REQ-05 — Coordinator approval and reportable output

Acceptance boundary: a student can submit eligible evidence for coordinator review; only an authorized coordinator can approve when core placement fields are complete, every active requirement is verified, and at least one non-deleted valid report exists. That report must have a real date within the inclusive internship start/end dates and numeric hours from 0.25 through 24 inclusive in 0.25-hour increments. A successful approval sets/reflects `is_approved` and drives Stage 1 completion; approved records are immutable. An authorized coordinator may explicitly reopen a placement only with a required audit reason, returning it to the unapproved state so the student can make corrections; the same ownership and validation rules then apply. Descriptions are optional narrative and do not gate approval. The free-string internship `status` must not drive authorization or completion. The filtered UI and PDF use identical authorized filter semantics and stable ordering; the PDF contains exactly the same records and key values visible to the requester. No-result UI shows a clear empty state and the valid PDF states `No matching records`; unauthorized records never appear in either output.

### REQ-06 — Authorization and ownership

Acceptance boundary: backend authorization mirrors the existing frontend RBAC for every protected placement/evidence read and mutation. Unauthenticated API requests return **401**; authenticated-but-forbidden and cross-owner requests, including edits, soft-deletes, and reopen attempts, return **403**; nonexistent resources return **404** without existence disclosure; validation failures return **422** safe JSON errors. A student cannot access another student’s placement or self-approve, approved records cannot be changed without the explicit coordinator reopen flow, and coordinator and superuser permissions remain separate.

### REQ-07 — Safe validation, errors, and failure behavior

Acceptance boundary: malformed placement/evidence/filter inputs return **422** safe JSON errors; unauthenticated API access returns **401**; authenticated forbidden/cross-owner access returns **403**; missing resources return **404**. Provider failures, partial failures, concurrency conflicts, empty results, loading states, and session expiry remain bounded, actionable, and non-sensitive. Persistence does not silently create cross-owner or partial unrelated records.

### REQ-08 — Security containment and remediation boundary

Acceptance boundary: runtime secrets are environment/config-backed, synthetic fixtures contain no real PII, logs and responses redact credentials/tokens/passwords, and the sensitive SQL dump and hard-coded provider key are contained from the distributable demo. The published local-only synthetic identities are `DEMO-STUDENT-001` (`demo.student.001@test.example`, password `DemoOnly-Student-001!`), `DEMO-COORD-001` (`demo.coord.001@test.example`, password `DemoOnly-Coord-001!`), and `DEMO-ADMIN-001` (`demo.admin.001@test.example`, password `DemoOnly-Admin-001!`). These credentials are non-secret demo fixtures, never production defaults, and must not contain real PII. Credential rotation and Git-history purge are documented with backup, dry-run, verification, coordination, and force-push gates; neither operation is executed in Stage 1 documentation.

### REQ-09 — Persistent, resettable, idempotent demo

Acceptance boundary: Compose-backed data persists across the supported restart path; migrations/auth initialization/seeders can be rerun without duplicate identities or broken relationships; seeded synthetic student, coordinator, and superuser fixtures are exactly the documented local-only identities `DEMO-STUDENT-001`, `DEMO-COORD-001`, and `DEMO-ADMIN-001`; an explicit local reset deterministically restores those identities and their safe synthetic fixture state without external-provider access. They are never production defaults.

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
| Errors/messages | TC-OJT-0013; Stage 1 target |
| Empty/loading/no-results | TC-OJT-0014; Stage 1 target |
| Integration success/failure/timeout/bad payload | TC-OJT-0015–TC-OJT-0018; Stage 1 target |
| Roles/permissions and ownership | TC-OJT-0008; Stage 1 target |
| Security unauthorized/injection/session expiry | TC-OJT-0019, TC-OJT-0021, TC-OJT-0032; Stage 1 target |
| Regression | TC-OJT-0020, TC-OJT-0023; current characterization plus Stage 1 quality target |
| Synthetic fixtures/reset | TC-OJT-0033; Stage 1 target |
| Happy-flow sealing gate | TC-OJT-0024; Stage 1 target |

Genuine N/A categories and reasons: **Automated Test Ref. IDs** are N/A because Ticket 1 must not create tests; **real-provider success beyond explicit opt-in** is N/A for the default demo because only the environment-held opt-in boundary is approved. Compose health, cross-platform report date/hour validation, fake-provider success/failure/timeout/bad-payload cases, student/coordinator edit/reopen cases, filtered UI/PDF parity, no-result output, security cases, and the quality gate are applicable Stage 1 targets, not N/A merely because current implementation is absent.

## Out of scope

- Application, test, Docker, configuration, README, happy-flow, and existing sealed-document edits.
- Credential rotation execution; SQL removal; Git-history rewriting; force-push; provider calls; or production integration.
- Inventing status enums, report-hour limits, provider semantics, approval persistence, or correction/reopen rules.
- Stage 2 modernization, framework/runtime replacement, frontend rewrite, new features, and broad test coverage.

## Open Questions

None. All Ticket 1 open questions are resolved; no unacknowledged questions remain. Future happy-flow sealing still requires the explicit user approval defined by REQ-10.
