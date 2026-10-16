# Spec Delta

## Purpose

Permite que quem se inscreveu no Avise-me ou ofereceu colaboração com a pesquisa da origem saia sozinho, por um link em cada e-mail, sem login e sem precisar pedir à equipe.

## ADDED Requirements

### Requirement: Link de descadastro em cada e-mail
Todo e-mail enviado a um inscrito do Avise-me ou a quem ofereceu colaboração SHALL trazer um link de descadastro assinado, que não expira e não exige login. O e-mail do Avise-me MUST trazer também o cabeçalho `List-Unsubscribe` com o mesmo endereço.

#### Scenario: E-mail de confirmação do Avise-me
- **WHEN** uma pessoa se inscreve no Avise-me
- **THEN** o e-mail de confirmação traz o link de descadastro e o cabeçalho `List-Unsubscribe`

#### Scenario: E-mail de agradecimento da colaboração
- **WHEN** uma pessoa envia uma oferta de colaboração
- **THEN** o e-mail de agradecimento traz o link de descadastro

### Requirement: Confirmação antes de remover
O link de descadastro SHALL abrir uma página que mostra o que vai ser cancelado e pede confirmação. Só o envio da confirmação MUST remover a inscrição ou a oferta, para que a pré-visualização de links de um programa de e-mail não cancele nada.

#### Scenario: Abrir o link
- **WHEN** a pessoa abre um link de descadastro válido
- **THEN** a página mostra o tipo de cadastro e o e-mail mascarado, e nada é removido

#### Scenario: Confirmar o descadastro
- **WHEN** a pessoa confirma na página
- **THEN** a inscrição ou a oferta é apagada e a página diz que o cancelamento foi feito

### Requirement: Retirada do consentimento registrada
Ao confirmar o descadastro, o sistema MUST marcar como retirados, com data, os consentimentos daquele tipo dados com o mesmo e-mail, mantendo o registro de que foram dados.

#### Scenario: Consentimento retirado
- **WHEN** um inscrito do Avise-me confirma o descadastro
- **THEN** o consentimento `newsletter` daquele e-mail fica com a data de retirada preenchida

### Requirement: Link inválido ou já usado
Um link com assinatura inválida MUST responder 403. Um link válido de uma inscrição ou oferta que já não existe SHALL responder com a página de que não há mais nada a cancelar, sem erro.

#### Scenario: Assinatura adulterada
- **WHEN** alguém troca o identificador de um link de descadastro
- **THEN** a resposta é 403

#### Scenario: Cancelamento repetido
- **WHEN** a pessoa abre de novo um link depois de confirmar o descadastro
- **THEN** a página informa que não há cadastro ativo para cancelar
