# dialogs Specification

## Purpose
Janelas modais que não prendem nem perdem quem navega por teclado ou leitor de tela.

## Requirements

### Requirement: Modal acessível
O modal SHALL ter `role="dialog"`, `aria-modal` e título associado; ao abrir, o foco SHALL ir para dentro e ficar preso nele; Esc, o botão fechar e o clique no fundo SHALL fechar; ao fechar, o foco SHALL voltar ao elemento que abriu. A página atrás MUST NOT rolar enquanto ele está aberto.

#### Scenario: Fechar pelo teclado
- **WHEN** o modal está aberto e a pessoa aperta Esc
- **THEN** o modal fecha e o foco volta ao botão que o abriu

#### Scenario: Tab no último elemento
- **WHEN** o foco está no último elemento do modal e a pessoa aperta Tab
- **THEN** o foco vai para o primeiro elemento do modal
