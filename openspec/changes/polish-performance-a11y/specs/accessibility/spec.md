# Spec Delta

## Purpose

Garante que o OVNIPORTO possa ser usado por teclado, leitor de tela e por pessoas sensíveis a movimento, em conformidade mínima com WCAG 2.1 AA.

## ADDED Requirements

### Requirement: Navegação completa por teclado
Menu, modais, assistente de relato, carrinho e lightbox SHALL ser totalmente operáveis por teclado, com ordem de foco lógica, foco preso dentro de modais abertos e devolvido ao elemento de origem ao fechar.

#### Scenario: Fechar modal
- **WHEN** o usuário fecha um modal com Esc
- **THEN** o modal fecha e o foco volta ao botão que o abriu

### Requirement: Foco visível
Todo elemento interativo MUST ter indicador de foco visível na cor beam.

#### Scenario: Tabulação
- **WHEN** o usuário navega com Tab por qualquer página
- **THEN** cada elemento focado exibe o contorno de foco

### Requirement: Contraste dentro dos tokens
Todas as combinações de texto e fundo SHALL atingir contraste AA, ajustando apenas opacidades dos tokens existentes, sem novas cores.

#### Scenario: Verificação de contraste
- **WHEN** a verificação automática de contraste roda sobre os pares de tokens usados
- **THEN** nenhum par usado em texto fica abaixo de AA

### Requirement: Texto alternativo obrigatório
Toda imagem de conteúdo MUST ter texto alternativo, e o painel MUST recusar imagens de conteúdo sem ele.

#### Scenario: Auditoria automática
- **WHEN** o axe-core analisa as páginas públicas
- **THEN** não há imagens de conteúdo sem texto alternativo

### Requirement: Alternativas a mapa e movimento
Todo mapa SHALL ter alternativa em lista com o mesmo conteúdo; a faixa corrida MUST poder ser pausada; nenhum elemento MUST piscar mais de 3 vezes por segundo; animações SHALL respeitar `prefers-reduced-motion`.

#### Scenario: Movimento reduzido
- **WHEN** o sistema do usuário pede movimento reduzido
- **THEN** a faixa corrida e o parallax ficam parados e as transições viram cortes simples

### Requirement: Fluxos testados com leitor de tela
Os fluxos de relato e de compra SHALL ser percorridos com leitor de tela (NVDA ou VoiceOver) antes do lançamento, e os problemas encontrados MUST ser corrigidos.

#### Scenario: Relato por leitor de tela
- **WHEN** uma pessoa usando leitor de tela percorre os 4 passos do relato
- **THEN** cada campo, erro e mudança de passo é anunciado
