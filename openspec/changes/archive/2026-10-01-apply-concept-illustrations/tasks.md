# Tasks

## 1. Pipeline de imagens

- [x] 1.1 Comando `concept:import {source}` gerando AVIF/WebP/JPEG/LQIP e o manifesto `resources/js/data/concept.json`, com teste Pest
- [x] 1.2 Rodar o comando com as ilustrações e versionar `public/concept/*`

## 2. Componentes

- [x] 2.1 Componente `Ui/Picture` (srcset, sizes, lazy/eager, LQIP) e `ConceptImage` com etiqueta "conceito"
- [x] 2.2 Capa: ilustração de fundo com véu, esmaecendo ao iniciar a abdução; estática em movimento reduzido
- [x] 2.3 O lugar: vista geral em "Como vai ficar" e ilustração nos cartões dos espaços
- [x] 2.4 Lenda: polaroid com a ilustração do carro amarelo
- [x] 2.5 Lembranças: Aduana como pano de fundo discreto

## 3. Dados e marca

- [x] 3.1 `PlaceSpaceSeeder` com `concept_image_path`; `PlaceSpaceCard` expõe `concept`; teste
- [x] 3.2 `brand:og` monta a imagem de compartilhamento sobre a ilustração da capa

## 4. Selo real

- [x] 4.1 Comando `brand:seal` recortando o adesivo com fundo transparente, com teste
- [x] 4.2 `<Seal>`/`SealArt` usando o adesivo em `<picture>` responsivo; OG com o adesivo sobre a capa

## 5. Verificação

- [x] 5.1 Testes, lint e typecheck verdes; conferir no navegador em 1440 px
- [x] 5.2 Conferir no navegador em 390 px
