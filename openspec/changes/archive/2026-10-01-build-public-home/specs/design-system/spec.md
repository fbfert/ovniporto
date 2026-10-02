# Spec Delta

## Purpose

Define a linguagem visual e de movimento do OVNIPORTO (tokens, componentes de assinatura e regras de acessibilidade) que todas as páginas públicas usam.

## ADDED Requirements

### Requirement: Paleta restrita aos tokens da marca
Toda cor exibida nas páginas públicas MUST vir dos sete tokens (moonlight, night, night-blue, horizon, beam, beam-glow, car) ou de variações de opacidade deles. Branco puro (#FFFFFF) MUST NOT ser usado como cor de fundo ou de texto.

#### Scenario: Página renderizada usa só tokens
- **WHEN** qualquer página pública é renderizada
- **THEN** as cores de fundo, texto, borda e brilho correspondem a um token ou a um token com opacidade

### Requirement: Tipografia em três papéis
Títulos display SHALL usar Unbounded em caixa alta, sobretítulos e legendas SHALL usar Caveat e o texto corrido SHALL usar Figtree, com as fontes carregadas com `font-display: swap`.

#### Scenario: Título de seção
- **WHEN** uma seção exibe um título principal
- **THEN** ele aparece em Unbounded, caixa alta, com tamanho fluido entre mobile e desktop

### Requirement: Foco visível e alvos de toque
Todo elemento interativo SHALL ter foco visível na cor beam ao ser navegado pelo teclado e área de toque de no mínimo 44×44 px.

#### Scenario: Navegação por teclado
- **WHEN** o usuário navega com Tab por botões e links
- **THEN** cada elemento focado mostra um contorno beam claramente visível

### Requirement: Movimento respeita reduced-motion
Com `prefers-reduced-motion: reduce`, a interface SHALL remover movimentos de posição, parallax, faixa corrida, céu animado e a abdução, mantendo o conteúdo completo e legível.

#### Scenario: Usuário pede menos movimento
- **WHEN** o sistema operacional do visitante declara reduced-motion
- **THEN** a faixa vira texto estático, as estrelas param, as revelações aparecem sem deslocamento e a capa mostra o quadro final sem animação

### Requirement: Feedback de pressão nos botões
Botões SHALL responder ao toque/clique com uma leve redução de escala (≈0,97) em até 160 ms, e efeitos de hover SHALL aparecer só em dispositivos com ponteiro fino.

#### Scenario: Toque em celular
- **WHEN** o usuário toca um botão no celular
- **THEN** o botão reage à pressão e nenhum estado de hover fica "preso" após o toque

### Requirement: Componentes de assinatura disponíveis
O design system SHALL oferecer polaroid com fita e rotação que endireita no hover, cartão-ingresso com picote, faixa corrida com bandeirinhas pausável no hover, selo com brilho pulsante, céu estrelado que pausa fora da viewport e seções clara/escura com transição ondulada.

#### Scenario: Styleguide local
- **WHEN** um desenvolvedor abre `/dev/styleguide` em ambiente local
- **THEN** todos os componentes aparecem nos tons claro e escuro com seus estados

#### Scenario: Styleguide em produção
- **WHEN** alguém acessa `/dev/styleguide` fora do ambiente local
- **THEN** o servidor responde 404

### Requirement: Céu estrelado econômico
O céu estrelado SHALL parar de desenhar quando estiver fora da viewport ou quando a aba estiver oculta.

#### Scenario: Seção escura sai da tela
- **WHEN** a seção com estrelas deixa a viewport
- **THEN** o desenho do canvas é suspenso até ela voltar
