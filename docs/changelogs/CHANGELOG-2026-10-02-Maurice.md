# Changelog — 2026-10-02

- Added Ticket 05 backend role fail-closed authorization, ownership policy seams, safe API errors, scoped internship search, and transactional cluster detachment.
- Added Ticket 06 authenticated placement/evidence ownership checks, atomic active-category requirements, strict validation, paginated evidence resources, and approval immutability (feature/fix-10032026-Maurice).
- Fixed Ticket 06 fake enrollment fixtures for synthetic users, company validation status boundaries, legacy boolean normalization, and the probe container URL propagation; preserved staff report review boundaries (test-10032026-Maurice).
- Tightened Ticket 06 report review validation, active-placement 404 boundaries, and requirement lazy-generation scoping (feature/fix-10032026-Maurice).

- Added Ticket 07 shared authenticated internship reporting JSON/PDF service, deterministic PII-minimized rendering, filter validation, and safe rendering errors (feature/fix-10032026-Maurice).
- Added Ticket 08 transactional approve/reopen lifecycle service, immutable numeric audit events, approved-only reporting, correction immutability, and lifecycle event reads (feature/fix-10042026-Maurice).

- Added Ticket 04 fail-closed academic provider boundary, deterministic fake profile API, explicit real adapter configuration, safe provider errors, bounded transport settings, and registration/internship provider injection.
- Added Ticket 03 local fake-provider bootstrap/reset executables, idempotent synthetic fixture seeding, Passport initialization, safe inspection/auth contracts, and persistent runtime startup wiring.
- Added Ticket 02 deterministic PHP-FPM/Nginx/MariaDB runtime images with pinned base digests, baked Composer dependencies, non-root app/web users, separate frontend/backend networks, app/DB-scoped generated secret volumes, Laravel/web readiness checks, and a status-only `/healthz` response.
- Added Ticket 1 OJT lifecycle stabilization requirements and acceptance boundaries.
- Added the traceable, not-run OJT lifecycle stabilization CSV.
- Added the evidence-led current baseline, risks, coverage summary, and open questions.
- Docs-only: updated the README with verified capabilities, current Laravel monolith structure, route/auth boundaries, deterministic local demo accounts, Compose readiness semantics, and screenshots of the login, student internship (placement/evidence), and coordinator company (company management) pages; preserved the Mermaid sections.

- Ticket09: added correlation propagation, structured JSON logging, redacted SessionTracer envelopes, JSONL container logs, Nginx access logging, and bounded local quality gate.
- Ticket10 docs-only: added local demo, security containment, and candidate happy-flow runbooks; linked them from the README with the exact quality and acceptance commands. The happy flow remains unsealed pending evidence and explicit user approval.
- Ticket10 TC-OJT-0043: normalized read-only lifecycle-event PUT/PATCH/DELETE rejections as exact safe 405 JSON with correlation propagation and expected-warning observability; strengthened unchanged-event coverage.
- Ticket10: sealed the final local happy-flow acceptance after explicit repository-owner approval on 2026-10-04; framework modernization, history rewrite/purge, and real credential rotation remain future or separately approval-gated operational work.

Author Name: Aguda, Maurice
