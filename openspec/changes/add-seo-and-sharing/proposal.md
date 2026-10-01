# Proposal

## Why

O OVNIPORTO cresce por compartilhamento, principalmente no WhatsApp. Cada link precisa chegar com título, descrição e imagem bonitos, e o site precisa ser encontrável sem rastrear ninguém com cookies.

## What Changes

- Metadados completos em toda página (título "{Página} · OVNIPORTO Lages", descrição única, canonical, Open Graph, Twitter Card) e imagem OG padrão (Prompt 18).
- Imagens OG dinâmicas para relatos, produtos, parceiros e posts da obra, com cache e invalidação ao editar.
- `sitemap.xml` automático e `robots.txt` bloqueando `/painel` e `/conta`.
- Dados estruturados: Organization, Product, Article, Place, FAQPage.
- Comando de verificação de prévia (`og:check {url}`).
- Página `/postal` com imagem para baixar e compartilhamento nativo com alternativas.
- Métrica sem cookie (Umami self-hosted) só em produção, com os eventos do funil.

## Capabilities

### New Capabilities
- `seo-sharing`: metadados, imagens de prévia, sitemap, dados estruturados, postal compartilhável e métrica sem cookie.

### Modified Capabilities
<!-- Nenhuma. Complementa o SEO básico de `public-layout` (change `build-public-home`) sem alterar seus requisitos. -->

## Impact

- Todas as páginas públicas; rotas `/og/*`, `/sitemap.xml`, `/robots.txt`, `/postal`; serviço Umami + Postgres no docker-compose.
