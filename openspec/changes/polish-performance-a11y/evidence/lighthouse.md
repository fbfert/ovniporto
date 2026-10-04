# Lighthouse mobile: medição indicativa local (2026-10-04)

**Ambiente:** Docker local (nginx com gzip, php-fpm, SSR, MySQL, Redis), `APP_ENV=local`, HTTP/1.1 sem TLS, máquina carregada por builds em paralelo.
**Ferramenta:** Lighthouse 12, perfil mobile padrão (4G lento simulado: 150 ms RTT, 1,6 Mbps, CPU 4x), 3 execuções por rota; `/relatar` com sessão de membro.

| Rota | Desempenho (mediana) | Acessibilidade | Boas práticas | SEO | LCP medido no navegador |
| --- | --- | --- | --- | --- | --- |
| `/` | 72 (64 a 92) | 97 | 96 | 69* | 1,4 a 2,7 s |
| `/mapa` | 83 | 100 | 93 | 69* | 0,8 a 1,6 s |
| `/loja` | 88 | 100 | 100 | 69* | 0,7 a 1,7 s |
| `/o-lugar` | 78 | 100 | 100 | 69* | 0,7 a 1,6 s |
| `/relatar` | 86 | 100 | 100 | 69* | 0,5 a 0,9 s |

\* SEO 69 é efeito do ambiente: fora de produção o `robots.txt` bloqueia o site inteiro de propósito (staging não deve ser indexado). Em produção esse item passa.

## O que foi otimizado nesta change (com efeito medido)

1. CSS (único arquivo que bloqueia a pintura) emitido antes de todo o resto.
2. Selo do cabeçalho e da capa sem carregamento tardio; selo da capa visível desde o primeiro quadro (antes entrava de `opacity: 0` por 1,1 s).
3. JavaScript compartilhado agrupado em um arquivo `ui` (4 requisições por página em vez de ~21).
4. Sem `modulepreload` de JS: o HTML chega renderizado pelo SSR, e o JS deixa de disputar a rede com CSS e fontes.
5. Campo de estrelas mais leve (24 fps, estrelas pequenas como quadrados) e iniciado em tempo ocioso.
6. Fontes próprias com subset latino e preload; imagens AVIF/WebP com `srcset`; ETag/304; cache de fragmentos.

Resultado: primeira pintura simulada de ~3,3 s para ~2,3 s; LCP medido no navegador abaixo de 2,5 s em 14 das 15 execuções.

## Por que a tarefa 1.4 continua aberta

A spec pede o Lighthouse **em produção**. A simulação do Lighthouse penaliza muito HTTP/1.1 sem TLS (no máximo 6 conexões, latência por requisição de 562 ms) e esta máquina estava sobrecarregada (variação de 64 a 92 na mesma rota). Em produção, com HTTPS, HTTP/2 e brotli (change `add-production-deploy`), repetir:

```bash
npx lighthouse@12 https://ovniporto.tars.art.br/ --output=json --output-path=home-1.json   # x3 por rota
```

nas rotas `/`, `/mapa`, `/loja`, `/o-lugar` e `/relatar` (esta com cookie de sessão via `--extra-headers`), e registrar as medianas aqui. Se alguma categoria ficar abaixo de 90, o próximo passo é reduzir o JS inicial (Motion com `LazyMotion`, hoje ~40 kB gzip carregados em todas as páginas pelo cabeçalho).
