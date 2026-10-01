# Tasks

## 1. Metadados e dados estruturados

- [ ] 1.1 Estender o `SeoHead` com dados por página e imagem OG padrão, verificado por teste de feature que percorre as rotas públicas conferindo título, descrição, canonical e og:image
- [ ] 1.2 Adicionar JSON-LD (Organization, Product, Article, Place, FAQPage), verificado por testes de feature que decodificam o JSON-LD de cada página

## 2. Imagens OG e sitemap

- [ ] 2.1 Definir `OgImageRenderer` e implementar rotas `/og/*` com cache e invalidação, verificado por testes (conteúdo não público = 404; edição gera nova versão)
- [ ] 2.2 Implementar `sitemap.xml` e `robots.txt`, verificado por teste que confirma ausência de `/painel` e `/conta` e presença de conteúdos publicados
- [ ] 2.3 Implementar comando `og:check {url}`, verificado por teste do comando contra uma rota local

## 3. Postal e métrica

- [ ] 3.1 Implementar `/postal` com download e compartilhamento nativo com alternativas, verificado por teste de feature e teste de componente do fallback
- [ ] 3.2 Adicionar Umami + Postgres ao compose e wrapper tipado de eventos só em produção, verificado por teste de feature (script ausente fora de produção) e conferência dos 6 eventos no painel do Umami em staging
