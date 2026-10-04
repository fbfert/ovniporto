# Design

## Context

Depende de `public-layout` (SeoHead, header, footer), `design-system` (Section, Polaroid, Accordion) e `waitlist` (formulário Avise-me), todos da change `build-public-home`. Ver proposal.md.

## Goals / Non-Goals

**Goals:** páginas SSR com conteúdo editável e estados de ausência de conteúdo explícitos.

**Non-Goals:** editor de conteúdo no painel (vem em `add-operations-panel`); texto jurídico final.

## Decisions

- **Blocos de conteúdo por chave** (`content_blocks.key` → markdown) no módulo Content, lidos por um UseCase com cache; vazio é um estado de primeira classe, não um erro. Alternativa (texto no código) rejeitada: impede edição sem deploy.
- **FAQ e regras em tabelas próprias** com ordem, para o painel reordenar; seeds com o conteúdo do plano.
- **Linha do tempo de Cachi** em dado estático versionado no front-end (fato histórico, não editável).
- **Markdown renderizado no servidor com sanitização** (sem HTML bruto), para evitar XSS vindo do painel.

## Risks / Trade-offs

- [Rascunho jurídico publicado por engano como final] → aviso fixo controlado por um flag de conteúdo; só some quando o encarregado marcar a versão como final.
