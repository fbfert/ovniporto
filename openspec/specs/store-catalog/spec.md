# store-catalog Specification

## Purpose
Vitrine pública da loja do OVNIPORTO: lista de produtos ativos, página de produto com variantes e prazos, e simulação de frete, mostrando só o que realmente pode ser vendido.

## Requirements

### Requirement: Só produtos ativos aparecem
A loja MUST listar e permitir abrir apenas produtos ativos; produtos inativos MUST responder 404 em `/loja/{slug}`. A instalação inicial SHALL ter somente o adesivo ativo.

#### Scenario: Loja recém-instalada
- **WHEN** a loja é aberta com os dados iniciais
- **THEN** só o adesivo aparece e `/loja/camiseta` (inativa) responde 404

### Requirement: Cartão de produto
Cada produto em `/loja` SHALL mostrar imagem, nome, preço em reais, preço anterior riscado quando houver, etiqueta opcional e o selo "pronta entrega" ou "feito sob pedido · {n} dias".

#### Scenario: Produto sob pedido
- **WHEN** um produto ativo é feito sob pedido com 7 dias de produção
- **THEN** o cartão mostra "feito sob pedido · 7 dias"

### Requirement: Bloco da pista condicionado
O bloco "Parte de cada venda vai virar pista" MUST aparecer apenas quando o percentual da loja estiver definido na campanha.

#### Scenario: Percentual nulo
- **WHEN** o percentual da loja é nulo
- **THEN** `/loja` não contém o bloco

### Requirement: Variantes e estoque
A página do produto SHALL permitir escolher variante e quantidade; variantes sem estoque ou inativas MUST aparecer desabilitadas e não podem ser adicionadas ao carrinho.

#### Scenario: Variante esgotada
- **WHEN** uma variante de produto de pronta entrega tem estoque zero
- **THEN** a opção aparece desabilitada e a tentativa de adicioná-la é recusada pelo servidor

### Requirement: Prazo de entrega exibido
A página do produto SHALL mostrar "Produção {n} dias + envio" para produtos sob pedido e "Pronta entrega" para produtos em estoque.

#### Scenario: Adesivo
- **WHEN** o visitante abre a página do adesivo
- **THEN** vê "Pronta entrega"

### Requirement: Simulador de frete
A página do produto SHALL simular o frete por CEP e listar opções com preço e prazo; CEP inválido MUST gerar mensagem de erro sem chamar o provedor.

#### Scenario: CEP válido
- **WHEN** o visitante informa um CEP válido
- **THEN** vê as opções de frete com preço e prazo

#### Scenario: CEP inválido
- **WHEN** o visitante informa um CEP com 5 dígitos
- **THEN** vê erro de CEP e nenhuma cotação é solicitada

### Requirement: Produtos relacionados e dados estruturados
A página do produto SHALL mostrar até 3 produtos ativos em "Combina com" e incluir dados estruturados de produto (nome, preço em BRL, disponibilidade).

#### Scenario: Dados estruturados
- **WHEN** um buscador lê a página do adesivo
- **THEN** encontra dados estruturados do tipo Product com preço e disponibilidade
