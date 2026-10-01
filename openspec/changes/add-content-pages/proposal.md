# Proposal

## Why

A home (change `build-public-home`) aponta para a lenda, a comunidade e as perguntas frequentes, e o site precisa de Privacidade e Termos antes de coletar qualquer dado de membro. Essas páginas também são o lugar onde o projeto é honesto: a lenda é criada e ainda não foi escrita, e o texto jurídico ainda não existe.

## What Changes

- Novas páginas públicas com SSR: `/lenda`, `/faq`, `/comunidade`, `/privacidade`, `/termos` (Prompt 6).
- `/lenda`: origem real (visita ao Ovnipuerto de Cachi, linha do tempo de Cachi) e bloco "O carro amarelo" com estado "aguardando conteúdo" enquanto o texto da lenda estiver vazio.
- `/faq`: perguntas frequentes em acordeão acessível, seed com 10 perguntas.
- `/comunidade`: regras de convivência (seed com 5 regras), links de WhatsApp e Instagram e o formulário Avise-me.
- `/privacidade` e `/termos`: texto longo com índice, data de atualização e conteúdo inicial marcado como RASCUNHO.
- Textos editáveis vindos de blocos de conteúdo (a edição no painel vem em `add-operations-panel`).

## Capabilities

### New Capabilities
- `content-pages`: páginas editoriais públicas (lenda, FAQ, comunidade, privacidade, termos) e seus estados de conteúdo ausente.

### Modified Capabilities
<!-- Nenhuma. Depende de `public-layout`, `design-system` e `waitlist` (change `build-public-home`, ainda não arquivada). -->

## Impact

- Módulo Content (blocos de conteúdo, FAQ), novas rotas públicas, seeds.
- Reutiliza o cadastro Avise-me da capability `waitlist` e o layout/SEO de `public-layout`.
- Fora de escopo: redação final da lenda e do texto jurídico; editor no painel.
