#!/usr/bin/env bash
#
# OVNIPORTO encrypted backup (the VPS, daily via cron; see DEPLOY.md).
#
#   scripts/backup.sh
#
# Makes one archive with: the MySQL dump, the Umami (Postgres) dump and the storage volume
# (photos, generated images). It is encrypted with age for BACKUP_AGE_RECIPIENT (a public key:
# the private one never lives on the VPS), sent to Google Drive with rclone and copies older than
# BACKUP_RETENTION_DAYS (30) are deleted there. Nothing unencrypted is left on disk.
#
# Settings (environment or .env.backup next to this repository):
#   BACKUP_AGE_RECIPIENT   age public key (age1...)                       required
#   BACKUP_REMOTE          rclone remote and folder                        default gdrive:ovniporto-backups
#   BACKUP_RETENTION_DAYS  days kept on the remote                         default 30

set -Eeuo pipefail
cd "$(dirname "$0")/.."

# shellcheck source=/dev/null
[ -f .env.backup ] && . ./.env.backup

COMPOSE_FILE=docker-compose.prod.yml
BACKUP_REMOTE=${BACKUP_REMOTE:-gdrive:ovniporto-backups}
BACKUP_RETENTION_DAYS=${BACKUP_RETENTION_DAYS:-30}
: "${BACKUP_AGE_RECIPIENT:?set BACKUP_AGE_RECIPIENT (age public key) in .env.backup}"

log() { printf '[backup %s] %s\n' "$(date '+%F %T')" "$*"; }
compose() { docker compose -f "$COMPOSE_FILE" "$@"; }

for bin in docker age rclone; do
    command -v "$bin" >/dev/null || { log "ERRO: $bin não encontrado"; exit 1; }
done

stamp=$(date -u '+%Y%m%dT%H%M%SZ')
work=$(mktemp -d)
trap 'rm -rf "$work"' EXIT
archive="ovniporto-$stamp.tar.age"

log "Banco do site (MySQL)"
# Credentials stay inside the container (its own MYSQL_* variables).
# shellcheck disable=SC2016
compose exec -T mysql sh -c \
    'exec mysqldump -u root -p"$MYSQL_ROOT_PASSWORD" --single-transaction --quick --routines --triggers --no-tablespaces "$MYSQL_DATABASE"' \
    | gzip > "$work/mysql.sql.gz"

log "Banco da métrica (Umami)"
compose exec -T umami-db pg_dump -U umami -d umami --no-owner | gzip > "$work/umami.sql.gz"

log "Arquivos (volume storage)"
compose exec -T app tar -C /var/www/html/storage -czf - app > "$work/storage.tar.gz"

{ git rev-parse --short=12 HEAD 2>/dev/null || echo desconhecida; } > "$work/release"

log "Criptografando para o destinatário age"
tar -C "$work" -cf - mysql.sql.gz umami.sql.gz storage.tar.gz release \
    | age -r "$BACKUP_AGE_RECIPIENT" -o "$work/$archive"

log "Enviando $archive para $BACKUP_REMOTE"
rclone copy "$work/$archive" "$BACKUP_REMOTE" --no-traverse

log "Apagando cópias com mais de $BACKUP_RETENTION_DAYS dias"
rclone delete "$BACKUP_REMOTE" --min-age "${BACKUP_RETENTION_DAYS}d" --include 'ovniporto-*.tar.age'

log "Pronto: $archive ($(du -h "$work/$archive" | cut -f1))"
