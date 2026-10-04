# campaign Specification

## Purpose
Controla a página `/apoie` e quando qualquer forma de arrecadação para a obra pode aparecer no site, garantindo que nada seja pedido antes de existir orçamento.

## Requirements

### Requirement: Estado da campanha
O sistema SHALL manter um único estado de campanha (`planning`, `open` ou `closed`) com meta, valor arrecadado, URL de crowdfunding e percentual da loja opcionais. A instalação inicial MUST ser `planning` com todos os demais campos vazios.

#### Scenario: Instalação nova
- **WHEN** o sistema é instalado com os dados iniciais
- **THEN** o estado da campanha é `planning` e meta, arrecadado, URL e percentual estão vazios

### Requirement: Apoie em modo planejamento
Enquanto o estado for `planning`, `/apoie` MUST NOT exibir placar, valores, botão ou link de pagamento ou de crowdfunding. A página SHALL explicar que o orçamento está sendo feito, mostrar "Como vai funcionar" e "O que o apoiador recebe", o selo "orçamento em planejamento", o Avise-me em destaque e o link "Enquanto isso, leve um adesivo" para `/loja`.

#### Scenario: Nenhum link de pagamento em planejamento
- **WHEN** o estado é `planning` e uma URL de crowdfunding foi preenchida por engano
- **THEN** a página não contém placar, valor arrecadado nem link para a URL de crowdfunding

### Requirement: Apoie em modo aberto
Quando o estado for `open`, `/apoie` SHALL exibir o placar com valor e meta, o botão "Apoiar no {plataforma}" para a URL de crowdfunding, o muro de apoiadores e a faixa de patrocinadores.

#### Scenario: Muro só com consentimento
- **WHEN** o estado é `open` e há apoiadores com e sem consentimento de publicação do nome
- **THEN** o muro lista apenas os nomes de quem consentiu

### Requirement: Aviso de vendas para a pista
O sistema MUST mostrar mensagens de "parte das vendas vai para a pista" somente quando o percentual da loja estiver definido.

#### Scenario: Percentual vazio
- **WHEN** o percentual da loja é nulo
- **THEN** nenhuma página exibe a promessa de percentual das vendas
