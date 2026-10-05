# Spec Delta

## Purpose

Permite que leitores do Atlas ofereçam ajuda à pesquisa da origem, com consentimento explícito, e que a equipe veja e gerencie essas ofertas no painel.

## ADDED Requirements

### Requirement: Convite a colaborar no Atlas
A página `/origem/atlas` SHALL mostrar, logo abaixo da lista "Limites desta pesquisa", um link que abre em modal o formulário "Seja colaborador". O modal SHALL prender o foco, fechar com Esc e devolver o foco ao link.

#### Scenario: Abrir o formulário
- **WHEN** a pessoa toca em "Quero colaborar com a pesquisa"
- **THEN** abre um diálogo com nome, e-mail, cidade e país, formas de ajudar, mensagem e consentimento

### Requirement: Envio de oferta
O sistema SHALL aceitar em `POST /colaborar` nome, e-mail, cidade e país, ao menos uma forma de ajudar da lista fechada, mensagem de 10 a 2.000 caracteres e consentimento marcado. Ao aceitar, SHALL guardar a oferta com o texto do consentimento, registrar o consentimento `colaboracao` com versão, e enfileirar um e-mail de agradecimento à pessoa e um aviso a cada administrador. O aviso MUST NOT conter nome, e-mail ou mensagem.

#### Scenario: Oferta válida
- **WHEN** alguém envia o formulário completo com consentimento
- **THEN** a oferta é guardada, o consentimento é registrado e os dois e-mails entram na fila

#### Scenario: Sem consentimento
- **WHEN** o consentimento não está marcado
- **THEN** nada é guardado e a resposta explica em português o que falta

### Requirement: Proteção contra abuso
O envio SHALL ser limitado a 5 por 10 minutos por IP. Um envio com o campo-armadilha preenchido SHALL receber a mesma resposta de sucesso e MUST NOT guardar dados nem enviar e-mails.

#### Scenario: Robô preenche o honeypot
- **WHEN** o campo `website` chega preenchido
- **THEN** a resposta é a mesma de uma oferta aceita e nada é guardado

### Requirement: Ofertas no painel
Administradores SHALL ver as ofertas em `/painel/colaboradores`, da mais nova para a mais antiga, exportá-las em CSV e removê-las com registro na auditoria sem o e-mail. Outros papéis MUST NOT acessar a tela.

#### Scenario: Moderador tenta abrir
- **WHEN** um moderador abre `/painel/colaboradores`
- **THEN** recebe 403

### Requirement: Direitos do titular
"Baixar meus dados" SHALL incluir as ofertas enviadas com o e-mail da conta, e "Excluir conta" SHALL apagá-las.

#### Scenario: Exclusão de conta
- **WHEN** um membro exclui a conta
- **THEN** as ofertas com o e-mail da conta são apagadas
