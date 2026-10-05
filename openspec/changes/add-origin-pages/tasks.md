# Tasks

## 1. Material de origem no repositório

- [x] 1.1 Escrever `scripts/origin/extract-atlas.php`, que lê o `.docx` do Atlas e gera o rascunho de `resources/content/origin/atlas.json` e as 12 imagens. Verificar que o JSON tem 12 casos, 3 candidatos, 39 fontes e a escala A–F, e que as imagens extraídas abrem.
- [x] 1.2 Transcrever a página de Cachi para `resources/content/origin/cachi.json` (os 14 capítulos, 6 casos do arquivo, 6 personagens, 13 marcos da linha do tempo, 4 vídeos e as fontes). Verificar por leitura lado a lado com o HTML original, sem trecho faltando.
- [x] 1.3 Criar `resources/content/origin/images.json` com autor, licença, URL da licença, página de origem e tipo (foto ou mapa de localização) das 18 imagens. Verificar que toda imagem citada em `cachi.json` e `atlas.json` tem crédito.
- [x] 1.4 Gerar as variantes das 17 fotos (a aérea de Cachi serve às duas páginas) com o comando novo `origin:images`, que usa o mesmo `ConceptImageBuilder` e lê `images.json`, gravando em `public/origin` e `resources/js/data/origin-images.json`; coberto por `OriginImagesTest`. Verificar que existem AVIF/WebP 400/800/1200/1600 (sem ampliar fotos menores) e os placeholders.
- [x] 1.5 Escrever `docs/ovniporto-ilustracoes-origem.md` com os prompts das 6 ilustrações conceito, no formato de `ovniporto-ilustracoes.md`, e incluir os slugs no manifesto de conceito como pendentes. Verificar que `ConceptImage` mostra "conceito em produção" para cada slug sem arquivo.

## 2. Domínio e casos de uso

- [x] 2.1 Criar `app/Domain/Origin` com a interface `OriginLibrary` e os value objects `ConfidenceGrade` (A–F e graus compostos como "A/B") e `SourceKind`. Verificar com testes de unidade de parse e rótulo de cada grau e tipo.
- [x] 2.2 Implementar `JsonOriginLibrary` em Infrastructure, com validação da forma dos arquivos e cache versionado por `filemtime` (cacheando arrays), e ligá-la no `DomainServiceProvider`. Verificar com teste de unidade: um arquivo malformado gera erro claro, e um arquivo alterado invalida o cache.
- [x] 2.3 Implementar os casos de uso `GetOriginHub`, `GetCachiDossier`, `GetAtlas` e `GetAtlasCase`; o último devolve `null` para slug desconhecido e inclui caso anterior e seguinte. Verificar com um teste de unidade por caso de uso.

## 3. Rotas, renomeação e SEO

- [x] 3.1 Trocar `/lenda` por `Route::redirect` 301 e criar as rotas `origin`, `origin.cachi`, `origin.atlas` e `origin.atlas.case` (com `whereIn` sobre os slugs), com controllers finos. Verificar com testes de feature:
  - 301 de `/lenda`;
  - 200 nas 3 páginas e em todos os 12 casos;
  - 404 em caso inexistente.
- [x] 3.2 Tirar "lenda" dos textos do OVNIPORTO: o nome da página vira "origem" e a história do carro vira "relato". Os dados históricos de Cachi e do Atlas ficam como estão. A troca cobre:
  - `nav.ts`, rodapé, `pt-BR.ts`, rótulos do painel para `legend_body` e `home_legend`;
  - `Checkout`, `Styleguide` e `Review`, onde houver link;
  - testes existentes.

  Verificar com `grep -ri "lenda" resources/js app lang routes database resources/views` sem resultados, exceto o redirect, o teste dele e `resources/content/origin/`.
- [x] 3.3 Acrescentar ao `seo.php` as entradas das novas rotas, usar como imagem de compartilhamento de cada caso a própria foto em JPG (`/origin/{arquivo}.jpg`), adicionar `Article` e `Place` ao `StructuredData` e as 15 URLs ao `BuildSitemap` (retirando `/lenda`). Verificar com testes de feature de título, descrição, canonical e `og:image` em `/origem/atlas/cachi`, e com o teste do sitemap.
- [x] 3.4 Acrescentar `https://www.youtube-nocookie.com` ao `frame-src` do `SecurityHeaders` no site todo (o Inertia mantém o CSP do primeiro documento nas visitas seguintes), e nenhum outro domínio do YouTube; verificar com teste de feature em `/` e em `/origem/cachi`.

## 4. Componentes da origem

