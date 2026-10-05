# Design

## Context

Estado atual: `/lenda` é servida por `LegendController`, que lê só o bloco editável `legend_body`. A página está em `Pages/Content/Legend.tsx`, a linha do tempo de Cachi vem de `resources/js/data/cachi.ts` (4 marcos) e os textos ficam em `i18n/pt-BR.ts` (`legendPage`).

O material novo está fora do repositório:
- `Dropbox/OVNIPORTO/Cachi/OVNIPUERTO_CACHI_pagina_lenda_v2/`: o HTML da página de Cachi, 6 fotos CC, os créditos e um manifesto.
- `Dropbox/OVNIPORTO/ATLAS_MUNDIAL_DOS_OVNIPUERTOS_DOSSIE_DE_PESQUISA.docx`: 12 casos, tabelas, 39 fontes e 12 imagens embutidas, das quais 3 são mapas.

Restrições de projeto que valem aqui:
- SSR em toda página pública.
- Nenhum cookie de terceiros.
- Imagens em AVIF/WebP com `srcset`.
- Mapa em Leaflet/OSM.
- Clean Architecture, com integrações atrás de interfaces.
- Textos de interface em `pt-BR.ts`.

## Goals / Non-Goals

**Goals:**
- O conteúdo longo (Cachi e Atlas) fica versionado em arquivos de dados revisáveis num diff, e não espalhado pelo JSX.
- O servidor conhece os dados, para gerar SEO, sitemap, OG e o 404 de caso inexistente sem duplicar nada no front.
- O conteúdo cabe na tela de 390 px primeiro: índice fixo que vira menu no celular e tabelas que viram listas de definição.

**Non-Goals:**
- Painel para editar casos, fontes ou linha do tempo.
- Busca dentro do Atlas.
- Tradução.
- Baixar as fotos jornalísticas protegidas citadas nas fontes.

## Decisions

### 1. Dados em JSON versionado, lidos por uma porta do Domain
Os arquivos ficam em `resources/content/origin/`:
- `cachi.json`: capítulos, casos do arquivo, personagens, linha do tempo, vídeos e fontes;
- `atlas.json`: escala, casos, cronologia, questões transversais, candidatos, fontes e créditos;
- `images.json`: créditos de cada foto (autor, licença, URL da licença, página de origem, tipo `photo` ou `location-map`).

Do lado do código:
- **Domain** (`app/Domain/Origin`): a interface `OriginLibrary` (`cachi()`, `atlas()`, `case(slug)`, `credits()`) e value objects pequenos: `ConfidenceGrade` (enum A–F, com os graus compostos "A/B" como lista) e `SourceKind` (documento, depoimento, relato, arquivo, hipótese).
- **Infrastructure:** `JsonOriginLibrary` lê, valida a forma e faz cache por versão do arquivo (`filemtime`) com o `FragmentCache` existente, guardando arrays, como já se faz na home.
- **Application:** os casos de uso `GetOriginHub`, `GetCachiDossier`, `GetAtlas` e `GetAtlasCase`. Este último devolve `null` quando o caso não existe, e o controller responde 404.

Alternativas consideradas:
- **TS em `resources/js/data`:** é o padrão atual de `cachi.ts`, mas o servidor não enxerga os dados para SEO, sitemap e 404.
- **Markdown com frontmatter:** fica bom para prosa, ruim para tabelas e fontes estruturadas.
- **Tabelas no banco:** foi descartado pelo usuário (conteúdo em arquivos).

### 2. Extração única do material, sem dependência nova em produção
Um script de apoio (`scripts/origin/extract-atlas.php`, executado uma vez) lê o `.docx` com `ZipArchive` e DOM, gera o rascunho de `atlas.json` e extrai as imagens. O resultado é revisado à mão e commitado; o `.docx` não entra no repositório. O texto de Cachi é transcrito do HTML para `cachi.json` na mesma revisão. O texto aprovado de "De Cachi a Lages" vai para `pt-BR.ts`, porque é interface e não dossiê.

### 3. Imagens pelo pipeline existente
As fotos entram por `concept:import`, que já aceita `--output` e `--manifest`:
- origem: as 18 fotos;
- saída: `public/origin/`;
- manifesto: `resources/js/data/origin-images.json`, com larguras 400, 800, 1200 e 1600, AVIF e WebP, e placeholder.

Um componente `CreditedImage` envolve o `Picture` e sempre renderiza a legenda de créditos a partir de `images.json`. Isso torna impossível exibir uma foto de terceiros sem crédito. Para mapas de localização, o componente troca a legenda para "Mapa de localização".

O arquivo SVG de Green River e Carbondale chega como JPG rasterizado do `.docx`. É aceitável para ilustração; o SVG original fica como link de origem.

Ilustrações conceito novas:
- os slugs entram no manifesto de conceito (`origin-journey`, `niva-abduction`, `stone-star-night`, `atlas-globe`, `cachi-casa-cueva`, `werner-stones`);
- até a imagem existir, `ConceptImage` mostra o placeholder "conceito em produção";
- os prompts ficam em `docs/ovniporto-ilustracoes-origem.md`, no mesmo formato de `ovniporto-ilustracoes.md`.

### 4. Mapa do Atlas reaproveita o LazyMap
O `LazyMap` (carregado só quando entra na tela) recebe pontos com `{slug, name, country, lat, lng, approximate}`. Vista inicial: mundo inteiro, com `worldCopyJump`. Lages tem um marcador próprio em amarelo-carro. Os demais pontos são verdes, com o mesmo estilo do Livro.

