# Proposal

## Why

Quem vem ver o céu de Lages precisa de onde dormir, comer e passear. Indicar pousadas e produtores da serra fortalece a comunidade local, mas só pode acontecer com o consentimento de cada parceiro, e hoje a lista ainda está vazia.

## What Changes

- Página `/regiao` com filtro por tipo, busca por texto sem recarregar, cartões com distância aproximada até o OVNIPORTO e alternância Lista | Mapa (Prompt 8).
- Página `/regiao/{slug}` com capa, galeria, contatos, mini-mapa e "Como chegar".
- Estado vazio bonito com "Quero aparecer aqui".
- Regra de Domain: parceiro sem consentimento registrado nunca é publicado.

## Capabilities

### New Capabilities
- `region-directory`: diretório público de parceiros da região, com regra de consentimento para publicação.

### Modified Capabilities
<!-- Nenhuma. Usa `design-system` e `public-layout` da change `build-public-home`. -->

## Impact

- Módulo Region, tabela de parceiros, rotas `/regiao` e `/regiao/{slug}`, Leaflet + OSM.
- O cadastro e a geocodificação no painel vêm em `add-operations-panel`.
