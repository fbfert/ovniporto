# Spec Delta

## Purpose

Limita os envios a cada rota do site sem que o uso de uma área gaste o limite de outra.

## ADDED Requirements

### Requirement: Limite contado por rota
Cada rota com limite de envios SHALL contar as tentativas separadamente das outras rotas, por membro logado ou, sem login, por IP. Atingir o limite de uma rota MUST NOT bloquear outra rota.

#### Scenario: Loja não gasta o limite da devolução
- **WHEN** um visitante faz 10 pedidos de cotação de frete e depois envia o formulário de devolução
- **THEN** a devolução é aceita normalmente, sem 429

#### Scenario: Limite da própria rota continua valendo
- **WHEN** um visitante envia o formulário de devolução mais de 5 vezes no mesmo minuto
- **THEN** as tentativas seguintes recebem 429
