# Design

## Context

O painel tem 85 rotas nomeadas `panel.*` em 7 áreas (`PanelArea`: inicio, relatos, membros, pedidos, produtos, conteudo, auditoria), mais Horizon (`/painel/filas`) e Pulse (`/painel/saude`) só para admin. O acesso é checado por `EnsurePanelArea` + gate `panel`. Conteúdo editorial versionado já segue um padrão: JSON em `resources/content/<modulo>/`, lido por uma classe da Infrastructure atrás de uma interface do Domain, validado e cacheado pela data de modificação (`JsonOriginLibrary`). Markdown é renderizado no servidor por `MarkdownRenderer` (`CommonMarkRenderer`). O front não tem biblioteca de diagramas, e o CLAUDE.md proíbe mudar a stack.

## Goals / Non-Goals

**Goals:**
- Manual completo e navegável dentro do painel, com um mapa mental por capítulo.
- Filtro por papel feito no servidor.
- Manual que não envelhece: a suíte falha quando o painel muda sem o manual, e o agente é lembrado na hora da edição.

**Non-Goals:**
- Edição do manual pelo painel (o manual muda junto com o código, no mesmo commit; editar pelo painel quebraria essa ligação).
- Manual público ou para membros comuns.
- Busca de texto completo (o índice + mapas cobrem 16 capítulos; busca pode vir depois).

## Decisions

### 1. Conteúdo em JSON por capítulo, corpo em Markdown
`resources/content/manual/<slug>.json`, um arquivo por capítulo:

```json
{
  "slug": "relatos",
  "area": "relatos",            // PanelArea ou null para capítulo geral
  "group": "community",         // ManualGroup: ramo do mapa geral e ordem do índice
  "order": 20,
  "title": "Moderação de relatos",
  "summary": "Da fila à publicação: aprovar, pedir ajuste, rejeitar e despublicar.",
  "reviewedAt": "2026-10-07",
  "routes": ["panel.sightings", "panel.sightings.show", "..."],
  "map": { "label": "Relatos", "children": [ { "label": "Fila", "section": "fila", "kind": "screen", "children": [] } ] },
  "sections": [ { "id": "fila", "title": "A fila", "screen": "panel.sightings", "body": "Markdown..." } ]
}
```

- **Por que JSON e não Markdown com frontmatter:** o mapa é uma árvore e as rotas são lista; JSON valida a forma sem parser novo e segue o padrão de `origin/`.
- `kind` do nó (`screen`, `action`, `state`, `care`) define a cor do ramo, com os tokens existentes: tela = horizon, ação = beam, estado = night-blue, cuidado = car. Nada de cor nova.
- `screen` na seção liga a tela ao link "Como funciona" (decisão 5).

**Alternativa descartada:** guardar no banco e editar no painel. Perde o versionamento junto ao código e o teste de cobertura.

### 2. Camadas
- `App\Domain\Manual\Contracts\ManualLibrary` (`chapters(): list`, `chapter(slug): ?array`) e `InvalidManualData`.
- `App\Infrastructure\Manual\JsonManualLibrary`: lê, valida (chaves obrigatórias, `area` existe em `PanelArea`, todo `map.*.section` existe em `sections`, ids únicos, `reviewedAt` é data) e cacheia pela data de modificação, como `JsonOriginLibrary`. Corpo convertido para HTML por `MarkdownRenderer` no carregamento.
- `App\Application\Manual\UseCases\ListManualChapters` e `ShowManualChapter`: recebem o `MemberRole`, filtram por `PanelArea::allows`. Capítulo de área fechada → `null` → controller responde 404 (não 403, para não confirmar que existe).
- `App\Http\Controllers\Panel\ManualController` (`index`, `show`), rotas `panel.manual` e `panel.manual.show` com middleware `panel:manual`.
- `PanelArea::Manual = 'manual'`, aberto a admin, moderator e store. Aparece no menu do painel por último.

### 3. Mapa mental: SVG próprio, layout determinístico
Componente `MindMap.tsx` sem dependência nova:
- **Desktop (≥ 768 px):** layout radial em dois níveis: centro em pílula `night`, ramos de 1º nível distribuídos em ângulos iguais, folhas em leque ao redor do ramo pai. Conexões em curvas Bézier (`path`) no SVG; nós são `<button>` HTML posicionados por cima (texto selecionável, foco real, alvo ≥ 44 px). Posições calculadas por função pura `layoutRadial(tree, size)` testada no Vitest.
- **Celular (< 768 px):** o mesmo dado vira árvore vertical com linhas-guia (outline com conectores), que cabe em 390 px sem rolagem lateral. Não é um "fallback feio": é o mapa redesenhado para a coluna.
- **Lista equivalente:** a árvore vertical já é uma `<ul>` aninhada com `role="tree"`/`treeitem`; no desktop o SVG é `aria-hidden` e os botões formam a mesma árvore acessível.
- **Teclado:** padrão de tree view da ARIA (setas cima/baixo entre irmãos visíveis, direita/esquerda entre pai e filho, Home/End, Enter abre a seção, roving tabindex).
- **Movimento:** os ramos desenham com `pathLength` (Motion) na primeira vista, 400 ms, nada em `prefers-reduced-motion`. O painel não tem movimento decorativo; esse é o único, e só na entrada.
- Assinatura visual: o centro do mapa usa o selo redondo pequeno (`Seal size="sm"`) quando é o mapa geral do índice, ligando o manual à identidade.

**Alternativa descartada:** Mermaid/markmap. Pesados (centenas de KB), estilo genérico, acessibilidade fraca e mudança de stack.

