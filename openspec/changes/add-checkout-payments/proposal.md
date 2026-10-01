# Proposal

## Why

Com o catálogo no ar, falta vender de verdade: identificar o cliente, calcular o frete, receber por cartão ou Pix e acompanhar o pedido até a entrega, sem que dados de cartão passem pelo nosso servidor.

## What Changes

- `/checkout` em 3 etapas numa página: identificação (Google ou visitante, telefone e CPF obrigatórios, CPF validado e criptografado), entrega (CEP com endereço automático, cotação Melhor Envio ou "Retirar em Lages" com frete zero) e pagamento PayPal (cartão e Pix) (Prompt 13).
- Pedido com número legível (`OVP-AAAA-NNNNNN`), itens como cópia do produto no momento da compra e log de eventos de status.
- Captura no servidor, confirmação por webhook com assinatura verificada e idempotência por id de ordem PayPal.
- E-mails em fila para cada etapa do pedido; página `/pedido/{number}` com linha do tempo, acessível por token assinado ou pelo membro dono.
- Regras: estoque decrementa só após pagamento; pedido pendente há mais de 2 h é cancelado; carrinho esvazia após pagamento; sandbox por `.env`.

## Capabilities

### New Capabilities
- `checkout`: fluxo de compra, cálculo do total, criação e acompanhamento de pedidos e notificações ao cliente.
- `payments`: criação, captura e confirmação de pagamentos via provedor externo, com verificação de webhook e idempotência.
- `shipping`: cotação de frete, retirada local, geração de etiqueta e rastreio via provedor externo.

### Modified Capabilities
<!-- Nenhuma. Depende de `store-catalog` e `shopping-cart` (change `add-store-catalog`) e de `member-accounts`. -->

## Impact

- Módulos Orders, Payments, Shipping; integrações PayPal (JS SDK + REST) e Melhor Envio; ViaCEP; comando agendado; filas de e-mail.
- Segredos só por variáveis de ambiente.
