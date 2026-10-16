# Design

## Context

Motivação em proposal.md. O que já existe e molda a solução:

- `CompleteProfile` grava apelido, cidade e o consentimento `termos`; `EnsureProfileCompleted` manda para `/boas-vindas` quem não tem perfil e para `/termos/aceitar` quem tem termos pendentes (`AcceptCurrentTerms::pending` compara a última versão aceita no `ConsentLedger` com `CurrentTermsVersion`).
- `ConsentType` tem cinco tipos, cada um com a versão do texto em `textVersion()`. O ledger só registra e anonimiza; não existe retirada.
- O Avise-me grava o consentimento com `subject = "avise-me:{id}"`; a colaboração com `subject = "colaborador:{id}"`. A remoção hoje só existe no painel (`ManageWaitlist::remove`, `ManageCollaborators::remove`), auditada.
- O e-mail de confirmação do Avise-me usa `URL::temporarySignedRoute` de 7 dias. Ainda não existe e-mail de anúncio da campanha; quando existir, passa pelo mesmo ponto que monta o link.
- Checkout aceita visitante. A identificação é uma etapa própria (`CheckoutController::identify`), e o pedido nasce em `place`.

## Goals / Non-Goals

**Goals:**
- Uma única fonte da versão de cada declaração nova, no mesmo lugar das outras (`ConsentType::textVersion`).
- Nenhuma tela extra para o membro antigo: idade e termos pendentes cabem na tela de aceite que já existe.
- Descadastro que funciona sem login, sem expirar e sem efeito colateral de pré-visualização de links.

**Non-Goals:**
- Verificar idade de verdade (documento, data de nascimento, serviço externo).
- Central de preferências de e-mail. Só existe "sair".
- Descadastro de e-mails de serviço (pedido, relato, exportação): eles não dependem de consentimento.

## Decisions

### D1. Idade como consentimento, não como coluna
A declaração vira o tipo `idade_minima` no ledger, com versão `2026-10-16`. A pendência é "o ledger não tem `idade_minima` na versão atual" (mesma forma de `AcceptCurrentTerms::pending`).
- *Alternativa:* coluna `age_confirmed_at` em `members`. Rejeitada: duplicaria o que o ledger já guarda (data, IP, navegador, versão) e não teria versão do texto.

### D2. Uma tela para pendências de aceite
`AcceptCurrentTerms` vira o caso de uso das pendências de aceite: `pending()` devolve quais itens faltam (`terms`, `age`) e `execute()` recebe as respostas e registra só o que estava pendente. A tela `/termos/aceitar` mostra só as caixas pendentes; o título muda conforme o caso (só idade: "Uma confirmação antes de seguir"). `EnsureProfileCompleted` continua com um único redirecionamento. `CompleteProfile` exige as duas caixas no primeiro acesso.
- *Alternativa:* middleware e tela separados para idade. Rejeitada: o membro antigo veria duas telas seguidas no mesmo deploy em que os termos finais forem publicados.

### D3. Declaração de compra no checkout (aprovada pelo encarregado em 2026-10-16)
Caixa obrigatória na etapa de identificação: "Tenho 18 anos ou mais, ou a autorização do meu responsável legal para esta compra". A caixa vai junto com os dados do cliente, é validada na identificação e de novo em `place` (sem estado extra na sessão), e o consentimento `compra_autorizada` é registrado em `place`, com `email` do cliente e `subject = "pedido:{number}"`.
- *Alternativa:* deixar só nos termos. Rejeitada por ora: visitantes compram sem conta e nunca passam pela declaração de idade; a caixa é a única evidência de que a regra foi informada. Se o encarregado preferir só os termos, a tarefa 3 sai inteira sem afetar as outras.

### D4. Link de descadastro assinado e permanente
`URL::signedRoute('unsubscribe.show', ['kind' => 'avise-me'|'colaboracao', 'id' => $id])`, sem expiração; o `POST` de confirmação usa a mesma assinatura. Um único `UnsubscribeController` e um caso de uso `Unsubscribe` no módulo Privacy, que recebe o tipo e o id e despacha para o repositório certo (`WaitlistRepository::remove`, `CollaboratorRepository::remove`).
- *Por que GET mostra e POST remove:* scanners de e-mail e pré-visualizações abrem links; um GET destrutivo cancelaria inscrições sozinho.
- *Por que sem expiração:* um e-mail antigo precisa continuar permitindo sair. A assinatura com `APP_KEY` já impede adivinhar ids.
- *`List-Unsubscribe`:* só no Avise-me, apontando para a página (sem `List-Unsubscribe-Post`, que exigiria um POST sem confirmação). A colaboração é um e-mail de resposta, não uma lista.
- *Alternativa:* token aleatório por registro. Rejeitada: exige coluna e migração para dar o mesmo que a assinatura já dá.

### D5. Retirada sem apagar o histórico
Coluna `withdrawn_at` em `consents`. `ConsentLedger::withdraw(ConsentType, string $subject)` preenche a data nos registros daquele tipo e assunto ainda não retirados. A exportação ganha `retirado_em`. A remoção pelo painel (`ManageWaitlist::remove`, `ManageCollaborators::remove`) também chama `withdraw`, porque ela acontece a pedido da pessoa.
- *Alternativa:* apagar os consentimentos. Rejeitada: o registro prova que houve consentimento enquanto durou a inscrição.

### D6. E-mail mascarado na página de confirmação
A página mostra `a***@dominio.com` para a pessoa reconhecer o cadastro sem expor o endereço inteiro a quem recebeu o e-mail encaminhado.

## Risks / Trade-offs

- [A caixa de idade é só uma declaração] → É o que o encarregado aprovou; o encarregado confirmou que atende, inclusive diante da ECA Digital.
- [Link permanente encaminhado a terceiros permite cancelar a inscrição de outra pessoa] → O pior efeito é a pessoa deixar de receber o aviso; ela pode se inscrever de novo.
- [Membros antigos são interrompidos no próximo acesso] → Uma única tela, com volta automática para a página que abriram (`setIntendedUrl`, como já acontece com os termos).
- [Checkout com mais uma caixa] → Fica junto do CPF, na mesma etapa, sem tela nova.

## Migration Plan

1. Migração adiciona `withdrawn_at` (nulo) em `consents`. Sem preenchimento de dados.
2. No deploy, todos os membros passam a ter a idade pendente; a pendência aparece no próximo acesso. Publicar este deploy antes ou junto com os textos finais de privacidade e termos, para que o membro veja uma tela só.
3. Rollback: reverter o código; a coluna nula não atrapalha a versão anterior.

## Open Questions

Nenhuma. Resolvido com o encarregado em 2026-10-16 (por telefone): a caixa de 16 anos no perfil e a de 18 anos ou autorização no checkout atendem, inclusive diante da ECA Digital (Lei 15.211/2025).
