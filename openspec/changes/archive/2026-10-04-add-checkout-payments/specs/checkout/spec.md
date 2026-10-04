# Spec Delta

## Purpose

Fluxo de compra da loja do OVNIPORTO: identificação do cliente, entrega, total do pedido, ciclo de vida do pedido e acompanhamento pelo cliente.

## ADDED Requirements

### Requirement: Identificação do cliente
O checkout SHALL aceitar membro logado ou visitante com nome e e-mail, e MUST exigir telefone (WhatsApp) e CPF com dígitos verificadores válidos, explicando por que o CPF é pedido. O CPF MUST ser armazenado criptografado.

#### Scenario: CPF inválido
- **WHEN** o cliente informa um CPF com dígito verificador errado
- **THEN** a etapa não avança e o campo mostra o erro

#### Scenario: CPF guardado criptografado
- **WHEN** um pedido é criado com CPF válido
- **THEN** o valor armazenado no banco não é o CPF em texto claro

### Requirement: Endereço e método de entrega
A etapa de entrega SHALL preencher o endereço a partir do CEP, listar as opções de frete cotadas com preço e prazo e oferecer "Retirar em Lages" com frete zero.

#### Scenario: Retirada local
- **WHEN** o cliente escolhe "Retirar em Lages"
- **THEN** o frete do pedido é zero e não há cotação externa

### Requirement: Total calculado no servidor
O total do pedido MUST ser calculado no servidor, em centavos, como soma dos itens a preços atuais mais o frete escolhido, e é o único valor enviado ao provedor de pagamento.

#### Scenario: Total com frete
- **WHEN** o carrinho tem 2 adesivos de R$ 8,00 e o frete escolhido custa R$ 12,50
- **THEN** o total do pedido é 2850 centavos

### Requirement: Pedido com número e histórico
Cada pedido SHALL ter número legível no formato `OVP-AAAA-NNNNNN`, itens com cópia de nome, variante e preço no momento da compra, e um registro de eventos de status com o autor de cada mudança.

#### Scenario: Preço do produto muda depois
- **WHEN** o preço de um produto é alterado após um pedido pago
- **THEN** o pedido continua mostrando o preço pago

### Requirement: Transições de status válidas
O pedido SHALL seguir os status `pending_payment`, `paid`, `in_production`, `shipped`, `delivered`, com `canceled` e `refunded` como saídas; transições fora dessa ordem MUST ser recusadas.

#### Scenario: Pular etapa
- **WHEN** alguém tenta marcar como `shipped` um pedido `pending_payment`
- **THEN** a transição é recusada e nenhum evento é registrado

### Requirement: Efeitos do pagamento confirmado
Quando o pagamento for confirmado, o sistema SHALL marcar o pedido como `paid`, decrementar o estoque dos itens de pronta entrega e esvaziar o carrinho. O estoque MUST NOT ser decrementado antes disso.

#### Scenario: Pedido pendente não consome estoque
- **WHEN** um pedido de 3 adesivos é criado e ainda não foi pago
- **THEN** o estoque do adesivo não muda

### Requirement: Cancelamento automático
Pedidos em `pending_payment` há mais de 2 horas SHALL ser cancelados por rotina agendada, com evento registrado.

#### Scenario: Pedido abandonado
- **WHEN** a rotina roda e há um pedido pendente criado há 3 horas
- **THEN** o pedido passa a `canceled` com evento de autor "sistema"

### Requirement: Notificações do pedido
O cliente SHALL receber e-mails em português, em segundo plano, para pedido recebido, pagamento confirmado, em produção, enviado com rastreio e entregue, todos com o fecho "Guardei um lugar pra você."

#### Scenario: Pagamento confirmado
- **WHEN** um pedido passa a `paid`
- **THEN** um e-mail de pagamento confirmado é enfileirado para o cliente

### Requirement: Página do pedido
A página `/pedido/{number}` SHALL mostrar linha do tempo de status, itens, endereço e rastreio, e MUST ser acessível apenas com token assinado enviado por e-mail ou pelo membro dono.

#### Scenario: Sem token
- **WHEN** alguém abre `/pedido/{number}` sem token válido e sem ser o dono
- **THEN** recebe 403 ou 404
