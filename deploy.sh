#!/usr/bin/env bash
#
# OVNIPORTO production deploy (the VPS). Idempotent, with rollback.
#
#   ./deploy.sh                      deploy origin/main
#   DEPLOY_REF=<sha|branch> ./deploy.sh
#   HEALTH_URL=http://127.0.0.1:9/up ./deploy.sh   simulate a failed health check (tests the rollback)
#
# Steps: fetch the target commit, build images tagged with it, start them, run the (additive)
# migrations, warm the caches, restart SSR and the queue workers and check /up. If /up does not
# answer, the previous release comes back and the script exits with an error. Running it again
# with nothing new to deploy changes nothing.
#
# Releases are recorded in .deploy/ (current, previous). See DEPLOY.md.

set -Eeuo pipefail
cd "$(dirname "$0")"

COMPOSE_FILE=docker-compose.prod.yml
DEPLOY_REF=${DEPLOY_REF:-origin/main}
HEALTH_URL=${HEALTH_URL:-http://127.0.0.1:8080/up}
HEALTH_TRIES=${HEALTH_TRIES:-30}
KEEP_RELEASES=${KEEP_RELEASES:-3}
STATE_DIR=.deploy

log() { printf '[deploy %s] %s\n' "$(date '+%H:%M:%S')" "$*"; }
fail() { log "ERRO: $*"; exit 1; }

compose() { RELEASE="$1" docker compose -f "$COMPOSE_FILE" "${@:2}"; }

healthy() {
    local i
    for ((i = 1; i <= HEALTH_TRIES; i++)); do
        if curl -fsS --max-time 5 -o /dev/null "$HEALTH_URL"; then
            return 0
        fi
        sleep 2
    done
    return 1
}

all_running() {
    # Every service of the release is up (none exited or restarting).
    local not_running
    not_running=$(compose "$1" ps --format '{{.Service}} {{.State}}' | awk '$2 != "running"' | wc -l)
    [ "$not_running" -eq 0 ] && [ "$(compose "$1" ps -q | wc -l)" -gt 0 ]
}

release_up() {
    local release="$1"
    log "Subindo a versão $release"
    compose "$release" up -d --remove-orphans --wait --wait-timeout 180 \
        || log "Aviso: nem todos os serviços ficaram saudáveis dentro do prazo"
}

warm_up() {
    local release="$1"
    log "Migrações (somente aditivas) e caches"
    compose "$release" exec -T app php artisan migrate --force --no-interaction
    compose "$release" exec -T app php artisan optimize
    compose "$release" exec -T app php artisan sitemap:generate
    log "Reiniciando SSR e workers"
    compose "$release" restart ssr
    compose "$release" exec -T worker php artisan horizon:terminate || true
}

rollback() {
    local previous="$1"
    if [ -z "$previous" ]; then
        fail "a verificação de /up falhou e não há versão anterior para voltar (primeiro deploy). Veja: docker compose -f $COMPOSE_FILE logs"
    fi
    log "A verificação de /up falhou: voltando para $previous"
    git -c advice.detachedHead=false checkout --quiet --detach "$previous"
    release_up "$previous"
    compose "$previous" exec -T app php artisan optimize || true
    if healthy; then
        fail "deploy desfeito: o site voltou para $previous e está respondendo. Corrija e rode de novo."
    fi
    fail "deploy desfeito, mas $previous também não responde em $HEALTH_URL. Intervenção manual necessária."
}

main() {
    command -v docker >/dev/null || fail "docker não encontrado"
    command -v curl >/dev/null || fail "curl não encontrado"
    [ -f .env ] || fail ".env não existe (veja DEPLOY.md, primeiro deploy)"
    mkdir -p "$STATE_DIR"

    log "Buscando $DEPLOY_REF"
    git fetch --quiet origin
    local target current
    target=$(git rev-parse --short=12 "$DEPLOY_REF^{commit}")
    current=$(cat "$STATE_DIR/current" 2>/dev/null || true)

    if [ "$target" = "$current" ] && all_running "$current" && healthy; then
        log "Já está em $target e respondendo em $HEALTH_URL: nada a fazer."
        exit 0
    fi

    git -c advice.detachedHead=false checkout --quiet --detach "$target"
    log "Construindo as imagens de $target"
    compose "$target" build --pull

    release_up "$target"
    warm_up "$target"

    log "Verificando $HEALTH_URL"
    if ! healthy; then
        rollback "$current"
    fi

    [ -n "$current" ] && [ "$current" != "$target" ] && echo "$current" > "$STATE_DIR/previous"
    echo "$target" > "$STATE_DIR/current"
    prune_old_images "$target"
    log "Pronto: $target no ar."
}

prune_old_images() {
    # Keeps the newest KEEP_RELEASES tags of each image (the current one and the rollback target included).
    local image
    for image in ovniporto/app ovniporto/web ovniporto/ssr; do
        docker image ls "$image" --format '{{.Tag}} {{.CreatedAt}}' \
            | sort -k2 -r | awk -v keep="$KEEP_RELEASES" -v current="$1" 'NR > keep && $1 != current && $1 != "latest" {print $1}' \
            | while read -r tag; do docker image rm "$image:$tag" >/dev/null 2>&1 || true; done
    done
}

# Same line on purpose: the checkout above may rewrite this file while bash is still reading it.
main "$@"; exit
