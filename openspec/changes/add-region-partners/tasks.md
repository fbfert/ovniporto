# Tasks

## 1. Domain e dados

- [x] 1.1 Criar migration e modelo `region_partners` com `consent_given_at` e `published_at`, verificado por `php artisan migrate:fresh`
- [x] 1.2 Implementar regra de publicação com consentimento na entidade e escopo "publicados" no repositório, verificado por testes de unidade (sem consentimento não publica; publicar sem consentimento lança erro)
- [x] 1.3 Implementar serviço de distância Haversine com arredondamento em km, verificado por teste de unidade com coordenadas conhecidas

## 2. Páginas

- [x] 2.1 Implementar `/regiao` com filtro por tipo, busca e estado na URL via partial reload, verificado por teste de feature (filtro retorna só o tipo pedido)
- [x] 2.2 Implementar alternância Lista | Mapa com o marcador do OVNIPORTO destacado, verificado por teste de componente que renderiza os marcadores esperados
- [x] 2.3 Implementar `/regiao/{slug}` com contatos condicionais e "Como chegar", verificado por teste de feature (200 para publicado; 404 para sem consentimento)
- [x] 2.4 Implementar estado vazio com "Quero aparecer aqui", verificado por teste de feature sem parceiros
