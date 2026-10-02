# Design

## Context

Os originais são PNGs de 1,1 a 1,9 mil px e cerca de 2,5 MB cada, numa pasta do Dropbox fora do repositório. O PHP já tem GD com AVIF e WebP, e o projeto já gera imagens de marca por GD (`NightCanvas`, `brand:og`).

## Goals / Non-Goals

**Goals:**
- Gerar uma vez e versionar só os derivados leves; o build não depende do Dropbox.
- Um único lugar que diz qual ilustração serve a qual uso (capa, vigília, carro...).

**Non-Goals:**
- Páginas /o-lugar e /lenda (cobertas por `add-place-and-campaign` e `add-content-pages`); elas reaproveitarão o mesmo manifesto.

## Decisions

- **Comando `concept:import {source}`** com um mapa fixo nome-do-arquivo → slug (`cover`, `cover-alt`, `vigil`, `yellow-car`, `yellow-car-sculpture`, `customs-shop`, `snack-bar`, `tower`, `overview`, `museum-path`, `museum-path-alt`). Saída em `public/concept/{slug}-{w}.{avif,webp}`, `public/concept/{slug}.jpg` (1200 px) e `resources/js/data/concept.json` com largura, altura, larguras geradas e LQIP base64. GD reencoda do zero, então nenhum metadado sobrevive.
  - Alternativa descartada: sharp/Node. Seria uma dependência nova só para isto.
- **Ilustração de cada espaço**: `place_spaces.concept_image_path` guarda o slug (coluna já existe). O repositório expõe `concept` no cartão; o front monta o `<picture>` pelo manifesto.
- **Capa**: a ilustração entra como camada de fundo do `AbductionHero` com véu night a 30%; a opacidade vai de 1 a 0 entre 6% e 26% do progresso da capa, antes de o disco animado descer, para não haver dois discos na tela. A serra e as araucárias desenhadas aparecem no mesmo intervalo; o Starfield fica por baixo e só aparece quando a imagem some. Em movimento reduzido só a ilustração é mostrada.
- **Selo real**: `brand:seal {source} --cx --cy --r` recorta o círculo do adesivo (centro e raio medidos no arquivo de 1024 px) com borda suavizada por supersampling e grava `public/brand/seal-{128,256,512,768}.{webp,avif}` e `seal.png`. O `<Seal>` usa `<picture>` com `sizes` por tamanho; o favicon continua o desenho simplificado, porque o adesivo não se lê em 16 px.
- **"OVNIPORTO nas Alturas"** não é usada: tem o nome escrito numa tipografia que não é a da marca.

## Risks / Trade-offs

- [~11 ilustrações × 4 larguras × 2 formatos no repositório, alguns MB] → só larguras até o original e qualidade 50 (AVIF) / 72 (WebP).
- [Ilustração mostra um Niva reconhecível, contra o guia "sem carros de modelo reconhecível"] → é o carro do próprio adesivo da marca; mantido, sem nenhuma marca escrita.
