# Design

## Context

`Fields.tsx` tem só `TextField` e `CheckboxField`. O único trap de foco vive dentro do `MobileMenu`. Os links da comunidade vêm de `content_blocks` e só chegam à home via `GetHomeContent`.

## Goals / Non-Goals

**Goals:**
- Componentes prontos para o relato (Prompt 10), o login (9) e o painel (14–17), sem retrabalho.

**Non-Goals:**
- Modal de login Google (fica em `add-members-google-login`, que vai usar este Modal).
- Ícones além dos listados no plano + redes sociais.

## Decisions

- **`useFocusTrap(ref, active, onEscape)`**: extraído do MobileMenu e reaproveitado pelo Modal; devolve o foco ao elemento ativo de antes.
- **Modal** com Motion: fundo em fade, painel `opacity` + `scale(0.96→1)` a partir do centro, 220 ms `ease.snap` na entrada e 160 ms na saída (saída mais rápida que a entrada). Em movimento reduzido, só opacidade. Renderizado em portal no `body`.
- **ChipGroup**: modo único usa `role="radiogroup"` com roving tabindex e setas; modo múltiplo usa botões com `aria-pressed`. Componente controlado (`value`/`onChange`).
- **Contador**: `aria-live="polite"` só ativo a partir de 90% do limite, para não falar a cada tecla.
- **Select**: `<select>` nativo estilizado (melhor no celular), seta em SVG.
- **Links da comunidade**: UseCase `GetCommunityLinks` lê `link_whatsapp`, `link_instagram`, `contact_email` e devolve `null` para vazios; o middleware compartilha `community` como prop lazy (uma consulta por request completo, nenhuma em partial reload).
- **Ícones**: um arquivo `Components/Icons/index.tsx`, cada ícone com `size` e `title` opcional (sem `title` vira `aria-hidden`), traço `currentColor`.

## Risks / Trade-offs

- [Prop compartilhada consulta o banco em toda página] → 1 query pequena com `whereIn`; cache fica para o `polish-performance-a11y`.