- [x] 4.1 Criar `CreditedImage`, com legenda de autor, licença com link e "origem e licença", e com a variante "Mapa de localização". Verificar com teste de componente que nenhuma foto renderiza sem crédito e que o mapa troca a legenda e o alt.
- [x] 4.2 Criar `SourceBadge`, com o tipo de fonte por extenso e cor, e `ConfidenceSeal`, o carimbo com o grau A–F e a descrição acessível. Verificar com teste de componente do texto visível e do `aria-label`.
- [x] 4.3 Criar `VideoFacade`, com capa local, aviso de conteúdo do YouTube e iframe do youtube-nocookie só após o clique, movendo o foco para o player. Verificar com teste de componente (sem iframe antes do clique, iframe com o domínio nocookie depois) e cobrir a variante de cartão-link para vídeos sem ID.
- [x] 4.4 Criar `ChapterIndex`, com índice lateral fixo no desktop e pílula "Capítulos" com painel no celular (foco preso e fecha com Esc), e `Timeline` vertical com anos vazados. Verificar com teste de componente de teclado e Esc.

## 5. Páginas

- [x] 5.1 Implementar `Pages/Origin/Hub.tsx`:
  - capa e "De Cachi a Lages" com o texto aprovado;
  - bloco "O relato do / O carro amarelo", com o estado vazio ("Ainda estamos capturando o relato do Julean que foi abduzido…") e o publicado;
  - portas ilustradas para Cachi e Atlas e convite para relatar.

  Verificar com testes de feature do texto "Os fundadores visitaram", da frase do Julean, dos dois estados do relato e dos links.
- [x] 5.2 Implementar `Pages/Origin/Cachi.tsx`:
  - os 14 capítulos com âncoras, cada caso do arquivo como cartão-ingresso com o `SourceBadge`;
  - a ficha da Estrella (48 m, 36 e 12 pontas) e os personagens;
  - a linha do tempo, a galeria em polaroids com crédito, os vídeos e as fontes.

  Verificar com teste de feature da ordem dos capítulos e da ressalva da investigação independente.
- [x] 5.3 Implementar `Pages/Origin/Atlas.tsx`:
  - intro, método e escala A–F;
  - grade de carimbos dos 12 casos, renderizada no SSR;
  - mapa `LazyMap` com Lages em amarelo e "posição aproximada";
  - cronologia comparada, questões transversais, candidatos, catálogo de fontes e créditos.

  Verificar com teste de feature (12 links na lista sem JS, 3 candidatos, contagem de fontes) e e2e do popup do mapa.
- [x] 5.4 Implementar `Pages/Origin/AtlasCase.tsx`:
  - imagem com crédito ou mapa de localização;
  - dados documentais como lista de definição, com o `ConfidenceSeal`;
  - fontes, questões em aberto e anterior/seguinte;
  - caso Lages com grau F, "em planejamento" e link para `/o-lugar`.

  Verificar com testes de feature de St. Paul (grau "A/B") e de Lages (sem afirmar que existe), e de Green River como mapa.
- [x] 5.5 Trocar a seção da home `LegendSection` por `OriginSection` (sobretítulo "De Cachi a Lages", título "A origem", texto `home_legend` atualizado no seeder, imagem e link "Conhecer a origem") e remover `resources/js/data/cachi.ts`. Verificar com o `HomePageTest` atualizado (ordem das seções e link para `/origem`).

## 6. Projeto e qualidade

- [x] 6.1 Atualizar o CLAUDE.md: "A lenda: aguardando conteúdo" vira a regra da origem (Cachi e Atlas documentados; o relato do carro amarelo com estado de espera honesto). A palavra "lenda" sai do tom e do vocabulário do projeto. Verificar por leitura do diff.
- [x] 6.2 Rodar as skills de design e animação nas 4 páginas, tirar capturas em 390, 768 e 1440 px e corrigir quebras e feiuras. Verificar com as capturas antes e depois anexadas em `evidence/`.
- [x] 6.3 Atualizar o e2e:
  - axe sem violações críticas em `/origem`, `/origem/cachi`, `/origem/atlas` e um caso;
  - teste de que nenhuma requisição ao YouTube sai antes do clique;
  - snapshots visuais da home.

  Verificar com `npm run e2e` verde.
- [ ] 6.4 Rodar `php artisan test`, PHPStan, `npm run lint` e `npm test`. Verificar tudo verde e LCP < 2,5 s nas 3 páginas no Lighthouse local, registrado em `evidence/`.
  - Feito: Pest (444), PHPStan, Pint, ESLint/tsc/Prettier e Vitest (58) verdes; e2e da origem e axe verdes. LCP medido localmente com SSR na faixa da `/o-lugar` (`evidence/performance.md`). Falta o Lighthouse em produção, junto da tarefa 1.4 de `polish-performance-a11y`.