A lista dos 12 casos é renderizada sempre no SSR, antes do mapa, e o mapa recebe `aria-hidden` apenas no contêiner de tiles; os pontos têm equivalentes na lista.

Alternativa: um mapa-múndi em SVG próprio. É bonito e leve, mas duplica a pilha de mapas e perde zoom.

### 5. Vídeos: fachada local e CSP só nas páginas de origem
O componente `VideoFacade` mostra uma capa local gerada por nós: um quadro night com título e selo, sem miniatura do `i.ytimg.com`, porque essa miniatura já seria uma requisição a terceiros. No clique, ele insere o `iframe` de `https://www.youtube-nocookie.com/embed/{id}?autoplay=1` e move o foco.

O `SecurityHeaders` acrescenta `https://www.youtube-nocookie.com` ao `frame-src` no site todo. Liberar só nas rotas `origem/*` não funciona: o Inertia navega sem recarregar o documento, então quem chega pela home continua com o CSP da home. A permissão de frame não faz requisição nenhuma; o player só carrega depois do clique.

Vídeos fora do YouTube viram cartões-link que abrem em nova aba, sem player no site. São eles a reportagem do Telenoche (Dailymotion, que não tem modo sem cookies) e o trailer de "Al centro de la Tierra" (Vimeo). Essa foi a decisão do usuário.

### 6. Roteamento e renomeação
Rotas novas:
- `Route::redirect('/lenda', '/origem', 301)`;
- `/origem` → `origin`;
- `/origem/cachi` → `origin.cachi`;
- `/origem/atlas` → `origin.atlas`;
- `/origem/atlas/{slug}` → `origin.atlas.case`, com `whereIn` sobre os slugs conhecidos, que vira 404 fora da lista.

Renomeações:
- `LegendController` → `OriginController` (hub). Os novos `CachiController` e `AtlasController` são finos.
- No front, `Pages/Content/Legend.tsx` → `Pages/Origin/Hub.tsx`, mais `Pages/Origin/Cachi.tsx`, `Atlas.tsx` e `AtlasCase.tsx`.
- `LegendSection` → `OriginSection`, e o item de menu vira "A origem".

As chaves internas `legend_body` e `home_legend` dos blocos editáveis são mantidas. Renomeá-las exigiria uma migração de dados sem ganho visível; o rótulo no painel passa a ser "Origem: o relato do carro amarelo" e "Página inicial: a origem".

### 7. SEO e OG
Entradas novas em `lang/pt_BR/seo.php` por nome de rota. O título e a descrição dos casos vêm de `atlas.json` via `ContentSeo`. A imagem de compartilhamento de cada caso é a própria foto em JPG (`/origin/{arquivo}.jpg`, 1200 px, gerada pelo pipeline). Não houve um `OgKind` novo, porque o repositório de cards é Eloquent e os dados da origem vêm de arquivos. O hub e Cachi usam a foto aérea, e o Atlas usa a foto de St. Paul. `StructuredData` ganha um `Article` para Cachi e um `Place` com `geo` para os casos com coordenada não provisória. O sitemap ganha as 3 páginas e os 12 casos.

### 8. Layout editorial
O tom visual vem das marcas do projeto, e não da página HTML de origem (Georgia e dourado ficam de fora):
- capítulos com o sobretítulo em Caveat e o título em Unbounded;
- índice lateral fixo no desktop, que no celular vira uma pílula "Capítulos" abrindo um painel;
- casos do arquivo como cartões-ingresso com o picote e a data no canhoto;
- linha do tempo vertical com os anos em Unbounded vazado (como os blocos 01/02/03);
- galeria em polaroids com fita, com legenda de crédito embaixo do papel;
- rótulos de tipo de fonte como `Badge` com texto, sempre com cor e palavra.

O Atlas abre com o mapa em região escura. Os casos aparecem como uma grade de "carimbos de passaporte": selo redondo com o país e o grau, porque a ideia de visitar ovnipuertos pelo mundo pede carimbo, e não cartão SaaS.

## Risks / Trade-offs

- [Volume de conteúdo deixa as páginas pesadas] → texto em SSR, imagens lazy com placeholder, mapa e players só sob demanda. Meta: LCP < 2,5 s, medida no e2e com o Lighthouse local.
- [Licença CC BY-SA exige compartilhar adaptações pela mesma licença] → as imagens só são redimensionadas e convertidas, sem recorte criativo. A legenda declara a licença, e a seção de créditos indica que derivados seguem a licença original.
- [Links das fontes quebram com o tempo] → cada fonte guarda a data de acesso, como no dossiê. Uma verificação periódica de links fica fora do escopo e entra no checklist editorial.
- [Afirmações de relatos lidas como fato] → o tipo de fonte é sempre visível em texto, e os números da investigação independente vêm com a ressalva; isso está coberto nas specs.
- [Coordenadas colaborativas imprecisas] → o campo `approximate` aparece no popup como "posição aproximada"; casos sem coordenada ficam só na lista.
- [Links externos para `/lenda`] → o 301 permanente preserva o SEO; o sitemap deixa de listar `/lenda`.

## Migration Plan

1. Fazer o deploy com o redirecionamento ativo. Não há migração de banco, e as chaves dos blocos se mantêm.
2. Depois do deploy, gerar de novo o sitemap (`sitemap:generate`) e as imagens OG.
3. Rollback: voltar a versão anterior com `deploy.sh`. `/lenda` volta a responder sozinha, porque os blocos não mudaram.

## Open Questions

- A posição exata de alguns monumentos é provisória no dossiê. Corrigir é só editar os dados.
