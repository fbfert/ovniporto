# Design

## Context

Ver proposal.md. Primeira change autenticada; define papéis usados depois pelo painel.

## Goals / Non-Goals

**Goals:** login sem senha, dados mínimos, isolamento por policy.

**Non-Goals:** outros provedores de login; painel de membros (`add-operations-panel`); detalhamento da retenção fiscal (`harden-privacy-lgpd`).

## Decisions

- **Provedor de identidade atrás de interface no Domain** (`IdentityProvider`), com implementação Socialite/Google em Infrastructure e fake nos testes. Permite trocar ou mockar o Google.
- **Membro como entidade sem senha**; `role` é enum no Domain (member, moderator, store, admin).
- **Middleware de perfil completo** em todas as rotas autenticadas exceto boas-vindas e logout.
- **Exportação e exclusão como UseCases enfileirados**; a exclusão chama portas de Sightings e Orders para apagar/anonimizar, mantendo o módulo Members desacoplado.
- **Privacidade:** só `google_id`, `name`, `email`, `avatar_url` são persistidos do Google; token OAuth não é guardado.

## Risks / Trade-offs

- [Apelido usado para se passar por outro] → unicidade case-insensitive e lista de termos reservados (ex.: "torre", "admin").
