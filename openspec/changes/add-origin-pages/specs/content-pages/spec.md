# Spec Delta

## MODIFIED Requirements

### Requirement: Páginas de conteúdo públicas
O sistema SHALL servir `/faq`, `/comunidade`, `/privacidade` e `/termos` para visitantes anônimos, renderizadas no servidor, com título no padrão do layout público e textos vindos de blocos de conteúdo editáveis. A antiga `/lenda` passou para a capacidade `origin-pages` como `/origem`.

#### Scenario: Visitante abre uma página de conteúdo
- **WHEN** um visitante sem login acessa qualquer uma das quatro rotas
- **THEN** a resposta é 200, o HTML já contém o título da página e o conteúdo principal sem depender de JavaScript

## REMOVED Requirements

### Requirement: Origem real da lenda
**Reason**: a página da lenda virou o hub `/origem`, com a página documental de Cachi e o Atlas.
**Migration**: os requisitos "Hub da origem" e "Página documental de Cachi" em `origin-pages`; `/lenda` redireciona com 301.

### Requirement: Lenda aguardando conteúdo
**Reason**: o bloco do carro amarelo mudou para o hub `/origem`.
**Migration**: o requisito "Relato do carro amarelo com estado honesto" em `origin-pages`, com o mesmo texto editável e o mesmo Avise-me.
