# Proposal

## Why

Chegaram 16 ilustrações novas (Dropbox `novasilustracoes`): as cenas da origem que estavam como "conceito em produção", os dois espaços de /o-lugar sem imagem e uma série de cartões-postais para os casos do Atlas. Três casos só tinham mapa de localização, e a lista do Atlas não tinha imagem nenhuma.

## What Changes

- Importação das 16 ilustrações (`concept:import`), que passa a manter no manifesto as imagens vindas de outras pastas.
- Selo por tipo: "conceito" quando a imagem mostra como o OVNIPORTO vai ficar; "ilustração" para cenas de relato, da origem e de outros lugares do mundo. `ConceptImage` recebe o selo como opção.
- Origem: Estrella de la Esperanza, Atlas dos Ovnipuertos, casa-cueva e pedras e cordas entram no lugar dos espaços reservados. O capítulo da casa-cueva deixa de mostrar a foto do Nevado de Cachi (que continua na galeria).
- /o-lugar: Hangar e Museu coberto ganham ilustração; uma migration preenche a ilustração só onde ela ainda está vazia.
- Atlas: cada caso tem um cartão-postal ilustrado no cartão da lista e no topo da página do caso; a foto documental continua abaixo, com crédito. Cachi usa a estrela noturna da origem; Lages usa a vista geral conceitual, com selo "conceito".
- Página do caso: título com tamanho e quebra ajustados e selo de confiança abaixo do nome, para nomes longos não vazarem da coluna.

## Capabilities

### New Capabilities
- `atlas-postcards`: ilustrações da origem, do lugar e do Atlas com o selo certo.

### Modified Capabilities
<!-- Nenhuma. -->

## Impact

- `ImportConceptIllustrations`, `resources/js/data/concept.json`, `public/concept/*`, `ConceptImage`, `OriginConcept`, `AtlasPostcard` (novo), `Pages/Origin/Atlas.tsx`, `Pages/Origin/AtlasCase.tsx`, `database/data/place_spaces.php`, migration `2026_10_05_140000_fill_place_space_illustrations`, `cachi.json`, `i18n/pt-BR.ts`, testes.
