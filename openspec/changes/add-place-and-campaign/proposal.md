# Proposal

## Why

O projeto físico (a pista ao lado da Hospedaria Vila das Pedras, meta 2028) é o motivo de longo prazo do OVNIPORTO, mas ainda não existe e não tem orçamento. O site precisa contá-lo com honestidade: mostrar o plano, nunca descrever o lugar como funcionando e não abrir arrecadação antes do orçamento.

## What Changes

- `/o-lugar`: localização (mapa estático ativado por clique), "O terreno hoje" x "Como vai ficar" (ilustração conceitual marcada), slot do mapa 3D, os 9 espaços em 4 fases, regras do céu escuro e Avise-me (Prompt 7).
- `/apoie`: modo "em planejamento" (estado atual) sem nenhum link de pagamento; modo "aberto" já implementado (placar, apoiadores, patrocinadores), mas sem dados.
- `/obra` e `/obra/{slug}`: diário da obra com estado vazio e feed RSS em `/obra.rss`.
- Modelos de espaços, fotos do terreno, posts da obra e configuração da campanha, com seeds (campanha `planning`).

## Capabilities

### New Capabilities
- `place-project`: apresentação pública do lugar planejado, seus espaços e fases.
- `campaign`: página de apoio e regras de quando a arrecadação pode aparecer.
- `construction-diary`: diário público da obra e feed RSS.

### Modified Capabilities
<!-- Nenhuma. Usa `waitlist`, `design-system` e `public-layout` da change `build-public-home`. -->

## Impact

- Módulos Place e Campaign; rotas `/o-lugar`, `/apoie`, `/obra`, `/obra/{slug}`, `/obra.rss`.
- Leaflet + OpenStreetMap na página do lugar. Edição pelo painel vem em `add-operations-panel`.
