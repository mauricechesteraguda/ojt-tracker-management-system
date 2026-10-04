# Security containment runbook

This is a documentation and approval boundary. Do not rotate credentials, rewrite history, force-push, or print secret values as part of Ticket10.

## Current-tree containment

The first containment check must confirm that the current tree contains neither tracked `ojt.sql` nor tracked `public/uploads/` content. Also confirm provider credentials are environment-held and `.gitignore` keeps `/ojt.sql` and `/public/uploads/` ignored. Do not delete application code, tests, or unrelated user changes to satisfy this check.

## Immediate owner credential-rotation checklist

Rotation is owner-executed and approval-gated:

1. Identify every owner, provider, database, app-secret, OAuth, CI, and hosting credential potentially exposed; record only secret *types* and scope.
2. Obtain owner approval and a maintenance window; preserve a private incident record without placing values in Git, tickets, logs, or chat.
3. Revoke or rotate each credential at its issuing system, starting with the highest-impact provider and repository/CI access.
4. Update the approved secret store/environment references, not source files.
5. Restart affected services through the normal deployment process and verify least-privilege access.
6. Notify dependent owners and record timestamps, rotation status, and remaining exposure without recording values.

## Verification

After owner-approved rotation, verify that current-tree scans find no tracked dump, upload artifact, hardcoded provider credential, token, password, or secret value; Compose still uses secret boundaries and fake provider mode for local demos; logs, traces, screenshots, and evidence manifests contain no secrets or raw PII; and the synthetic local demo still resets deterministically. Records should contain command names, redacted results, revision, timestamp, and owner—not credential values.

## History purge: approval-gated procedure only

History removal is a coordinated security operation, not a Ticket10 action. If approved by repository owners and incident leads, use a disposable clone:

1. Create a fresh **dry-run clone** and inventory all refs and tags; never begin in the working checkout.
2. Use `git-filter-repo` in dry-run/planned mode to define path and secret replacements, including all refs/tags in scope. Do not paste secret values into commands or documentation.
3. Review the proposed rewritten object/ref set with security and repository owners; record approval, scope, and rollback plan.
4. During the protected-branch window, perform the approved rewrite in the disposable clone, validate refs/tags, and run secret rescans before publish.
5. Publish only with an explicit owner-approved `git push --force-with-lease` plan; never use an unreviewed force push.
6. Invalidate old clones and cached artifacts, rotate still-exposed credentials, and notify every clone/CI/package consumer.
7. Repeat secret scans over rewritten refs, tags, CI artifacts, logs, and mirrors; retain redacted evidence and close only after owner sign-off.

Ticket10 may document this procedure and its approval requirements only. It must not execute rotation, history rewrite, purge, force-push, or clone invalidation.

## Incident boundaries

This runbook covers suspected repository/current-tree exposure and local demo containment. It does not determine legal notification, production compromise, forensic scope, or credential ownership. Escalate those decisions to the repository owner and incident/security lead; preserve evidence and avoid destructive cleanup until they approve it.
