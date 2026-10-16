# Design

## Context

Veja proposal.md (Why). Estado atual relevante:

- O checkout cota o frete em `CheckoutController::delivery` e de novo em `PlaceOrder::chosenOption`, pelo peso e valor do carrinho inteiro (`ManageCart::view`), e `OrderTotals::of` soma itens + preço da opção. Nenhum preço vindo do navegador é usado.
- `ShippingProvider` só compra etiqueta de ida (`createLabel`); não há etiqueta reversa.
- A data de entrega existe só como evento `order_events` com `to_status = delivered` (marcado no painel ou por `orders:track-shipments`).
- Formulários públicos (`/avise-me`, `/colaborar`) usam FormRequest com honeypot `website`, `throttle` na rota e notificador em fila; o aviso da equipe leva só id e link do painel.
- `/painel/coordenadas` guarda uma lista fechada de `OperationalSetting`; o bloco Alertas tem só `alerts.email`.
- A exclusão de conta passa por `MemberContentEraser`s; o de pedidos chama `OrderRepository::anonymizeFor`, e `privacy:purge-orders` apaga os pedidos anonimizados vencidos.

## Goals / Non-Goals

**Goals:**
- Registrar e atender pedidos de devolução com prova de data, sem abrir dados de pedidos a quem não comprou.
- Cobrar do comprador só o frete dos itens sem frete incluso, com uma regra determinística e uma única cotação.

**Non-Goals:**
- Etiqueta reversa pelo Melhor Envio (o adapter não tem). O operador combina o envio de volta e registra na nota do status.
- Reembolso automático: a ficha da devolução leva ao pedido, onde o "Reembolsar" existente continua sendo a ação de dinheiro.
- Área do comprador para acompanhar a devolução; o acompanhamento é pelo e-mail com o protocolo.
- Frete incluso por variante ou por região.

## Decisions

**1. Devolução como sub-recurso de Orders, não módulo novo.** Uma devolução sempre pertence a um pedido pago; fica em `app/Domain/Orders` (`ReturnRequestStatus`, `ReturnReason`, `WithdrawalWindow`, contratos `ReturnRequestRepository` e `ReturnNotifier`) e em `app/Application/Orders/UseCases` (`RequestReturn`, `OperateReturns`). Alternativa considerada: módulo `Returns` próprio. Rejeitada porque duplicaria o acesso a pedidos e a área do painel já é Pedidos.

**2. Conferência por número + e-mail, com erro genérico.** `RequestReturn` busca o pedido pelo número e compara o e-mail normalizado (minúsculas, sem espaços); qualquer falha (número inexistente, e-mail diferente, status `pending_payment`/`canceled`, pedido anonimizado) devolve a mesma mensagem. Com throttle de 5/min por IP, adivinhar pares número+e-mail fica impraticável. Alternativa: aceitar qualquer pedido e conferir no painel; rejeitada porque gravaria dados de quem não é cliente.

**3. Prazo calculado na hora do envio e gravado.** `WithdrawalWindow` recebe a data do evento `delivered` (ou nula) e a data do pedido de devolução: dentro do prazo se nula ou se `requested_at <= delivered_at + 7 dias corridos`. O resultado e a data de entrega usada ficam gravados (`within_withdrawal`, `delivered_at`), para a decisão não mudar se o pedido for editado depois. Dias corridos seguem o texto do CDC; prazo que vence em fim de semana não é estendido (o formulário funciona todo dia).

**4. Tabela `return_requests`.** Colunas: `protocol` (único, `DEV-AAAA-NNNNNN`, mesmo esquema de sequência de `OrderNumber`), `order_id` (FK `cascadeOnDelete`, para o expurgo do pedido levar a devolução), `name`, `email`, `reason`, `message`, `within_withdrawal`, `delivered_at`, `status`, timestamps; e `return_request_events` (de/para status, nota, autor, data), espelhando `order_events`. Nome, e-mail e mensagem são anuláveis para a anonimização.

**5. Anonimização junto com o pedido.** `EloquentOrderRepository::anonymizeFor` passa a anular `name`, `email` e `message` das devoluções dos pedidos que anonimiza, na mesma transação. A change `enforce-data-retention` usa o mesmo caminho para a anonimização por prazo; o expurgo definitivo é coberto pela FK em cascata. A exportação (`BuildMemberExport`) inclui as devoluções dos pedidos do membro.

