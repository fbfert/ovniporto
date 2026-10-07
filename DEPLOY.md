# Deploy do OVNIPORTO (VPS)

Produção roda em uma VPS no Brasil com `docker-compose.prod.yml`. Os serviços são:

| Serviço | Função |
|---|---|
| web | nginx sem root, porta 8080 |
| app | php-fpm |
| ssr | Node |
| worker | Horizon |
| scheduler | tarefas agendadas |
| pulse | dados do painel de saúde |
| mysql | banco do site |
| redis | filas e cache |
| umami + umami-db | métrica sem cookie |
| caddy | opcional, só no perfil `caddy` |

Na frente fica um proxy reverso com HTTPS. Há duas formas: usar o proxy que a VPS já tem, ou deixar o Caddy deste repositório cuidar das portas 80 e 443.

Os segredos ficam só no `.env` da VPS (`chmod 600`), nunca no Git. O modelo está em `.env.production.example`.

## Primeiro deploy

1. **Pré-requisitos na VPS:** Docker com o plugin compose, `git`, `curl`, `age` e `rclone`.
2. **Clonar com a deploy key só de leitura:**
   ```sh
   git clone git@github.com:fbfert/ovniporto.git /srv/ovniporto
   cd /srv/ovniporto
   ```
3. **Criar o `.env`:**
   - `cp .env.production.example .env && chmod 600 .env`.
   - Preencha todas as chaves; gere senhas com `openssl rand -base64 32`.
   - `APP_KEY` sai de `docker compose -f docker-compose.prod.yml run --rm --no-deps app php artisan key:generate --show`.
4. **Escolher o proxy** (seção abaixo).
5. **Rodar o deploy:** `./deploy.sh`. Ele constrói, sobe, migra e confere `/up`.
6. **Primeiro admin:** entre com o Google pelo site e complete o perfil. Depois promova a conta (troque o e-mail):
   ```sh
   docker compose -f docker-compose.prod.yml exec mysql sh -c \
     'mysql -u root -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" -e "UPDATE members SET role = '\''admin'\'' WHERE email = '\''voce@exemplo.com'\''"'
   ```
   Os próximos papéis são dados pelo próprio painel, em /painel/membros.
7. **Configurar o Umami:**
   - Abra o painel da métrica e troque a senha padrão (admin/umami).
   - Cadastre o site e copie o id para `UMAMI_WEBSITE_ID`.
   - Rode `./deploy.sh` de novo para aplicar o `.env`.
8. **Agendar o backup** (seção Backup) e o **uptime externo** (seção Monitoramento).

## Proxy reverso

O site responde em `WEB_BIND`, que por padrão é `127.0.0.1:8080`. A métrica responde em `UMAMI_BIND`, por padrão `127.0.0.1:3001`.

### A. A VPS já tem proxy (nginx, Caddy, Traefik, painel)

Crie dois hosts.

**Host do site:** `ovniporto.tars.art.br` → `http://127.0.0.1:8080`.
- Certificado Let's Encrypt e HTTP/2 ligados.
- Redirecionar `http://` → `https://`.
- Redirecionar `www.ovniporto.tars.art.br` → domínio sem www (301).
- Repassar os cabeçalhos `Host`, `X-Forwarded-For`, `X-Forwarded-Proto` e `X-Forwarded-Host`. O Laravel confia em proxies de 127.0.0.1 e das redes privadas.
- Limite de upload de pelo menos 24 MB (`client_max_body_size 24m` no nginx).
- Não é preciso comprimir no proxy, porque o nginx do site já entrega gzip. Se o proxy fizer brotli ou zstd, melhor ainda.

**Host da métrica:** `metrica.ovniporto.tars.art.br` → `http://127.0.0.1:3001`.

Exemplo para um nginx já existente:

```nginx
server {
    listen 443 ssl http2;
    server_name ovniporto.tars.art.br;
    # ssl_certificate ... (certbot)
    client_max_body_size 24m;
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
    }
}
server { listen 80; server_name ovniporto.tars.art.br www.ovniporto.tars.art.br; return 301 https://ovniporto.tars.art.br$request_uri; }
server { listen 443 ssl http2; server_name www.ovniporto.tars.art.br; return 301 https://ovniporto.tars.art.br$request_uri; }
```

