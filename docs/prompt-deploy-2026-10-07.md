# Prompt para o Claude da VPS: deploy de 07/10/2026

Cole o bloco abaixo no Claude Code rodando na VPS. Ele publica o topo de `main`: o manual de
operação dentro do painel (`/painel/manual`, com mapas mentais e o link "Como funciona" em
cada tela), o botão "Salvar e voltar" no cadastro de produto e as medidas de embalagem com
décimos de milímetro.

Esta versão **não tem migration** e não muda nada público fora da loja. A versão anterior
no ar deve ser cc5552f (casos históricos do Livro de avistamentos, deploy de 05/10).

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
- A versão anterior esperada em produção é cc5552f. O topo de origin/main é o commit
  "Add the panel operations manual with mind maps" (confira com git log).
- O que muda nesta versão:
  1. Nova área do painel "Manual" em /painel/manual e /painel/manual/{capitulo}, aberta a
     admin, moderação e loja. São 17 capítulos versionados em resources/content/manual/*.json,
     lidos e validados pelo servidor (cache pela data do arquivo). Cada papel só vê os
     capítulos das áreas que abre; capítulo fechado ao papel responde 404.
  2. Toda tela do painel ganhou o link "Como funciona" no topo, que leva à seção do manual
     daquela tela. O início do painel ganhou um link para o manual.
  3. /painel/produtos/novo: botão "Salvar e voltar" (cria e volta para a lista).
  4. Medidas da embalagem dos produtos aceitam até duas casas decimais em cm
     (0,01 cm = 0,1 mm). A loja mostra as medidas com vírgula. O frete não muda.
  5. DEPLOY.md: seção "Manual do painel", correção do caminho /painel/pedidos no checklist
     de restauração e do comportamento do aviso de fila longa (só vai para ALERTS_EMAIL).
  6. Arquivos de desenvolvimento que não afetam produção: .claude/settings.json e
     .claude/hooks/manual-reminder.mjs (lembrete para o Claude Code atualizar o manual),
     testes, specs do OpenSpec e docs/panel-findings-2026-10-07.md.
- Migrations novas: nenhuma.

## Regras

1. Não altere código nem arquivos versionados no servidor. Se algo precisar de correção
   no projeto, DESCREVA no relatório final; eu corrijo no repositório.
2. Nunca mostre segredos na saída (.env, senhas, tokens).
3. Não rode `make seed-demo`, `migrate:fresh`, `migrate:rollback`, nem apague volumes.
4. Antes de qualquer ação destrutiva ou fora do roteiro, peça confirmação.

## Fase 1: Conferência antes (somente leitura)

- `cd /srv/ovniporto && git fetch origin && git log --oneline -3 origin/main`:
  confirme que o commit do manual está no topo e que cc5552f vem logo abaixo
  (pode haver um commit intermediário, se eu tiver publicado algo depois: me mostre).
- Versão em produção agora: `cat .deploy/current` (esperado: cc5552f).
- Estado dos containers: `docker compose -f docker-compose.prod.yml ps`.
- Migrations pendentes (esperado: nenhuma):
  `docker compose -f docker-compose.prod.yml exec -T app php artisan migrate:status | grep -i pending`
- Confirme que o último backup do banco é de hoje ou de ontem (DEPLOY.md, seção "Backup").
  Se não for, me avise antes de seguir: proponho rodar um backup manual.

## Fase 2: Deploy

- `./deploy.sh`
- Mostre o fim do log. Se ele tiver voltado para a versão anterior, pare, mostre o erro
  e os logs (`docker compose -f docker-compose.prod.yml logs --tail=150 app ssr worker`)
  e não tente de novo sem falar comigo.

## Fase 3: Verificação

Rode e mostre o resultado de cada item:

1. Versão: `cat .deploy/current` deve ser o commit do topo de origin/main.
2. Saúde: `curl -fsS https://ovniporto.tars.art.br/up`.
3. Rotas do manual registradas:
   `docker compose -f docker-compose.prod.yml exec -T app php artisan route:list --name=panel.manual`
   deve listar panel.manual e panel.manual.show.
4. Os 17 capítulos carregam sem erro de formato (um capítulo quebrado deixaria o manual fora do ar e tiraria o "Como funciona", sem derrubar o resto do painel):
   `docker compose -f docker-compose.prod.yml exec -T app php artisan tinker --execute="echo count(app(App\Domain\Manual\Contracts\ManualLibrary::class)->chapters());"`
   deve imprimir 17.
5. "Como funciona" resolve para a loja:
   `docker compose -f docker-compose.prod.yml exec -T app php artisan tinker --execute="echo app(App\Application\Manual\UseCases\FindManualSection::class)->execute(App\Domain\Members\MemberRole::Store, 'panel.products.create');"`
   deve imprimir /painel/manual/produtos#novo.
6. Manual protegido: `curl -s -o /dev/null -w '%{http_code} %{redirect_url}\n' https://ovniporto.tars.art.br/painel/manual`
   deve responder 302 para /entrar (nunca 200 sem login, nunca 500).
7. Loja intacta: `curl -s -o /dev/null -w '%{http_code}\n' https://ovniporto.tars.art.br/loja` responde 200,
   e a página de um produto ativo também (pegue um slug com
   `php artisan tinker --execute="echo App\Models\Product::where('is_active',true)->value('slug');"`).
   Se esse produto tiver medidas, o HTML contém "Embalagem:".
8. Alertas: diga só se ALERTS_EMAIL está preenchido ou vazio no .env (sem mostrar o valor).
   Se estiver vazio, avise: o aviso de fila longa do Horizon não vai para ninguém.
9. Logs sem erro novo:
   `docker compose -f docker-compose.prod.yml logs --since=10m app ssr | grep -i -E "error|exception|InvalidManualData" | head`.

## Relatório final

1. Versão anterior e versão publicada.
2. Resultado de cada item da Fase 3 (ok / falhou, com a saída relevante).
3. Problemas encontrados, com arquivo e sugestão de correção para eu levar ao repositório.
4. Lembrete para mim: entrar no painel como admin e conferir no navegador
   (computador e celular) /painel/manual, um capítulo com o mapa mental e o link
   "Como funciona" em /painel/produtos/novo e em /painel/pedidos.
```
