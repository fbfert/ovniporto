# Prompt para o Claude da VPS

Cole o bloco abaixo no Claude Code rodando na VPS. Antes, copie a chave de deploy
do Windows para o servidor:

```
scp ~/.ssh/ovniporto_deploy usuario@IP_DA_VPS:~/.ssh/ovniporto_deploy
```

---

```
Você vai preparar esta VPS para hospedar o OVNIPORTO Lages (ovniporto.tars.art.br).
Fale comigo em português do Brasil. Trabalhe em fases, mostre o que encontrou em cada
fase e espere meu "ok" antes de passar para a próxima.

## Contexto

- Projeto: Laravel 11 (PHP 8.3) + Inertia + React/TypeScript com SSR, MySQL 8, Redis.
  Tudo roda em Docker (containers web/nginx, app/php-fpm, ssr/node, worker, mysql, redis).
- Repositório PRIVADO: git@github.com:fbfert/ovniporto.git, branch main.
- Acesso ao repositório: deploy key SOMENTE LEITURA em ~/.ssh/ovniporto_deploy
  (ed25519, fingerprint SHA256:FrPefsJe/CWG9SAqa+0MUlvh5/IYdPKhqYHUc4qYSOI).
- Leia o CLAUDE.md do repositório depois de clonar: ele é a fonte da verdade do projeto.
- Estado atual: a infraestrutura de PRODUÇÃO ainda não foi escrita. Ela está planejada
  na change openspec/changes/add-production-deploy/ (docker-compose.prod.yml, deploy.sh,
  nginx com HTTPS, backup, DEPLOY.md). Hoje só existe o docker-compose.yml de
  desenvolvimento, que usa o target "dev" das imagens.

## Regras

1. Não altere código nem arquivos versionados no servidor. A deploy key é só leitura;
   qualquer correção necessária no projeto você DESCREVE no relatório final e eu faço
   no repositório. Arquivos locais do servidor (.env, config do proxy, systemd, ssh)
   podem ser criados.
2. Esta VPS pode ter outros sites e serviços. Antes de mexer em portas 80/443, proxy
   reverso, firewall, Docker ou usuários, mostre o que existe e peça confirmação.
   Nunca derrube nem reconfigure um serviço existente sem eu autorizar.
3. Nunca mostre segredos na saída (senhas, APP_KEY, tokens). Gere senhas fortes com
   `openssl rand` e grave direto no .env com permissão 600.
4. Não invente credenciais de terceiros. Para Google OAuth, PayPal, Melhor Envio e SMTP,
   deixe a variável vazia e me pergunte. PayPal e Melhor Envio ficam em sandbox até o
   go-live.
5. Nada do site fica público nesta etapa: os containers escutam só em 127.0.0.1 e eu
   acesso por túnel SSH.
6. Antes de qualquer ação destrutiva (apagar volume, apagar banco, `docker system prune`,
   reinstalar pacote), peça confirmação.

## Fase 1: Reconhecimento (somente leitura)

- Sistema operacional, CPU, RAM, disco livre, swap.
- Docker e Docker Compose v2 instalados? Versões.
- O que escuta nas portas 80, 443, 3306, 6379 e 8080 (`ss -tlnp`).
- Proxy reverso existente (nginx, Caddy, Traefik) e quais domínios ele atende.
- Firewall (ufw/iptables) e regras atuais.
- DNS: para onde aponta ovniporto.tars.art.br (`dig +short`) e o IP público desta VPS.
- Relate tudo e proponha o plano das fases 2 a 4 adaptado ao que encontrou. Se a RAM
  for menor que 2 GB, avise: a build das imagens compila os assets com Node e pode
  precisar de swap.

## Fase 2: Acesso ao repositório e clone

- Confirme que ~/.ssh/ovniporto_deploy existe; se não existir, pare e me peça para
  copiá-la. Ajuste a permissão para 600.
- Adicione ao ~/.ssh/config:
    Host github-ovniporto
      HostName github.com
      User git
      IdentityFile ~/.ssh/ovniporto_deploy
      IdentitiesOnly yes
- Teste com `ssh -T github-ovniporto` (a resposta deve citar fbfert/ovniporto).
- Clone em /srv/ovniporto (ou no diretório que você propuser na fase 1):
  `git clone github-ovniporto:fbfert/ovniporto.git /srv/ovniporto`
- Leia o CLAUDE.md, o Makefile, o docker-compose.yml, docker/ e
  openspec/changes/add-production-deploy/ e resuma o que vai ser usado.

## Fase 3: Ambiente (.env)

- Crie o .env a partir do .env.example, com permissão 600:
  APP_ENV=production, APP_DEBUG=false, APP_URL=https://ovniporto.tars.art.br,
  LOG_LEVEL=warning, SESSION_SECURE_COOKIE=true, APP_LOCALE=pt_BR,
  DB_PASSWORD e DB_ROOT_PASSWORD fortes e gerados, PAYPAL_MODE=sandbox,
  MELHOR_ENVIO_ENV=sandbox.
- O .env.example ainda não lista as variáveis do Google: acrescente
  GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET e
  GOOGLE_REDIRECT_URI=https://ovniporto.tars.art.br/auth/google/callback (confira a
  rota real em routes/ antes de gravar). Anote no relatório que o .env.example precisa
  ser atualizado no repositório.
- No APP_PORT, use só 127.0.0.1 (o compose atual publica "${APP_PORT}:80"; ponha
  APP_PORT=127.0.0.1:8080).
- Liste para mim as variáveis que ficaram vazias e que eu preciso fornecer.

## Fase 4: Homologação privada com o compose atual

- `docker compose build` e `docker compose up -d` (ou `make up`).
- Gere a APP_KEY com `docker compose exec app php artisan key:generate --force`
  (se o .env estiver montado só para leitura, gere com `--show` e grave no .env).
- `make migrate`. NÃO rode `make seed-demo` sem me perguntar.
- Verifique: todos os containers "Up/healthy", `curl -fsS http://127.0.0.1:8080/up`,
  a home renderizando com SSR (o HTML já deve trazer o conteúdo, não só a div vazia),
  logs sem erro (`docker compose logs --tail=100`).
- Me passe o comando do túnel para eu abrir no meu PC:
  `ssh -L 8080:127.0.0.1:8080 usuario@IP_DA_VPS` e depois http://localhost:8080.
- Primeiro admin: depois que eu entrar com Google, promova minha conta mudando a
  coluna `role` da tabela `members` para 'admin' (confirme comigo o e-mail antes).

## Pare aqui

Não configure HTTPS público, DNS, backup nem cron de produção nesta etapa: isso
entra com a change add-production-deploy, quando docker-compose.prod.yml, deploy.sh
e DEPLOY.md existirem no repositório. Se ao clonar esses arquivos JÁ existirem, me
avise e siga o DEPLOY.md em vez das fases 3 e 4, ainda pedindo confirmação a cada
fase.

## Relatório final

Termine com:
1. O que foi instalado ou configurado no servidor (caminhos e comandos).
2. Estado de cada container e o resultado do /up.
3. Variáveis que faltam e quem precisa fornecer cada uma.
4. Problemas encontrados no projeto, com arquivo e sugestão de correção, para eu
   levar ao repositório (não corrija no servidor).
5. Riscos ou conflitos com outros serviços desta VPS.
```
