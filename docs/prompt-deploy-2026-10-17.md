# Prompt para o Claude da VPS: deploy de privacidade, devoluções e frete incluso (17/10/2026)

Cole o bloco abaixo no Claude Code rodando na VPS. O deploy publica o topo de `main` (`0cedd8c`):

- textos-modelo de **/privacidade** e **/termos** preenchidos com os dados do encarregado (continuam marcados como rascunho até a aprovação);
- declaração de **idade mínima (16+)** no cadastro e no reaceite, e caixa **18+ ou autorização do responsável** no checkout;
- **descadastro por link** no Avise-me e na colaboração, com a coluna `link_descadastro` nos CSV do painel;
- **retenção**: contas sem acesso por 5 anos são anonimizadas, e pedidos pagos há mais de 5 anos também;
- formulário **/devolucao** com a área **Pedidos → Devoluções** no painel, e **frete incluso** por produto;
- limite de envios contado **por formulário**, sem que um formulário bloqueie outro;
- **login do app Android** (`/auth/google/token`) e sessões lembradas (commit do Felipe).

Esta versão **tem migrations que alteram tabelas existentes** (`members`, `consents`, `products`, `orders`), então o backup do dia é obrigatório antes.

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
- O topo de origin/main deve ser 0cedd8c ("Merge the app sign-in into main and end
  remembered sessions on anonymization"). Confira com git log.
- O que muda nesta versão:
  1. Privacidade e termos: duas migrations de dados preenchem os blocos privacy_body e
     terms_body com os textos-modelo SÓ quando o bloco está vazio ou ainda tem o esqueleto
     ("Tópico a ser redigido" ou "[A preencher"). Texto já editado no painel não é tocado.
     As páginas continuam com o aviso de rascunho até alguém marcar "final" no painel.
  2. Idade mínima: membros já cadastrados vão para /termos/aceitar no próximo acesso e
     precisam marcar "Tenho 16 anos ou mais". Isso é esperado.
  3. Checkout: caixa obrigatória "18+ ou autorização do responsável", registrada como
     consentimento compra_autorizada.
  4. Descadastro: rotas assinadas GET/POST /descadastrar/{avise-me|colaboracao}/{id}. Os
     e-mails do Avise-me passam a levar o link e o cabeçalho List-Unsubscribe.
  5. Retenção: colunas novas em members (last_seen_at, inactivity_notice_sent_at,
     inactivity_alerted_at, anonymized_at). A migration preenche last_seen_at com
     updated_at. Rotinas no scheduler: members:anonymize-inactive às 03:15 (aviso 30 dias
     antes, depois anonimiza; contas com papel no painel ficam fora e só geram alerta aos
     admins) e privacy:purge-orders às 03:30 (agora também anonimiza pedidos pagos há
     mais de 5 anos). Nenhuma conta do site tem 5 anos, então nada deve ser anonimizado.
  6. Devoluções: página pública /devolucao, tabelas novas de pedidos de devolução, área
     /painel/pedidos/devolucoes (papéis store e admin). E-mail para a equipe em
     RETURNS_EMAIL (padrão contato@tars.art.br; também ajustável em Coordenadas).
  7. Frete incluso: campo novo em products; o comprador paga só a parte do frete dos
     itens sem frete incluso. orders ganha colunas do frete cobrado e do custo cotado.
  8. Limites de envio: o alias throttle agora conta cada rota separadamente
     (App\Http\Middleware\ThrottlePerRoute). Os limites continuam os mesmos.
  9. Login do app: POST /auth/google/token e coluna remember_token em members.
- Migrations novas (todas aditivas):
  2026_10_15_000000_add_remember_token_to_members_table (pode já ter rodado, se o
  commit do Felipe foi publicado antes)
  2026_10_16_000100_fill_legal_model_texts
  2026_10_16_000200_refresh_legal_model_texts
  2026_10_16_000300_add_withdrawn_at_to_consents
  2026_10_16_000400_add_retention_columns
  2026_10_16_000500_add_included_shipping
  2026_10_16_000600_create_return_requests_tables
- Variáveis de ambiente novas, todas opcionais (os padrões já estão certos, não precisa
  adicionar ao .env): RETURNS_EMAIL, PRIVACY_INACTIVE_ACCOUNT_YEARS (5),
  PRIVACY_INACTIVITY_NOTICE_DAYS (30).

## Regras

1. Não altere código nem arquivos versionados no servidor. Se algo precisar de correção
   no projeto, DESCREVA no relatório final; eu corrijo no repositório.
2. Nunca mostre segredos na saída (.env, senhas, tokens, e-mails de membros).
3. Não rode seeders, `make seed-demo`, `migrate:fresh`, `migrate:rollback`, nem apague volumes.
4. Não rode à mão `members:anonymize-inactive` nem `privacy:purge-orders`.
5. Não marque privacidade nem termos como "final": isso é feito no painel depois da
   aprovação do encarregado.
6. Antes de qualquer ação destrutiva ou fora do roteiro, peça confirmação.

## Fase 1: Conferência antes (somente leitura)

- `cd /srv/ovniporto && git fetch origin && git log --oneline -5 origin/main`:
  confirme 0cedd8c no topo.
- Versão em produção agora: `cat .deploy/current`. Diga qual é.
- Estado dos containers, incluindo o scheduler:
  `docker compose -f docker-compose.prod.yml ps`.
- Migrations pendentes antes do pull (esperado: nenhuma):
  `docker compose -f docker-compose.prod.yml exec -T app php artisan migrate:status | grep -i pending`
- Backup: esta versão altera tabelas existentes. Confirme que existe backup de hoje. Se não
  existir, proponha rodar `scripts/backup.sh` e espere meu ok. Mostre o tamanho dos três
  últimos backups.

## Fase 2: Deploy

- `./deploy.sh`
- Mostre o fim do log. Se ele tiver voltado para a versão anterior, pare, mostre o erro
  e os logs (`docker compose -f docker-compose.prod.yml logs --tail=150 app ssr worker scheduler`)
  e não tente de novo sem falar comigo.

## Fase 3: Verificação

Rode e mostre o resultado de cada item:

1. Versão: `cat .deploy/current` deve ser 0cedd8c.
2. Saúde: `curl -fsS https://ovniporto.tars.art.br/up`.
3. Migrations aplicadas: `docker compose -f docker-compose.prod.yml exec -T app php artisan migrate:status | grep -E "2026_10_1[56]"`
   deve mostrar as 7 como Ran.
4. Textos legais preenchidos e ainda em rascunho:
   `curl -s https://ovniporto.tars.art.br/privacidade | grep -c "62.881.282"` deve ser 1 ou mais;
   `curl -s https://ovniporto.tars.art.br/termos | grep -c "Lages"` deve ser 1 ou mais.
   Diga também se as duas páginas ainda mostram o aviso de rascunho.
   Se o CNPJ não aparecer, NÃO edite nada: diga o que a página mostra (o bloco pode ter
   sido editado no painel antes, e aí a migration o preservou de propósito).
5. Rotas registradas:
   `docker compose -f docker-compose.prod.yml exec -T app php artisan route:list | grep -E "devolucao|devolucoes|descadastrar|auth/google/token"`
   deve listar returns.create, returns.store, panel.orders.returns, .show, .status,
   unsubscribe.show, unsubscribe.store e auth.google.token.
6. Páginas públicas e protegidas:
   `for p in /devolucao /painel/pedidos/devolucoes; do curl -s -o /dev/null -w "$p %{http_code} %{redirect_url}\n" https://ovniporto.tars.art.br$p; done`
   /devolucao deve dar 200; o painel deve dar 302 para /entrar.
7. Link de descadastro adulterado é recusado:
   `curl -s -o /dev/null -w '%{http_code}\n' "https://ovniporto.tars.art.br/descadastrar/avise-me/1?signature=x"`
   deve dar 403.
8. Rotinas agendadas:
   `docker compose -f docker-compose.prod.yml exec -T app php artisan schedule:list`
   deve listar members:anonymize-inactive (03:15) e privacy:purge-orders (03:30), e o
   container scheduler deve estar Up.
9. Ninguém perto da anonimização (esperado: 0 e 0):
   `docker compose -f docker-compose.prod.yml exec -T app php artisan tinker --execute="echo DB::table('members')->whereNull('last_seen_at')->count().' sem last_seen_at; '.DB::table('members')->where('last_seen_at','<=',now()->subYears(5)->addDays(30))->count().' perto dos 5 anos';"`
10. Workers e Horizon: `docker compose -f docker-compose.prod.yml exec -T app php artisan horizon:status`.
11. Logs sem erro novo:
    `docker compose -f docker-compose.prod.yml logs --since=10m app worker ssr scheduler | grep -i -E "error|exception" | head`.
12. Manual do painel com 18 capítulos:
    `docker compose -f docker-compose.prod.yml exec -T app php artisan tinker --execute="echo count(app(App\Domain\Manual\Contracts\ManualLibrary::class)->chapters());"`

## Relatório final

1. Versão anterior e versão publicada.
2. Resultado de cada item da Fase 3 (ok / falhou, com a saída relevante).
3. Tamanho dos últimos backups.
4. Problemas encontrados, com arquivo e sugestão de correção para eu levar ao repositório.
5. Lembrete para mim: as conferências no navegador listadas no fim deste arquivo.
```

---

## Depois do deploy: o que você faz no navegador

1. **Reaceite:** entre com sua conta. Você deve cair em `/termos/aceitar` com a caixa "Tenho 16 anos ou mais". Marque e confirme que volta para a página que abriu.
2. **Textos legais:** abra `/privacidade` e `/termos`, confira os dados (CNPJ, encarregado, Xiax, foro de Lages) e mande os links ao Felipe. Eles continuam como rascunho. Quando ele aprovar, marque **os dois como final juntos** em **Painel → Conteúdo → Configurações**. Isso pede um novo aceite a todos os membros.
3. **Avise-me (tarefa 6.1):** inscreva um e-mail seu no Avise-me. No e-mail que chegar, confira o link "descadastrar" e, no Gmail, a opção "Cancelar inscrição" ao lado do remetente (é o cabeçalho List-Unsubscribe). Abra o link, confirme e veja a página dizer que o cancelamento foi feito.
4. **Frete incluso (tarefa 7.2):** em **Painel → Produtos** (`/painel/produtos`), marque um produto como **Frete incluso**. Monte um carrinho com esse produto e outro sem, faça a cotação para um CEP de fora de Lages e confira que o frete cobrado é menor que a cotação do carrinho inteiro. Um carrinho só com o produto de frete incluso deve dar frete zero.
5. **Devolução:** em **Painel → Coordenadas → Alertas**, confira o endereço de devoluções (padrão `contato@tars.art.br`). Se houver um pedido pago de teste, envie `/devolucao` com o número e o e-mail dele, confira os dois e-mails (confirmação com protocolo e aviso da equipe) e leve o pedido até **Concluído** em **Painel → Pedidos → Devoluções**.
6. **CSV:** em **Painel → Campanha** (`/painel/campanha`), exporte o CSV do Avise-me e confira a coluna `link_descadastro`.
7. Com as conferências 3 e 4 ok, me avise para eu marcar as tarefas 6.1 e 7.2 e arquivar as changes.
