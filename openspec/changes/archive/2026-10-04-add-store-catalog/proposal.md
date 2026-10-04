# Proposal

## Why

A loja é a fonte de renda possível hoje e a lembrança física da comunidade. Ela deve abrir só com o que existe (o adesivo) e deixar claro o que é pronta entrega e o que é feito sob pedido.

## What Changes

- `/loja`: grade de cartões-ingresso com preço, etiqueta e prazo; faixa corrida; bloco "Parte de cada venda vai virar pista" oculto enquanto o percentual da loja for nulo (Prompt 12).
- `/loja/{slug}`: galeria, variantes (sem estoque desabilitadas), quantidade, prazo, simulador de frete por CEP, detalhes em markdown, "Combina com" e dados estruturados de produto.
- Carrinho no servidor (sessão para visitante, membro quando logado) com drawer lateral e contador no header.
- Seeds: adesivo ativo (500 unidades, R$ 8); camiseta, caneca e Kit Abdução inativos.

## Capabilities

### New Capabilities
- `store-catalog`: vitrine, página de produto, variantes, estoque visível e simulação de frete.
- `shopping-cart`: carrinho persistido no servidor para visitantes e membros.

### Modified Capabilities
<!-- Nenhuma. Usa `design-system` e `public-layout` (change `build-public-home`) e a configuração de `campaign` (change `add-place-and-campaign`). -->

## Impact

- Módulo Catalog; rotas `/loja`, `/loja/{slug}`, endpoints de carrinho e de cotação de frete.
- Cotação usa a porta de frete definida em `add-checkout-payments`; até lá, um fake.
