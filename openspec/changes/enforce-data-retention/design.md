# Design

## Context

- A exclusão de conta (`DeleteAccount`) chama cada `MemberContentEraser` (Sightings, Orders, Consents, Waitlist, Collaborator) e depois apaga o membro. É o padrão a seguir para a anonimização, que precisa de quase os mesmos passos, com duas diferenças: relatos publicados ficam e o membro não é apagado.
- `EloquentOrderRepository::anonymizeFor` já troca o cliente por "Titular excluído", deixa só cidade/UF no endereço e preenche `retention_until`, que `privacy:purge-orders` usa para apagar de vez. O CPF continua cifrado, por causa da obrigação fiscal.
- `members` não guarda último acesso. `google_id`, `email` e `name` são `NOT NULL` e os dois primeiros são únicos.
- A sessão dura 120 minutos e o login é sempre pelo Google (`GoogleAuthController` → `SignInWithIdentity`), então quase todo uso real passa por um login.
- Alertas operacionais vão para `config('ovniporto.alerts_email')` ou, vazio, para todos os admins (`JobFailureAlert`).
- A rotina diária `privacy:purge-orders` já roda às 03:30 em `routes/console.php`.

## Goals / Non-Goals

**Goals:**
- Último acesso confiável sem uma escrita por requisição.
- Aviso e anonimização por inatividade, idempotentes e testáveis com o relógio congelado.
- Anonimização de qualquer pedido pago após o prazo fiscal, reaproveitando a lógica que já existe.
- Prazos em um só lugar (`config/privacy.php`) e lidos também pela página de privacidade.

**Non-Goals:**
- Mudar a exclusão de conta pela própria pessoa ou pelo admin.
- Anonimizar pedidos não pagos de contas ativas (não há obrigação fiscal nem regra pedida).
- Revogar sessões abertas no momento da anonimização: uma conta só é anonimizada depois de 5 anos sem acesso, e a sessão dura 2 horas.
- Painel para disparar a anonimização à mão.

## Decisions

1. **Último acesso: `members.last_seen_at`**, gravado em `SignInWithIdentity` (login) e por um middleware `TouchLastSeen` no grupo `auth`, que só grava quando o valor guardado é de um dia anterior. O dia do último toque fica na sessão (`last_seen_day`), então a comparação não consulta o banco. Alternativa descartada: gravar em toda requisição (escrita desnecessária) ou só no login (uma sessão aberta por dias não contaria).
   - **Backfill**: a migration preenche `last_seen_at` das contas existentes com `updated_at`, para ninguém ficar "inativo desde sempre".

2. **Caso de uso `AnonymizeInactiveMembers`** (Application/Members), chamado por `members:anonymize-inactive` às 03:15:
   - **aviso**: membros não anonimizados, com ou sem papel, com `last_seen_at <= agora − 5 anos + 30 dias` e `inactivity_notice_sent_at` nulo recebem `InactivityNoticeMail` (fila) com a data prevista, e `inactivity_notice_sent_at` é gravado;
   - **anonimização**: membros sem papel com `last_seen_at <= agora − 5 anos` e `inactivity_notice_sent_at <= agora − 30 dias`. Exigir o aviso garante que ninguém é anonimizado sem 30 dias de aviso, mesmo depois de a rotina ficar parada;
   - **papéis no painel**: membros com papel que atingem o prazo não são anonimizados; geram um `InactiveStaffAlertMail` para o destino de alertas, uma vez por ciclo, marcado em `members.inactivity_alerted_at`, que o login também zera;
   - o login zera `inactivity_notice_sent_at` (e `inactivity_alerted_at`).

3. **Contrato `MemberContentAnonymizer`** ao lado de `MemberContentEraser`, com implementações por módulo. Consents, Waitlist e Collaborator reaproveitam os erasers atuais: a mesma classe implementa as duas interfaces. Sightings ganha `deleteUnpublishedOf($memberId)`, que apaga relatos pendentes, com ajuste pedido ou recusados, mais as fotos, as variantes e os uploads soltos, e mantém os aprovados. Orders não entra: os pedidos da conta seguem a regra geral (decisão 5), sem prazo antecipado.

