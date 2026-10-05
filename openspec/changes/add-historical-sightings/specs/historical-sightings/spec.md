# Spec Delta

## ADDED Requirements

### Requirement: Casos históricos separados dos relatos
A página `/mapa` SHALL mostrar os casos históricos numa seção própria, agrupados por região, e MUST NOT misturá-los com os relatos da comunidade nem atribuí-los a membros.

#### Scenario: Lista do Livro
- **WHEN** alguém abre `/mapa`
- **THEN** vê os relatos da comunidade e, abaixo, "Casos históricos" com os 12 casos em Santa Catarina, Brasil e Mundo

### Requirement: Página de cada caso
Cada caso SHALL ter página em `/mapa/casos/{slug}` com data, local, resumo, situação da documentação e fontes com link. A ilustração MUST levar o selo "ilustração" e a legenda de reconstituição artística gerada por IA.

#### Scenario: Caso inexistente
- **WHEN** alguém pede `/mapa/casos/nao-existe`
- **THEN** recebe 404

### Requirement: Marcador próprio no mapa
Os casos históricos SHALL aparecer no mapa com marcador diferente do dos relatos, numa camada que não é agrupada com eles nem usada para enquadrar o mapa, e a legenda SHALL dizer que a posição é aproximada.

#### Scenario: Mapa abre na serra
- **WHEN** o mapa carrega
- **THEN** o enquadramento segue só os relatos da comunidade, e os casos do mundo aparecem ao afastar o zoom