### B. Portas 80/443 livres: Caddy do repositório

Use esta opção quando nada mais ocupa as portas 80 e 443.

1. No `.env`, defina `ACME_EMAIL`, `APP_DOMAIN`, `METRIC_DOMAIN` e `COMPOSE_PROFILES=caddy`. Com isso, o `deploy.sh`, o backup e qualquer `docker compose` sobem o Caddy junto.
2. Rode `./deploy.sh`.

O Caddy obtém os certificados sozinho e entrega HTTP/2 e HTTP/3, o redirecionamento de www e HTTP, e compressão zstd/gzip (`docker/caddy/Caddyfile`).

### Conferência (os dois casos)

```sh
curl -I http://ovniporto.tars.art.br/            # 301 para https
curl -I https://www.ovniporto.tars.art.br/       # 301 para o domínio sem www
curl -I --http2 https://ovniporto.tars.art.br/   # HTTP/2 200 com Strict-Transport-Security e Content-Security-Policy
curl -sI -H 'Accept-Encoding: gzip' https://ovniporto.tars.art.br/build/manifest.json | grep -i 'content-encoding\|cache-control'
curl -sI https://ovniporto.tars.art.br/build/assets/<arquivo>.js | grep -i cache-control   # max-age=31536000, immutable
```

## Deploy do dia a dia

```sh
cd /srv/ovniporto && ./deploy.sh
```

O script faz, em ordem:
1. Busca `origin/main` (ou `DEPLOY_REF=<sha>`).
2. Constrói as imagens com a tag do commit.
3. Sobe os serviços.
4. Roda as migrações, que são sempre aditivas.
5. Aquece os caches e gera o sitemap.
6. Reinicia o SSR e os workers.
7. Confere `/up`.

Se `/up` não responder, ele volta para a versão anterior (`.deploy/current` e `.deploy/previous`) e termina com erro. Rodar de novo sem nada novo não muda nada.

Para testar a volta automática, use `SIMULATE_FAILURE=1 DEPLOY_REF=<outro commit> ./deploy.sh`: a versão nova é tratada como fora do ar, a anterior volta e é conferida em `/up`.

**Voltar manualmente:**
```sh
DEPLOY_REF=$(cat .deploy/previous) ./deploy.sh
```

As migrações não são desfeitas. Como elas só acrescentam, o código anterior continua funcionando com o banco novo.

## Backup

`scripts/backup.sh` gera um único arquivo criptografado com:
- o dump do MySQL;
- o dump do Umami;
- os arquivos do volume `storage` (fotos e imagens geradas).

O arquivo é cifrado com `age` e enviado ao Google Drive com `rclone`. Cópias com mais de 30 dias são apagadas lá.

### Configurar uma vez

1. **Gere o par de chaves age no seu computador**, não na VPS:
   ```sh
   age-keygen -o ovniporto-backup.key
   ```
   - Guarde `ovniporto-backup.key` fora da VPS: no gerenciador de senhas e numa cópia offline. Sem ela nenhum backup abre.
   - A linha `# public key: age1...` é o destinatário.
2. **Conecte o Google Drive na VPS:**
   - Rode `rclone config`, crie um remote `gdrive` do tipo `drive` com escopo `drive.file`.
   - Como a VPS não tem navegador, use `rclone authorize "drive"` no seu computador e cole o token.
   - Crie a pasta: `rclone mkdir gdrive:ovniporto-backups`.
3. **Crie `.env.backup` na raiz do repositório** (`chmod 600`):
   ```sh
   BACKUP_AGE_RECIPIENT=age1...
   BACKUP_REMOTE=gdrive:ovniporto-backups
   BACKUP_RETENTION_DAYS=30
   ```
4. **Teste:** `scripts/backup.sh`, depois `rclone ls gdrive:ovniporto-backups`.
5. **Agende no cron do root,** todo dia às 3h10 (horário da VPS):
   ```
   10 3 * * * /srv/ovniporto/scripts/backup.sh >> /var/log/ovniporto-backup.log 2>&1
   ```

### Restaurar um backup

