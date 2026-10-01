# Proposal

## Why

Quem usa o OVNIPORTO está muitas vezes na serra, com 4G fraco, à noite e com o celular numa mão. Antes do lançamento o site precisa ser rápido, navegável por teclado e leitor de tela, consistente em todos os estados, e ter os fluxos críticos protegidos por testes de ponta a ponta.

## What Changes

- Performance: imagens AVIF/WebP com srcset e placeholder borrado, fontes com subset e preload, code-splitting e carregamento sob demanda de mapa e animação, cache HTTP com ETag e cache de fragmentos invalidado por eventos; meta Lighthouse mobile ≥ 90 (Prompt 20).
- Acessibilidade: teclado em menu, modais, assistente, carrinho e lightbox; foco visível; contraste AA dentro dos tokens; alt obrigatório; alternativa em lista ao mapa; faixa corrida pausável; nada piscando mais de 3 vezes por segundo.
- Polimento: estados vazios, de carregamento e de erro em todas as listas; transição curta entre páginas com restauração de rolagem; ícones e manifest para instalação; página offline "Sem sinal da torre".
- Testes de ponta a ponta dos fluxos críticos, axe-core e regressão visual da home, com `make e2e` (Prompt T).

## Capabilities

### New Capabilities
- `quality-gates`: metas de performance, estados de interface, instalação básica e suíte de testes de ponta a ponta exigidos antes do lançamento.
- `accessibility`: requisitos de acessibilidade aplicados a todo o site público e aos fluxos de relato e compra.

### Modified Capabilities
<!-- Nenhuma. Aplica-se sobre `design-system`, `public-layout` e demais capabilities sem alterar seus requisitos. -->

## Impact

- Pipeline de imagens, configuração Vite, cabeçalhos HTTP, componentes de lista, service worker, Playwright no Makefile e README.
