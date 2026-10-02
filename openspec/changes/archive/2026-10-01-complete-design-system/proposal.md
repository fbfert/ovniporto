# Proposal

## Why

A auditoria do plano (Prompts 2 e 3) achou lacunas no design system que bloqueiam as próximas fases: o relato em 4 passos, o login, a loja e o painel precisam de campo de texto longo, seleção em pílulas, select e modal acessível, e o conjunto de ícones nunca foi criado. Os links de WhatsApp e Instagram só chegam à home, então o rodapé das outras páginas e o menu mobile não os mostram.

## What Changes

- Novos campos: área de texto com contador de caracteres, select e grupo de pílulas (seleção única ou múltipla), nos tons claro e escuro, com erros em português. O campo de texto também ganha contador opcional.
- Modal acessível (foco preso, Esc fecha, foco volta ao gatilho, rolagem travada); o trap de foco do menu mobile passa a ser compartilhado.
- Conjunto de ícones em SVG: disco voador, estrela, feixe, carro, pin, câmera, bússola, carimbo, WhatsApp e Instagram.
- `InfoCard` aceita ícone; `Section` ganha o padrão "grid".
- Links da comunidade (WhatsApp, Instagram, e-mail) compartilhados em todas as páginas; o menu mobile e o rodapé passam a usá-los.
- Styleguide mostra tudo isso.
- Verificação pendente da home: formulário Avise-me com Toast no navegador e layout em 390 px.

## Capabilities

### New Capabilities
- `form-controls`: campos de formulário do site (texto, texto longo, select, pílulas, checkbox) e seu comportamento de validação e acessibilidade.
- `dialogs`: janelas modais acessíveis.
- `community-links`: links públicos da comunidade disponíveis em todas as páginas.

### Modified Capabilities

## Impact

- `resources/js/Components/Ui/Fields.tsx`, novo `Ui/Modal.tsx`, novo `Components/Icons`, `hooks/useFocusTrap.ts`, `Layout/MobileMenu.tsx`, `Layout/Footer.tsx`, `Pages/Dev/Styleguide.tsx`.
- Novo UseCase `GetCommunityLinks` (módulo Content) e o middleware do Inertia.
- Sem dependências novas.
