# Spec Delta

## Purpose

A home do OVNIPORTO: apresenta a marca, o Livro de avistamentos, o projeto da pista, a loja, a lenda, a região e a comunidade, sempre com estados honestos para o que ainda não existe.

## ADDED Requirements

### Requirement: Dez seções em ordem
A home SHALL renderizar, nesta ordem: capa, boas-vindas, Livro de avistamentos, faixa corrida, O lugar, lembranças, a lenda, conheça a região, comunidade e postal, rodapé.

#### Scenario: Ordem das seções
- **WHEN** a home é renderizada no servidor
- **THEN** o HTML contém as seções na ordem definida e o texto "OVNIPORTO"

### Requirement: Capa com abdução guiada pelo scroll
A capa SHALL mostrar o céu noturno, o selo com brilho, a pílula "Vigília grátis", a silhueta da serra e o carro amarelo; ao rolar a capa, o feixe verde SHALL abrir e levantar o carro até ele sumir no céu, de forma reversível ao rolar de volta.

#### Scenario: Visitante rola a capa
- **WHEN** o visitante rola a página a partir do topo
- **THEN** o feixe se acende, o carro sobe dentro do feixe e desaparece antes da seção seguinte

#### Scenario: Visitante volta ao topo
- **WHEN** o visitante rola de volta ao topo
- **THEN** o carro desce e o feixe se recolhe ao estado inicial

#### Scenario: Reduced motion
- **WHEN** o visitante tem reduced-motion ativo
- **THEN** a capa aparece estática, sem feixe animado, e não prende a rolagem

### Requirement: Boas-vindas com dados reais
A seção de boas-vindas SHALL mostrar o texto editável `home_intro` e quatro cartões: ONDE, CIDADE, RELATOS (contagem real de relatos aprovados, animada de 0 ao valor ao entrar na tela) e PISTA "Meta 2028" com a etiqueta "em planejamento".

#### Scenario: Contador de relatos
- **WHEN** o cartão RELATOS entra na viewport
- **THEN** o número conta de 0 até o total de relatos aprovados, e o HTML do servidor já contém o valor final

### Requirement: Livro de avistamentos com estado vazio
A seção do Livro SHALL mostrar os 4 relatos aprovados mais recentes como polaroids; sem relatos, SHALL mostrar 4 polaroids "Seu relato aqui" na mesma composição. Relatos não aprovados MUST NOT aparecer.

#### Scenario: Sem relatos aprovados
- **WHEN** não há nenhum relato aprovado
- **THEN** a seção mostra quatro polaroids "Seu relato aqui" e os botões "Relatar avistamento" e "Ver o mapa"

#### Scenario: Relato pendente
- **WHEN** existe um relato com status pendente
- **THEN** ele não aparece na home nem conta no contador

### Requirement: O lugar descrito como planejado
A seção O lugar SHALL apresentar o projeto como "em planejamento", com os espaços numerados por fase vindos do cadastro, e imagens conceituais MUST levar a etiqueta "conceito". O site MUST NOT descrever a pista como já existente.

#### Scenario: Espaços do plano
- **WHEN** a seção é renderizada
- **THEN** os nove espaços aparecem numerados com a fase e o status "em planejamento"

### Requirement: Lembranças com estado vazio
A seção de lembranças SHALL mostrar até 3 produtos ativos em destaque como cartões-ingresso com preço; sem produtos, SHALL mostrar um estado vazio que leva ao cadastro Avise-me.

#### Scenario: Loja sem produtos ativos
- **WHEN** não há produto ativo em destaque
- **THEN** a seção mostra o estado vazio em vez de cartões quebrados

### Requirement: Lenda aguardando conteúdo
Enquanto o texto da lenda estiver vazio, a seção da lenda SHALL exibir a etiqueta "aguardando conteúdo" e o carro amarelo como placeholder, sem inventar a história.

#### Scenario: Lenda ainda não escrita
- **WHEN** `legend_body` está vazio
- **THEN** a seção mostra "aguardando conteúdo"

### Requirement: Região com estado vazio
A seção Conheça a região SHALL mostrar parceiros publicados e com consentimento; sem parceiros, SHALL mostrar "Pousadas, trilhas e produtores da serra em breve." com o botão "Quero aparecer aqui".

#### Scenario: Nenhum parceiro publicado
- **WHEN** não há parceiro publicado
- **THEN** a seção mostra o estado vazio com o link de contato

### Requirement: Mande um postal
A seção de comunidade SHALL oferecer um cartão-postal com o selo, o carimbo "LAGES · SC" e botões para compartilhar no WhatsApp com texto pronto e para copiar o link do site.

#### Scenario: Copiar link
- **WHEN** o visitante toca em "Copiar link"
- **THEN** o endereço do site vai para a área de transferência e aparece a confirmação "Link copiado"
