# Design

## Context

Ver proposal.md. Depende de `waitlist`, `design-system` e `public-layout` (change `build-public-home`).

## Goals / Non-Goals

**Goals:** regras de honestidade (lugar não existe, sem arrecadação em `planning`) aplicadas no Domain, não só na view.

**Non-Goals:** edição pelo painel (`add-operations-panel`); integração com plataforma de crowdfunding (é só um link externo).

## Decisions

- **A visibilidade de pagamento é decidida no Domain.** Um serviço de Campaign devolve um "view model" público; em `planning` ele simplesmente não contém placar, URL nem apoiadores, então a view não tem como vazar o link. Alternativa (condicional no React) rejeitada: frágil e não testável no servidor.
- **Configuração da campanha como linha única** com cache e invalidação ao salvar.
- **Mapa estático até o clique:** imagem/tiles sem interação e Leaflet carregado sob demanda, por performance.
- **RSS gerado no servidor** a partir do mesmo UseCase da lista, garantindo o mesmo filtro de publicação.

## Risks / Trade-offs

- [Status `open` ativado sem orçamento] → aviso fixo no painel (change `add-operations-panel`) e só papel admin altera o estado.
