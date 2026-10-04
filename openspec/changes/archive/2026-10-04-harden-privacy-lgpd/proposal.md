# Proposal

## Why

As regras de privacidade do OVNIPORTO estão espalhadas por várias changes. Antes de lançar, elas precisam ser consolidadas em garantias verificáveis por teste, com registro de consentimentos e retenção fiscal definida, para cumprir a LGPD e dar ao encarregado algo concreto para revisar.

## What Changes

- Testes de ponta a ponta das garantias: nenhum metadado em fotos salvas (original e variantes), fotos pendentes só por URL assinada de 10 min para moderador ou autor (Prompt 19).
- Dados do Google mínimos e escopos restritos.
- Conteúdo definido da exportação "Baixar meus dados".
- Exclusão de conta com anonimização de pedidos e retenção fiscal de 5 anos após o pagamento, seguida de apagamento definitivo agendado.
- Registro de consentimentos versionado (termos, publicação de relato, newsletter, listagem de parceiro) e novo aceite quando a versão dos termos mudar.
- Seção "O que fazemos na prática" em `/privacidade`, retenção de 6 meses dos logs do nginx e verificação de que não há cookies de terceiros.

## Capabilities

### New Capabilities
- `privacy-compliance`: garantias de privacidade transversais, consentimentos, exportação, exclusão com retenção fiscal e ausência de rastreamento.

### Modified Capabilities
<!-- Nenhuma (as capabilities relacionadas ainda não estão arquivadas). Reforça `member-accounts`, `sighting-submission`, `checkout` e `region-directory` com requisitos próprios. -->

## Impact

- Módulo Privacy, tabela de consentimentos, jobs e comandos agendados, configuração de logrotate no container web, página `/privacidade`.
