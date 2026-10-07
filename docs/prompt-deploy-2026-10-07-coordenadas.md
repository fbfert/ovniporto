# Prompt para o Claude da VPS: deploy das Coordenadas (07/10/2026)

Cole o bloco abaixo no Claude Code rodando na VPS. O deploy publica o topo de `main`:

- a área **Coordenadas** do painel (`/painel/coordenadas`), com SMTP, alertas e remetente do frete editáveis pelo admin;
- as correções da devolutiva do deploy anterior (testes fora do build e o aviso do Vite).

Esta versão **tem uma migration** (`create_operational_settings_table`). Ela só cria uma tabela nova; nenhuma tabela existente muda. A versão anterior no ar deve ser `1594fab` (manual do painel).

Depois do deploy, a configuração do SMTP é feita por você no navegador, não pelo Claude da VPS (veja o fim deste arquivo).

---

```
Você vai publicar uma nova versão do OVNIPORTO Lages (ovniporto.tars.art.br) nesta VPS.
Fale comigo em português do Brasil. Trabalhe em fases, mostre o resultado de cada uma e
espere meu "ok" antes da próxima.

## Contexto

- Projeto em /srv/ovniporto (confirme o caminho; se for outro, me diga).
- O deploy do dia a dia é `./deploy.sh`, descrito no DEPLOY.md, seção "Deploy do dia a dia".
  Ele busca origin/main, constrói as imagens, sobe, roda as migrações (sempre aditivas),
  aquece os caches, gera o sitemap, reinicia SSR e workers e confere /up. Se /up falhar,
  ele volta sozinho para a versão anterior.
- A versão anterior esperada em produção é 1594fab. O topo de origin/main é o commit
  "Add the Coordenadas panel area for mail, alerts and shipping settings"
  (confira com git log).
- O que muda nesta versão:
  1. Nova área do painel "Coordenadas" em /painel/coordenadas, só para admin. Ela ajusta
     SMTP, endereço de alertas, remetente do frete e embalagem padrão sem editar o .env.
     O valor salvo no painel vale no lugar do .env; campo vazio volta ao .env. Com nada
     salvo, o site se comporta exatamente como hoje. A senha do SMTP fica cifrada com a
     APP_KEY na tabela nova operational_settings. Os valores são aplicados a cada
     requisição, antes de cada tarefa dos workers e a cada volta do Horizon; nunca no
     boot, então o config:cache não os contém.
  2. A área mostra o estado das integrações que continuam no .env (Google, PayPal,
     Melhor Envio, Umami, geocodificação), sem nenhum valor.
  3. O início do painel ganhou o cartão "Coordenadas" (só admin).
  4. Build: os arquivos *.test.tsx não vão mais para public/build nem para o SSR.
     O aviso "advancedChunks option is deprecated" do Vite sumiu. O aviso de tamanho do
     ui-*.js (cerca de 660 kB) continua, de propósito: será tratado junto com o Lighthouse.
  5. DEPLOY.md: seção "Coordenadas (configuração pelo painel)", roteiro "SMTP parado"
     e o item 17 do checklist (alertas com destino).
  6. Manual do painel: capítulo novo "coordenadas" e revisão de outros cinco capítulos.
- Migrations novas: 2026_10_07_120000_create_operational_settings_table (só cria tabela).

## Regras

1. Não altere código nem arquivos versionados no servidor. Se algo precisar de correção
   no projeto, DESCREVA no relatório final; eu corrijo no repositório.
2. Nunca mostre segredos na saída (.env, senhas, tokens, conteúdo de operational_settings).
3. Não rode `make seed-demo`, `migrate:fresh`, `migrate:rollback`, nem apague volumes.
4. Antes de qualquer ação destrutiva ou fora do roteiro, peça confirmação.
5. Não mexa na configuração de SMTP, nem pelo .env nem pelo banco: isso agora é feito
   por mim no painel.

## Fase 1: Conferência antes (somente leitura)

- `cd /srv/ovniporto && git fetch origin && git log --oneline -3 origin/main`:
  confirme que o commit das Coordenadas está no topo e que 1594fab vem logo abaixo.
- Versão em produção agora: `cat .deploy/current` (esperado: 1594fab).
- Estado dos containers: `docker compose -f docker-compose.prod.yml ps`.
- Migrations pendentes (esperado: só a create_operational_settings_table depois do pull;
  antes do pull, nenhuma):
  `docker compose -f docker-compose.prod.yml exec -T app php artisan migrate:status | grep -i pending`
- Backup: como esta versão tem migration, confirme que existe backup de hoje. Se não
  existir, proponha rodar `scripts/backup.sh` antes e espere meu ok.
- Devolutiva anterior, item 4: compare o tamanho dos três últimos backups e diga se a
  queda de 71.896 para 61.640 bytes se repetiu ou se voltou a crescer (só tamanhos).

## Fase 2: Deploy

- `./deploy.sh`
- Mostre o fim do log. Se ele tiver voltado para a versão anterior, pare, mostre o erro
  e os logs (`docker compose -f docker-compose.prod.yml logs --tail=150 app ssr worker`)
  e não tente de novo sem falar comigo.

## Fase 3: Verificação

Rode e mostre o resultado de cada item:

1. Versão: `cat .deploy/current` deve ser o commit do topo de origin/main.
2. Saúde: `curl -fsS https://ovniporto.tars.art.br/up`.
3. Migration aplicada, tabela vazia:
   `docker compose -f docker-compose.prod.yml exec -T app php artisan tinker --execute="echo Schema::hasTable('operational_settings') ? 'tabela ok, linhas: '.DB::table('operational_settings')->count() : 'SEM TABELA';"`
   deve imprimir "tabela ok, linhas: 0".
