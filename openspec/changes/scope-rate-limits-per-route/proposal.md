# Proposal

## Why

As rotas do site limitam envios com `throttle:N,M`. Sem um nome de limitador, o Laravel conta todas essas rotas na mesma chave: o IP de quem não entrou, ou o id do membro logado. Uma pessoa que usa a loja (carrinho, frete, checkout) gasta o mesmo contador do Avise-me, da devolução e do relato. Ela pode receber 429 num formulário que nunca usou, e uma família atrás do mesmo IP divide tudo isso. Encontrado no teste no navegador das mudanças de privacidade e loja, antes do lançamento.

## What Changes

- Cada rota passa a ter o próprio contador, sem mudar nenhum dos limites atuais: a chave do limite inclui o nome da rota (ou o caminho, quando a rota não tem nome).
- A troca é feita uma vez, no alias `throttle` do middleware. As 27 rotas continuam declarando `throttle:N,M`, e as rotas novas já nascem certas.
- Limitadores nomeados (`RateLimiter::for`, como o do geocodificador) não mudam.

## Capabilities

### New Capabilities
- `request-limits`: limites de envio por rota, contados por pessoa ou IP, sem que uma área do site gaste o limite de outra.

## Impact

- `app/Http/Middleware/ThrottlePerRoute.php` (novo) e `bootstrap/app.php` (alias `throttle`).
- Teste de feature novo em `tests/Feature/EdgeProtectionTest.php`. Os testes de 429 que já existem continuam valendo.
- Nada muda no painel nem nos textos.
