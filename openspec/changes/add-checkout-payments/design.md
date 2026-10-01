# Design

## Context

Ver proposal.md. Depende de `store-catalog`, `shopping-cart` e `member-accounts`.

## Goals / Non-Goals

**Goals:** integrações trocáveis e testáveis com fakes; dinheiro sempre em centavos; nenhum dado de cartão no servidor.

**Non-Goals:** emissão de nota fiscal; operação pelo painel (`add-operations-panel`).

## Decisions

- **Portas no Domain:** `PaymentGateway` (createOrder, capture, verifyWebhook) e `ShippingProvider` (quote, createLabel, track); implementações PayPal e Melhor Envio em Infrastructure; fakes para testes e desenvolvimento. CEP → endereço via porta `AddressLookup` (ViaCEP).
- **Máquina de estados do pedido no Domain**, emitindo eventos que registram `order_events` e disparam e-mails.
- **Idempotência** por chave única em `paypal_order_id` + transação de banco com lock na captura; webhook e retorno do navegador convergem no mesmo UseCase.
- **CPF com cast criptografado**; exibido mascarado em todo lugar fora do painel.
- **Token da página do pedido** como URL assinada sem expiração curta, revogável ao excluir conta.
- **Privacidade:** dados do cliente compartilhados com PayPal e Melhor Envio só no mínimo necessário (nome, e-mail, endereço de entrega).

## Risks / Trade-offs

- [Webhook chega antes do retorno do navegador] → ambos chamam o mesmo UseCase idempotente.
- [Pedido cancelado após 2 h mas pago depois via Pix] → a ordem PayPal expira junto com o pedido; se ainda assim chegar captura, registrar evento e alertar o papel store para reembolso manual.

## Open Questions

- Prazo exato de expiração do Pix no PayPal versus a janela de 2 h (confirmar no sandbox; não muda specs).
