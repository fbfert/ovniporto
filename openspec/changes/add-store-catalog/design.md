# Design

## Context

Ver proposal.md. A porta de frete (`ShippingProvider`) é formalizada em `add-checkout-payments`; aqui só a operação de cotação é usada.

## Goals / Non-Goals

**Goals:** catálogo e carrinho com valores em centavos decididos no servidor.

**Non-Goals:** checkout e pagamento (`add-checkout-payments`); CRUD de produtos no painel (`add-operations-panel`).

## Decisions

- **Dinheiro como Value Object `Money` em centavos** no Domain; nenhum float.
- **Carrinho como agregado** com dono "sessão" ou "membro"; fusão no evento de login.
- **Cotação de frete atrás de `ShippingProvider`** (interface no Domain); nesta change usa-se a implementação fake até o Melhor Envio entrar, com cache curto por CEP + peso.
- **Visibilidade do bloco da pista** lida da configuração de campanha (módulo Campaign) via consulta de aplicação, sem acoplamento direto de tabela.

## Risks / Trade-offs

- [Estoque reservado no carrinho e vendido por outro] → estoque só decrementa após pagamento (`add-checkout-payments`); o carrinho revalida no checkout.
