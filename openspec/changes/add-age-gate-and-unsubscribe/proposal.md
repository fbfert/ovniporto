# Proposal

## Why

Os textos modelo de /privacidade e /termos (aprovados em princípio pelo encarregado em 2026-10-16) prometem duas coisas que o site ainda não faz: idade mínima de 16 anos confirmada no cadastro e um link para cancelar o Avise-me e a oferta de colaboração em cada e-mail. Os textos só podem ser marcados como finais quando o site cumprir o que eles dizem.

## What Changes

- Tela de boas-vindas ganha a caixa obrigatória "Tenho 16 anos ou mais". Sem ela a conta não é ativada. A declaração vira um consentimento registrado (novo tipo `idade_minima`, com versão), sem guardar data de nascimento.
- Membros que já têm conta declaram a idade no próximo acesso, na mesma tela do novo aceite dos termos: uma tela só, mesmo quando os dois estão pendentes.
- Checkout ganha a declaração obrigatória "Tenho 18 anos ou mais, ou a autorização do meu responsável legal para esta compra", registrada como consentimento ligado ao pedido (novo tipo `compra_autorizada`). **Decisão a validar com o encarregado.**
- Todo e-mail enviado a um inscrito do Avise-me e a quem ofereceu colaboração leva um link de descadastro assinado, que não expira e não pede login. O e-mail do Avise-me leva também o cabeçalho `List-Unsubscribe`.
- O link abre uma página de confirmação; ao confirmar, a inscrição ou a oferta é apagada e os consentimentos daquele e-mail ficam marcados como retirados, com data.
- "O que fazemos na prática" ganha a linha sobre o descadastro e a idade mínima.

## Capabilities

### New Capabilities
- `email-unsubscribe`: descadastro de um clique por link assinado, para o Avise-me e as ofertas de colaboração, com retirada do consentimento registrada.

### Modified Capabilities
- `member-accounts`: o perfil completo passa a exigir também a declaração de idade mínima, e o membro existente a faz junto com o aceite de termos pendente.
- `privacy-compliance`: o registro de consentimentos inclui a declaração de idade e a autorização de compra, e guarda a data de retirada.
- `checkout`: a identificação do cliente exige a declaração de maioridade ou de autorização do responsável.

## Impact

- **Código:** `ConsentType`, `ConsentLedger` e a implementação Eloquent (retirada); `CompleteProfile`, `AcceptCurrentTerms`, `EnsureProfileCompleted`; telas `Members/Welcome.tsx` e `Members/AcceptTerms.tsx`; `CheckoutController::identify` e a tela de identificação; `MailWaitlistNotifier`, `MailCollaboratorNotifier`, `ConfirmWaitlistMail`, `CollaboratorThanksMail` e as views de e-mail; novo controller e página de descadastro; `lang/pt_BR/privacy.php`; `resources/js/i18n/pt-BR.ts`.
- **Banco:** coluna `withdrawn_at` em `consents`.
- **Rotas públicas novas:** `GET` e `POST /descadastrar/{tipo}/{id}` (assinadas).
- **Manual do painel:** capítulos `campanha` e `privacidade`, porque as pessoas passam a sair do Avise-me e da colaboração sozinhas.
- **Dependência de outras changes:** `add-atlas-collaborators` ainda não foi arquivada. O descadastro da colaboração entra aqui, na capability nova, sem tocar a spec dela.
