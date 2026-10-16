# Spec Delta

## MODIFIED Requirements

### Requirement: Total calculado no servidor
O total do pedido MUST ser calculado no servidor, em centavos, como soma dos itens a preços atuais mais o frete cobrado do comprador, e é o único valor enviado ao provedor de pagamento.

#### Scenario: Total com frete
- **WHEN** o carrinho tem 2 adesivos de R$ 8,00 e o frete escolhido custa R$ 12,50
- **THEN** o total do pedido é 2850 centavos

#### Scenario: Carrinho só com frete incluso
- **WHEN** todos os itens do carrinho têm frete incluso e o comprador escolhe uma opção de envio cotada em R$ 20,00
- **THEN** o frete cobrado é zero e o total do pedido é a soma dos itens

## ADDED Requirements

### Requirement: Frete cobrado com itens de frete incluso
Quando o carrinho mistura itens com e sem frete incluso, o frete cobrado de cada opção SHALL ser o preço cotado para o carrinho inteiro multiplicado pela fração do peso dos itens sem frete incluso, arredondado para cima ao centavo; a etapa de entrega MUST mostrar esse valor cobrado e indicar quando há frete incluso.

#### Scenario: Carrinho misto
- **WHEN** o carrinho pesa 400 g, dos quais 100 g são de itens sem frete incluso, e a opção escolhida é cotada em R$ 20,00
- **THEN** a etapa de entrega mostra R$ 5,00 para essa opção e o pedido cobra 500 centavos de frete

#### Scenario: Carrinho sem peso cadastrado
- **WHEN** nenhum item do carrinho tem peso cadastrado e há pelo menos um item sem frete incluso
- **THEN** o frete cobrado é o preço cotado inteiro

#### Scenario: Retirada continua grátis
- **WHEN** o comprador escolhe "Retirar em Lages"
- **THEN** o frete cobrado é zero, haja ou não itens com frete incluso

### Requirement: Custo real do envio registrado
O pedido SHALL guardar, além do frete cobrado, o preço cotado da opção escolhida para o carrinho inteiro, e cada item SHALL guardar se tinha frete incluso no momento da compra.

#### Scenario: Pedido com frete absorvido
- **WHEN** um pedido misto é criado com opção cotada em R$ 20,00 e frete cobrado de R$ 5,00
- **THEN** o pedido registra 2000 centavos de custo do envio e 500 centavos de frete cobrado
