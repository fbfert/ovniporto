# Tasks

## 1. Catálogo

- [x] 1.1 Criar migrations `products`, `product_variants`, `product_images` e o Value Object `Money`, verificado por `php artisan migrate:fresh` e teste de unidade de `Money`
- [x] 1.2 Criar seeds (adesivo ativo com 500 unidades; demais inativos), verificado por teste Pest que confirma só o adesivo ativo
- [x] 1.3 Implementar `/loja` com cartões, faixa e bloco da pista condicionado, verificado por testes de feature (inativo ausente; bloco oculto com percentual nulo)
- [x] 1.4 Implementar `/loja/{slug}` com variantes, prazo, detalhes, "Combina com" e dados estruturados, verificado por testes de feature (inativo 404; JSON-LD Product presente)
- [x] 1.5 Implementar endpoint de simulação de frete via `ShippingProvider` fake, verificado por teste de feature (CEP inválido não chama o provedor)

## 2. Carrinho

- [x] 2.1 Implementar agregado de carrinho com dono sessão/membro e fusão no login, verificado por testes de unidade (preço do servidor; fusão; limite de estoque)
- [x] 2.2 Implementar endpoints de carrinho e drawer com contador no header, verificado por testes de feature de adicionar, alterar e remover
