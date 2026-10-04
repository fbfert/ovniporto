#!/usr/bin/env bash
#
# OVNIPORTO restore from an encrypted backup (see DEPLOY.md, "Restaurar um backup").
#
#   scripts/restore.sh <ovniporto-YYYYmmddTHHMMSSZ.tar.age | local file> <age identity file>
#
# Downloads the archive from BACKUP_REMOTE when it is not a local file, decrypts it with the
# age identity (the private key, brought only for the restore and deleted afterwards), and
# replaces the MySQL database, the Umami database and the storage files of the running stack.
# Asks for confirmation: everything currently there is overwritten.

set -Eeuo pipefail
cd "$(dirname "$0")/.."

# shellcheck source=/dev/null
[ -f .env.backup ] && . ./.env.backup

COMPOSE_FILE=docker-compose.prod.yml
BACKUP_REMOTE=${BACKUP_REMOTE:-gdrive:ovniporto-backups}

log() { printf '[restore %s] %s\n' "$(date '+%F %T')" "$*"; }
compose() { docker compose -f "$COMPOSE_FILE" "$@"; }

[ $# -eq 2 ] || { echo "uso: $0 <arquivo .tar.age> <arquivo de identidade age>"; exit 2; }
source_archive=$1
identity=$2
[ -f "$identity" ] || { log "ERRO: identidade age não encontrada: $identity"; exit 1; }

work=$(mktemp -d)
trap 'rm -rf "$work"' EXIT

if [ -f "$source_archive" ]; then
    cp "$source_archive" "$work/backup.tar.age"
else
    log "Baixando $source_archive de $BACKUP_REMOTE"
    rclone copyto "$BACKUP_REMOTE/$source_archive" "$work/backup.tar.age"
fi

log "Descriptografando"
age -d -i "$identity" "$work/backup.tar.age" | tar -C "$work" -xf -
log "Backup da versão $(cat "$work/release" 2>/dev/null || echo '?')"

read -r -p "Isto SUBSTITUI o banco e os arquivos atuais. Digite RESTAURAR para continuar: " answer
[ "$answer" = "RESTAURAR" ] || { log "Cancelado."; exit 1; }

log "Pondo o site em manutenção"
compose exec -T app php artisan down --retry=60 || true

log "Banco do site (MySQL)"
# shellcheck disable=SC2016 # expanded inside the container
gunzip -c "$work/mysql.sql.gz" | compose exec -T mysql sh -c \
    'exec mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'

log "Banco da métrica (Umami)"
compose exec -T umami-db psql -U umami -d postgres -c 'DROP DATABASE IF EXISTS umami WITH (FORCE)' -c 'CREATE DATABASE umami OWNER umami' >/dev/null
gunzip -c "$work/umami.sql.gz" | compose exec -T umami-db psql -q -U umami -d umami >/dev/null

log "Arquivos (volume storage)"
compose exec -T app sh -c 'rm -rf /var/www/html/storage/app && tar -C /var/www/html/storage -xzf -' < "$work/storage.tar.gz"

log "Caches e volta ao ar"
compose exec -T app php artisan optimize:clear
compose exec -T app php artisan optimize
compose restart umami worker
compose exec -T app php artisan up
log "Pronto. Confira /up, o mapa, uma foto de relato e a métrica."
