# Tasks

## 1. Imagens e composição

- [ ] 1.1 Escrever Dockerfile multi-stage (sem Node no app, usuário não-root), verificado por `docker build` e `docker run --rm app whoami` diferente de root
- [ ] 1.2 Escrever `docker-compose.prod.yml` com web, app, ssr, worker, scheduler, mysql, redis, umami e postgres, verificado por `docker compose -f docker-compose.prod.yml up` com todos os serviços saudáveis
- [ ] 1.3 Configurar nginx (gzip/brotli, HTTP/2, cache de estáticos com hash) e proxy reverso com HTTPS e redirecionamentos, verificado por `curl -I` em HTTP, `www` e estáticos

## 2. Deploy e segurança

- [ ] 2.1 Escrever `deploy.sh` idempotente com health check e rollback, verificado executando duas vezes e simulando falha de `/up`
- [ ] 2.2 Configurar CSP (report-only no staging, depois aplicada), HSTS, X-Frame-Options e rate limits, verificado por testes de feature de 429 e checagem dos cabeçalhos
- [ ] 2.3 Garantir segredos só por variáveis, verificado por varredura de segredos (ex.: gitleaks) sem achados

## 3. Backup e monitoramento

- [ ] 3.1 Escrever script de backup criptografado com envio ao bucket e expiração de 30 dias, verificado por restauração completa em ambiente limpo documentada
- [x] 3.2 Proteger Horizon e Pulse por papel admin e configurar alertas de fila e de 3 falhas, verificado por teste de feature de acesso e job de falha forçada gerando e-mail
- [ ] 3.3 Configurar uptime externo em `/up`, verificado por alerta recebido ao derrubar o serviço em staging

## 4. Go-live

- [ ] 4.1 Escrever `DEPLOY.md` com o checklist completo, verificado por revisão de cada item com evidência
- [ ] 4.2 Executar o checklist em produção (DNS, certificados, webhooks PayPal, Melhor Envio, SMTP, Google OAuth, Umami, backup, 404/500, Lighthouse), verificado por checklist assinado no `DEPLOY.md`
