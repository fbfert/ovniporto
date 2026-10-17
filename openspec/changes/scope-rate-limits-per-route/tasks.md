# Tasks

## 1. Limite por rota

- [x] 1.1 Middleware `ThrottlePerRoute` (estende `ThrottleRequests`) que prefixa a assinatura com o nome da rota, ou o caminho, registrado como alias `throttle` em `bootstrap/app.php`, verificado em `tests/Feature/EdgeProtectionTest.php` (cotações de frete não bloqueiam a devolução; a devolução ainda recebe 429 na sexta tentativa)
- [x] 1.2 Rodar a suíte completa (`php artisan test`), com os testes de 429 que já existem passando