1. Num ambiente limpo, faça o primeiro deploy (passos 1 a 5) com um `.env` equivalente.
2. Copie a chave privada só para a restauração: `scp ovniporto-backup.key vps:/root/`.
3. Restaure:
   ```sh
   scripts/restore.sh ovniporto-AAAAMMDDTHHMMSSZ.tar.age /root/ovniporto-backup.key
   ```
   Para ver os nomes disponíveis: `rclone ls gdrive:ovniporto-backups`.
4. Apague a chave: `shred -u /root/ovniporto-backup.key`.
5. Confira:
   - `/up`;
   - o mapa com os relatos;
   - a foto de um relato aprovado;
   - um pedido em /painel/pedidos;
   - a métrica.

Registre a restauração completa em `openspec/changes/add-production-deploy/evidence/restore.md`, com data, arquivo usado e o que foi conferido.

## Monitoramento

- **Painéis (só admin):**
  - Filas (Horizon): `/painel/filas`.
  - Saúde (Pulse): `/painel/saude`.
- **Alertas por e-mail:**
  - Um job que falha 3 vezes envia e-mail para o endereço de alertas; sem endereço, vai para todos os admins.
  - Espera longa na fila (mais de 60 s) gera aviso pelo Horizon, mas só para o endereço de alertas: sem ele, esse aviso não vai para ninguém.
  - O endereço de alertas é o de **Painel → Coordenadas → Alertas**; vazio lá, vale o `ALERTS_EMAIL` do `.env`.
  - Os dois alertas saem pelo SMTP do site: se o envio não funciona, nenhum alerta chega, e o uptime externo abaixo é o único aviso que sai do servidor. Confira com **Enviar alerta de teste** nas Coordenadas.
- **Uptime externo:**
  - Cadastre `https://ovniporto.tars.art.br/up` num monitor externo (UptimeRobot, Better Stack ou Healthchecks), conferindo a cada 5 minutos, com alerta por e-mail.
  - Teste no staging parando o web (`docker compose -f docker-compose.prod.yml stop web`) e confirme que o alerta chegou.
- **Logs:**
  - `docker compose -f docker-compose.prod.yml logs -f app worker`.
  - nginx no volume `nginx-logs`, com rotação de 6 meses.

## Coordenadas (configuração pelo painel)

Em `/painel/coordenadas` (só admin) ficam configurações que antes exigiam editar o `.env`:

