# Tasks

## 1. Domínio, leitura e acesso

- [x] 1.1 Adicionar `PanelArea::Manual` (`manual`) aberto a admin, moderator e store, com o rótulo "Manual" em `pt-BR.ts` (último item do menu), verificado por teste de unidade de `PanelArea::allows` para os 4 papéis
- [x] 1.2 Criar `App\Domain\Manual\Contracts\ManualLibrary` e `InvalidManualData`, e `App\Infrastructure\Manual\JsonManualLibrary` (valida chaves, área existente, nós apontando para seções existentes, ids únicos, `reviewedAt` como data, limite de 7 ramos × 8 folhas; renderiza Markdown com `MarkdownRenderer`; cache pela data de modificação), ligado no `DomainServiceProvider`, verificado por testes de unidade com fixtures válidas e cada tipo de erro nomeando capítulo e nó
- [x] 1.3 Criar os use cases `ListManualChapters` e `ShowManualChapter` filtrando por papel (capítulo fechado → `null`), verificados por testes de unidade para admin, moderator e store
- [x] 1.4 Criar `ManualController` (`index`, `show`) e as rotas `panel.manual` e `panel.manual.show` com `panel:manual`, verificadas por testes de feature: os 3 papéis abrem o índice, member e visitante recebem 403/redirect, moderator em `/painel/manual/pedidos` recebe 404, slug inexistente recebe 404
- [x] 1.5 Atualizar a spec principal implícita do acesso: teste de feature "todo papel administrativo abre o manual" e "membro comum não abre", verificado rodando `php artisan test --filter=Panel`

## 2. Mapa mental e telas

- [x] 2.1 Carregar as skills de design/animação do `.claude/skills/README.md` (frontend-design e animate) e registrar em `design.md` a direção visual do mapa com os tokens existentes, verificado pelo registro no design
- [x] 2.2 Escrever `layoutMindMap(tree)` como função pura (centro, ramos dos dois lados balanceados pela altura, folhas empilhadas ao lado do ramo, sem sobreposição de caixas), verificada por testes Vitest de posições, ausência de sobreposição e limite de nós
- [x] 2.3 Criar `Components/Panel/MindMap.tsx`: mapa bilateral com SVG + nós posicionados no desktop, árvore vertical com conectores no celular, `role="tree"`/`treeitem`, roving tabindex, setas/Home/End/Enter, cor do ramo pelo `kind` com tokens, desenho dos ramos com `pathLength` em CSS, desligado em `prefers-reduced-motion`, verificado por testes Vitest de teclado e de nome acessível
- [x] 2.4 Criar `Pages/Panel/Manual/Index.tsx` (mapa geral com o selo no centro + lista 01/02/03 com resumo e "revisado em") e `Pages/Panel/Manual/Chapter.tsx` (mapa do capítulo, seções com âncora, foco no título ao abrir um nó, "voltar ao índice"), textos em `pt-BR.ts`, verificado por testes Vitest de render e por `npm run lint`
- [x] 2.5 Mostrar "Como funciona" em toda tela do painel: campo `screen` (rota ou lista) nas seções, prop partilhada `manualLink` (`FindManualSection`) e link no `PanelLayout`, verificado por teste de feature que exige uma seção para cada tela GET do painel e confere o link partilhado por papel
- [x] 2.6 Link "Manual" no início do painel (dashboard), verificado por teste de feature/render do `Panel/Home`

## 3. Conteúdo do manual

Escrito a partir do código real (controllers, use cases, e-mails em `app/Mail`, specs em `openspec/specs/`, `DEPLOY.md`), nunca inventado. Cada capítulo cobre, por tela: para que serve, quem usa, cada ação e seu efeito (e-mails, auditoria, o que o público vê), estados, cuidados de privacidade.

