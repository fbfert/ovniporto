# Proposal

## Why

A página `/origem/cachi` é o dossiê mais completo em português sobre o Ovnipuerto de Cachi, mas hoje ela não se apresenta como tal para buscadores e assistentes de IA: o H1 não diz "Ovnipuerto de Cachi", o título não cita Werner Jaisli, o JSON-LD é um Article mínimo sem fontes, e não existe um resumo direto que um buscador ou uma IA possa citar. Queremos que ela seja encontrada (e citada) quando alguém procurar por Ovnipuerto, Ovniporto, OVNIPORTO, Cachi, Estrella de la Esperanza ou Werner "Terry" Jaisli.

## What Changes

- Título e descrição da página com os termos de busca (Ovnipuerto de Cachi, Werner Jaisli, Estrella de la Esperanza, Salta, Argentina).
- H1 da capa passa a nomear o lugar ("Ovnipuerto de Cachi") antes do título editorial.
- Nova seção visível "Em poucas palavras": perguntas e respostas curtas, tiradas só do que o dossiê já documenta, com fonte entre parênteses quando há documento.
- JSON-LD em grafo: Article (datas, autor, about, mentions, citation com as fontes do dossiê, keywords), Place (Ovnipuerto de Cachi, nomes alternativos, endereço; sem coordenada enquanto ela for aproximada), Person (Werner Jaisli, "Terry"), BreadcrumbList e FAQPage espelhando a seção visível.
- `/llms.txt`: resumo em texto simples do site e do dossiê de Cachi (fatos, perguntas e fontes) para assistentes de IA, gerado a partir do mesmo JSON.
- `sitemap.xml` com `lastmod` do dossiê.

## Capabilities

### New Capabilities
- `origin-reference`: a página de Cachi como referência documental para buscadores e assistentes de IA.

### Modified Capabilities
<!-- Nenhuma: complementa `seo-sharing` sem alterar seus requisitos. -->

## Impact

- `resources/content/origin/cachi.json` (blocos `summary` e `reference`), `ContentSeo`, `StructuredData`, `CrawlerController` (rota `/llms.txt`), `BuildSitemap`, `Pages/Origin/Cachi.tsx`, `i18n/pt-BR.ts`, testes de feature.
