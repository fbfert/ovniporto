# Spec Delta

## Purpose

Campos de formulário consistentes com a marca, usados no relato, no login, na loja e no painel, acessíveis por teclado e leitor de tela.

## ADDED Requirements

### Requirement: Campos rotulados e com erro associado
Todo campo SHALL ter rótulo associado (visível ou só para leitor de tela) e, quando houver erro, SHALL marcar `aria-invalid` e ligar a mensagem ao campo por `aria-describedby`. As mensagens MUST estar em português.

#### Scenario: Campo com erro
- **WHEN** a validação devolve erro para um campo
- **THEN** o campo fica marcado como inválido e o leitor de tela lê a mensagem junto com o campo

### Requirement: Contador de caracteres
Campos de texto com limite SHALL poder mostrar um contador "n/limite" que o leitor de tela anuncia de forma educada só quando o limite está perto.

#### Scenario: Descrição perto do limite
- **WHEN** a pessoa digita e passa de 90% do limite
- **THEN** o contador muda de cor e é anunciado

### Requirement: Seleção em pílulas
O grupo de pílulas SHALL funcionar em modo único (comportamento de rádio, setas navegam) ou múltiplo (comportamento de checkbox), com alvo de toque de pelo menos 44 px e foco visível em verde.

#### Scenario: Escolha do tipo de avistamento
- **WHEN** a pessoa usa as setas num grupo de seleção única
- **THEN** a seleção e o foco passam para a pílula vizinha
