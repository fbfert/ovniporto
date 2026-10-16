# Spec Delta

## ADDED Requirements

### Requirement: Selo de frete incluso
Produtos marcados com frete incluso SHALL mostrar o selo "Frete incluso" no cartão em `/loja` e na página do produto, e o simulador de frete da página desses produtos SHALL listar as opções com o prazo e o preço exibido como "Incluso".

#### Scenario: Produto com frete incluso
- **WHEN** o visitante abre a página de um produto ativo com frete incluso e simula o frete com um CEP válido
- **THEN** vê o selo "Frete incluso" e as opções de envio com prazo e o preço "Incluso"

#### Scenario: Produto sem frete incluso
- **WHEN** o visitante abre um produto sem frete incluso
- **THEN** não há selo e o simulador mostra o preço de cada opção como hoje
