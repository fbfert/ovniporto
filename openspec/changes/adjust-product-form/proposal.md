# Proposal

## Why

Quem cadastra vários produtos seguidos precisava salvar, esperar a ficha abrir e voltar à lista a cada um. E as medidas da embalagem só aceitavam centímetros inteiros, o que não descreve um adesivo de 210,5 mm.

## What Changes

- Em `/painel/produtos/novo`, um segundo botão **Salvar e voltar** cria o produto e volta para a lista. **Salvar** continua abrindo a ficha do produto criado.
- Comprimento, largura e altura da embalagem aceitam até duas casas decimais em centímetros (0,01 cm = um décimo de milímetro), de 0,01 a 100 cm, com mensagens em português. A loja mostra as medidas com vírgula.
- O frete não muda: a cotação continua usando o peso do pedido e a embalagem padrão da configuração.

## Capabilities

### New Capabilities

### Modified Capabilities
- `store-operations`: o cadastro de produtos ganha o "Salvar e voltar" e medidas com décimos de milímetro.

## Impact

- `app/Http/Controllers/Panel/ProductAdminController.php` (redirecionamento e validação), `app/Domain/Catalog/Data/ProductDraft.php`, `app/Models/Product.php` (tipos), `resources/js/Pages/Panel/Products/Edit.tsx`, `resources/js/i18n/pt-BR.ts`.
- Sem migração: as medidas já ficam num campo JSON, e os inteiros antigos continuam válidos.
- Manual: capítulo `produtos` (seções "novo" e "medidas").
