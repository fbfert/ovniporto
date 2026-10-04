# Design

## Context

Ver proposal.md. Inclui o Prompt T (testes de ponta a ponta), que roda contra o ambiente local com os seeds de demonstração.

## Goals / Non-Goals

**Goals:** metas medidas e automatizadas sempre que possível.

**Non-Goals:** redesenho visual; novas funcionalidades.

## Decisions

- **Componente `Picture` único** consome as variantes geradas no servidor; LQIP de 20 px.
  - Imagens do painel (disco público): cada `uuid.webp` tem irmãs `uuid-{400,800,1200,1600}.{avif,webp}` e `uuid-20.webp` (placeholder). O `Picture` deriva o `srcset` da URL por essa convenção; o comando `images:responsive` gera as irmãs que faltarem, então a convenção vale para toda imagem enviada.
  - Fotos de relato: o servidor entrega o `srcset` pronto (URLs públicas ou assinadas), em AVIF e WebP, com o placeholder de 20 px.
  - Ilustrações de conceito: continuam no manifesto `concept.json` (LQIP embutido).
- **Code-splitting por página** no Vite; mapa e animação via import dinâmico; Starfield em idle callback.
- **Cache de fragmentos** (home, mapa) invalidado pelos mesmos eventos de domínio que invalidam a API.
- **Playwright com fakes:** login Google via fake do `IdentityProvider`, pagamento via fake do `PaymentGateway` (ou sandbox), axe-core embutido; capturas de referência versionadas no repositório.
- **Service worker mínimo** só para a página offline e ícones; nada de cache agressivo de HTML (evita conteúdo velho).

## Risks / Trade-offs

- [Regressão visual instável por fontes/animação] → capturas com `prefers-reduced-motion` e fontes locais pré-carregadas.
- [Lighthouse varia entre execuções] → mediana de 3 execuções.
