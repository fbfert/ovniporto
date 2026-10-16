# Spec Delta

## ADDED Requirements

### Requirement: Frete incluso no cadastro do produto
O cadastro de produtos no painel SHALL ter o campo "Frete incluso", desmarcado por padrão, com a explicação de que o OVNIPORTO paga o envio desse produto; a mudança MUST gerar registro de auditoria e vale para pedidos feitos depois dela.

#### Scenario: Marcar frete incluso
- **WHEN** o operador marca "Frete incluso" em um produto e salva
- **THEN** a loja passa a mostrar o selo nesse produto e a auditoria registra a mudança

#### Scenario: Pedido antigo não muda
- **WHEN** um produto passa a ter frete incluso depois de um pedido já criado
- **THEN** o frete cobrado e os itens desse pedido continuam como estavam

### Requirement: Frete absorvido na ficha do pedido
A ficha do pedido no painel SHALL mostrar o frete cobrado do comprador e, quando ele for menor que o custo cotado do envio, o valor absorvido pelo OVNIPORTO; os itens com frete incluso MUST aparecer identificados.

#### Scenario: Pedido misto
- **WHEN** o operador abre um pedido com custo cotado de R$ 20,00 e frete cobrado de R$ 5,00
- **THEN** a ficha mostra "Frete cobrado R$ 5,00" e "Frete incluso pago pelo OVNIPORTO R$ 15,00"
