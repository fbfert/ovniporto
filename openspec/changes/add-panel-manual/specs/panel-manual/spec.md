# Spec Delta

## Purpose

Manual de operação do OVNIPORTO dentro do painel: ensina cada papel administrativo a operar as áreas que ele abre, com mapas mentais e texto, e garante que o manual acompanha cada mudança do painel.

## ADDED Requirements

### Requirement: Índice do manual
O painel SHALL oferecer `/painel/manual` com o índice dos capítulos: os capítulos gerais e um capítulo por área do painel. Cada capítulo listado SHALL mostrar título, resumo de uma linha e a data da última revisão.

#### Scenario: Admin abre o índice
- **WHEN** um `admin` acessa `/painel/manual`
- **THEN** vê os capítulos gerais e os capítulos de todas as áreas do painel, cada um com resumo e data de revisão

### Requirement: Capítulos filtrados pelo papel
O manual SHALL mostrar a cada pessoa apenas os capítulos gerais e os capítulos das áreas que o papel dela abre. O servidor MUST recusar com 404 o acesso direto a um capítulo de área fechada ao papel, sem revelar o conteúdo.

#### Scenario: Loja não vê o capítulo de relatos
- **WHEN** um `store` abre `/painel/manual`
- **THEN** a lista não inclui o capítulo de moderação de relatos nem os de conteúdo e auditoria

#### Scenario: Acesso direto a capítulo fechado
- **WHEN** um `moderator` acessa `/painel/manual/pedidos`
- **THEN** recebe 404

### Requirement: Mapa mental por capítulo
Cada capítulo SHALL abrir com um mapa mental: o tema no centro e ramos para telas, ações, estados e cuidados. Cada nó com texto associado MUST levar à seção correspondente do capítulo.

#### Scenario: Nó leva à explicação
- **WHEN** a pessoa ativa o nó "Aprovar" no mapa do capítulo de relatos
- **THEN** a página leva à seção que explica a aprovação e o foco vai para o título dela

### Requirement: Mapa mental acessível e legível no celular
O mapa mental MUST ser navegável por teclado (setas entre nós, Enter abre), ter nome acessível em cada nó, alvos de toque de pelo menos 44 px e uma versão em lista hierárquica equivalente. Em telas de 390 px o mapa SHALL caber sem rolagem horizontal da página.

#### Scenario: Navegação por teclado
- **WHEN** a pessoa foca o mapa e usa as setas
- **THEN** o foco visível passa de nó em nó e Enter abre a seção do nó focado

#### Scenario: Celular
- **WHEN** o capítulo é aberto com 390 px de largura
- **THEN** o mapa é exibido sem rolagem horizontal da página e todos os nós são alcançáveis

### Requirement: Conteúdo completo de cada capítulo
Cada capítulo de área MUST explicar, para cada tela da área: para que serve, quem pode usar, cada ação disponível com o efeito que ela causa (inclusive e-mails enviados e registros de auditoria), os estados possíveis e os cuidados de privacidade. O manual MUST incluir capítulos gerais de primeiros passos, papéis, rotina diária, privacidade e LGPD, problemas comuns e glossário.

#### Scenario: Ação explicada com efeito
- **WHEN** a pessoa lê a seção "Reembolsar" do capítulo de pedidos
- **THEN** encontra quem pode reembolsar, o que acontece com o pagamento, que e-mail o cliente recebe e que a ação fica na auditoria

### Requirement: Atalho de cada tela para o manual
Cada tela do painel coberta pelo manual SHALL ter um link "Como funciona" que abre o capítulo da sua área na seção daquela tela.

#### Scenario: Da fila de relatos para o manual
- **WHEN** um `moderator` ativa "Como funciona" na fila de relatos
- **THEN** abre o capítulo de relatos na seção da fila

### Requirement: Manual acompanha cada mudança do painel
Toda rota do painel MUST estar citada em algum capítulo do manual, e nenhum capítulo MAY citar uma rota que não existe. A verificação MUST rodar na suíte de testes, de modo que adicionar, renomear ou remover uma rota do painel sem atualizar o manual faça a suíte falhar.

#### Scenario: Rota nova sem manual
- **WHEN** uma rota `panel.*` nova é registrada e nenhum capítulo a cita
- **THEN** o teste de cobertura do manual falha nomeando a rota

#### Scenario: Rota removida ainda citada
- **WHEN** um capítulo cita uma rota que não existe mais
- **THEN** o teste de cobertura falha nomeando o capítulo e a rota

### Requirement: Conteúdo do manual validado
O conteúdo do manual MUST ser validado ao carregar: capítulo sem título, sem data de revisão, com nó do mapa apontando para seção inexistente ou com área desconhecida MUST gerar erro claro, nunca uma página quebrada em silêncio.

#### Scenario: Nó aponta para seção inexistente
- **WHEN** um nó do mapa referencia uma seção que o capítulo não tem
- **THEN** o carregamento falha com mensagem que nomeia o capítulo e o nó
