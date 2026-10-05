# Proposal

## Why

A equipe pesquisou 12 casos históricos de avistamento (Santa Catarina, outros estados e mundo), com fontes, situação da documentação e reconstituições artísticas geradas por IA (Dropbox `livro-de-avistamentos/avistamentos`). O Livro de avistamentos hoje só mostra relatos de membros; gravar esses casos como relatos os faria parecer enviados pela comunidade.

## What Changes

- Conteúdo versionado em `resources/content/sightings/historical-cases.json`, lido por `HistoricalCaseLibrary` (Domain) / `JsonHistoricalCaseLibrary` (Infrastructure).
- `/mapa` ganha a seção "Casos históricos", agrupada em Santa Catarina, Brasil e Mundo, separada dos relatos da comunidade, com o aviso de que as imagens são reconstituições geradas por IA.
- Página própria `/mapa/casos/{slug}` com data, local, resumo, o que a documentação diz, fontes, legenda da reconstituição e navegação entre casos.
- No mapa, os casos aparecem numa camada própria (losango lilás, sem o halo dos relatos), fora do agrupamento e do enquadramento automático, com legenda e aviso de posição aproximada.
- As 12 ilustrações entram pelo `concept:import`, com selo "ilustração" e texto alternativo no i18n.
- SEO e sitemap para cada caso.

## Capabilities

### New Capabilities
- `historical-sightings`: casos históricos pesquisados no Livro de avistamentos.

### Modified Capabilities
<!-- Nenhuma: os relatos da comunidade não mudam. -->

## Impact

- `ListHistoricalCases`, `GetHistoricalCase`, `HistoricalCaseController`, rota `logbook.historical`, `LogbookController`, `SightingsMap`, `Pages/Sightings/Logbook.tsx`, `Pages/Sightings/HistoricalCase.tsx`, `Components/Sightings/HistoricalCases.tsx`, `ContentSeo`, `BuildSitemap`, `app.css`, `i18n/pt-BR.ts`, testes.
