# Spec Delta

## Purpose

Cotação de frete, retirada em Lages, geração de etiqueta e rastreio dos envios da loja por meio de um provedor de logística externo.

## ADDED Requirements

### Requirement: Cotação de frete
O sistema SHALL cotar o frete pelo CEP de destino e pelo peso e dimensões dos itens, devolvendo para cada opção o nome do serviço, o preço em centavos e o prazo em dias.

#### Scenario: Provedor indisponível
- **WHEN** o provedor de frete não responde
- **THEN** o cliente vê uma mensagem de indisponibilidade e a opção "Retirar em Lages" continua disponível

### Requirement: Etiqueta só para pedido pago
A geração de etiqueta SHALL criar o envio no provedor e salvar código e URL de rastreio, e MUST ser recusada para pedidos que não estejam pagos ou em produção.

#### Scenario: Pedido não pago
- **WHEN** alguém pede etiqueta para um pedido `pending_payment`
- **THEN** a operação é recusada e o provedor não é chamado

### Requirement: Consulta de rastreio
O sistema SHALL consultar o rastreio dos pedidos enviados e permitir marcar como entregue a partir do status do provedor.

#### Scenario: Entrega detectada
- **WHEN** a consulta diária indica que o envio foi entregue
- **THEN** o pedido passa a `delivered` com evento de autor "sistema"
