# Tasks

## 1. Banco e configuração

- [ ] 1.1 Migration aditiva: `members.last_seen_at`, `inactivity_notice_sent_at`, `inactivity_alerted_at` e `anonymized_at` (timestamps anuláveis, `last_seen_at` indexado); `name`, `email` e `google_id` anuláveis, mantendo os índices únicos; backfill de `last_seen_at` com `updated_at`; `orders.anonymized_at`. Verificado por `php artisan migrate` e `migrate:rollback` no MySQL de desenvolvimento
- [ ] 1.2 `config/privacy.php`: `inactive_account_years` (5) e `inactivity_notice_days` (30), com `env` como `fiscal_retention_years`, e as variáveis no `.env.example`. Verificado por teste de unidade que lê os valores padrão

## 2. Último acesso

- [ ] 2.1 `MemberRepository::touchLastSeen(int $memberId, DateTimeInterface $at)`, que também zera `inactivity_notice_sent_at` e `inactivity_alerted_at`, chamado por `SignInWithIdentity` em todo login. Verificado por teste de unidade do caso de uso
- [ ] 2.2 Middleware `TouchLastSeen` no grupo `auth`, que grava só quando `last_seen_day` da sessão é anterior a hoje. Verificado por teste de feature: duas requisições no mesmo dia geram uma escrita; com o relógio no dia seguinte, gera outra

## 3. Anonimização da conta

- [ ] 3.1 Contrato `MemberContentAnonymizer` no Domain de Members; `ConsentsContentEraser`, `WaitlistContentEraser` e `CollaboratorContentEraser` passam a implementar também esse contrato, reaproveitando o mesmo método. Registro no container ao lado dos erasers. Verificado pelos testes existentes de exclusão, que continuam passando
- [ ] 3.2 `MemberSightingRepository::deleteUnpublishedOf(int $memberId)` e `SightingsContentAnonymizer`: apaga relatos pendentes, com ajuste pedido e recusados, com fotos, variantes e uploads soltos, e mantém os aprovados. Verificado por teste de feature que confere os arquivos no disco falso
- [ ] 3.3 `MemberRepository`: `inactiveSince`/consultas por lote (sem papel, com papel, avisados), `markInactivityNoticeSent`, `markInactivityAlerted` e `anonymize(int $memberId, DateTimeInterface $at)` (nome, e-mail, Google, foto e cidade nulos, `anonymized_at` preenchido). Verificado por teste de feature do repositório
- [ ] 3.4 `InactivityNoticeMail` (fila, pt-BR, com a data prevista e o link de login) e `InactiveStaffAlertMail` para `config('ovniporto.alerts_email')` ou para todos os admins, como em `JobFailureAlert`. Textos em português sem a palavra "lenda". Verificado por teste de render dos dois e-mails
- [ ] 3.5 Caso de uso `AnonymizeInactiveMembers` (aviso, anonimização de quem não tem papel com aviso há pelo menos 30 dias, alerta dos papéis), idempotente e em lotes. Verificado por teste de unidade em Pest com o relógio congelado: aviso 30 dias antes e só uma vez; anonimização no prazo; nada antes do aviso; conta já anonimizada intocada; moderador só gera alerta, uma vez; login zera o ciclo
- [ ] 3.6 Comando `members:anonymize-inactive` agendado às 03:15 em `routes/console.php`, com comentário. Verificado por teste de feature que roda o comando com uma conta avisada há 30 dias e confere o relato publicado ainda visível em `/relatos/{id}` com o apelido
- [ ] 3.7 Login de um Google cuja conta foi anonimizada cria uma conta nova. Verificado por teste de feature

## 4. Pedidos após o prazo fiscal

- [ ] 4.1 `EloquentOrderRepository`: extrair a troca de campos de `anonymizeFor` para um método privado e criar `anonymizePaidBefore(DateTimeInterface $cutoff): int` (CPF nulo, "Titular anonimizado", `retention_until` nulo, `anonymized_at` preenchido, ignora pedidos com `retention_until`). Verificado por teste de feature: pedido sem conta com 5 anos anonimizado; com 4 anos intocado; de conta excluída segue para a purga; segunda execução não muda nada
- [ ] 4.2 `privacy:purge-orders` chama `anonymizePaidBefore(now()->subYears(fiscal_retention_years))` antes da purga e informa as duas contagens; descrição do comando atualizada. Verificado por teste de feature do comando
- [ ] 4.3 Painel de pedidos e exportação de dados lidam com um pedido sem cliente ("Titular anonimizado", sem CPF). Verificado por teste de feature da ficha do pedido

## 5. Painel e transparência

- [ ] 5.1 `MemberAdminRepository::search/detail` expõem `anonymizedAt`; a lista mostra a etiqueta "Conta anonimizada" e a ficha não mostra dados pessoais nem as ações de papel, bloqueio e exclusão. Textos em `pt-BR.ts`. Antes de mexer na UI, carregar as skills de design de `.claude/skills/README.md`. Verificado por teste de feature das props e por Vitest da ficha
- [ ] 5.2 Pontos do painel que mostram autor ou cliente (moderação, auditoria, pedidos) não quebram com membro anonimizado. Verificado por teste de feature de cada tela com uma conta anonimizada
- [ ] 5.3 `lang/pt_BR/privacy.php` ganha as frases de inatividade (`:inactiveYears`, `:noticeDays`) e a de pedidos anonimizados após `:years` anos; `CodePrivacyPractices` passa os valores do config. Verificado por teste de feature de `/privacidade` com o prazo trocado na configuração
- [ ] 5.4 Atualizar o manual do painel (`resources/content/manual/privacidade.json` e `membros.json`: texto da conta anonimizada, rotina diária, aviso de 30 dias, alerta das contas com papel, pedidos anonimizados após o prazo, mapa, rotas e `reviewedAt`), verificado por `php artisan test --filter=PanelManual`

## 6. Fechamento

- [ ] 6.1 `openspec validate enforce-data-retention --strict`, `vendor/bin/pint --test`, `npm run typecheck` e a suíte completa (`php artisan test`) passando
