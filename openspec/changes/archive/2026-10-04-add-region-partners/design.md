# Design

## Context

Ver proposal.md. Parceiros fictícios de demonstração (Prompt C) existem só em ambiente local, marcados "[EXEMPLO]".

## Goals / Non-Goals

**Goals:** regra de consentimento no Domain; filtros por partial reload do Inertia.

**Non-Goals:** CRUD, geocodificação e upload no painel (`add-operations-panel`).

## Decisions

- **Regra de publicação na entidade Partner** (`canBePublished()` exige consentimento) e um escopo de repositório "publicados" usado por toda leitura pública. Alternativa (só validar no formulário do painel) rejeitada: importações ou seeds poderiam furar a regra.
- **Haversine no servidor** em um serviço de Domain puro (testável), com o ponto do OVNIPORTO em configuração.
- **Filtros na query string** com partial reload; a mesma consulta alimenta lista e mapa.
- **Privacidade:** comprovante de consentimento (quando houver) fica em disco privado e nunca é exposto em rota pública.

## Risks / Trade-offs

- [Distância em linha reta difere da distância de estrada] → rótulo "aprox." e link "Como chegar" para a rota real.
