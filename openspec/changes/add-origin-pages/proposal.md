# Proposal

## Why

A página `/lenda` hoje é curta: dois parágrafos sobre Cachi, quatro marcos numa linha do tempo e um bloco "aguardando conteúdo". Agora existe material de pesquisa de verdade para contar de onde o OVNIPORTO vem:
- a página documental do Ovnipuerto de Cachi, com fotos de licença aberta e fontes;
- o dossiê "Atlas Mundial dos Ovnipuertos", com 12 casos, grau de confiança, cronologia e 39 fontes.

Os fundadores também decidiram tirar a palavra "lenda" do site: a página é a "origem", e a história do carro amarelo é "o relato". O Niva amarelo do Julean entra com humor e com a ressalva "segundo contam...".

## What Changes

- **BREAKING (URL):** `/lenda` passa a ser `/origem`. A rota antiga responde com 301 para a nova, e nenhum link interno continua apontando para `/lenda`.
- **Novo hub `/origem`.** Reúne:
  - "De Cachi a Lages", com o texto novo: os fundadores, os avistamentos da serra, o Niva amarelo do Julean e a visita esperada;
  - o bloco "O relato do / O carro amarelo", com o texto editável que já existe. Enquanto vazio, mostra "Ainda estamos capturando o relato do Julean que foi abduzido. Deixe o e-mail e a torre de controle avisa quando sair." e o Avise-me;
  - duas portas grandes, para Cachi e para o Atlas;
  - o convite para relatar um avistamento.
- **Nova página `/origem/cachi`.** É a história documentada do Ovnipuerto de Cachi:
  - o prólogo e a noite de 24/11/2008;
  - a Estrella de la Esperanza (48 m, 36 e 12 pontas) e a casa-cueva;
  - os relatos, cada um com o tipo de fonte;
  - o desaparecimento de Werner, o retorno em 2019 e a Llamada al Cielo;
  - a obra sob os cuidados do município e a proteção patrimonial;
  - o Barrio Ovnipuerto, os personagens e a linha do tempo de 1997 a 2026;
  - a galeria, os vídeos e as fontes.
- **Nova página `/origem/atlas`.** É o mapa-múndi (Leaflet/OSM) com os 12 ovnipuertos, e Lages em destaque. Traz ainda:
  - a escala de confiança de A a F e a cronologia comparada;
  - as questões transversais e os 3 casos candidatos;
  - o catálogo completo das fontes e os créditos das imagens.
- **Nova página `/origem/atlas/{caso}` para cada um dos 12 casos.** Mostra a foto licenciada (ou um mapa de localização, sempre identificado como mapa), os dados documentais com o grau de confiança, as fontes e as questões em aberto, além da navegação para o caso anterior e o seguinte. O caso "OVNIPORTO Lages" aparece com grau F e o estado real: projeto, meta 2028, nada construído.
- **Muitas imagens.**
  - As 6 fotos de Cachi e as 12 do Atlas, de licença aberta, viram AVIF/WebP em vários tamanhos.
  - Toda imagem leva autor, licença e link de origem na legenda. As licenças CC BY-SA ficam registradas.
  - Ilustrações conceito novas entram como placeholders marcados "conceito em produção". Os prompts são entregues para os fundadores gerarem as imagens.
- **Vídeos clique-para-carregar.** A capa é local. O player de `youtube-nocookie.com` só carrega depois do clique, e o aviso de conteúdo externo aparece antes. Não há cookie nem requisição a terceiros antes do clique.
- **Fim da palavra "lenda" nos textos do OVNIPORTO.** O nome da página vira "origem" e a história do carro vira "relato". Os textos históricos de Cachi e do Atlas ficam como documentados, porque falam da lenda local desses lugares. A troca vale para o menu, a seção da home, o rodapé, o sitemap, o SEO e a imagem de compartilhamento, os testes e o CLAUDE.md. A seção da home passa a apresentar a origem e leva a `/origem`.

## Capabilities

### New Capabilities
- `origin-pages`: o hub `/origem`, a página documental de Cachi, o Atlas com mapa e uma página por caso. Cobre também as regras editoriais (grau de confiança, separação entre documento, relato e tradição, créditos das imagens) e os vídeos clique-para-carregar.

### Modified Capabilities
- `content-pages`:
  - `/lenda` sai da lista de páginas de conteúdo e passa a redirecionar para `/origem`;
  - os requisitos "Origem real da lenda" e "Lenda aguardando conteúdo" migram para `origin-pages`.
- `home-page`: a seção "a lenda" vira "a origem", com o novo texto curto e o link para `/origem`. Muda a ordem declarada das seções e sai o requisito "Lenda aguardando conteúdo".

## Impact

- **Rotas:** `routes/web.php` (novas rotas e o 301), `BuildSitemap`, `lang/pt_BR/seo.php`, OG (`OgKind`), `StructuredData` e o CSP (`frame-src` para `www.youtube-nocookie.com`, só nas páginas de origem).
- **Back-end:** um novo módulo de conteúdo de origem (porta de leitura no Domain e leitor de arquivos de dados versionados em Infrastructure), com controllers finos.
- **Front-end:** páginas em `resources/js/Pages/Origin/`, componentes em `resources/js/Components/Origin/`, `nav.ts`, a seção da home `LegendSection` (renomeada) e `i18n/pt-BR.ts`. Sai `resources/js/data/cachi.ts`.
- **Assets:** `public/origin/` recebe as fotos convertidas pelo pipeline existente; `docs/` recebe os prompts das ilustrações.
- **Testes:** Pest de feature para todas as rotas novas e o 301, Pest de unidade para os casos de uso, e e2e (axe e visual) atualizados.
- **Fora do escopo:**
  - edição dessas páginas pelo painel (o conteúdo fica em arquivos do repositório);
  - fotos autorais da visita de 2026;
  - geração das ilustrações conceito (os fundadores geram);
  - versão em outros idiomas.
