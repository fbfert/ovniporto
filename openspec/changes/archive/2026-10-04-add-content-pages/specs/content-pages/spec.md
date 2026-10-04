# Spec Delta

## Purpose

Páginas editoriais públicas do OVNIPORTO (lenda, FAQ, comunidade, privacidade e termos), renderizadas no servidor, com texto editável e estados honestos quando o conteúdo ainda não existe.

## ADDED Requirements

### Requirement: Páginas de conteúdo públicas
O sistema SHALL servir `/lenda`, `/faq`, `/comunidade`, `/privacidade` e `/termos` para visitantes anônimos, renderizadas no servidor, com título no padrão do layout público e textos vindos de blocos de conteúdo editáveis.

#### Scenario: Visitante abre uma página de conteúdo
- **WHEN** um visitante sem login acessa qualquer uma das cinco rotas
- **THEN** a resposta é 200, o HTML já contém o título da página e o conteúdo principal sem depender de JavaScript

### Requirement: Origem real da lenda
A página `/lenda` SHALL apresentar a origem real do projeto (visita ao Ovnipuerto de Cachi) e uma linha do tempo expansível de Cachi com dados fixos, sem apresentar a lenda criada como fato.

#### Scenario: Linha do tempo de Cachi
- **WHEN** o visitante aciona "Saiba mais sobre Cachi"
- **THEN** a linha do tempo de Cachi é expandida de forma acessível por teclado e leitor de tela

### Requirement: Lenda aguardando conteúdo
Enquanto o texto da lenda estiver vazio, o bloco "O carro amarelo" MUST exibir o estado "aguardando conteúdo" com a legenda "Essa história ainda está sendo escrita." e o botão "Me avise quando sair", que usa o mesmo cadastro Avise-me da home. O sistema MUST NOT inventar texto de lenda.

#### Scenario: Lenda sem texto
- **WHEN** o bloco de conteúdo da lenda está vazio
- **THEN** a página mostra o estado "aguardando conteúdo", a ilustração marcada como conceito e o botão de Avise-me

#### Scenario: Lenda publicada
- **WHEN** o bloco de conteúdo da lenda tem texto em markdown
- **THEN** a página renderiza o markdown com tipografia de leitura e não mostra o estado de espera

### Requirement: Perguntas frequentes
A página `/faq` SHALL listar pares pergunta/resposta editáveis em um acordeão acessível com no máximo uma resposta aberta por vez. A instalação inicial MUST conter as 10 perguntas do plano (o lugar existe?, é de graça?, como relatar?, entre outras).

#### Scenario: Abrir uma pergunta fecha a outra
- **WHEN** uma resposta está aberta e o visitante abre outra pergunta
- **THEN** a anterior fecha, a nova abre e o estado expandido é anunciado por `aria-expanded`

### Requirement: Página da comunidade
A página `/comunidade` SHALL exibir as regras de convivência editáveis (5 regras iniciais), links para o grupo de WhatsApp e o Instagram configurados e o formulário Avise-me.

#### Scenario: Regras e canais
- **WHEN** o visitante abre `/comunidade`
- **THEN** vê as regras, os dois links externos e o formulário de inscrição

### Requirement: Privacidade e termos em rascunho marcado
As páginas `/privacidade` e `/termos` SHALL exibir índice de tópicos, data de atualização e, enquanto o texto final não existir, o aviso "RASCUNHO — texto final será redigido pelo encarregado (Felipe)". O sistema MUST NOT apresentar texto jurídico inventado como versão final.

#### Scenario: Rascunho visível
- **WHEN** o texto jurídico ainda é o inicial
- **THEN** a página mostra o aviso de rascunho e os tópicos: dados coletados, finalidades, bases legais, compartilhamento (PayPal, Melhor Envio, Google), retenção, direitos do titular, contato do encarregado, cookies e alterações