4. Rotas registradas:
   `docker compose -f docker-compose.prod.yml exec -T app php artisan route:list --name=panel.coordinates`
   deve listar panel.coordinates, .update, .password.destroy, .test-mail e .test-alert.
5. Área protegida: `curl -s -o /dev/null -w '%{http_code} %{redirect_url}\n' https://ovniporto.tars.art.br/painel/coordenadas`
   deve responder 302 para /entrar (nunca 200 sem login, nunca 500).
6. Testes fora do build:
   `docker compose -f docker-compose.prod.yml exec -T app sh -c "ls public/build/assets bootstrap/ssr/assets | grep -ci test || true"`
   deve imprimir 0.
7. Workers e Horizon de pé depois do deploy:
   `docker compose -f docker-compose.prod.yml exec -T app php artisan horizon:status`.
8. Logs sem erro novo:
   `docker compose -f docker-compose.prod.yml logs --since=10m app worker ssr | grep -i -E "error|exception|DecryptException" | head`.
9. O manual carrega com o capítulo novo (18 capítulos):
    `docker compose -f docker-compose.prod.yml exec -T app php artisan tinker --execute="echo count(app(App\Domain\Manual\Contracts\ManualLibrary::class)->chapters());"`

## Relatório final

1. Versão anterior e versão publicada.
2. Resultado de cada item da Fase 3 (ok / falhou, com a saída relevante).
3. Tamanho dos últimos backups (item da Fase 1).
4. Problemas encontrados, com arquivo e sugestão de correção para eu levar ao repositório.
5. Lembrete para mim: configurar o SMTP em /painel/coordenadas e testar (passos abaixo).
```

---

## Depois do deploy: o que você faz no navegador

1. Entre como admin e abra **Painel → Coordenadas**.
2. Em **Correio**, preencha servidor, porta e segurança (587 com STARTTLS ou 465 com SSL/TLS), usuário, senha e o e-mail do remetente (do próprio domínio). Clique em **Salvar**.
3. Clique em **Enviar e-mail de teste** e leia a resposta.
   - **"O servidor recusou o usuário ou a senha"** (535) com usuário e senha conferidos: o problema é na caixa de e-mail (o Dovecot recusando o login), não no site. Corrija no servidor de e-mail e teste de novo.
   - **Enviado**: confira a caixa de entrada e o spam.
4. Em **Alertas**, defina um endereço que alguém lê todo dia, salve e use **Enviar alerta de teste**.
5. Em **Frete**, confira o remetente das etiquetas: o que vier do `.env` aparece apagado dentro do campo, como sugestão.
6. Com o teste passando, abra `/painel/filas` → **Failed** e use **Retry** nos e-mails que falharam enquanto o SMTP estava parado.
7. No checklist do `DEPLOY.md`, marque o item 9 (o e-mail de teste passa) e o item 17 (alertas com destino).
