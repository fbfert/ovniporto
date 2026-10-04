# Tasks

## 1. Performance

- [x] 1.1 Implementar pipeline AVIF/WebP 400–1600 e componente `Picture` com LQIP, verificado por teste de unidade do pipeline e inspeção do `srcset` no HTML
- [x] 1.2 Configurar subset e preload de fontes e code-splitting com carregamento sob demanda de mapa e animação, verificado por análise do bundle mostrando `/faq` sem código de mapa
- [x] 1.3 Adicionar `Cache-Control`/`ETag` e cache de fragmentos invalidado por eventos, verificado por teste de feature que recebe 304
- [ ] 1.4 Rodar Lighthouse mobile (mediana de 3) nas 5 rotas, verificado por relatórios com todas as categorias ≥ 90

## 2. Acessibilidade

- [x] 2.1 Revisar teclado e foco em menu, modais, assistente, carrinho e lightbox, verificado por testes Playwright de teclado
- [x] 2.2 Verificar contraste dos pares de tokens e ajustar opacidades, verificado por script de contraste sem falhas AA
- [x] 2.3 Garantir alternativa em lista ao mapa, faixa pausável e reduced-motion, verificado por teste Playwright com `reducedMotion: 'reduce'`
- [ ] 2.4 Percorrer relato e compra com NVDA ou VoiceOver e corrigir, verificado por checklist anexado à change

## 3. Polimento e instalação

- [x] 3.1 Adicionar estados vazio/carregando/erro em todas as listas e transição de página de 150 ms com restauração de rolagem, verificado por testes de componente dos três estados
- [x] 3.2 Adicionar favicon, ícones, manifest e página offline "Sem sinal da torre", verificado por Lighthouse PWA instalável e teste Playwright offline
  - O Lighthouse atual (12+) não tem mais a categoria PWA: a instalabilidade foi verificada pelos critérios equivalentes em `e2e/install.spec.ts` (manifest com tema night, ícones 192/512/maskable que carregam, service worker controlando a página e "Sem sinal da torre" sem rede).
- [x] 3.3 Revisar todos os textos de interface em `pt-BR.ts`, verificado por revisão registrada na change

## 4. Testes de ponta a ponta

- [x] 4.1 Configurar Playwright com seeds de demonstração e `make e2e` documentado no README, verificado por `make e2e` rodando localmente
  - Verificado com `npm run e2e` (o alvo `make e2e` chama o mesmo script; esta máquina Windows não tem make).
- [x] 4.2 Escrever os fluxos 1 a 4 (navegar e compartilhar; relatar e aprovar; comprar e operar pedido; exportar e excluir conta), verificado por `make e2e` verde
- [x] 4.3 Adicionar axe-core em `/`, `/mapa`, `/loja`, `/relatar`, `/o-lugar` e regressão visual da home em 390/1440 px com tolerância 0,5%, verificado por `make e2e` verde
