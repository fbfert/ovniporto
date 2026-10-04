# content-admin Specification

## Purpose
Edição pelo painel de tudo que o público lê e que não é relato nem pedido: textos, configurações, lugar, obra, região, campanha e inscritos do Avise-me, preservando as regras de honestidade e consentimento.

## Requirements

### Requirement: Configurações e textos
O admin SHALL editar links de WhatsApp e Instagram, e-mail de contato, data de lançamento, metas, percentual da loja (vazio = oculto), blocos de conteúdo em markdown com prévia, FAQ e regras da comunidade.

#### Scenario: Prévia de markdown
- **WHEN** o admin edita um bloco de conteúdo
- **THEN** vê a prévia renderizada antes de salvar

### Requirement: Lugar e obra
O painel SHALL permitir editar e reordenar os espaços planejados, enviar fotos reais do terreno e ilustrações conceituais, definir o embed do mapa 3D apenas de domínios configurados e gerenciar posts da obra com prévia e agendamento.

#### Scenario: Embed de domínio não permitido
- **WHEN** alguém informa um iframe de domínio fora da lista configurada
- **THEN** o valor é recusado

#### Scenario: Post agendado
- **WHEN** um post da obra é salvo com publicação futura
- **THEN** ele só aparece em `/obra` a partir da data definida

### Requirement: Parceiro só publica com consentimento
O cadastro de parceiros da região SHALL incluir geocodificação do endereço com ajuste manual no mapa e o campo "consentimento recebido em" com comprovante opcional; sem esse campo, o botão Publicar MUST ficar desabilitado com explicação e o servidor MUST recusar a publicação.

#### Scenario: Publicar sem consentimento
- **WHEN** um operador tenta publicar parceiro sem data de consentimento, inclusive por requisição direta
- **THEN** a publicação é recusada

### Requirement: Campanha controlada pelo admin
Somente o admin SHALL alterar estado, meta, valor arrecadado, URL de crowdfunding, percentual da loja, apoiadores (com consentimento de publicação do nome, importação por CSV) e patrocinadores; a tela MUST exibir o aviso fixo "Não abra a campanha sem orçamento aprovado."

#### Scenario: Aviso sempre visível
- **WHEN** o admin abre `/painel/campanha`
- **THEN** o aviso fixo está presente

### Requirement: Inscritos do Avise-me
O painel SHALL listar inscritos do Avise-me com status da confirmação dupla, exportar CSV e remover inscritos.

#### Scenario: Remoção
- **WHEN** o admin remove um inscrito
- **THEN** o e-mail deixa de constar na lista e na exportação
