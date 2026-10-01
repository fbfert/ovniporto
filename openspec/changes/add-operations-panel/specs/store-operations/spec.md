# Spec Delta

## Purpose

Operação da loja pelo painel: acompanhamento e avanço dos pedidos, etiquetas, reembolsos, documentos de produção e cadastro de produtos e estoque.

## ADDED Requirements

### Requirement: Lista e ficha do pedido
O painel SHALL listar pedidos por status com número, cliente, itens, total, método de pagamento e idade, e a ficha SHALL mostrar itens, cliente, endereço, linha do tempo e dados do pagamento. O CPF MUST aparecer mascarado e só ser revelado por clique, com registro de auditoria.

#### Scenario: Revelar CPF
- **WHEN** um operador da loja revela o CPF de um pedido
- **THEN** o CPF completo aparece e um registro de auditoria é criado

### Requirement: Ações por status
O painel SHALL oferecer apenas as ações válidas para o status atual: pago → "Marcar em produção" (com resumo copiável para o fornecedor); em produção → "Gerar etiqueta" e "Marcar enviado"; enviado → "Marcar entregue"; qualquer → "Cancelar" com motivo. Transições inválidas MUST falhar.

#### Scenario: Etiqueta em pedido não pago
- **WHEN** alguém solicita etiqueta para um pedido `pending_payment`
- **THEN** a operação falha e nenhum envio é criado

#### Scenario: Marcar enviado
- **WHEN** um pedido em produção com rastreio é marcado como enviado
- **THEN** o cliente recebe e-mail com o rastreio

### Requirement: Reembolso
Para pedido pago, o painel SHALL permitir reembolso via provedor de pagamento após confirmação explícita, registrando evento no pedido.

#### Scenario: Reembolso registrado
- **WHEN** o operador confirma o reembolso de um pedido pago
- **THEN** o pedido passa a `refunded` e há um evento de reembolso com o autor

### Requirement: Documentos e exportação
O painel SHALL gerar a ordem de produção em PDF (selo, itens e variantes) e exportar pedidos de um período em CSV.

#### Scenario: Exportar período
- **WHEN** o operador exporta os pedidos de um mês
- **THEN** recebe um CSV com um pedido por linha daquele período

### Requirement: Cadastro de produtos
O painel SHALL permitir criar, editar, ativar e desativar produtos, variantes e imagens (corte quadrado, reordenação, texto alternativo obrigatório), com prévia do cartão ao vivo.

#### Scenario: Imagem sem texto alternativo
- **WHEN** o operador salva uma imagem de produto sem texto alternativo
- **THEN** o salvamento é recusado

### Requirement: Estoque com histórico
Ajustes de estoque de itens de pronta entrega SHALL ser registrados em histórico com autor, quantidade e motivo.

#### Scenario: Ajuste manual
- **WHEN** o operador ajusta o estoque do adesivo de 500 para 480
- **THEN** o histórico registra -20 com autor e motivo
