# Spec Delta

## Purpose

Identidade dos membros do OVNIPORTO: login exclusivo com Google, perfil com apelido público, área "Minha conta" e pedidos de exportação e exclusão dos próprios dados.

## ADDED Requirements

### Requirement: Login somente com Google
O sistema SHALL autenticar membros exclusivamente pela conta Google, pedindo apenas os escopos `openid`, `email` e `profile`, e MUST NOT oferecer cadastro ou login por senha.

#### Scenario: Primeiro login cria membro
- **WHEN** uma pessoa conclui o login Google pela primeira vez
- **THEN** um membro é criado com papel `member` contendo apenas id Google, nome, e-mail e avatar, e ela é levada à tela de boas-vindas

#### Scenario: Login recorrente
- **WHEN** um membro existente faz login com a mesma conta Google
- **THEN** nenhum membro novo é criado e a sessão é iniciada

### Requirement: Perfil completo antes de usar a conta
Um membro sem apelido ou sem aceite dos termos MUST ser redirecionado à tela de boas-vindas ao acessar qualquer área autenticada. O apelido SHALL ser único, sugerido a partir do primeiro nome e validado enquanto a pessoa digita.

#### Scenario: Acesso sem perfil completo
- **WHEN** um membro sem apelido tenta abrir `/conta`
- **THEN** é redirecionado para a tela de boas-vindas

#### Scenario: Apelido já em uso
- **WHEN** a pessoa digita um apelido que já pertence a outro membro
- **THEN** o campo indica que o apelido está indisponível e o envio é bloqueado

#### Scenario: Termos não aceitos
- **WHEN** a pessoa envia a tela de boas-vindas sem marcar o aceite dos termos
- **THEN** a conta não é ativada e o erro é mostrado no campo

### Requirement: Somente o apelido é público
O sistema MUST exibir publicamente apenas o apelido do membro; nome real, e-mail e avatar do Google MUST NOT aparecer em páginas públicas.

#### Scenario: Página pública com conteúdo de membro
- **WHEN** qualquer página pública mostra conteúdo de um membro
- **THEN** o HTML contém o apelido e não contém nome real nem e-mail

### Requirement: Minha conta
A página `/conta` SHALL oferecer as abas Meus relatos (com status e motivo quando houver), Meus pedidos (status e rastreio), Meus dados (editar apelido e cidade; nome e e-mail do Google só leitura) e Privacidade.

#### Scenario: Editar apelido
- **WHEN** o membro altera o apelido para um valor livre e salva
- **THEN** o novo apelido passa a ser exibido nos próximos conteúdos

### Requirement: Isolamento entre membros
Um membro MUST ver e alterar somente os próprios relatos, pedidos e dados.

#### Scenario: Acesso a recurso de outro membro
- **WHEN** um membro tenta abrir ou editar um relato de outro membro
- **THEN** recebe 403 ou 404 e nada é alterado

### Requirement: Baixar meus dados
Na aba Privacidade, o membro SHALL poder pedir a exportação dos seus dados; o arquivo é gerado em segundo plano e enviado ao e-mail da conta.

#### Scenario: Pedido de exportação
- **WHEN** o membro aciona "Baixar meus dados"
- **THEN** recebe a confirmação na tela e, em seguida, um e-mail com o arquivo JSON

### Requirement: Excluir minha conta
O membro SHALL poder excluir a conta após confirmar digitando o próprio apelido; o sistema MUST apagar perfil, relatos e fotos e anonimizar os pedidos mantendo apenas o necessário fiscal, enviando e-mail de confirmação.

#### Scenario: Confirmação incorreta
- **WHEN** o membro digita um apelido diferente do seu na confirmação
- **THEN** a exclusão não acontece

#### Scenario: Exclusão confirmada
- **WHEN** o membro confirma com o apelido correto
- **THEN** perfil, relatos e fotos são apagados, os pedidos ficam anonimizados e a sessão é encerrada
