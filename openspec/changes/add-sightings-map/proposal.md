# Proposal

## Why

Relatos aprovados precisam virar um livro público e navegável: um mapa do céu de Lages que dá vontade de olhar para cima e de compartilhar. Ao mesmo tempo, nada que não foi aprovado, e nenhum dado além do apelido, pode vazar.

## What Changes

- `/mapa` (Livro de avistamentos): contador de aprovados, mapa escuro com marcadores agrupados, filtros de período e tipo na URL, grade de polaroids com "Carregar mais", botão flutuante "Relatar" no mobile (Prompt 11).
- API `GET /api/sightings` com apenas relatos aprovados e campos públicos, cache de 60 s.
- `/relatos/{id}`: ficha da torre com galeria, dados públicos, mini-mapa, carimbo "APROVADO PELA TORRE", "Mande um postal" (WhatsApp e copiar link) e "Outros relatos perto daqui" (20 km).
- Relato não aprovado: 404 para o público; o autor vê com a faixa "Em análise".

## Capabilities

### New Capabilities
- `sightings-map`: exibição pública de relatos aprovados (mapa, lista, API pública e página do relato).

### Modified Capabilities
<!-- Nenhuma. Depende de `sighting-submission` (change `add-sighting-submission`). A imagem OG dinâmica do relato é especificada em `add-seo-and-sharing`. -->

## Impact

- Módulos Sightings e Map, nova rota de API pública, Leaflet + markercluster, cache Redis.
