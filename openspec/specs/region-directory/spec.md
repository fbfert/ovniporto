# region-directory Specification

## Purpose
Diretório público de parceiros da região de Lages (pousadas, passeios, produtores, comida), publicado apenas com consentimento registrado de cada parceiro.

## Requirements

### Requirement: Publicação exige consentimento
Um parceiro MUST ser visível publicamente somente se tiver data de consentimento registrada e data de publicação no passado. Tentativas de publicar sem consentimento MUST ser recusadas pelo sistema.

#### Scenario: Parceiro sem consentimento
- **WHEN** um parceiro tem data de publicação mas não tem data de consentimento
- **THEN** ele não aparece em `/regiao`, no mapa nem em `/regiao/{slug}` (404)

#### Scenario: Tentativa de publicar sem consentimento
- **WHEN** alguém tenta publicar um parceiro sem data de consentimento
- **THEN** a operação falha com erro de validação de domínio

### Requirement: Lista filtrável
A página `/regiao` SHALL listar os parceiros publicados em cartões com foto, nome, tipo, cidade e distância até o OVNIPORTO, com filtro por tipo (Todos, Pousadas, Passeios, Produtores, Comida) e busca por texto aplicados sem recarregar a página e refletidos na URL.

#### Scenario: Filtrar por tipo
- **WHEN** o visitante escolhe "Pousadas"
- **THEN** só parceiros do tipo pousada aparecem e a URL passa a conter o filtro

### Requirement: Distância aproximada
O sistema SHALL calcular no servidor a distância em linha reta entre o parceiro e o ponto -27.85495, -50.21841, arredondada para km inteiros.

#### Scenario: Distância exibida
- **WHEN** um parceiro está a 12,4 km do OVNIPORTO
- **THEN** o cartão mostra "12 km"

### Requirement: Alternância Lista e Mapa
A página SHALL oferecer a visão de mapa com todos os parceiros filtrados e o marcador do OVNIPORTO destacado.

#### Scenario: Ver no mapa
- **WHEN** o visitante escolhe "Mapa"
- **THEN** vê os parceiros filtrados como marcadores e o OVNIPORTO destacado

### Requirement: Página do parceiro
A rota `/regiao/{slug}` SHALL mostrar capa, galeria, descrição, contatos como botões (WhatsApp, Instagram, site, ligar) apenas para os campos preenchidos, mini-mapa e link "Como chegar" com origem no OVNIPORTO.

#### Scenario: Contato ausente
- **WHEN** o parceiro não tem Instagram cadastrado
- **THEN** o botão de Instagram não aparece

### Requirement: Estado vazio da região
Sem parceiros publicados, `/regiao` MUST mostrar ilustração placeholder, o texto "A lista está sendo montada com as pousadas e produtores da região." e o botão "Quero aparecer aqui" para o e-mail de contato. O sistema MUST NOT exibir parceiros inventados em produção.

#### Scenario: Lista vazia
- **WHEN** não há parceiros publicados
- **THEN** a página responde 200 com o estado vazio e o botão de contato