| Bloco | Substitui no `.env` |
|---|---|
| Correio | `MAIL_HOST`, `MAIL_PORT`, `MAIL_SCHEME`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` |
| Alertas | `ALERTS_EMAIL` |
| Frete | `SHIPPING_SENDER_*`, `MELHOR_ENVIO_FROM_POSTAL_CODE`, `SHIPPING_PACKAGE_*` |

- **Regra:** o valor salvo no painel vale no lugar do `.env`; campo vazio volta ao `.env`. Com nada salvo, o site se comporta exatamente como antes. O `.env` continua sendo o valor de reserva: não apague essas chaves.
- Valem sem reiniciar: o site aplica a cada requisição; os workers antes de cada tarefa; o mestre do Horizon a cada volta. Nada disso entra no `config:cache` (a senha nunca vai para `bootstrap/cache`).
- A senha do SMTP fica na tabela `operational_settings`, cifrada com a `APP_KEY`. **Trocar a `APP_KEY` torna essa senha ilegível:** depois de uma troca, salve a senha de novo no painel. O backup do banco leva a senha cifrada, mas não o `.env`: ao restaurar com outra `APP_KEY`, salve a senha de novo no painel.
- Com servidor SMTP salvo no painel e `MAIL_MAILER=log` no `.env`, o site passa a enviar por SMTP.
- Continuam **só** no `.env`: `APP_KEY`, banco, Redis, Google, PayPal, Melhor Envio (token e ambiente), Umami e `CSP_MODE`. A tela mostra o estado dessas integrações (configurada, sandbox ou produção) sem valores.
- Toda alteração vai para a Auditoria ("mudou as coordenadas"), com a senha só como "alterada". Tenha **dois admins**: quem muda o Correio pode desviar os e-mails do site.

### SMTP parado (login recusado)

1. Em **Coordenadas → Correio**, confira servidor, porta e segurança (587 com STARTTLS ou 465 com SSL/TLS), usuário e senha. Salve.
2. Clique em **Enviar e-mail de teste**. A tela mostra a resposta do servidor (sem usuário nem senha).
3. Se vier **535** (login recusado) com usuário e senha conferidos, o problema está na caixa de e-mail, no servidor de e-mail (por exemplo, o Dovecot recusando o login ou a conta sem permissão de SMTP), e não no site.
4. Quando o teste passar, reenvie as tarefas de e-mail que falharam em `/painel/filas` (**Failed → Retry**).

## Manual do painel

- Fica em `/painel/manual`, aberto a admin, moderação e loja; cada papel vê só os capítulos das áreas que abre. Cada tela do painel tem o link **Como funciona** para a seção dela.
- O conteúdo é versionado em `resources/content/manual/<capítulo>.json` (texto em Markdown, mapa mental, rotas citadas e `reviewedAt`) e vai para produção junto com o código, no deploy normal. Não há edição pelo painel.
- **Toda mudança no painel atualiza o capítulo no mesmo commit.** O `PanelManualTest` falha se uma rota ou tela do painel ficar sem capítulo, ou se um capítulo citar rota que não existe; o hook `.claude/hooks/manual-reminder.mjs` (registrado em `.claude/settings.json`) lembra qual capítulo revisar.
- Um capítulo quebrado é recusado com uma mensagem que nomeia o capítulo e o nó, e a suíte de testes acusa antes do deploy. Se mesmo assim chegar a produção, só o manual fica fora do ar e o link **Como funciona** some; o resto do painel continua, e o erro vai para o log.

## CSP

Em produção o padrão é `CSP_MODE=enforce`. No staging, comece com `report-only`:
1. Pague com o PayPal sandbox.
2. Abra o mapa, /o-lugar e a métrica.
3. Procure "CSP violation" nos logs do app.

Mude para `enforce` só quando não houver violação legítima.

## Checklist de go-live

Marque cada item com data e evidência (print, saída de comando ou link).

| # | Item | Como conferir | Feito em / evidência |
|---|---|---|---|
| 1 | DNS: `ovniporto`, `www.ovniporto` e `metrica.ovniporto` apontam para a VPS | `dig +short ovniporto.tars.art.br` | |
| 2 | Certificados válidos nos três hosts | `curl -vI https://...` sem erro | |
| 3 | Redirecionamentos HTTP→HTTPS e www→sem www | `curl -I` da seção Conferência | |
| 4 | `.env` completo, `APP_DEBUG=false`, `chmod 600` | `stat -c %a .env` = 600 | |
| 5 | Todos os serviços saudáveis | `docker compose -f docker-compose.prod.yml ps` | |
| 6 | Google OAuth: URI de retorno `https://ovniporto.tars.art.br/auth/google/callback` no console do Google | entrar no site | |
| 7 | PayPal live: credenciais, webhook `https://ovniporto.tars.art.br/webhooks/paypal` e `PAYPAL_WEBHOOK_ID` | pedido real de valor baixo e estorno | |
| 8 | Melhor Envio produção: token e CEP de origem | cotação no carrinho | |
| 9 | SMTP: SPF, DKIM e DMARC do domínio de envio | **Enviar e-mail de teste** em Coordenadas passa, e o e-mail de pedido chega fora do spam | |
| 10 | Umami: senha trocada, site cadastrado, eventos chegando | painel da métrica | |
| 11 | Backup: primeiro envio ao Drive e restauração completa registrada | `evidence/restore.md` | |
| 12 | Uptime externo ativo e alerta testado | e-mail do monitor | |
| 13 | Páginas 404 e 500 com a identidade do site | `/nao-existe`; 500 no staging | |
| 14 | CSP aplicada sem violações legítimas | logs | |
| 15 | Lighthouse no celular (home, /mapa, /loja) com LCP < 2,5 s | relatório salvo | |
| 16 | Primeiro admin criado; Horizon e Pulse abrem só para ele | `/painel/filas` deslogado → 403 | |
| 17 | Alertas com destino | **Enviar alerta de teste** em Coordenadas chega | |

Assinado por: ______________________ Data: ____/____/______
