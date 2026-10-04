# OJT happy flow — Candidate — not sealed

**Status: Candidate — not sealed.** This is the authoritative current lifecycle description, aligned with the [README sequence](../README.md#happy-flow). It remains unsealed pending successful Ticket10 evidence and explicit user approval. No QA Actual Result or Status is asserted here.

```mermaid
sequenceDiagram
    participant Ops as Operator
    participant Compose as Docker Compose
    participant Student
    participant Coordinator
    participant App as Laravel application
    participant DB as MariaDB
    participant Academic as Fake academic provider
    participant Report as Shared reporting
    Ops->>Compose: docker compose up --build -d --wait
    Compose->>DB: Wait for healthy MariaDB
    Compose->>App: Start PHP-FPM and bootstrap migrations/fixtures
    Student->>App: Authenticate with synthetic student role
    App->>Academic: Request academic profile
    Academic-->>App: Deterministic fake profile
    Student->>App: Create owned placement for existing company
    App->>DB: Transactionally create placement and active-category requirements
    Student->>App: Add descriptions and valid reports; submit evidence
    Coordinator->>App: Verify requirements and validate evidence
    Coordinator->>App: Approve eligible placement
    App->>DB: Set is_approved=true, status=approved, append one audit event
    Coordinator->>Report: Request approved-only JSON and PDF with shared filters
    Report-->>Coordinator: Matching page-level projections
    opt Correction required
        Coordinator->>App: Reopen with trimmed 10–500 character reason
        App->>DB: Set is_approved=false, status=pending, append reopen event
        Student->>App: Correct and resubmit evidence
        Coordinator->>App: Reverify and reapprove
    end
```

## Invariants

- Roles are **student**, **coordinator**, and **superuser**; superuser inherits coordinator permissions. Guests are unauthenticated, and unknown roles fail closed.
- `is_approved` plus approval prerequisites is authoritative. `status` is display/backward-compatibility only: approval sets `approved`, reopen sets `pending`; status alone never authorizes, completes, or reports.
- Placement creation requires an existing company, dates with start <= end, representative, and `student_position`; owner/updater/approval fields are server-authoritative. One requirement is created for each active category in the same transaction.
- Approval requires complete core placement fields, every active requirement verified, and at least one non-deleted report. A report date is within inclusive internship dates and hours are numeric, 0.25–24 inclusive, in 0.25 increments. Descriptions are optional when absent; submitted blank descriptions are invalid.
- Students manage only their own pre-approval evidence. Approved placement, evidence, requirements, deletion, and visit mutations are immutable until an authorized coordinator/superuser reopens with a 10–500 character reason.
- JSON/PDF reporting is POST-only, approved-only, authorized, stably ordered, and page/per-page parity is exact. Defaults are `page=1`, `per_page=15`, with `per_page` bounded to 1–100.

## Evidence manifest schema

Each acceptance run should produce `manifest.jsonl` outside the repository. It contains one object per artifact row; values are redacted or identifiers, never credentials or raw PII:

```json
{
  "case": "TC-OJT-0024",
  "artifact": "<external-path>",
  "sha256": "<digest>",
  "source": "<suite/command>",
  "contains_secrets": false,
  "revision": "<clean-checkout-revision>",
  "command": "<exact-command>",
  "timestamp": "<UTC ISO-8601>"
}
```

## Acceptance criteria

Sealing requires a clean checkout with current-tree containment, Compose startup/readiness, deterministic reset/persistence, the synthetic browser journey, linked API/domain/security/regression coverage, secret-safe external artifacts, the aggregate quality gate including forced-failure continuation, and a repeat run within the 600-second bound. A user must explicitly approve the exact revision, command, timestamp, and evidence manifest.

## Known legacy boundaries

Laravel 5.7/PHP 7.4, React 16, Passport, MariaDB 10.11, the preserved web session form, and compatibility fields are legacy constraints. Real-provider success is explicit opt-in and outside the default demo. Playwright and the quality gate are contracts/evidence producers; this document does not claim that their current results pass. Credential rotation and Git-history purge are separate approval-gated security procedures.
