# Proposal

## Why

O site precisa ir ao ar em `ovniporto.tars.art.br`, numa VPS no Brasil, de forma repetível, segura e recuperável: deploy sem susto, backup fora da VPS e alerta quando algo quebra.

## What Changes

- Composição de produção em Docker: web (nginx com compressão, HTTP/2 e cache de estáticos), app (php-fpm com opcache), ssr, worker (Horizon), scheduler, MySQL, Redis, Umami + Postgres, atrás de proxy reverso com HTTPS automático (Prompt 21).
- Imagem multi-stage enxuta, sem Node no container da aplicação, com usuário não-root.
- `deploy.sh` idempotente com migração, caches, reinício de ssr/Horizon, health check em `/up` e rollback.
- Backups diários criptografados para armazenamento S3-compatível fora da VPS, retenção de 30 dias e restauração testada.
- Monitoramento: Horizon e Pulse só para admin, alertas de fila e de jobs com falha, uptime externo.
- Segurança: CSP compatível com PayPal, tiles do mapa e Umami; HSTS; X-Frame-Options; rate limit em login, relato, checkout e API; segredos só em variáveis.
- `DEPLOY.md` com o checklist de go-live.

## Capabilities

### New Capabilities
- `deployment`: operação de produção (deploy, backup, monitoramento, segurança de borda e checklist de lançamento).

### Modified Capabilities
<!-- Nenhuma. -->

## Impact

- Arquivos de infraestrutura (Dockerfile, compose de produção, scripts, nginx), VPS, DNS, bucket externo, credenciais de produção de PayPal, Melhor Envio, Google e SMTP.
