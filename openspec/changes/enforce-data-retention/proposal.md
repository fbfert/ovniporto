# Proposal

## Why

O encarregado de dados confirmou duas regras de retenção que a política de privacidade já anuncia, mas que o código ainda não cumpre: contas sem acesso por 5 anos são anonimizadas (não excluídas), e pedidos pagos são anonimizados 5 anos após o pagamento, qualquer que seja a origem. Hoje o site nem registra o último acesso, e só os pedidos de contas excluídas têm prazo de retenção. Sem isso, os textos de /privacidade e /termos não podem ser marcados como final.

## What Changes

- Registrar o **último acesso** de cada membro: no login com o Google e, para sessões em uso, no máximo uma vez por dia, sem gravar no banco a cada requisição.
- **Aviso de inatividade**: 30 dias antes de completar 5 anos sem acesso, o membro recebe um e-mail dizendo que basta entrar para manter a conta. Entrar zera a contagem.
- **Anonimização por inatividade** (rotina diária): a conta fica, mas perde nome, e-mail, foto e o vínculo com o Google. Relatos publicados continuam no Livro com o apelido. Relatos não publicados (pendentes, com ajuste pedido ou recusados) e as fotos deles são apagados. Consentimentos são anonimizados e a inscrição no Avise-me e as ofertas de colaboração com o mesmo e-mail são apagadas, como na exclusão. Contas com papel no painel não são anonimizadas automaticamente: o admin recebe um alerta e decide.
- **Pedidos pagos com mais de 5 anos do pagamento** (contas ativas, contas anonimizadas e compras sem conta) são anonimizados por rotina diária: somem nome, e-mail, telefone, CPF e endereço (ficam cidade e UF); ficam número, itens, valores e datas. Os pedidos de contas excluídas continuam sendo apagados de vez ao fim do prazo, como hoje.
- No painel, membro anonimizado aparece como "Conta anonimizada", com o apelido e a data, sem dados pessoais.
- A lista "O que fazemos na prática" em /privacidade ganha as duas garantias novas, com os números lidos da configuração.
- Prazos (anos de inatividade e dias de aviso) em `config/privacy.php`, ao lado do prazo fiscal.

## Capabilities

### New Capabilities

_Nenhuma._

### Modified Capabilities

- `privacy-compliance`: novas exigências de registro do último acesso, aviso e anonimização por inatividade, anonimização dos pedidos pagos após o prazo fiscal, e a seção "O que fazemos na prática" com essas garantias.

## Impact

- **Banco**: `members` ganha `last_seen_at`, `inactivity_notice_sent_at`, `inactivity_alerted_at` e `anonymized_at`; `name`, `email` e `google_id` passam a aceitar nulo (os índices únicos continuam). `orders` ganha `anonymized_at`.
- **Código**: Members (novo caso de uso de anonimização por inatividade, registro do último acesso, middleware), Orders (anonimização após o prazo, reaproveitando a de `EloquentOrderRepository::anonymizeFor`), Sightings (apagar só os relatos não publicados), Privacy (lista de garantias), agendamento em `routes/console.php`.
- **E-mail**: novo e-mail de aviso de inatividade (fila) e alerta ao admin para contas com papel.
- **Painel**: lista e ficha de membros mostram o estado anonimizado; manual do painel (privacidade e membros) atualizado.
- **Sem mudança** para quem exclui a conta: o fluxo atual segue igual.