**6. E-mail de devoluções em Coordenadas.** Nova `OperationalSetting::ReturnsEmail = 'alerts.returns_email'` (grupo Alertas pelo prefixo), com `configKey()` explícito `ovniporto.returns_email` e padrão `env('RETURNS_EMAIL', 'contato@tars.art.br')` em `config/ovniporto.php`. Alternativa: mandar para os admins como a colaboração faz; rejeitada porque o usuário pediu um endereço específico. O aviso da equipe leva só protocolo e link, como o da colaboração; a confirmação ao comprador leva protocolo, resumo do motivo e o prazo de resposta.

**7. Regra do carrinho misto: proporção do peso sobre uma única cotação.** O comprador paga `ceil(preço_cotado × peso_sem_frete_incluso / peso_total)`. Se o peso total for zero, paga o preço inteiro quando houver algum item sem frete incluso, e zero quando todos tiverem. Fica num objeto de domínio `ChargedShipping` (ao lado de `OrderTotals`), usado por `CheckoutController::delivery` (mostrar) e `PlaceOrder` (cobrar). Alternativas consideradas:
- *Cotar só os itens pagantes*: mais "justo" pela tabela da transportadora, mas exige duas cotações por CEP, e as opções (ids) podem não coincidir entre elas, criando opções sem preço.
- *Qualquer item com frete incluso zera o frete*: simples, mas convida a juntar um adesivo de frete incluso a um pedido grande.
A proporção do peso é uma cotação só, sempre tem preço para cada opção e é fácil de explicar ("você paga a parte do frete dos seus itens"). **Aprovada pelo usuário em 2026-10-16.**

**8. Snapshot no pedido.** `order_items.shipping_included` (cópia do produto na hora da compra) e `orders.shipping_cost_cents` (preço cotado da opção para o carrinho inteiro); `orders.shipping_cents` continua sendo o frete cobrado, que é o que vai ao PayPal. Pedidos antigos ficam com `shipping_cost_cents = shipping_cents` na migração.

**9. Simulador do produto.** `QuoteShipping::forVariant` devolve um campo `included` (do produto); a página troca o preço por "Incluso" e mantém prazos. O cache da cotação não muda (a chave já é CEP+peso+valor).

**10. Rotas.** Públicas: `GET /devolucao` (com `?pedido=` opcional para preencher) e `POST /devolucao` com `throttle:5,1`. Painel, dentro de `panel:pedidos`: `GET /painel/pedidos/devolucoes`, `GET /painel/pedidos/devolucoes/{protocol}`, `POST /painel/pedidos/devolucoes/{protocol}/status`. As rotas de devolução vêm antes de `/{number}`; o padrão `OVP-...` já evita colisão.

## Risks / Trade-offs

- [A proporção do peso não reproduz a tabela da transportadora] → O OVNIPORTO absorve a diferença, que a ficha do pedido mostra; a regra pode ser trocada em um só objeto de domínio se o usuário preferir outra.
- [Produtos com peso zero distorcem a proporção] → Regra explícita para peso total zero; o painel de produtos já pede peso, e o manual lembra que frete incluso exige peso cadastrado.
- [Data de entrega depende de alguém marcar "entregue"] → Pedido não entregue conta como dentro do prazo, a favor do consumidor; o operador vê a data usada na ficha.
- [Aviso para um endereço fora do domínio] → Só protocolo e link no corpo; os dados ficam atrás do login do painel.
- [Enumeração de pedidos pelo formulário] → Erro genérico, exigência de par número+e-mail, throttle e honeypot.

## Migration Plan

1. Migrações: `return_requests`, `return_request_events`, `products.shipping_included` (padrão `false`), `order_items.shipping_included` (padrão `false`), `orders.shipping_cost_cents` (preenchido com `shipping_cents`).
2. Deploy normal; nenhum produto muda de comportamento até alguém marcar "Frete incluso".
3. Definir o e-mail de devoluções em /painel/coordenadas, se diferente do padrão.
4. Rollback: as migrações têm `down`; a página /devolucao some com a rota. Pedidos com frete incluso já pagos continuam válidos, porque o total cobrado está gravado.

## Open Questions

Nenhuma. Aprovado pelo usuário em 2026-10-16: a regra proporcional do carrinho misto e o prazo "em até 5 dias úteis" no e-mail de confirmação.
