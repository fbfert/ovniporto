# Spec Delta

## ADDED Requirements

### Requirement: Registro do último acesso
O sistema SHALL registrar a data do último acesso de cada membro ao entrar com o Google e, enquanto a sessão estiver em uso, no máximo uma vez por dia. Uma requisição comum MUST NOT gravar no banco quando o último acesso já é do mesmo dia.

#### Scenario: Login com o Google
- **WHEN** um membro entra com o Google
- **THEN** a data do último acesso passa a ser a data e hora do login

#### Scenario: Navegação no mesmo dia
- **WHEN** um membro com último acesso registrado hoje abre outra página
- **THEN** nenhuma escrita no banco é feita para o último acesso

#### Scenario: Sessão aberta de um dia para o outro
- **WHEN** um membro com último acesso de ontem abre uma página com a sessão ainda válida
- **THEN** o último acesso passa a ser o de hoje

### Requirement: Aviso antes da anonimização por inatividade
Quando faltarem 30 dias para uma conta completar 5 anos sem acesso, o sistema SHALL enviar ao e-mail da conta um único aviso dizendo a data da anonimização e que basta entrar para manter a conta. Entrar depois do aviso MUST zerar a contagem e permitir um novo aviso no ciclo seguinte.

#### Scenario: Conta perto do prazo
- **WHEN** a rotina diária roda e uma conta está sem acesso há 5 anos menos 30 dias
- **THEN** o membro recebe o aviso por e-mail uma vez

#### Scenario: Rotina roda de novo
- **WHEN** a rotina roda no dia seguinte para a mesma conta já avisada
- **THEN** nenhum novo aviso é enviado

#### Scenario: Membro volta depois do aviso
- **WHEN** o membro avisado entra com o Google antes do prazo
- **THEN** a conta não é anonimizada e o registro de aviso é limpo

### Requirement: Anonimização da conta por inatividade
Uma conta sem acesso há 5 anos e já avisada há pelo menos 30 dias MUST ser anonimizada pela rotina diária, sem ser excluída: nome, e-mail, foto e vínculo com o Google são apagados e o apelido permanece. A rotina MUST ser idempotente.

#### Scenario: Conta inativa anonimizada
- **WHEN** a rotina roda para uma conta sem acesso há 5 anos e avisada há 30 dias
- **THEN** a conta continua existindo com o apelido e a data da anonimização, sem nome, e-mail, foto nem vínculo com o Google

#### Scenario: Rotina repetida
- **WHEN** a rotina roda de novo sobre uma conta já anonimizada
- **THEN** nada muda e nenhum e-mail é enviado

#### Scenario: Mesmo Google volta depois
- **WHEN** a pessoa dona da conta anonimizada entra de novo com o mesmo Google
- **THEN** uma conta nova é criada, sem ligação com a anonimizada

### Requirement: Conteúdo de uma conta anonimizada
Na anonimização por inatividade, relatos publicados SHALL continuar no Livro e no mapa com o apelido; relatos não publicados (em análise, com ajuste pedido ou recusados) e todas as fotos deles MUST ser apagados; consentimentos MUST ser anonimizados e a inscrição no Avise-me e as ofertas de colaboração com o e-mail da conta MUST ser apagadas, como na exclusão.

#### Scenario: Relato publicado
- **WHEN** uma conta com um relato publicado e um em análise é anonimizada
- **THEN** o publicado continua visível com o apelido e o em análise some com as fotos

#### Scenario: Consentimentos e listas
- **WHEN** uma conta inscrita no Avise-me é anonimizada
- **THEN** a inscrição é apagada e os consentimentos guardam só tipo, versão e data

### Requirement: Contas com papel no painel
Contas com papel no painel (moderação, loja ou admin) MUST NOT ser anonimizadas automaticamente. Quando uma delas alcançar o prazo, o sistema SHALL avisar os admins por e-mail uma única vez por ciclo.

#### Scenario: Moderador inativo
- **WHEN** a rotina encontra um moderador sem acesso há 5 anos
- **THEN** a conta não é anonimizada e os admins recebem um alerta com o apelido e a data do último acesso

### Requirement: Conta anonimizada no painel
No painel de membros, uma conta anonimizada SHALL aparecer como "Conta anonimizada", com o apelido e a data da anonimização, sem nenhum dado pessoal e sem ações de papel ou bloqueio.

#### Scenario: Ficha de membro anonimizado
- **WHEN** o admin abre a ficha de uma conta anonimizada
- **THEN** vê "Conta anonimizada", o apelido e a data, e nenhum nome, e-mail ou foto

### Requirement: Anonimização dos pedidos após o prazo fiscal
Pedidos pagos que completarem 5 anos desde o pagamento MUST ser anonimizados pela rotina diária, venham de conta ativa, de conta anonimizada ou de compra sem conta: nome, e-mail, telefone, CPF e endereço são apagados, exceto cidade e UF; número, itens, valores e datas permanecem. Pedidos de contas excluídas MUST seguir sendo apagados ao fim do prazo.

#### Scenario: Pedido de compra sem conta
- **WHEN** a rotina roda e um pedido pago sem conta completou 5 anos do pagamento
- **THEN** o pedido continua com número, itens, valores, data, cidade e UF, sem nome, e-mail, telefone, CPF nem endereço

#### Scenario: Pedido ainda no prazo
- **WHEN** a rotina roda e um pedido pago tem 4 anos
- **THEN** o pedido não muda

#### Scenario: Pedido de conta excluída
- **WHEN** a rotina roda após a data de retenção de um pedido de conta excluída
- **THEN** o pedido é apagado, como antes desta mudança

### Requirement: Garantias de retenção na página de privacidade
A seção "O que fazemos na prática" de `/privacidade` SHALL incluir a anonimização da conta após o prazo de inatividade, com o aviso prévio, e a anonimização dos pedidos após o prazo fiscal, com os números lidos da mesma configuração que a rotina usa.

#### Scenario: Prazo alterado na configuração
- **WHEN** o prazo de inatividade configurado muda
- **THEN** o número exibido em "O que fazemos na prática" muda junto
