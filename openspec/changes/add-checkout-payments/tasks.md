# Tasks

## 1. Pedidos

- [ ] 1.1 Criar migrations `orders`, `order_items` e `order_events` com CPF criptografado, verificado por `php artisan migrate:fresh` e teste que lê o valor bruto do banco
- [ ] 1.2 Implementar máquina de estados do pedido e gerador de número `OVP-AAAA-NNNNNN`, verificado por testes de unidade (transição inválida falha; formato do número)
- [ ] 1.3 Implementar cálculo de total em centavos com frete, verificado por teste de unidade (2 × 800 + 1250 = 2850)

## 2. Frete

- [ ] 2.1 Definir `ShippingProvider` e `AddressLookup` no Domain com implementações Melhor Envio e ViaCEP e fakes, verificado por testes de unidade com fakes e teste de contrato com respostas gravadas
- [ ] 2.2 Implementar "Retirar em Lages" e tratamento de indisponibilidade, verificado por teste de feature

## 3. Pagamento

- [ ] 3.1 Definir `PaymentGateway` no Domain com implementação PayPal (sandbox por env) e fake, verificado por teste de unidade com o fake
- [ ] 3.2 Implementar criação de ordem e captura idempotente, verificado por teste de captura duplicada (um único `paid`, estoque decrementado uma vez)
- [ ] 3.3 Implementar webhook com verificação de assinatura, verificado por teste de webhook inválido rejeitado

## 4. Checkout e acompanhamento

- [ ] 4.1 Implementar `/checkout` em 3 etapas com validação de CPF e telefone, verificado por testes de feature (CPF inválido falha; visitante e membro concluem)
- [ ] 4.2 Implementar efeitos do pagamento (estoque, carrinho vazio) e e-mails em fila, verificado por teste com `Mail::fake` e asserção de estoque
- [ ] 4.3 Implementar comando agendado de cancelamento após 2 h, verificado por teste com tempo simulado
- [ ] 4.4 Implementar `/pedido/{number}` com token assinado ou dono, verificado por testes de feature (sem token 403/404)
