# Design

## Context

Ver proposal.md. Depende de `sighting-submission`.

## Goals / Non-Goals

**Goals:** uma única consulta "relatos públicos" usada por API, mapa, lista e home; dados mínimos.

**Non-Goals:** imagem OG dinâmica (`add-seo-and-sharing`); moderação (`add-operations-panel`).

## Decisions

- **Escopo de repositório "publicados"** (`status = approved`) e um Resource público com whitelist de campos. Alternativa (esconder campos no front) rejeitada: vazaria no JSON.
- **Cache de 60 s por combinação de filtros**, invalidado por evento de domínio de aprovação/despublicação.
- **Tiles escuros via provedor OSM com atribuição**, configurável por env; nada de Google Maps.
- **Raio de 20 km** com o mesmo serviço de distância usado em outros módulos.
- **Privacidade:** fotos públicas só de relatos aprovados; miniatura servida a partir das variantes sem metadados.

## Risks / Trade-offs

- [Cache servindo relato recém-despublicado] → invalidação por evento, TTL curto como rede de segurança.
