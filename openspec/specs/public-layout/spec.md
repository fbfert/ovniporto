# public-layout Specification

## Purpose
Estrutura comum de todas as páginas públicas: navegação, rodapé, metadados de compartilhamento e páginas de erro ou ainda não construídas.

## Requirements

### Requirement: Menu em pílula flutuante
O cabeçalho SHALL ser uma pílula flutuante fixa no topo com os links "Conheça a região", "O lugar", "Livro de avistamentos", "Loja", o selo no centro levando à home e o botão "Entrar na comunidade".

#### Scenario: Desktop
- **WHEN** a página é aberta com largura de 1024 px ou mais
- **THEN** a pílula mostra os quatro links, o selo e o botão principal

### Requirement: Menu se adapta ao fundo
O cabeçalho SHALL inverter para o tom escuro quando estiver sobre uma seção escura e voltar ao claro sobre seções claras.

#### Scenario: Rolagem sobre região escura
- **WHEN** o topo da viewport passa sobre uma seção escura
- **THEN** a pílula muda para fundo night com texto moonlight

### Requirement: Menu some ao descer e volta ao subir
O cabeçalho SHALL se esconder ao rolar para baixo e reaparecer ao rolar para cima.

#### Scenario: Leitura da página
- **WHEN** o usuário rola para baixo mais de 120 px
- **THEN** o menu se esconde, e reaparece assim que o usuário rola para cima

### Requirement: Menu mobile em tela cheia
Abaixo de 1024 px, o cabeçalho SHALL mostrar o selo e um botão de menu que abre um painel em tela cheia, fundo night com estrelas e links grandes; o painel MUST prender o foco e fechar com Esc.

#### Scenario: Abrir e fechar no celular
- **WHEN** o usuário toca no botão de menu e depois pressiona Esc ou toca em fechar
- **THEN** o painel abre a partir do botão e, ao fechar, o foco volta ao botão de menu

### Requirement: Rodapé noturno
Todas as páginas públicas SHALL terminar com o rodapé escuro contendo "Guardei um lugar pra você.", as colunas OVNIPORTO / Navegue / Comunidade, a localização "Ao lado da Hospedaria Vila das Pedras · Lages, SC", links de Privacidade e Termos e o crédito "Feito na serra por Xiax".

#### Scenario: Rodapé presente
- **WHEN** qualquer página pública é renderizada
- **THEN** o rodapé aparece com o fecho "Guardei um lugar pra você."

### Requirement: Link para pular ao conteúdo
Toda página SHALL ter um link "Ir para o conteúdo" que fica visível ao receber foco e leva ao conteúdo principal.

#### Scenario: Primeiro Tab
- **WHEN** o usuário pressiona Tab ao abrir a página
- **THEN** o link "Ir para o conteúdo" aparece e, ativado, move o foco para o conteúdo principal

### Requirement: Metadados de compartilhamento
Toda página SHALL emitir título no padrão "{Página} · OVNIPORTO Lages", descrição, Open Graph e Twitter Card com imagem padrão, renderizados no servidor.

#### Scenario: Prévia no WhatsApp
- **WHEN** um robô de prévia busca a home sem executar JavaScript
- **THEN** o HTML já contém título, descrição e imagem Open Graph

### Requirement: Páginas de erro com identidade
Rotas inexistentes SHALL mostrar a página 404 "Esse ponto do céu ainda não foi mapeado." com o carro amarelo e um botão para a home; erros de servidor SHALL mostrar uma página 500 no mesmo espírito.

#### Scenario: Rota inexistente
- **WHEN** alguém acessa um endereço que não existe
- **THEN** o servidor responde 404 com a página temática e o botão para voltar à home

### Requirement: Páginas futuras são honestas
Links do menu para áreas ainda não construídas SHALL abrir uma página "em construção" que diz o que a área vai ter e em que fase entra, sem fingir que o recurso já funciona.

#### Scenario: Loja ainda não construída
- **WHEN** o visitante abre `/loja` antes da change da loja
- **THEN** a página responde 200, explica que a loja está a caminho e oferece o cadastro Avise-me
