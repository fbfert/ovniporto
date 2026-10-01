# Spec Delta

## Purpose

Diário público da obra do OVNIPORTO: posts datados por fase, página individual e feed RSS, com estado vazio honesto enquanto a obra não começa.

## ADDED Requirements

### Requirement: Lista de posts da obra
A página `/obra` SHALL listar apenas posts com data de publicação no passado, em ordem cronológica inversa, com capa, título, fase e data.

#### Scenario: Post agendado não aparece
- **WHEN** um post tem data de publicação futura
- **THEN** ele não aparece na lista, na página individual nem no RSS

### Requirement: Estado vazio da obra
Sem posts publicados, `/obra` MUST exibir "A obra ainda não começou. O primeiro post será o dia em que a primeira pedra for colocada." com a ilustração conceitual da pista.

#### Scenario: Nenhum post
- **WHEN** não há posts publicados
- **THEN** a página mostra o estado vazio e responde 200

### Requirement: Página do post
A rota `/obra/{slug}` SHALL exibir o markdown renderizado e a galeria do post publicado e MUST responder 404 para slug inexistente ou não publicado.

#### Scenario: Slug não publicado
- **WHEN** o visitante abre o slug de um post agendado
- **THEN** recebe 404

### Requirement: Feed RSS
O sistema SHALL servir `/obra.rss` como RSS 2.0 válido com os posts publicados.

#### Scenario: Feed com posts
- **WHEN** um leitor de feed busca `/obra.rss`
- **THEN** recebe XML RSS 2.0 com um item por post publicado, com título, link absoluto e data