- [x] 3.1 Capítulos gerais: `primeiros-passos` (entrar, primeiro admin, menu, celular), `papeis` (admin/moderador/loja, o que cada um abre, como dar papel), `rotina` (o que olhar todo dia: "Precisa de você", fila de relatos, pedidos pagos, avisos de fila), verificados pelo carregamento sem erro e revisão do texto contra `PanelArea`, `GetPanelHome` e `DEPLOY.md`
- [x] 3.2 Capítulos `inicio` (dashboard: contagens, metas, gráfico, "Precisa de você") e `relatos` (fila, revisão, aprovar, pedir ajuste, rejeitar, despublicar, fotos por URL assinada, EXIF, apelido, e-mails ao autor), verificados contra os controllers e a spec `sighting-moderation`
- [x] 3.3 Capítulos `membros` (busca, papel, bloqueio, desbloqueio, exclusão e LGPD) e `auditoria` (o que é registrado, como ler antes/depois), verificados contra `MemberAdminController`, `AuditController` e a spec `admin-access`
- [x] 3.4 Capítulos `pedidos` (estados do pedido, CPF, produção e PDF, etiqueta Melhor Envio, enviado, entregue, cancelar, reembolsar PayPal, exportar, rastreio automático e cancelamento de abandonados) e `produtos` (produto, imagens, variantes, estoque, embalagem para frete), verificados contra os controllers, os comandos agendados e as specs `store-operations`, `payments`, `shipping`
- [x] 3.5 Capítulos `conteudo` (hub, links, metas, blocos de texto como `legend_body`, FAQ, regras, prévia), `lugar-e-obra` (espaços, selo "conceito", fotos, mapa 3D, diário da obra), `regiao` (parceiros, localizar, consentimento, publicar/despublicar) e `campanha` (campanha "em planejamento", apoiadores, importação, patrocinadores, Avise-me e exportação, colaboradores do Atlas), verificados contra os controllers e as specs `content-admin`, `place-project`, `construction-diary`, `region-directory`, `campaign`, `waitlist`
- [x] 3.6 Capítulos `monitoramento` (Filas/Horizon, Saúde/Pulse, alertas por e-mail, métrica Umami; só admin), `privacidade` (regras fixas do CLAUDE.md na operação, pedidos de dados e exclusão, encarregado), `problemas` (pagamento não confirmado, etiqueta falhou, e-mail não chegou, relato preso, 403 no painel) e `glossario`, verificados contra `DEPLOY.md`, a spec `privacy-compliance` e `docs/emails/`
- [x] 3.7 Teste de cobertura `tests/Feature/Panel/ManualCoverageTest.php`: toda rota `panel.*` citada em algum capítulo e toda rota citada existe, com mensagem que nomeia rota e capítulo, verificado verde com o conteúdo completo e vermelho ao registrar uma rota de teste não citada

## 4. Gatilho de atualização

- [x] 4.1 Criar `.claude/hooks/manual-reminder.mjs` (Node puro: lê o JSON do hook, mapeia o caminho editado para o capítulo provável, devolve `hookSpecificOutput.additionalContext`; ignora edições em `resources/content/manual/`) com teste Vitest do mapeamento caminho → capítulo, verificado executando o script com um JSON de exemplo no Git Bash e no PowerShell
- [x] 4.2 Registrar o hook `PostToolUse` (`Edit|Write|MultiEdit`) em `.claude/settings.json` versionado, verificado editando `app/Http/Controllers/Panel/AuditController.php` numa sessão do Claude Code e vendo o lembrete do capítulo `auditoria`
- [x] 4.3 Adicionar a regra de tarefas em `openspec/config.yaml` (`rules.tasks`) e a linha no `CLAUDE.md` em "Fluxo de trabalho", verificado por `openspec instructions tasks --change add-panel-manual --json` mostrando a regra
- [x] 4.4 Seção "Manual do painel" no `DEPLOY.md` (onde fica, como atualizar, o teste que trava) e corrigir a menção a `/admin` para `/painel/pedidos`, verificado por leitura

## 5. Integração

- [x] 5.1 Conferir no navegador em 390 px e 1440 px com admin, moderador e loja: menu, índice, mapa, teclado, foco verde, "Como funciona" de cada tela, sem rolagem horizontal, e rodar axe no índice e num capítulo, verificado por capturas em `evidence/`
- [x] 5.2 Rodar `php artisan test`, PHPStan, `npm run lint`, `npm test` e `openspec validate add-panel-manual --strict`, verificado tudo verde

## Workflow follow-up

- Arquivar a change depois da revisão (`/opsx:archive`).
