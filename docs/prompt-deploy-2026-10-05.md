# Prompt para o Claude da VPS: deploy de 05/10/2026

Cole o bloco abaixo no Claude Code rodando na VPS. Ele publica o topo de `main`
(Cachi como referência, colaboradores do Atlas, menu novo, espaços de /o-lugar e a página
do relato do carro amarelo).

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
- Commits desta versão em origin/main: 5ddfce6 "Make Cachi a search reference, add Atlas
  collaborators and fill the place spaces" e, logo depois, o commit da página do relato
  do carro amarelo.
- O que muda nesta versão:
  1. /origem/cachi: título e descrição novos, seção "Em poucas palavras", JSON-LD em
     grafo (Article, Place, Person, BreadcrumbList, FAQPage) e lastmod no sitemap.
  2. Nova rota pública /llms.txt (texto simples para assistentes de IA).
  3. /origem/atlas: candidatos com situação e pendências, e formulário "Seja colaborador"
     (POST /colaborar) que salva no banco, lista em /painel/colaboradores e manda e-mail
     para os admins e para a pessoa.
  4. Menu do topo com OVNIPORTO, A origem e Atlas.
  5. /o-lugar: a migration 2026_10_05_120000_fill_place_spaces_from_plan grava os 9 espaços
     do plano (em produção a tabela place_spaces estava vazia).
  6. Nova página /origem/relato com o relato do Julean. A migration
     2026_10_05_130000_fill_yellow_car_relato grava o texto no bloco legend_body só se ele
     estiver vazio. A home ganhou o cartão do Ovnipuerto de Cachi e uma seção do carro amarelo.
     As ilustrações do Niva (public/concept/niva-roadside*) vão versionadas no repositório:
     a página usa a vertical de fundo no celular e a horizontal no desktop.
- Migrations novas: 2026_10_05_000200_create_research_collaborators_table,
  2026_10_05_120000_fill_place_spaces_from_plan e 2026_10_05_130000_fill_yellow_car_relato
  (e qualquer outra pendente, como
  2026_10_05_000100_add_avif_to_sighting_photos, se ainda não rodou).

## Regras

1. Não altere código nem arquivos versionados no servidor. Se algo precisar de correção
   no projeto, DESCREVA no relatório final; eu corrijo no repositório.
2. Nunca mostre segredos na saída (.env, senhas, tokens).
3. Não rode `make seed-demo`, `migrate:fresh`, `migrate:rollback`, nem apague volumes.
4. Antes de qualquer ação destrutiva ou fora do roteiro, peça confirmação.

## Fase 1: Conferência antes (somente leitura)

- `cd /srv/ovniporto && git fetch origin && git log --oneline -3 origin/main`:
  confirme que o commit da página do relato está no topo e 5ddfce6 logo abaixo.
- Versão em produção agora: `cat .deploy/current`.
- Estado dos containers: `docker compose -f docker-compose.prod.yml ps`.
- Migrations pendentes:
  `docker compose -f docker-compose.prod.yml exec -T app php artisan migrate:status | grep -i pending`
- Quantos espaços existem hoje:
  `docker compose -f docker-compose.prod.yml exec -T app php artisan tinker --execute="echo App\Models\PlaceSpace::count();"`
  (esperado: 0).
- O relato já está no painel? Só diga se o bloco legend_body está vazio ou preenchido:
  `docker compose -f docker-compose.prod.yml exec -T app php artisan tinker --execute="echo blank(App\Models\ContentBlock::where('key','legend_body')->value('value')) ? 'vazio' : 'preenchido';"`
  Se estiver preenchido, a migration não mexe nele: me avise, porque então o texto do
  Julean não entra sozinho.
- Confirme que o último backup do banco é de hoje ou de ontem (veja DEPLOY.md, seção
  "Backup"). Se não for, me avise antes de seguir: proponho rodar um backup manual.

## Fase 2: Deploy

- `./deploy.sh`
- Mostre o fim do log. Se ele tiver voltado para a versão anterior, pare, mostre o erro
  e os logs (`docker compose -f docker-compose.prod.yml logs --tail=150 app ssr worker`)
  e não tente de novo sem falar comigo.

## Fase 3: Verificação

Rode e mostre o resultado de cada item:

1. Versão: `cat .deploy/current` deve ser o commit do topo de origin/main.
2. Migrations: nenhuma "Pending" no `migrate:status`.
3. Espaços: `App\Models\PlaceSpace::count()` deve ser 9.
4. Saúde: `curl -fsS https://ovniporto.tars.art.br/up`.
5. Cachi com SSR e metadados:
   `curl -s https://ovniporto.tars.art.br/origem/cachi | grep -o '<title>[^<]*</title>'`
   deve conter "Ovnipuerto de Cachi: Werner Jaisli e a Estrella de la Esperanza".
   `curl -s https://ovniporto.tars.art.br/origem/cachi | grep -c 'application/ld+json'`
   deve ser pelo menos 1, e o HTML deve conter "Em poucas palavras" e
   "OVNIPUERTO DE CACHI" ou "Ovnipuerto de Cachi" dentro do <h1> (prova de que o SSR
   está renderizando).
6. llms.txt: `curl -sI https://ovniporto.tars.art.br/llms.txt` com 200 e
   Content-Type text/plain; `curl -s https://ovniporto.tars.art.br/llms.txt | head -20`.
7. Sitemap: `curl -s https://ovniporto.tars.art.br/sitemap.xml | grep -A1 'origem/cachi<'`
   deve mostrar <lastmod>2026-10-05</lastmod>.
8. robots.txt continua liberando o site e apontando o sitemap.
9. /o-lugar: `curl -s https://ovniporto.tars.art.br/o-lugar | grep -c 'Pista de pouso'`
   maior que 0.
10. /origem/atlas responde 200 e o HTML contém "O que falta confirmar".
10b. Relato: `curl -s https://ovniporto.tars.art.br/origem/relato` contém
    "O relato do carro amarelo do Julean", "Era tarde da noite" e "relato-beat";
    a home contém "Ovnipuerto Cachi" e "Ler o relato inteiro".
10c. Ilustrações do Niva servidas: `curl -sI https://ovniporto.tars.art.br/concept/niva-roadside-1600.avif`
    e `curl -sI https://ovniporto.tars.art.br/concept/niva-roadside-tall-800.avif` respondem 200
    com Content-Type image/avif.
11. Formulário de colaborador: NÃO envie um formulário real. Só confirme que
    `curl -s -o /dev/null -w '%{http_code}' -X POST https://ovniporto.tars.art.br/colaborar`
    responde 419 (proteção CSRF ativa) e não 404 ou 500.
12. E-mail: confirme que MAIL_MAILER no .env de produção não é "log" (só diga o valor
    do mailer, não as credenciais) e que o worker está processando a fila
    (`docker compose -f docker-compose.prod.yml logs --tail=50 worker`). Se o mailer
    for "log", avise: os e-mails de colaborador não vão sair.
13. Logs sem erro novo: `docker compose -f docker-compose.prod.yml logs --since=10m app | grep -i -E "error|exception" | head`.

## Relatório final

1. Versão anterior e versão publicada.
2. Resultado de cada item da Fase 3 (ok / falhou, com a saída relevante).
3. Problemas encontrados, com arquivo e sugestão de correção para eu levar ao repositório.
4. Lembrete para mim: pedir indexação de https://ovniporto.tars.art.br/origem/cachi
   no Google Search Console e enviar o sitemap no Bing Webmaster Tools.
```
