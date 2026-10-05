# Spec Delta

## MODIFIED Requirements

### Requirement: Dez seções em ordem
A home SHALL renderizar, nesta ordem: capa, boas-vindas, Livro de avistamentos, faixa corrida, O lugar, lembranças, a origem, conheça a região, comunidade e postal, rodapé.

#### Scenario: Ordem das seções
- **WHEN** a home é renderizada no servidor
- **THEN** o HTML contém as seções na ordem definida e o texto "OVNIPORTO"

## ADDED Requirements

### Requirement: Seção da origem
A seção da origem SHALL exibir o sobretítulo "De Cachi a Lages", o título "A origem", o texto curto editável da home, uma imagem com crédito ou selo "conceito" e o link "Conhecer a origem" para `/origem`.

#### Scenario: Link para a origem
- **WHEN** o visitante vê a seção da origem na home
- **THEN** o link leva a `/origem` e nenhum texto da seção usa a palavra "lenda"

## REMOVED Requirements

### Requirement: Lenda aguardando conteúdo
**Reason**: a seção deixou de ser "a lenda" e passou a apresentar a origem, que já tem conteúdo.
**Migration**: o requisito "Seção da origem"; o estado de espera da história do carro vive no hub `/origem` ("Relato do carro amarelo com estado honesto").
