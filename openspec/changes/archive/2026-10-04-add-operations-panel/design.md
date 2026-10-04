# Design

## Context

Ver proposal.md. Junta os Prompts 14 a 17; cada capability pode ser implementada em sequência (acesso → moderação → loja → conteúdo).

## Goals / Non-Goals

**Goals:** autorização no servidor por papel; toda alteração auditada; regras de Domain dos módulos reaproveitadas, nunca duplicadas no painel.

**Non-Goals:** animações decorativas no painel; editor WYSIWYG (markdown com prévia basta).

## Decisions

- **Gates/Policies por área** (`relatos`, `pedidos`, `produtos`, `membros`, `conteudo`, `auditoria`) mapeadas a papéis em um único lugar. Front-end apenas esconde menus; o servidor decide.
- **Auditoria como decorador de UseCase** do painel: registra antes/depois em `audit_logs` na mesma transação. Alternativa (observers de Eloquent) rejeitada: não sabe o autor nem a intenção.
- **Integrações atrás das portas existentes:** reembolso e etiqueta via `PaymentGateway`/`ShippingProvider`; geocodificação via nova porta `Geocoder` (Nominatim com rate limit, cache e atribuição); PDF e CSV via interfaces de exportação.
- **Moderação usa a máquina de estados do relato**; eventos de aprovação invalidam caches de mapa, API e home.
- **Privacidade:** CPF revelado sob demanda e auditado; dados reais do autor do relato só na tela de moderação; comprovantes de consentimento em disco privado.

## Risks / Trade-offs

- [Escopo grande numa change] → tasks agrupadas por capability, cada grupo entregável e testado isoladamente.
- [Nominatim com limite de uso] → cache por coordenada arredondada e uma requisição por segundo.
