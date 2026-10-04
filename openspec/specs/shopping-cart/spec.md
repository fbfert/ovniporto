# shopping-cart Specification

## Purpose
Carrinho de compras guardado no servidor, ligado à sessão do visitante ou ao membro logado, com valores sempre calculados pelo servidor.

## Requirements

### Requirement: Carrinho no servidor
O carrinho SHALL ser mantido no servidor, associado à sessão do visitante ou ao membro autenticado, e o subtotal MUST ser calculado pelo servidor em centavos a partir dos preços atuais.

#### Scenario: Preço adulterado pelo cliente
- **WHEN** o cliente envia um preço diferente ao adicionar um item
- **THEN** o servidor ignora o valor enviado e usa o preço cadastrado

### Requirement: Carrinho do visitante passa ao membro
Ao fazer login, os itens do carrinho da sessão SHALL ser incorporados ao carrinho do membro.

#### Scenario: Login com carrinho
- **WHEN** um visitante com 2 itens no carrinho faz login
- **THEN** o carrinho do membro contém esses 2 itens

### Requirement: Drawer do carrinho
Adicionar um produto SHALL abrir o drawer do carrinho com itens, variantes, quantidades editáveis, subtotal e o botão "Finalizar compra", e atualizar o contador do header.

#### Scenario: Alterar quantidade
- **WHEN** a pessoa muda a quantidade de um item no drawer
- **THEN** o subtotal e o contador são atualizados com os valores do servidor

### Requirement: Limite de estoque no carrinho
Para itens de pronta entrega, a quantidade no carrinho MUST NOT exceder o estoque disponível.

#### Scenario: Quantidade acima do estoque
- **WHEN** a pessoa pede 600 adesivos com 500 em estoque
- **THEN** o servidor recusa e informa o máximo disponível
