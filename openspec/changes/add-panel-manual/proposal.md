# Proposal

## Why

O painel já tem 7 áreas e dezenas de ações (moderar relatos, produzir e enviar pedidos, editar o lugar, a campanha, a região, papéis de membros), mas o único guia de operação é o `DEPLOY.md`, escrito para quem mexe no servidor. Quem opera o dia a dia (admin, moderação, loja) não tem onde aprender o fluxo de cada tela, e qualquer manual escrito à parte envelhece na primeira mudança. Precisamos de um manual dentro do painel, visual (mapas mentais), e de um gatilho que impeça o manual de ficar para trás.

## What Changes

- Nova área do painel **Manual** em `/painel/manual`, aberta a todo papel administrativo (admin, moderador, loja), com um capítulo por área do painel e capítulos gerais (primeiros passos, papéis, rotina diária, privacidade e LGPD, o que fazer quando algo dá errado, glossário).
- Cada capítulo tem um **mapa mental** (o tema no centro, ramos para telas, ações, estados e cuidados) e, abaixo, as seções em texto que cada nó do mapa abre.
- O manual mostra a cada pessoa só os capítulos das áreas que o papel dela abre; o servidor filtra, não só o front.
- Conteúdo versionado no repositório em `resources/content/manual/*.json`, validado ao carregar, com data de revisão por capítulo visível na tela.
- **Gatilho de atualização**, em três camadas:
  1. Teste de cobertura (Pest): toda rota nomeada `panel.*` precisa estar citada em algum capítulo, e todo capítulo só pode citar rotas que existem. Uma rota nova ou removida sem manual quebra a suíte.
  2. Hook do Claude Code (`PostToolUse` em Edit/Write): ao mexer em rotas, controllers, use cases ou páginas do painel, injeta o lembrete de atualizar o capítulo correspondente.
  3. Regra do fluxo OpenSpec: `openspec/config.yaml` passa a exigir, em toda change que toca o painel, uma tarefa "Atualizar o manual do painel", e o CLAUDE.md registra a regra.
- Link "Manual" também no início do painel (dashboard) e um link "Como funciona" no topo de cada tela que leva ao capítulo dela.

## Capabilities

### New Capabilities
- `panel-manual`: manual de operação dentro do painel, com capítulos por área, mapas mentais, filtro por papel e a garantia de que o manual acompanha cada mudança do painel.

### Modified Capabilities
- `admin-access`: o requisito "Acesso por papel" passa a incluir a área Manual, aberta a todos os papéis administrativos e fechada a membros comuns.

## Impact

- **Backend:** `App\Domain\Panel\PanelArea` (novo caso `Manual`), novo módulo de leitura do manual (interface no Domain, leitor JSON na Infrastructure, use case na Application), controller fino `ManualController`, rota `/painel/manual` e `/painel/manual/{capitulo}`.
- **Frontend:** `resources/js/Pages/Panel/Manual/{Index,Chapter}.tsx`, componente `resources/js/Components/Panel/MindMap.tsx` (SVG próprio, sem nova dependência), textos em `pt-BR.ts`, link "Como funciona" em `PanelSection`.
- **Conteúdo:** `resources/content/manual/*.json` (um arquivo por capítulo).
- **Ferramentas:** `.claude/settings.json` com o hook, `.claude/hooks/manual-reminder.mjs`, regra em `openspec/config.yaml`, linha no `CLAUDE.md`.
- **Testes:** feature (acesso por papel, filtro, 404 de capítulo fechado), cobertura de rotas, unidade do use case e do leitor, Vitest do mapa mental, e2e de teclado no mapa.
- Sem nova dependência de pacote; sem migração de banco.
