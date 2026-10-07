# Spec Delta

## ADDED Requirements

### Requirement: Salvar e voltar no cadastro
O cadastro de um produto novo SHALL oferecer dois botões: **Salvar**, que cria o produto e abre a ficha dele, e **Salvar e voltar**, que cria o produto e volta para a lista de produtos.

#### Scenario: Salvar abre a ficha
- **WHEN** o operador cria um produto com **Salvar**
- **THEN** o painel abre a ficha do produto criado

#### Scenario: Salvar e voltar
- **WHEN** o operador cria um produto com **Salvar e voltar**
- **THEN** o produto é criado e o painel volta para `/painel/produtos` com o aviso de produto criado

### Requirement: Medidas com décimos de milímetro
Comprimento, largura e altura da embalagem SHALL ser informados em centímetros com até duas casas decimais (0,01 cm), entre 0,01 e 100 cm. Valores com mais casas ou menores que 0,01 MUST ser recusados com mensagem em português. A loja SHALL mostrar as medidas com vírgula decimal.

#### Scenario: Medida com duas casas
- **WHEN** o operador salva 21,05 × 14,8 × 0,02 cm
- **THEN** o produto guarda exatamente essas medidas e a loja mostra "21,05 × 14,8 × 0,02 cm"

#### Scenario: Casas demais
- **WHEN** o operador informa 21,055 cm
- **THEN** o salvamento é recusado com "Use no máximo duas casas: 0,01 cm é um décimo de milímetro."