4. **Membro anonimizado**: `name`, `email`, `google_id`, `avatar_url` e `city` ficam nulos, `anonymized_at` é preenchido e o apelido fica. Uma migration torna `name`, `email` e `google_id` anuláveis; os índices únicos continuam, porque o MySQL aceita vários nulos num índice único. Como `google_id` fica nulo, o mesmo Google cria uma conta nova no próximo login. O apelido continua reservado para a conta anonimizada, para os relatos publicados não mudarem de autor.
   - O painel (`MemberAdminRepository::search/detail`) expõe `anonymizedAt`; a lista mostra a etiqueta "Conta anonimizada" e a ficha esconde papel, bloqueio e exclusão.
   - Exportação de dados e Minha conta não se aplicam, porque ninguém consegue entrar numa conta anonimizada.

5. **Pedidos após o prazo: `OrderRepository::anonymizePaidBefore(DateTimeInterface $cutoff)`**, chamado por `privacy:purge-orders` antes da purga, com `cutoff = agora − fiscal_retention_years`. Ele anonimiza os pedidos com `paid_at <= cutoff`, `anonymized_at` nulo e `retention_until` nulo (os de conta excluída já têm data de purga e seguem para ela).
   - A troca dos campos vira um método privado reaproveitado por `anonymizeFor` e por `anonymizePaidBefore`, que difere só em: CPF nulo (o prazo fiscal acabou), rótulo "Titular anonimizado" e `retention_until` nulo (não é apagado).
   - `orders.anonymized_at` marca o pedido e torna a rotina idempotente.
   - O comando passa a se chamar, na descrição, "anonimizar e apagar pedidos após a retenção fiscal". A assinatura `privacy:purge-orders` continua, para não mexer no deploy.

6. **Configuração**: `config/privacy.php` ganha `inactive_account_years` (5) e `inactivity_notice_days` (30), com variáveis de ambiente como `fiscal_retention_years`. `CodePrivacyPractices` passa `:inactiveYears` e `:noticeDays` para as frases novas de `lang/pt_BR/privacy.php`, e a frase de exclusão é ajustada para dizer que, sem exclusão, o pedido é anonimizado depois do prazo.

## Risks / Trade-offs

- **Backfill com `updated_at`** dá a contas antigas um último acesso aproximado. Isso só adia a anonimização, nunca a antecipa, já que `updated_at` é sempre igual ou posterior ao último login real.
- **E-mail do aviso que volta** (caixa desativada): a conta é anonimizada 30 dias depois mesmo assim, que é o comportamento pedido. O aviso é só a chance de manter a conta.
- **Apelido reservado para sempre** por contas anonimizadas. É aceitável no volume esperado e preserva a autoria pública dos relatos.
- **Rotina parada por mais de um dia** acumula trabalho. Por isso a rotina processa em lotes (`chunkById`) e cada passo grava o seu marcador antes de seguir.
- **Mudança de nulidade em `members`**: o código que lê `name`/`email` de membro passa a poder receber nulo. Isso fica restrito a contas anonimizadas, que não logam. Os pontos que mostram nome no painel (moderação, auditoria) usam o apelido ou "Conta anonimizada", o que é verificado por testes.

## Migration Plan

1. Migration aditiva: `members.last_seen_at`, `inactivity_notice_sent_at`, `inactivity_alerted_at` e `anonymized_at`; nulidade de `name`, `email` e `google_id`; backfill de `last_seen_at` com `updated_at`; `orders.anonymized_at`.
2. Deploy do código e do agendamento. Na primeira execução, nenhuma conta está perto de 5 anos (o site é de 2026), então nenhum aviso sai. Pedidos pagos antes de 2021 não existem.
3. Rollback: reverter o código. As colunas novas são ignoradas, e a nulidade só importa se alguma conta já tiver sido anonimizada.

## Open Questions

- Quem tem papel recebe o mesmo aviso de inatividade 30 dias antes, e os admins recebem o alerta no prazo. Se o encarregado preferir que contas com papel também sejam anonimizadas (perdendo o papel), basta remover o filtro de papel da anonimização.
