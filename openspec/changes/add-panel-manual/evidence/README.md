# Evidência: manual do painel

Conferência em 2026-10-07, servidor local (`php artisan serve`, build de produção do front), conta admin de desenvolvimento.

## Geometria e alvos (Playwright, 1440 px e 390 px)

- 18 páginas por largura (índice + 17 capítulos), 1.076 nós de mapa no total.
- 0 sobreposições entre nós, 0 px de rolagem horizontal, 0 nós com menos de 44 px de altura.
- Teclado em `/painel/manual/produtos`: foco no centro, `→` vai para "Novo produto", `Enter` leva o foco ao título "Cadastrar um produto novo" e a URL ganha `#novo`.
- "Como funciona" em `/painel/produtos/novo` aponta para `/painel/manual/produtos#novo` nas duas larguras.

Ajustes feitos por causa da conferência: altura das linhas do mapa 58 → 64; coluna das folhas mais larga (rótulos de três linhas se encostavam); espaço entre sobretítulo e título no índice.

## Acessibilidade (axe, WCAG 2.1 AA)

`/painel/manual`, `/painel/manual/pedidos` e `/painel/manual/produtos`, em 1440 e 390 px: sem violações, depois de escurecer o "Revisado em" (`night/55` → `night/70`, que falhava em contraste).

## Capturas

- `manual-indice-1440.png`, `manual-indice-390.png`
- `manual-pedidos-1440.png`, `manual-pedidos-390.png`
- `manual-produtos-1440.png`, `manual-produtos-390.png`
- `como-funciona-produtos-novo-390.png`

## Limite

Só existe conta admin no banco local. O filtro por papel (moderação e loja) está coberto pelos testes de feature e de unidade, não por captura.
