# Spec Delta

## MODIFIED Requirements

### Requirement: Identificação do cliente
O checkout SHALL aceitar membro logado ou visitante com nome e e-mail, e MUST exigir telefone (WhatsApp) e CPF com dígitos verificadores válidos, explicando por que o CPF é pedido. O CPF MUST ser armazenado criptografado. A etapa MUST exigir a declaração "Tenho 18 anos ou mais, ou a autorização do meu responsável legal para esta compra".

#### Scenario: CPF inválido
- **WHEN** o cliente informa um CPF com dígito verificador errado
- **THEN** a etapa não avança e o campo mostra o erro

#### Scenario: CPF guardado criptografado
- **WHEN** um pedido é criado com CPF válido
- **THEN** o valor armazenado no banco não é o CPF em texto claro

#### Scenario: Declaração de maioridade ausente
- **WHEN** o cliente tenta avançar da identificação sem marcar a declaração
- **THEN** a etapa não avança e o campo mostra o erro

#### Scenario: Declaração registrada com o pedido
- **WHEN** um pedido é criado
- **THEN** um consentimento `compra_autorizada` é registrado com o e-mail do cliente, o número do pedido, versão, data, IP e navegador
