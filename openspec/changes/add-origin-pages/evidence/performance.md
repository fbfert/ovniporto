# Desempenho das páginas da origem: medição local indicativa (2026-10-05)

**Ambiente:**
- servidor embutido do PHP com 4 workers, **sem gzip**;
- SSR do próprio build (`node bootstrap/ssr/ssr.js`);
- SQLite com dados de demonstração;
- Chromium do Playwright em 390 × 844 com DPR 2.

**Condições:** 4G lento emulado (150 ms de latência, 1,6 Mbps) e CPU 4× mais lenta. O LCP foi medido no navegador com `PerformanceObserver`, em 3 execuções por rota.

| Rota | LCP (3 execuções) | Elemento do LCP | HTML bruto → gzip |
|---|---|---|---|
| `/origem` | 4,2 / 3,6 / 2,6 s | parágrafo de abertura da capa | 49 KB → 11 KB |
| `/origem/cachi` | 3,4 / 3,7 / 3,4 s | foto aérea da capa (`cachi-aereo-800.avif`, prioridade alta) | 120 KB → 24 KB |
| `/origem/atlas` | 3,8 / 3,6 / 3,7 s | parágrafo ao lado do mapa | 185 KB → 36 KB |
| `/o-lugar` (referência, já aprovada) | 3,1 / 2,9 / 2,9 s | ilustração da vista geral | 51 KB → 10 KB |

Sem SSR, as mesmas rotas ficaram entre 5,2 e 9,0 s, inclusive `/o-lugar`. Esses números refletem o ambiente, não a página.

## Leitura

- Nas mesmas condições, as páginas novas ficam na faixa da `/o-lugar`. Cachi e Atlas levam uns 0,5 s a mais, por causa do HTML maior: o dossiê vem inteiro no SSR e nos dados da página, o que é bom para busca e para ler sem JavaScript.
- Este ambiente não comprime as respostas. Com o gzip do nginx de produção, a diferença de HTML entre o Atlas e a `/o-lugar` cai de 134 KB para 26 KB, uns 0,13 s nesse 4G.
- Nas medições do polimento no Docker com nginx, a `/o-lugar` ficou com LCP de 0,7 a 1,6 s. A mesma proporção põe as páginas da origem abaixo de 2,5 s.
- O que já ajuda: a capa de Cachi carrega com prioridade alta em AVIF de 800 px com prévia desfocada; todas as outras imagens, o mapa e os players carregam sob demanda.

## O que falta

Rodar o Lighthouse mobile em produção (HTTPS, HTTP/2, gzip) em `/origem`, `/origem/cachi` e `/origem/atlas`, junto com a mesma pendência da change `polish-performance-a11y` (tarefa 1.4).
