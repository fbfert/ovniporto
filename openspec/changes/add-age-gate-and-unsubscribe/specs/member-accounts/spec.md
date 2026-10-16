# Spec Delta

## MODIFIED Requirements

### Requirement: Perfil completo antes de usar a conta
Um membro sem apelido, sem aceite dos termos ou sem a declaração de que tem 16 anos ou mais MUST ser redirecionado à tela de boas-vindas ao acessar qualquer área autenticada. O apelido SHALL ser único, sugerido a partir do primeiro nome e validado enquanto a pessoa digita.

#### Scenario: Acesso sem perfil completo
- **WHEN** um membro sem apelido tenta abrir `/conta`
- **THEN** é redirecionado para a tela de boas-vindas

#### Scenario: Apelido já em uso
- **WHEN** a pessoa digita um apelido que já pertence a outro membro
- **THEN** o campo indica que o apelido está indisponível e o envio é bloqueado

#### Scenario: Termos não aceitos
- **WHEN** a pessoa envia a tela de boas-vindas sem marcar o aceite dos termos
- **THEN** a conta não é ativada e o erro é mostrado no campo

#### Scenario: Idade não declarada
- **WHEN** a pessoa envia a tela de boas-vindas sem marcar "Tenho 16 anos ou mais"
- **THEN** a conta não é ativada, o erro é mostrado no campo e nenhuma data de nascimento é pedida

## ADDED Requirements

### Requirement: Declaração de idade de quem já tem conta
Um membro com perfil completo que ainda não declarou ter 16 anos ou mais MUST fazer a declaração no próximo acesso a uma área autenticada, antes de continuar. Quando o aceite de novos termos também estiver pendente, as duas confirmações SHALL ficar na mesma tela.

#### Scenario: Membro antigo sem declaração
- **WHEN** um membro cadastrado antes desta mudança abre `/conta`
- **THEN** vê a tela de aceite com a caixa "Tenho 16 anos ou mais" e só segue depois de marcá-la

#### Scenario: Termos e idade pendentes juntos
- **WHEN** a versão dos termos mudou e o membro ainda não declarou a idade
- **THEN** uma única tela pede o aceite dos termos e a declaração, e o envio registra os dois consentimentos
