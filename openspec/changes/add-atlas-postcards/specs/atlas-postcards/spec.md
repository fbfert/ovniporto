# Spec Delta

## ADDED Requirements

### Requirement: Selo de cada ilustração
Toda ilustração SHALL aparecer com selo visível. O selo SHALL ser "conceito" quando a imagem mostra como o OVNIPORTO vai ficar e "ilustração" quando reconstitui um relato, a origem ou um lugar de outro país. Nenhuma ilustração MUST ser apresentada como foto.

#### Scenario: Cena da origem
- **WHEN** a casa-cueva aparece em /origem/cachi
- **THEN** ela leva o selo "ilustração" e não o selo "conceito"

### Requirement: Cartão-postal de cada caso do Atlas
Cada caso do Atlas SHALL ter uma ilustração 4:3 no cartão da lista e no topo da página do caso, mantendo a foto ou o mapa documental com crédito. O caso Lages SHALL usar uma ilustração conceitual com selo "conceito".

#### Scenario: Caso só com mapa
- **WHEN** alguém abre /origem/atlas/green-river
- **THEN** vê a ilustração do aeroporto com selo "ilustração" e, abaixo, o mapa de localização com crédito

### Requirement: Ilustrações dos espaços em produção
Uma migration SHALL preencher a ilustração do Hangar e do Museu coberto somente quando ela estiver vazia, preservando a escolhida no painel.

#### Scenario: Ilustração do painel preservada
- **WHEN** a migration roda com uma ilustração já escolhida no painel
- **THEN** a do painel continua

### Requirement: Título do caso sem vazamento
O nome do caso SHALL caber na coluna sem rolagem horizontal e sem quebrar palavras no meio, em 390, 1024 e 1440 px.

#### Scenario: Nome longo
- **WHEN** a página de Lajas abre em 390 px
- **THEN** "Extraterrestre" fica inteiro numa linha e a página não rola para o lado
