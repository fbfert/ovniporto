# Production stack, local verification (2026-10-04)

Run on a clean git worktree with its own `.env` built from `.env.production.example` (random secrets,
`COMPOSE_PROJECT_NAME=ovniporto-prodtest`, `WEB_BIND=127.0.0.1:8095`), Docker Desktop 29.8.1.

## 1.1 Images run without root

| Image | `docker run --rm --entrypoint whoami` |
|---|---|
| ovniporto/app | www-data |
| ovniporto/web (nginx-unprivileged) | nginx |
| ovniporto/ssr | node |

## 1.2 `docker compose -f docker-compose.prod.yml up -d --wait`

All 10 services up: app, web, worker (Horizon), mysql, redis, ssr and umami-db are healthy; scheduler,
pulse and umami have no healthcheck and are running (umami `/api/heartbeat` 200).
The first MySQL initialisation took about 4 minutes on Docker Desktop, so its `start_period` is 180 s.

Smoke test through the production nginx after `migrate --force`, `optimize` and `sitemap:generate`:

| Path | Status |
|---|---|
| /up | 200 |
| / (cold cache, SSR) | 200 |
| /mapa | 200 |
| /loja | 200 |
| /relatar | 302 (sign-in) |
| /nao-existe | 404 (site page) |
| /.env | 403 |
| /painel/filas (signed out) | 403 |

Headers: a single `X-Frame-Options: DENY` (from Laravel), CSP enforced, `Server: nginx` without version,
hashed assets with `Cache-Control: public, max-age=31536000, immutable` and `Content-Encoding: gzip`,
`/sw.js` with `no-cache`. HTTPS, HTTP/2 and the www/HTTP redirects belong to the reverse proxy (task 1.3).

## 2.1 deploy.sh

| Run | Result |
|---|---|
| `DEPLOY_REF=91fa83b ./deploy.sh` | built, migrated, `/up` ok, `.deploy/current = 91fa83b324d5`, exit 0 |
| same command again | "Já está em 91fa83b324d5 ...: nada a fazer.", exit 0, nothing rebuilt |
| `SIMULATE_FAILURE=1 DEPLOY_REF=1aca99a ./deploy.sh` | release 1aca99a started, treated as down, rolled back: containers back on `ovniporto/app:91fa83b324d5`, `/up` 200, `.deploy/current` unchanged, exit 1 |

## 3.1 Backup and restore

Run in a container with `age` and `rclone` (docker socket mounted), with a local rclone remote standing in
for Google Drive (`RCLONE_CONFIG_LOCALTEST_TYPE=local`):

1. Marker row inserted in `members` and marker file written to `storage/app/public/restore/`.
2. `scripts/backup.sh`: one `ovniporto-20261004T231714Z.tar.age` (104 KB) sent to the remote; the file
   starts with `age-encryption.org/v1` and contains no plaintext (marker e-mail not found in it).
3. Marker row and file deleted.
4. `scripts/restore.sh <archive> <identity>` with "RESTAURAR": maintenance mode, MySQL, Umami and
   storage restored, caches rebuilt, site back up.
5. Marker row back (1), marker file back, `/up` 200, home 200, Umami 200.

Still to do on the VPS: the same run against `gdrive:ovniporto-backups` (DEPLOY.md, go-live item 11).
