# Proposal

## Why

Relatos, pedidos e a comunidade precisam de uma identidade simples e confiável. Login só com Google evita senhas e reduz dados guardados; o apelido público separa a identidade real do que aparece no site.

## What Changes

- Login exclusivo com Google (sem senha), modal "Entrar na comunidade" (Prompt 9).
- Primeiro acesso: tela de boas-vindas com apelido público único (sugerido), cidade opcional e aceite obrigatório dos termos; a conta só fica ativa depois disso.
- `/conta` com abas Meus relatos, Meus pedidos, Meus dados e Privacidade ("Baixar meus dados" e "Excluir minha conta").
- Papéis `member`, `moderator`, `store`, `admin`; cada membro só vê e edita o que é dele.

## Capabilities

### New Capabilities
- `member-accounts`: identidade de membro via Google, perfil público por apelido, área "Minha conta" e solicitações de exportação e exclusão.

### Modified Capabilities
<!-- Nenhuma. Usa `public-layout` e `design-system` da change `build-public-home`. -->

## Impact

- Módulo Members, Laravel Socialite (Google), middleware de perfil completo, policies.
- As abas de relatos e pedidos ficam vazias até `add-sighting-submission` e `add-checkout-payments`. Garantias detalhadas de LGPD (conteúdo da exportação, retenção fiscal) são aprofundadas em `harden-privacy-lgpd`.
