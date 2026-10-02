# Proposal

## Why

As ilustrações conceituais encomendadas em `ovniporto-ilustracoes.md` ficaram prontas (12 imagens: capa, vigília, carro amarelo, aduana, torre, lanchonete, vista geral e trilha do museu). Hoje o site mostra só desenhos provisórios em SVG/GD no lugar delas, e o plano (Prompts 4, 5, C e 20) já previa a ilustração de verdade na capa, em "Como vai ficar", na lenda e nos espaços.

## What Changes

- Pipeline de imagens conceituais: um comando importa as ilustrações de uma pasta de origem e gera AVIF e WebP em 400/800/1200/1600 px (sem passar do original), um JPEG de fallback e um LQIP de 20 px, sem metadados, mais um manifesto para o front.
- Componente de imagem responsiva (`<picture>` com srcset, sizes, lazy por padrão e eager só na capa, com o LQIP desfocado por baixo).
- Capa da home: a ilustração da pista à noite entra como fundo, com véu escuro, e se apaga quando a abdução guiada pelo scroll começa.
- O lugar: o painel "Como vai ficar" usa a vista geral; os cartões dos espaços ganham a ilustração do espaço quando existe, sempre com a etiqueta "conceito".
- A lenda: a polaroid usa a ilustração do carro amarelo no lugar do desenho provisório.
- Lembranças: a ilustração da Aduana interplanetária aparece como pano de fundo discreto da seção.
- Imagem de compartilhamento (`og/default.jpg`) passa a ser o adesivo sobre a ilustração da capa.
- O selo provisório desenhado em código é trocado pelo adesivo impresso: um comando recorta o círculo do arquivo do adesivo com fundo transparente e gera WebP/AVIF/PNG em `public/brand`.

## Capabilities

### New Capabilities
- `concept-illustrations`: como as ilustrações conceituais são preparadas, entregues e rotuladas no site.

### Modified Capabilities

## Impact

- Novo comando artisan e `public/concept/*` (arquivos gerados, versionados).
- `PlaceSpaceSeeder` passa a preencher `concept_image_path`; `PlaceSpaceCard` expõe a ilustração.
- Componentes: `AbductionHero`, `PlaceSection`, `LegendSection`, `SouvenirsSection`, `Brand/Seal`, novo `Ui/Picture`.
- Sem dependências novas (GD já tem AVIF e WebP).
