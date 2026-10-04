# Spec Delta

## Purpose

Apresenta ao público o lugar físico planejado do OVNIPORTO (localização, espaços por fase e conceito visual), sempre como projeto futuro e nunca como lugar em funcionamento.

## ADDED Requirements

### Requirement: Lugar descrito como projeto
A página `/o-lugar` MUST descrever o lugar físico como planejado (meta 2028) e MUST NOT usar linguagem, horários, preços de entrada ou chamadas que indiquem que ele já existe ou está aberto ao público.

#### Scenario: Selo de meta
- **WHEN** o visitante abre `/o-lugar`
- **THEN** a capa mostra o selo "meta 2028" e a referência "Ao lado da Hospedaria Vila das Pedras"

#### Scenario: Nenhum espaço aparece como aberto antes da hora
- **WHEN** nenhum espaço tem status `open`
- **THEN** nenhum cartão de espaço exibe o selo de aberto e todos mostram o status de planejamento ou obra

### Requirement: Localização no mapa sob demanda
A página SHALL mostrar um mapa OpenStreetMap centrado em -27.85495, -50.21841 com marcador próprio, sem interação até o visitante ativá-lo, e um link "Abrir no Google Maps".

#### Scenario: Mapa inerte até o clique
- **WHEN** a página carrega
- **THEN** o mapa não captura rolagem nem gestos até o visitante clicar nele

### Requirement: Terreno hoje e conceito
A página SHALL exibir "O terreno hoje" com as fotos reais cadastradas (galeria com lightbox acessível) e "Como vai ficar" com ilustração marcada como "conceito". Sem fotos, MUST mostrar um estado vazio tracejado; o mapa 3D MUST aparecer como "Em produção pela arquiteta do projeto" até existir.

#### Scenario: Sem fotos do terreno
- **WHEN** não há fotos do terreno cadastradas
- **THEN** o bloco mostra o estado vazio e não usa imagens de outro lugar

### Requirement: Espaços por fase
A página SHALL listar os espaços planejados agrupados nas fases 1 a 4, em linha do tempo, com nome, descrição, status e ilustração quando houver; a instalação inicial MUST conter os 9 espaços do plano e a fase 1 MUST ter destaque visual.

#### Scenario: Nove espaços em quatro fases
- **WHEN** a página é aberta com os dados iniciais
- **THEN** aparecem 5 espaços na fase 1, 1 na fase 2 (Aduana + Loja), 1 na fase 3 e 2 na fase 4

### Requirement: Regras do céu escuro e aviso
A página SHALL apresentar as três regras do céu escuro e terminar com o convite "Avise-me quando a campanha abrir" usando o cadastro Avise-me existente.

#### Scenario: Fecho com Avise-me
- **WHEN** o visitante chega ao fim da página
- **THEN** encontra o formulário Avise-me funcional
