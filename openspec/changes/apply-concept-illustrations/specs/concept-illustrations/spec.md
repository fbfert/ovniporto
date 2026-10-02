# Spec Delta

## Purpose

Define como as ilustrações conceituais do OVNIPORTO chegam ao site: em formatos leves e responsivos, sempre rotuladas como conceito e nunca apresentadas como o lugar já pronto.

## ADDED Requirements

### Requirement: Ilustrações sempre rotuladas como conceito
Toda ilustração conceitual exibida no site SHALL ter a etiqueta visível "conceito" e um texto alternativo em português que a descreva como ilustração. O site MUST NOT apresentar uma ilustração como foto do lugar.

#### Scenario: Visitante vê um espaço com ilustração
- **WHEN** um cartão de espaço do lugar tem ilustração
- **THEN** a imagem aparece com a etiqueta "conceito" e um alt que começa por "Ilustração conceitual"

#### Scenario: Espaço sem ilustração
- **WHEN** um espaço não tem ilustração cadastrada
- **THEN** o cartão aparece sem imagem e sem quebrar o layout

### Requirement: Imagens responsivas e leves
As ilustrações SHALL ser servidas em AVIF e WebP com várias larguras e um fallback JPEG, sem metadados. Fora da capa MUST carregar sob demanda (lazy); a capa MUST carregar com prioridade.

#### Scenario: Navegador moderno no celular
- **WHEN** a home abre em 390 px de largura
- **THEN** o navegador recebe a variante AVIF de menor largura suficiente e as ilustrações abaixo da dobra só carregam ao se aproximar da viewport

### Requirement: Capa com a ilustração da pista
A capa da home SHALL mostrar a ilustração da pista à noite como fundo, com véu escuro para legibilidade do selo; ao começar a abdução guiada pelo scroll, a ilustração SHALL se apagar e dar lugar à cena animada. Em movimento reduzido, a ilustração SHALL ficar estática como fundo.

#### Scenario: Primeiro segundo da home
- **WHEN** o visitante abre a home sem rolar
- **THEN** vê a ilustração da pista com o selo e a pílula "Vigília grátis" por cima

#### Scenario: Visitante rola a capa
- **WHEN** a abdução começa
- **THEN** a ilustração esmaece até sumir e a cena animada continua como antes

### Requirement: Imagem de compartilhamento sobre a capa
A imagem padrão de compartilhamento (1200×630) SHALL ser o adesivo sobre a ilustração da capa, com "Lages · SC".

#### Scenario: Link enviado no WhatsApp
- **WHEN** alguém compartilha a home
- **THEN** a prévia mostra o adesivo sobre a ilustração da capa
