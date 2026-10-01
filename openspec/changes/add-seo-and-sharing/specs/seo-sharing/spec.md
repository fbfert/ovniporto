# Spec Delta

## Purpose

Garante que cada página pública do OVNIPORTO seja encontrável e gere prévias ricas ao ser compartilhada, e mede o uso sem cookies nem rastreamento de terceiros.

## ADDED Requirements

### Requirement: Metadados em toda página pública
Toda página pública SHALL ter, no HTML renderizado no servidor, título no padrão "{Página} · OVNIPORTO Lages", descrição única, URL canônica absoluta e tags Open Graph e Twitter Card com imagem 1200×630.

#### Scenario: Prévia sem JavaScript
- **WHEN** um robô de prévia sem JavaScript busca `/loja`
- **THEN** encontra título, descrição, canonical e imagem OG no HTML

### Requirement: Imagens OG dinâmicas
Relatos aprovados, produtos ativos, parceiros publicados e posts publicados SHALL ter imagem OG própria gerada pelo sistema, mantida em cache e regenerada quando o conteúdo é editado. Conteúdo não público MUST NOT ter imagem OG acessível.

#### Scenario: Relato editado
- **WHEN** a primeira foto de um relato aprovado muda
- **THEN** a próxima requisição da imagem OG reflete a nova foto

#### Scenario: Relato pendente
- **WHEN** alguém pede a imagem OG de um relato pendente
- **THEN** recebe 404

### Requirement: Sitemap e robots
O sistema SHALL servir `sitemap.xml` com as páginas públicas e conteúdos publicados, e `robots.txt` que bloqueia `/painel` e `/conta`.

#### Scenario: Área privada fora do índice
- **WHEN** um buscador lê `robots.txt`
- **THEN** `/painel` e `/conta` estão marcados como não permitidos e não constam do sitemap

### Requirement: Dados estruturados
O sistema SHALL publicar dados estruturados: Organization na home, Product em produtos, Article em posts da obra, Place em `/o-lugar` com coordenadas e FAQPage em `/faq`.

#### Scenario: FAQ estruturado
- **WHEN** um buscador lê `/faq`
- **THEN** encontra FAQPage com as perguntas e respostas publicadas

### Requirement: Verificação de prévia
O sistema SHALL oferecer um comando que busca uma URL e imprime as metatags de prévia encontradas.

#### Scenario: Conferir uma página
- **WHEN** o operador executa a verificação para a URL da home
- **THEN** vê título, descrição e imagem que o WhatsApp vai usar

### Requirement: Postal compartilhável
A página `/postal` SHALL gerar a imagem de postal (selo, "Lages · SC" e frase) para baixar e oferecer compartilhamento nativo do aparelho, com alternativas de WhatsApp e copiar link quando o nativo não existir.

#### Scenario: Navegador sem compartilhamento nativo
- **WHEN** o navegador não suporta compartilhamento nativo
- **THEN** aparecem os botões de WhatsApp e copiar link

### Requirement: Métrica sem cookie
A métrica de uso SHALL ser carregada apenas em produção, MUST NOT criar cookies nem usar serviços de terceiros, e SHALL registrar os eventos entrar_comunidade, relatar_iniciado, relatar_enviado, adicionar_carrinho, compra_concluida e avise_me.

#### Scenario: Ambiente local
- **WHEN** a aplicação roda fora de produção
- **THEN** o script de métrica não é carregado
