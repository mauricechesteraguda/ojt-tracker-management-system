# OJT lifecycle stabilization — current baseline

## Evidence-led baseline

| Area | Current evidence and line reference | Baseline conclusion |
|---|---|---|
| Runtime | `composer.json:8-15`; `package.json:27-29` | Laravel 5.7/PHP `^7.1.3`/Passport and React 16.2.0 are present. No Docker/Compose file is present at repository top level. |
| Tests | `phpunit.xml:11-18`; `tests/Unit/ExampleTest.php`; `tests/Feature/ExampleTest.php` | Only two example tests are present, so meaningful lifecycle coverage is not established. |
| Initialization | `database/seeds/DatabaseSeeder.php:12-15` | `DatabaseSeeder` is effectively empty. Idempotent fixture behavior is unproven. |
| Authentication/roles | `app/Http/Controllers/Auth/LoginController.php:40-43`; `app/User.php:20` | Login uses `sr_code`/password. Guest/student/coordinator/superuser vocabulary is retained as current domain language. |
| Placement | `app/Internship.php:15` | `user_id` and `company_id` are model inputs; `is_approved` and `is_deleted` are observed 0/1 fields. Stage 1 completion derives from approval prerequisites and `is_approved`; `status` is a free string for display/backward compatibility only and must not drive authorization or completion. |
| Requirements | `app/RequirementController.php:20-40` | Active requirement categories can create placement-linked requirements; current approval/deletion flags are string-like 0/1 values. |
| Descriptions/reports | `app/Http/Controllers/DescriptionController.php:35-42`; `app/Http/Controllers/ReportController.php:35-42`; `resources/coreui/src/views/Base/Internship/Report.js:109,451-455` | Description backend requires internship ID and description. Report target requires a real date within inclusive internship dates and numeric 0.25–24 hours in 0.25 increments; current implementation evidence does not show that rule. |
| Reporting | `app/Http/Controllers/InternshipController.php:226` | Current PDF/report filter seam uses campus, schoolyear, semester, college, and course. Parity is not proven. |
| Authorization | `routes/api.php:58,80`; `app/Http/Controllers/InternshipController.php:65-86,200` | Stage 1 authorization contract is resolved: backend mirrors existing frontend RBAC; unauthenticated API is 401, authenticated forbidden/cross-owner is 403, nonexistent resource is 404, and validation is 422 safe JSON. Current implementation still requires characterization/remediation. |
| Sensitive artifacts | `ojt.sql` exists; provider-key evidence is in the inspected application/provider paths | Sensitive SQL/key containment is a risk. The resolved provider contract requires environment-held credentials for explicit real-provider opt-in; values are intentionally not reproduced. |

## Approved flow boundary

The Stage 1 target flow is authentication → deterministic fake academic profile → placement with owner/company → generated requirements → description/report evidence → submission → coordinator review/approval → authorized filtered view → PDF projection. The default provider makes no network call; known synthetic SR credentials return normalized profile, placeholder photo, and enrollment metadata; unknown credentials return safe 422 JSON; explicit timeout/unavailable scenarios return safe 503; real-provider access is opt-in with environment-held credentials and no secret/PII logging. Approval requires complete core placement fields, every active requirement verified, and at least one non-deleted valid report; that report has a real date within inclusive internship start/end dates and numeric hours from 0.25 through 24 in 0.25 increments; descriptions are optional. Before approval, students may edit/soft-delete their own descriptions/reports; approved records are immutable. An authorized coordinator may explicitly reopen a placement only with a required audit reason, returning it to unapproved state for student corrections. Successful approval drives completion through `is_approved`; free-string `status` is display/backward-compatibility only and must not drive authorization or completion. Filtered UI and PDF use identical authorized filters and stable ordering; PDFs contain exactly the requester-visible records/key values, while no-result UI shows a clear empty state and the valid PDF states `No matching records`; unauthorized records never leak. This is a target acceptance boundary, not a claim that the current application completes it. The resolved authorization contract is: backend mirrors existing frontend RBAC; unauthenticated API is 401; authenticated forbidden and cross-owner access are 403; nonexistent resources are 404; validation failures are 422 safe JSON. Student self-approval, cross-owner identifier access, unauthorized role actions, and secret leakage remain target behaviors to characterize against that contract.

