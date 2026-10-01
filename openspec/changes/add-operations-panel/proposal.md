# Proposal

## Why

O site só se sustenta se o Felipe e os amigos conseguirem operá-lo sem mexer em código: moderar relatos, produzir e enviar pedidos, atualizar o lugar, a obra, a região e a campanha. Tudo com papéis separados e trilha de auditoria.

## What Changes

- `/painel` ("Torre de controle") com papéis admin, moderator e store, dashboard com metas de 6 meses, lista "Precisa de você", gestão de membros e auditoria (Prompt 14).
- Moderação de relatos: fila por status, aprovar, pedir ajuste, rejeitar com motivo, despublicar, atalhos de teclado e edição do relato pelo autor (Prompt 15).
- Pedidos e produtos: ações por status, etiqueta, reembolso, ordem de produção em PDF, CSV, CRUD de produtos e estoque, configurações gerais (Prompt 16).
- Conteúdo do projeto físico: lugar, obra, região (publicação exige consentimento), campanha e inscritos do Avise-me (Prompt 17).

## Capabilities

### New Capabilities
- `admin-access`: painel, papéis, dashboard, gestão de membros e auditoria.
- `sighting-moderation`: moderação de relatos e reenvio pelo autor.
- `store-operations`: operação de pedidos, produtos e estoque.
- `content-admin`: edição de conteúdos, configurações, lugar, obra, região, campanha e Avise-me.

### Modified Capabilities
<!-- Nenhuma (as capabilities operadas ainda não estão arquivadas). Opera dados de `sighting-submission`, `checkout`, `shipping`, `payments`, `store-catalog`, `place-project`, `campaign`, `construction-diary`, `region-directory`, `content-pages` e `waitlist`. -->

## Impact

- Novo layout administrativo, rotas `/painel/*`, tabela de auditoria, geração de PDF/CSV, geocodificação via Nominatim, Recharts.
