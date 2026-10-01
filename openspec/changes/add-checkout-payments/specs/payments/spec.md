# Spec Delta

## Purpose

Recebimento de pagamentos dos pedidos por provedor externo (cartão e Pix), com confirmação verificada e sem que dados de cartão passem pelo servidor do OVNIPORTO.

## ADDED Requirements

### Requirement: Dados de cartão fora do servidor
Os dados de cartão MUST ser digitados apenas nos componentes do provedor de pagamento no navegador; o servidor MUST NOT receber, registrar ou armazenar número, validade ou código do cartão.

#### Scenario: Requisição ao servidor durante o pagamento
- **WHEN** o cliente paga com cartão
- **THEN** nenhuma requisição ao servidor do OVNIPORTO contém dados do cartão

### Requirement: Ordem criada pelo servidor
A ordem no provedor de pagamento SHALL ser criada pelo servidor com o total calculado do pedido; valores vindos do navegador MUST ser ignorados.

#### Scenario: Total adulterado
- **WHEN** o navegador tenta iniciar o pagamento com um valor diferente
- **THEN** a ordem é criada com o total do servidor

### Requirement: Captura idempotente
A captura do pagamento SHALL ser feita pelo servidor após a aprovação e MUST ser idempotente pelo id da ordem no provedor: repetições não geram nova captura nem novos efeitos.

#### Scenario: Captura repetida
- **WHEN** a mesma ordem aprovada é capturada duas vezes
- **THEN** o pedido é marcado como pago uma única vez e o estoque é decrementado uma única vez

### Requirement: Webhook verificado
Notificações de captura concluída SHALL confirmar o pagamento somente após verificação da assinatura junto ao provedor; notificações com assinatura inválida MUST ser rejeitadas sem alterar o pedido.

#### Scenario: Webhook falso
- **WHEN** chega uma notificação de captura com assinatura inválida
- **THEN** a resposta é de erro e o pedido continua `pending_payment`

### Requirement: Ambiente de testes por configuração
O modo sandbox ou produção do provedor SHALL ser escolhido por variável de ambiente, e credenciais MUST NOT estar no repositório.

#### Scenario: Ambiente local
- **WHEN** a aplicação roda com a variável de sandbox ligada
- **THEN** todas as chamadas vão ao ambiente sandbox do provedor
