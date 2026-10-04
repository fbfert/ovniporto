# Spec Delta

## Purpose

Torna públicos os relatos aprovados do Livro de avistamentos em mapa, lista, API e página individual, expondo apenas campos públicos e nunca relatos não aprovados.

## ADDED Requirements

### Requirement: Somente relatos aprovados são públicos
Relatos com status diferente de `approved` MUST NOT aparecer em `/mapa`, na API pública, na home nem em `/relatos/{id}` para o público.

#### Scenario: Relato pendente na API
- **WHEN** existe um relato `pending` e outro `approved`
- **THEN** a API pública retorna apenas o aprovado

#### Scenario: Relato em ajuste acessado pelo público
- **WHEN** um visitante abre `/relatos/{id}` de um relato `changes_requested`
- **THEN** recebe 404

#### Scenario: Autor vê o próprio relato em análise
- **WHEN** o autor abre `/relatos/{id}` do seu relato `pending`
- **THEN** vê a página com a faixa "Em análise"

### Requirement: API pública com campos mínimos
`GET /api/sightings` SHALL retornar JSON apenas com id, tipo, latitude, longitude, data, apelido e miniatura de relatos aprovados, aceitando os mesmos filtros de período e tipo, com cache de até 60 s. Nome real, e-mail e id do membro MUST NOT aparecer.

#### Scenario: Campos da resposta
- **WHEN** um cliente chama a API
- **THEN** cada item contém só os sete campos públicos

### Requirement: Mapa do livro
A página `/mapa` SHALL mostrar o total de relatos aprovados, um mapa OpenStreetMap em largura total com marcadores agrupados e popup em formato de polaroid (foto, tipo, data, apelido, "Ver relato").

#### Scenario: Contador
- **WHEN** há 12 relatos aprovados
- **THEN** a capa mostra "12 relatos aprovados"

### Requirement: Filtros na URL
A página SHALL filtrar por período (Últimos 30 dias, 6 meses, 1 ano, Tudo) e por tipo, atualizando mapa e lista sem recarregar a página e mantendo o estado na URL.

#### Scenario: Link com filtro
- **WHEN** alguém abre `/mapa` com o filtro de tipo "Luz" na URL
- **THEN** mapa e lista já chegam filtrados por "Luz"

### Requirement: Lista em polaroids
Abaixo do mapa, a página SHALL listar os relatos filtrados em grade de polaroids com paginação "Carregar mais", servindo também como alternativa acessível ao mapa.

#### Scenario: Carregar mais
- **WHEN** há mais relatos que o tamanho da página e o visitante aciona "Carregar mais"
- **THEN** a próxima página é anexada sem perder os filtros

### Requirement: Página do relato
A rota `/relatos/{id}` SHALL exibir galeria com lightbox, tipo, data e faixa ou hora, direção do olhar, apelido, descrição, mini-mapa e o carimbo "APROVADO PELA TORRE" com a data de publicação.

#### Scenario: Relato aprovado
- **WHEN** um visitante abre um relato aprovado
- **THEN** vê os dados públicos e o carimbo com a data de publicação

### Requirement: Mande um postal
A página do relato SHALL oferecer compartilhamento no WhatsApp com o texto "Olha o que viram no céu de Lages: {url}" e a cópia do link.

#### Scenario: Compartilhar no WhatsApp
- **WHEN** o visitante aciona o compartilhamento no WhatsApp
- **THEN** abre o WhatsApp com o texto e a URL absoluta do relato

### Requirement: Relatos próximos
A página do relato SHALL listar até 3 outros relatos aprovados num raio de 20 km.

#### Scenario: Sem vizinhos
- **WHEN** não há outros relatos aprovados a até 20 km
- **THEN** o bloco não é exibido
