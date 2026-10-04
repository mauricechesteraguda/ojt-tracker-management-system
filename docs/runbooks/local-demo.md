# Local demo runbook

This runbook describes the supported local demonstration on macOS and Linux. It is a synthetic, legacy-stabilization environment, not a production deployment.

## Prerequisites

- macOS or Linux with Docker Engine and the Docker Compose v2 plugin.
- Git, POSIX `sh`, and `timeout` or `gtimeout` for the quality gate.
- At least 4 GB of free memory and enough disk for images and named volumes.
- Port `8088` available, or set `APP_PORT` to another local port.

## Start

From the repository root:

```sh
docker compose up --build -d --wait
```

Open <http://localhost:8088>. The default provider is fake (`PROVIDER_MODE=fake` and `ACADEMIC_PROVIDER_MODE=fake`); no provider network call or live credential is needed.

## Health semantics

- MariaDB must report healthy before PHP-FPM starts.
- PHP-FPM health runs `php artisan --version` with the mounted app secret.
- Nginx health requires `/healthz` with `{"status":"ok"}` and a reachable `/login` page.
- `/healthz` is edge liveness, not proof that every application operation is ready. Use Compose health status and `/login` together.

## Synthetic accounts and data

`demo-reset` creates the local synthetic student, coordinator, and superuser fixtures. Their role IDs and login details are listed in the [README demo-account table](../../README.md#demo-accounts); never reuse those credentials outside this local fake-provider environment and never print them in logs or evidence. The fixture graph is deterministic.

## Persistence and reset

Normal restarts preserve the named database, runtime, and secret volumes:

```sh
docker compose restart
```

Reset synthetic data and Passport fixtures explicitly:

```sh
docker compose exec -T php-fpm demo-reset
```

Reset is local-only and requires the fake provider. `docker compose down` stops containers but preserves named volumes; `docker compose down -v` removes local data and generated secrets.

## Quality and acceptance commands

```sh
sh tests/quality-gate.sh
sh tests/Infrastructure/ticket10_acceptance_test.sh
```

The second command is the checked-in Ticket10 acceptance contract. It starts an isolated Compose project, runs the synthetic browser journey with the pinned Playwright dependency, validates external evidence, and removes its temporary evidence and containers before exit:

```sh
npx playwright test tests/E2E/ticket10_happy_flow.spec.js --workers=1 --max-failures=1
```

The acceptance command is a candidate evidence producer, not a retained-manifest or sealing operation. Do not mark the happy flow sealed or fill QA Actual Result/Status until the required evidence and explicit user approval exist.

## Troubleshooting

- Inspect state with `docker compose ps`.
- Inspect recent output without copying credentials: `docker compose logs --tail=200 nginx php-fpm mariadb`.
- If startup is stale, run `docker compose down` and start again; use `down -v` only when discarding local data is intended.
- If reset refuses, verify `APP_ENV=local` and fake-provider mode.
- If port `8088` is busy, set `APP_PORT`, restart, and use the new URL.

## Logs, cleanup, and supported limits

Quality-gate and browser trace output belongs outside the repository (temporary directories and the user cache, such as `~/.cache/agent-trace/`). Do not commit logs, screenshots, dumps, credentials, tokens, or generated evidence.

Clean up with `docker compose down`; use `docker compose down -v` for a full local teardown. This runbook supports one local Compose project, one synthetic fixture graph, the documented `8088` default port, and the bounded 600-second quality-gate ceiling. It does not support production traffic, real-provider credentials by default, multi-node operation, or data-retention guarantees.
