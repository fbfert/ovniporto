# Proposal

## Why

Os termos de uso que vão para a aprovação do encarregado prometem duas coisas que a loja ainda não faz: um formulário de devolução pelo qual o comprador exerce o direito de arrependimento (CDC, art. 49), com frete de volta pago pelo OVNIPORTO, e produtos com **frete incluso**, pagos pelo OVNIPORTO. Os textos só podem ser marcados como finais depois que o site fizer o que eles dizem.

## What Changes

- Nova página pública **/devolucao** com formulário de devolução e arrependimento: número do pedido, e-mail da compra, nome, motivo e mensagem. O pedido só é aceito quando número e e-mail batem com um pedido pago; a resposta de erro não revela qual dos dois está errado. Rate limit e honeypot como nos outros formulários públicos.
- Cada pedido de devolução é gravado no banco com protocolo, motivo, data e a marcação **dentro do prazo de arrependimento** (até 7 dias depois da entrega; pedido ainda não entregue conta como dentro do prazo). O formulário aceita pedidos fora do prazo (defeito, por exemplo) e só os marca.
- E-mails em fila: confirmação com o protocolo para quem pediu e aviso para a equipe no endereço de devoluções (padrão `contato@tars.art.br`), configurável em **/painel/coordenadas** no bloco Alertas. O aviso da equipe leva só o protocolo e o link do painel.
- Painel: nova lista **/painel/pedidos/devolucoes** (área Pedidos: papéis `store` e `admin`) com busca, filtro por status e ficha com link para o pedido. Status: recebido → em andamento → concluído ou recusado, cada mudança com nota e auditoria. Pedidos recebidos aparecem em "Precisa de você".
- Links para /devolucao na página do pedido, no e-mail de pedido entregue e no rodapé.
- Produto ganha o campo **Frete incluso** no cadastro do painel. A loja mostra o selo "Frete incluso" no cartão e na página do produto; o simulador continua mostrando prazos, com o preço como "Incluso".
- Checkout: o comprador paga só a parte do frete que corresponde ao peso dos itens sem frete incluso; carrinho só com itens de frete incluso tem frete zero. O pedido guarda o valor cobrado e o custo cotado, para o painel mostrar quanto o OVNIPORTO absorveu. Retirada em Lages continua com frete zero.
- Privacidade: a exportação "Baixar meus dados" passa a incluir os pedidos de devolução; a exclusão de conta apaga deles nome, e-mail e mensagem junto com os dados do pedido.

## Capabilities

### New Capabilities
- `store-returns`: pedidos de devolução e arrependimento, do formulário público ao atendimento no painel, com prazo do CDC, avisos e retenção ligada ao pedido.

### Modified Capabilities
- `checkout`: o total passa a somar o frete cobrado do comprador, que desconta a parte dos itens com frete incluso.
- `store-catalog`: cartão, página do produto e simulador mostram o frete incluso.
- `store-operations`: o cadastro de produtos ganha o campo "Frete incluso" e a ficha do pedido mostra o frete absorvido.
- `privacy-compliance`: a exportação de dados inclui os pedidos de devolução.

## Impact

- Banco: nova tabela `return_requests`; `products.shipping_included`; `order_items.shipping_included`; `orders.shipping_cost_cents`.
- Domínio e aplicação: novo módulo de devoluções em `app/Domain/Orders` e `app/Application/Orders/UseCases` (pedir devolução, operar devoluções), cálculo do frete cobrado junto de `OrderTotals`/`PlaceOrder`, `QuoteShipping` e `CheckoutController::delivery`.
- Configuração: nova chave em `OperationalSetting` (e-mail de devoluções) e `RETURNS_EMAIL` no `.env.example`.
- Front: página `Pages/Store/Return.tsx`, lista e ficha no painel, selo no cartão/página do produto, checkout e textos em `resources/js/i18n/pt-BR.ts`.
- Mail: confirmação ao comprador e aviso à equipe (fila); link no e-mail de pedido entregue.
- Manual do painel: capítulos `pedidos`, `produtos` e `coordenadas`.
- Depende do encarregado para o texto final dos termos; a retenção dos pedidos (5 anos e anonimização) vem da change `enforce-data-retention`, que deve tratar as devoluções junto com o pedido.