Published local-only synthetic fixtures (non-secret; never production defaults): `DEMO-STUDENT-001` / `demo.student.001@test.example` / `DemoOnly-Student-001!`; `DEMO-COORD-001` / `demo.coord.001@test.example` / `DemoOnly-Coord-001!`; `DEMO-ADMIN-001` / `demo.admin.001@test.example` / `DemoOnly-Admin-001!`. These identities use no real PII, and deterministic reset restores them and their safe synthetic data.

## Sealed-document finding

Discovery found no dedicated happy-flow document. The stale `README.md` is excluded from this ticket and is not evidence of a sealed contract. No existing sealed document was edited. Future happy-flow documentation becomes sealed only after Compose startup/reset, the authoritative browser journey, linked API/domain/security/regression tests, and the under-10-minute local gate all pass, followed by explicit user approval.

## Risks

1. Legacy Laravel/PHP/Passport versions may constrain a reproducible runtime.
2. Empty seeding and absent Docker evidence prevent a deterministic clean-run baseline.
3. `ojt.sql` and the hard-coded provider key create exposure and fixture-contamination risk.
4. Current backend authorization gaps may violate the resolved frontend-RBAC and 401/403/404/422 response contract.
5. Free-string internship status could be incorrectly used as a lifecycle/authorization source; the resolved contract makes approval prerequisites and `is_approved` authoritative, with descriptions optional.
6. UI/backend report-field mismatch can produce ambiguous validation and no safe hour-rule assertion.
7. The resolved UI/PDF filter parity, stable ordering, exact-record/value, no-result, and no-leakage contract must be implemented consistently; real-provider success beyond explicit opt-in is not part of the default demo.

## Coverage summary

Mechanically verified by `/tmp/agent-scripts/ojt-tracker/validate_test_case_spec.py`: requirements covered **10/10**; total cases **38**. The CSV `Test Type` vocabulary is exactly **Positive, Negative, Boundary, Permission, Regression, Security, Integration**. Counts: Boundary **5**, Integration **6**, Negative **6**, Permission **2**, Positive **9**, Regression **5**, Security **5**. Newly added Ticket 03 documentation cases are TC-OJT-0034–TC-OJT-0038.

Checklist coverage is mapped in `ojt-lifecycle-stabilization-requirements.md` and encoded in case titles/descriptions: happy flow (TC-OJT-0005, TC-OJT-0022), alternate (TC-OJT-0006), validation (TC-OJT-0004, TC-OJT-0007, TC-OJT-0026), preconditions/state (TC-OJT-0009, TC-OJT-0024), report date/hour boundary (TC-OJT-0025–TC-OJT-0026), edit/delete/undo/correction (TC-OJT-0010, TC-OJT-0027–TC-OJT-0031), data integrity/concurrency/partial failure/rollback (TC-OJT-0011–TC-OJT-0012), errors/messages (TC-OJT-0013), empty/loading/no-results (TC-OJT-0014), filtered UI/PDF parity/no-results (TC-OJT-0014, TC-OJT-0022, TC-OJT-0032), integration success/failure/timeout/bad payload (TC-OJT-0015–TC-OJT-0018), roles/permissions/ownership (TC-OJT-0008, TC-OJT-0029–TC-OJT-0030), security unauthorized/injection/session expiry (TC-OJT-0019, TC-OJT-0021, TC-OJT-0032), synthetic fixtures/reset (TC-OJT-0033–TC-OJT-0038), Compose bootstrap/persistence/idempotence/Passport repeatability (TC-OJT-0034–TC-OJT-0037), happy-flow sealing gate (TC-OJT-0024), and regression (TC-OJT-0020, TC-OJT-0023, TC-OJT-0031, TC-OJT-0035–TC-OJT-0036). Cases explicitly identify **Current characterization** versus **Stage 1 target**.

Genuine N/A categories/reasons: **Automated Test Ref. IDs** are N/A because Ticket 1 must not create tests; **real-provider success beyond explicit opt-in** is N/A for the default demo because only the environment-held opt-in boundary is approved. Compose health, cross-platform report date/hour validation, fake-provider success/failure/timeout/bad-payload cases, student edit/soft-delete, approved immutability, audited reopen, filtered UI/PDF parity, no-result output, synthetic fixtures/reset, security cases, and the under-10-minute quality gate are applicable Stage 1 targets, not N/A.

## Open Questions

None. All Ticket 1 open questions are resolved; no unacknowledged questions remain. Future happy-flow sealing still requires the explicit user approval defined by REQ-10.