### 4. Índice
`/painel/manual` mostra no topo o mapa geral (centro "Painel", um ramo por capítulo permitido, cada ramo leva ao capítulo) e abaixo a lista numerada 01/02/03 dos capítulos com resumo e "revisado em". Capítulos gerais primeiro, depois áreas na ordem do menu.

### 5. "Como funciona" em cada tela
O servidor conhece a rota atual; o front não. Então cada seção diz no campo `screen` qual tela explica (um nome de rota ou uma lista, para pares como criar/editar), o `HandleInertiaRequests` partilha `manualLink` (use case `FindManualSection`, só entre capítulos que o papel lê) e o `PanelLayout` mostra "Como funciona" acima de toda página do painel. Nenhuma página precisa ser editada, e um teste de feature exige que toda tela GET do painel (exceto downloads e o próprio manual) tenha uma seção. Substitui a ideia inicial de um componente com `href` fixo em cada página, que sairia de sincronia.

### 6. Gatilho de atualização em três camadas
1. **Teste de cobertura (a trava):** `tests/Feature/Panel/ManualCoverageTest.php` compara `Route::getRoutes()` com nome `panel.*` contra a união de `routes` de todos os capítulos. Falha em rota órfã (lista os nomes e sugere o capítulo pela área do middleware) e em rota citada que não existe. Comparar `reviewedAt` com a data dos arquivos da área foi descartado: depende de git/mtime e seria instável no CI. A trava fica nas rotas; o texto é responsabilidade das camadas 2 e 3.
2. **Hook do Claude Code:** `.claude/settings.json` (versionado) com `PostToolUse` para `Edit|Write|MultiEdit`, executando `node .claude/hooks/manual-reminder.mjs`. O script lê o JSON do stdin, e se o caminho editado casa com `routes/web.php`, `app/Http/Controllers/Panel/`, `app/Application/**`, `app/Domain/**`, `resources/js/Pages/Panel/`, `app/Mail/` ou `config/horizon.php`/`pulse.php`, devolve `additionalContext` dizendo qual capítulo provavelmente mudou (mapa caminho → capítulo) e pedindo para atualizar `resources/content/manual/<slug>.json` e o `reviewedAt`. Edições no próprio manual não disparam. Script em Node puro, sem dependência, funciona no Git Bash e no PowerShell.
3. **Regra do fluxo:** `openspec/config.yaml` ganha `rules.tasks`: "Se a change toca o painel (rotas, telas, ações, e-mails, papéis), inclua a tarefa 'Atualizar o manual do painel (capítulo X) e o reviewedAt'". O CLAUDE.md ganha a mesma regra numa linha em "Fluxo de trabalho".

## Risks / Trade-offs

- **Manual com texto desatualizado mesmo com rotas cobertas** (ex.: muda o texto de um e-mail) → camadas 2 e 3; o `reviewedAt` visível deixa o atraso evidente para quem lê.
- **Volume de texto** (16 capítulos, escrita completa) → conteúdo escrito a partir do código real (controllers, use cases, e-mails, specs em `openspec/specs/`), nunca inventado; onde algo depende de configuração de produção, o texto diz isso.
- **Mapa radial com muitos nós** fica apertado → no máximo 7 ramos de 1º nível e 8 folhas por ramo, em dois níveis; acima disso o capítulo é dividido. Validado no carregamento (`JsonManualLibrary::MAX_BRANCHES` e `MAX_LEAVES`).
- **Hook só ajuda quem usa Claude Code** → por isso a trava real é o teste.

## Migration Plan

Sem migração de banco. Deploy normal. Rollback: reverter o commit; nenhum dado é criado.

## Open Questions

Nenhuma bloqueante. Busca no manual fica para uma change futura se o volume crescer.

## Direção visual do mapa (registrada na implementação)

Skills carregadas: `frontend-design` e `animate`.

- **Layout bilateral, não radial:** com até 8 folhas por ramo, o radial puro sobrepunha as caixas. O mapa usa o formato clássico de mapa mental: tema no centro, ramos divididos entre direita e esquerda pela altura (não pela contagem), folhas empilhadas ao lado do ramo. Função pura `layoutMindMap` em `Components/Panel/mindMapLayout.ts`, com teste de não sobreposição no pior caso (7 × 8).
- **O detalhe da casa:** atrás do mapa, três anéis tracejados quase invisíveis, como a tela de radar da torre. Nada mais decora.
- **Tipos com cor e forma:** tela = horizon/quadrado, ação = beam/círculo, estado = night-blue/losango, cuidado = car/triângulo. O texto é sempre night sobre moonlight (AA); a cor fica no contorno, no glifo e no traço, nunca no texto. Legenda sob o mapa.
- **Centro:** pílula night com o título em Unbounded; no índice, o selo pequeno dentro dela.
- **Movimento:** o único do painel. Ramos desenham do centro para fora (`pathLength` + `stroke-dashoffset`, 260 ms, `--ease-snap`, 40 ms entre ramos), nós entram 180 ms depois do seu traço (opacidade + `scale` 0.95, 200 ms). Só em telas ≥ 64rem e só com `prefers-reduced-motion: no-preference`; no celular a árvore aparece pronta. CSS puro, sem Motion.
- **Celular:** a mesma árvore vira lista recuada com trilhos coloridos (borda esquerda na cor do ramo).
- **Acessibilidade:** um único `role="tree"` com `treeitem` planos (`aria-level`, `aria-setsize`, `aria-posinset`), roving tabindex, setas/Home/End/Enter/Espaço; o SVG é `aria-hidden`.
