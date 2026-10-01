# Spec Delta

## Purpose

Permite que visitantes deixem o e-mail para serem avisados quando a campanha da pista abrir, com consentimento explícito e confirmação por e-mail.

## ADDED Requirements

### Requirement: Inscrição com consentimento
O formulário "Avise-me da campanha" SHALL aceitar um e-mail válido e MUST registrar a data do consentimento e o texto aceito; um e-mail inválido SHALL ser recusado com mensagem em português.

#### Scenario: Inscrição válida
- **WHEN** o visitante envia um e-mail válido
- **THEN** a inscrição é salva como pendente de confirmação e aparece a mensagem "Quase lá: confirme no seu e-mail."

#### Scenario: E-mail inválido
- **WHEN** o visitante envia "abc"
- **THEN** nada é salvo e aparece "Esse e-mail não parece certo."

### Requirement: Double opt-in
Após a inscrição, o sistema SHALL enviar, por fila, um e-mail com um link assinado de confirmação; só após o clique a inscrição MUST ser considerada confirmada.

#### Scenario: Confirmação pelo link
- **WHEN** o inscrito abre o link assinado do e-mail
- **THEN** a inscrição passa a confirmada e a página mostra "Pronto. Guardei um lugar pra você."

#### Scenario: Link adulterado
- **WHEN** alguém abre o link com a assinatura alterada
- **THEN** a inscrição não é confirmada e o servidor responde 403

### Requirement: Inscrição repetida não vaza dados
Reenviar um e-mail já inscrito SHALL dar a mesma resposta de sucesso, sem revelar se o e-mail já existia e sem duplicar o registro.

#### Scenario: Mesmo e-mail duas vezes
- **WHEN** o mesmo e-mail é enviado duas vezes
- **THEN** existe um único registro e as duas respostas são idênticas

### Requirement: Proteção contra abuso
O envio do formulário SHALL ser limitado por IP.

#### Scenario: Muitas tentativas
- **WHEN** um mesmo IP envia mais de 5 inscrições em um minuto
- **THEN** as tentativas seguintes recebem 429
