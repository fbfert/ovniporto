# Tasks

## 1. Frete incluso: dados e regra

- [x] 1.1 Criar migração com `products.shipping_included`, `order_items.shipping_included` (padrão `false`) e `orders.shipping_cost_cents` preenchido com `shipping_cents`; verificar com `php artisan migrate:fresh --seed` e um teste que confere o preenchimento dos pedidos existentes
- [x] 1.2 Criar o objeto de domínio `ChargedShipping` (proporção do peso, arredondamento para cima, peso total zero) e testes unitários Pest para carrinho só pagante, só incluso, misto (400 g/100 g → R$ 5,00 de R$ 20,00) e peso zero
- [x] 1.3 Levar `shippingIncluded` do produto para o catálogo e o carrinho (`ProductReadRepository`, `ManageCart::view` com peso pagante) e verificar com o teste unitário de `ManageCart`

## 2. Frete incluso: loja, checkout e painel

- [x] 2.1 `QuoteShipping::forVariant` devolve `included`; página do produto e cartão mostram o selo "Frete incluso" e o simulador mostra "Incluso" no preço; verificar com teste de feature de `/loja/frete` e de `/loja/{slug}`
- [x] 2.2 `CheckoutController::delivery` devolve o preço cobrado por opção e o aviso de frete incluso; `PlaceOrder` cobra o frete via `ChargedShipping` e grava `shipping_cost_cents` e `shipping_included` dos itens; verificar com testes em `CheckoutTest` (misto, só incluso, retirada) e o cenário de total do spec `checkout`
- [x] 2.3 Campo "Frete incluso" no cadastro de produto do painel, com auditoria, e ficha do pedido com frete cobrado, frete absorvido e itens identificados; verificar com testes de feature do painel de produtos e da ficha do pedido
- [x] 2.4 Textos em `resources/js/i18n/pt-BR.ts` (selo, simulador, checkout, painel) e e-mails/página do pedido mostrando "Frete incluso" quando o frete cobrado for zero com entrega; verificar com `npm run typecheck` e o teste do e-mail de pedido
- [x] 2.5 Atualizar o manual do painel (resources/content/manual/produtos.json e pedidos.json: texto, mapa, rotas e reviewedAt) explicando o frete incluso, a regra do carrinho misto e que o produto precisa de peso; verificar com `php artisan test --filter=PanelManual`

## 3. Devoluções: domínio e gravação

- [x] 3.1 Criar migrações de `return_requests` (FK `order_id` em cascata, protocolo único) e `return_request_events`; verificar com `php artisan migrate:fresh`
- [x] 3.2 Criar `ReturnReason`, `ReturnRequestStatus` (transições do spec) e `WithdrawalWindow` em `app/Domain/Orders`, com testes unitários de transições válidas/inválidas e do prazo (5 dias, 20 dias, não entregue, exatamente 7 dias)
- [x] 3.3 Criar contratos `ReturnRequestRepository` e `ReturnNotifier` e a implementação Eloquent (protocolo `DEV-AAAA-NNNNNN`, data de entrega lida do evento `delivered`); verificar com teste de integração do repositório
- [x] 3.4 Criar o UseCase `RequestReturn` (conferência número+e-mail, erro genérico, pedido não pago recusado, prazo gravado, notificação) com testes unitários Pest de cada cenário do spec

## 4. Devoluções: formulário público e e-mails

- [x] 4.1 Adicionar `OperationalSetting::ReturnsEmail` (bloco Alertas, `ovniporto.returns_email`, padrão `RETURNS_EMAIL=contato@tars.art.br` em `config/ovniporto.php` e `.env.example`) e o campo em /painel/coordenadas; verificar com `CoordinatesTest`
- [x] 4.2 Criar `MailReturnNotifier` com `ReturnReceivedMail` (comprador: protocolo, motivo, prazo de resposta, fecho "Guardei um lugar pra você.") e `ReturnRequestedMail` (equipe: só protocolo e link), ambos em fila; verificar com `Mail::fake()` que o aviso da equipe não contém nome, e-mail nem mensagem
- [x] 4.3 Criar `RequestReturnRequest` (validação em português e honeypot `website`), `ReturnController` e as rotas `GET/POST /devolucao` com `throttle:5,1`; verificar com teste de feature: sucesso, erro genérico, honeypot, 429 e número preenchido por `?pedido=`
- [x] 4.4 Criar a página `resources/js/Pages/Store/Return.tsx` com os textos em `pt-BR.ts`, seguindo a identidade visual (mobile first, foco verde, alvos de 44 px), e os links no rodapé, na página do pedido e no e-mail de pedido entregue; verificar com `npm run typecheck`, teste de feature da página do pedido e revisão visual a 390 px

## 5. Devoluções: painel

- [x] 5.1 Criar o UseCase `OperateReturns` (listar com busca/filtro, ver ficha, mudar status com nota obrigatória e auditoria) com testes unitários Pest
- [x] 5.2 Criar as rotas `/painel/pedidos/devolucoes`, ficha e `POST .../status` dentro de `panel:pedidos`, controller e páginas `Panel/Returns/Index.tsx` e `Show.tsx`; verificar com teste de feature: `store` acessa, `moderator` recebe 403, transição inválida recusada, auditoria gravada
- [x] 5.3 Incluir devoluções recebidas em "Precisa de você" (`GetPanelHome`) para quem vê Pedidos; verificar com o teste do início do painel
- [x] 5.4 Atualizar o manual do painel (resources/content/manual/pedidos.json e coordenadas.json: texto, mapa, rotas e reviewedAt) com a lista de devoluções, os status, o frete de volta pago pelo OVNIPORTO e o e-mail de devoluções; verificar com `php artisan test --filter=PanelManual`

## 6. Devoluções: privacidade

- [x] 6.1 `EloquentOrderRepository::anonymizeFor` anula nome, e-mail e mensagem das devoluções dos pedidos anonimizados, e o expurgo apaga as devoluções em cascata; verificar com testes em `DataExportTest`/exclusão de conta e de `privacy:purge-orders`
- [x] 6.2 `BuildMemberExport` inclui a seção de pedidos de devolução; verificar com `DataExportTest` (seis seções, só dados do membro)

## 7. Verificação final

- [x] 7.1 Rodar `php artisan test`, `vendor/bin/pint --test`, `npm run typecheck` e `openspec validate add-returns-and-included-shipping --strict` sem falhas
- [ ] 7.2 Percorrer no navegador uma compra mista (frete cobrado proporcional), um pedido de devolução a partir da página do pedido e o atendimento no painel até "concluído"

## Workflow follow-up

- Validar com o usuário a regra do carrinho misto (proporção do peso) antes de aplicar o grupo 2.
- Arquivar a change depois do deploy e da aprovação dos termos pelo encarregado.
