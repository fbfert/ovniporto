# Tasks

## 1. Consentimentos: novos tipos e retirada

- [ ] 1.1 Adicionar `ConsentType::MinimumAge` (`idade_minima`, versão `2026-10-16`) e `ConsentType::PurchaseAuthorization` (`compra_autorizada`, versão `2026-10-16`), com o texto de cada um em `resources/js/i18n/pt-BR.ts`, verificado por teste de unidade em `tests/Unit/Privacy`
- [ ] 1.2 Migração `add_withdrawn_at_to_consents` e `ConsentLedger::withdraw(ConsentType, string $subject)` na implementação Eloquent, que só marca registros ainda não retirados, verificado em `tests/Feature/ConsentLedgerTest.php` (retirada com data, repetição não muda a data)
- [ ] 1.3 Incluir `retirado_em` em `historyOf` e na exportação "Baixar meus dados", verificado pelo teste da exportação

## 2. Idade mínima no cadastro e no reaceite

- [ ] 2.1 `CompleteProfile` exige a declaração de idade e registra `idade_minima` junto com `termos`, verificado por teste de unidade (sem a caixa: exceção e nada gravado)
- [ ] 2.2 `AcceptCurrentTerms` passa a devolver as pendências (`terms`, `age`) e a registrar só o que estava pendente, verificado por teste de unidade com os quatro casos (nada, só termos, só idade, os dois)
- [ ] 2.3 `EnsureProfileCompleted` redireciona para `/termos/aceitar` quando houver qualquer pendência, e o `TermsAcceptanceController` valida só as caixas pendentes, verificado em `tests/Feature/TermsVersionTest.php` (membro antigo sem idade vai para a tela e volta para a página que abriu)
- [ ] 2.4 Caixa "Tenho 16 anos ou mais" em `Members/Welcome.tsx` e caixas condicionais em `Members/AcceptTerms.tsx`, com erro no campo e alvo de 44px, verificado por teste de componente e no navegador em 390px

## 3. Declaração de compra no checkout (depende da validação do encarregado, D3)

- [ ] 3.1 Caixa obrigatória na etapa de identificação, guardada na sessão do checkout, verificada em `tests/Feature/CheckoutTest.php` (sem a caixa a etapa não avança)
- [ ] 3.2 Registrar `compra_autorizada` com `email` e `subject = "pedido:{number}"` ao criar o pedido, verificado em `tests/Feature/CheckoutTest.php`

## 4. Descadastro por link

- [ ] 4.1 Caso de uso `Unsubscribe` (tipo `avise-me` ou `colaboracao` + id): remove pelo `WaitlistRepository` ou `CollaboratorRepository` e chama `withdraw`; registro inexistente não é erro. Verificado por teste de unidade
- [ ] 4.2 Rotas assinadas `GET` e `POST /descadastrar/{kind}/{id}` com `UnsubscribeController` e a página `Privacy/Unsubscribe.tsx` (e-mail mascarado, confirmação, estado "nada a cancelar"), verificado por teste de feature: GET não remove, POST remove, assinatura adulterada 403, segunda visita mostra "nada a cancelar"
- [ ] 4.3 Link de descadastro em `ConfirmWaitlistMail` (com cabeçalho `List-Unsubscribe`) e em `CollaboratorThanksMail`, montado em `MailWaitlistNotifier` e `MailCollaboratorNotifier`, verificado em `tests/Feature/WaitlistTest.php` e `tests/Feature/CollaboratorTest.php` com `Mail::fake()`
- [ ] 4.4 `ManageWaitlist::remove` e `ManageCollaborators::remove` também chamam `withdraw`, verificado nos testes de unidade de cada um

## 5. Transparência e manual

- [ ] 5.1 Atualizar `lang/pt_BR/privacy.php` ("O que fazemos na prática": idade mínima e link para sair em cada e-mail do Avise-me e da colaboração), verificado em `tests/Feature/PrivacyPracticesTest.php`
- [ ] 5.2 Atualizar o manual do painel (resources/content/manual/campanha.json e privacidade.json: texto, mapa, rotas e reviewedAt), dizendo que inscritos e colaboradores podem sair sozinhos e que a remoção pelo painel marca a retirada do consentimento, verificado por php artisan test --filter=PanelManual

## 6. Integração

- [ ] 6.1 Rodar a suíte completa (`php artisan test`, testes de componente e e2e) e conferir em staging o e-mail real do Avise-me com o link e o cabeçalho, e o fluxo de reaceite de um membro antigo em 390px
