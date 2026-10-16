# Spec Delta

## MODIFIED Requirements

### Requirement: Registro de consentimentos
Cada consentimento (termos, declaração de idade mínima, publicação de relato, newsletter, colaboração, listagem de parceiro e autorização de compra) SHALL ser registrado com tipo, versão, data, IP e agente do navegador. Quando a versão dos termos mudar, o membro MUST aceitar a nova versão no próximo acesso antes de continuar. Um consentimento retirado MUST guardar a data da retirada sem apagar o registro.

#### Scenario: Nova versão dos termos
- **WHEN** a versão dos termos é atualizada e um membro faz login
- **THEN** ele precisa aceitar a nova versão e um novo consentimento é registrado

#### Scenario: Declaração de idade registrada
- **WHEN** um membro marca "Tenho 16 anos ou mais"
- **THEN** um consentimento `idade_minima` é registrado com versão, data, IP e navegador, e nenhuma data de nascimento é guardada

#### Scenario: Retirada aparece na exportação
- **WHEN** um membro que cancelou o Avise-me pede "Baixar meus dados"
- **THEN** o consentimento `newsletter` aparece com a data em que foi dado e a data em que foi retirado
