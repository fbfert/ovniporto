# Spec Delta

## Purpose

Contar de onde o OVNIPORTO vem: o hub `/origem`, a história documentada do Ovnipuerto de Cachi e o Atlas Mundial dos Ovnipuertos, com o rigor editorial do dossiê de pesquisa. Documento, relato e lenda ficam sempre separados.

## ADDED Requirements

### Requirement: Hub da origem
O sistema SHALL servir `/origem` para visitantes anônimos, renderizada no servidor, com:
- a capa "A origem";
- o bloco "De Cachi a Lages" com o texto aprovado pelos fundadores;
- o bloco "O relato do / O carro amarelo";
- portas para `/origem/cachi` e `/origem/atlas`;
- o convite para relatar um avistamento.

#### Scenario: Visitante abre a origem
- **WHEN** um visitante sem login acessa `/origem`
- **THEN** a resposta é 200 e o HTML, sem depender de JavaScript, contém o título, o texto "Os fundadores visitaram o Ovnipuerto de Cachi", a frase sobre o Lada Niva amarelo do Julean e os links para Cachi e para o Atlas

### Requirement: Rota antiga redireciona
A rota `/lenda` MUST responder com redirecionamento permanente (301) para `/origem`, e nenhuma página do site SHALL linkar para `/lenda`.

#### Scenario: Link antigo
- **WHEN** alguém acessa `/lenda`
- **THEN** recebe 301 com `Location` apontando para `/origem`

### Requirement: Relato do carro amarelo com estado honesto
O bloco do carro amarelo SHALL ter o sobretítulo "O relato do" e o título "O carro amarelo", e SHALL mostrar o texto editável do relato quando existir. Enquanto o texto estiver vazio, SHALL mostrar "Ainda estamos capturando o relato do Julean que foi abduzido. Deixe o e-mail e a torre de controle avisa quando sair.", a ilustração marcada como conceito e o botão "Me avise quando sair", ligado ao cadastro Avise-me. O sistema MUST NOT inventar o relato.

#### Scenario: Relato ainda não capturado
- **WHEN** o texto do relato do carro está vazio
- **THEN** o bloco mostra "O relato do" / "O carro amarelo", a frase "Ainda estamos capturando o relato do Julean que foi abduzido.", a ilustração com o selo "conceito" e o botão de Avise-me

#### Scenario: Relato publicado
- **WHEN** o texto do relato do carro tem conteúdo em markdown
- **THEN** o bloco renderiza o markdown com tipografia de leitura e não mostra o estado de espera

### Requirement: Vocabulário sem "lenda"
Os textos próprios do OVNIPORTO (interface, SEO, e-mails, painel e textos editáveis iniciais) MUST NOT usar a palavra "lenda". O nome da página é "origem", e a história do carro amarelo é "o relato". Os textos históricos do dossiê de Cachi e do Atlas SHALL ser mantidos como foram documentados, porque descrevem a lenda local de Cachi e de outros lugares.

#### Scenario: Busca pela palavra
- **WHEN** se procura "lenda" nos textos de interface, SEO, e-mails e seeders do OVNIPORTO
- **THEN** não há ocorrência fora dos arquivos de dados do dossiê de Cachi e do Atlas

### Requirement: Página documental de Cachi
O sistema SHALL servir `/origem/cachi`, renderizada no servidor, com os capítulos do dossiê de Cachi em ordem: prólogo, a noite de 24/11/2008, a Estrella de la Esperanza, a casa-cueva, o arquivo de relatos, o desaparecimento, o retorno, a lenda que vira lugar, a cidade que chega, os personagens, a linha do tempo, a galeria, os vídeos e as fontes. Um índice fixo SHALL levar a cada capítulo.

#### Scenario: Capítulos e índice
- **WHEN** um visitante abre `/origem/cachi`
- **THEN** o HTML contém os capítulos na ordem definida, cada um com âncora, e o índice com um link para cada âncora acessível por teclado

### Requirement: Tipo de fonte visível em Cachi
Cada afirmação marcada no dossiê de Cachi SHALL exibir o tipo de fonte em texto, nunca só por cor: documento oficial, depoimento direto, relato de fenômeno, arquivo histórico ou hipótese ordinária. Os números da investigação ufológica independente MUST aparecer identificados como tal, e não como levantamento científico ou oficial.

#### Scenario: Relato do arquivo
- **WHEN** o visitante vê um caso do arquivo de relatos
- **THEN** o caso mostra data, título, resumo e o rótulo do tipo de fonte por extenso

### Requirement: Atlas com mapa-múndi
O sistema SHALL servir `/origem/atlas`, renderizada no servidor, com:
- a introdução e o método;
- a escala de confiança de A a F;
- a lista dos 12 casos, com país, categoria e grau;
- um mapa com um ponto por caso que tenha coordenada;
- a cronologia comparada e as questões transversais;
- os casos candidatos e o catálogo completo de fontes;
- os créditos das imagens.

#### Scenario: Lista sem JavaScript
- **WHEN** um visitante abre `/origem/atlas` com JavaScript desativado
- **THEN** os 12 casos aparecem como lista com links para as páginas de cada caso, e o mapa é um complemento, não a única forma de navegar

#### Scenario: Ponto no mapa
- **WHEN** o visitante toca num ponto do mapa
- **THEN** abre um resumo com o nome, o país e o link para a página do caso

#### Scenario: Coordenada provisória
- **WHEN** um caso tem coordenada marcada como provisória ou não tem coordenada
- **THEN** o ponto traz a ressalva "posição aproximada", ou o caso fica fora do mapa e aparece só na lista

### Requirement: Página de cada caso do Atlas
O sistema SHALL servir `/origem/atlas/{caso}` para cada um dos 12 casos, com:
- nome, país e categoria;
- a imagem licenciada ou o mapa de localização;
- a tabela de dados documentais com o grau de confiança;
- as fontes do caso, com tipo, data de acesso e link;
- as questões em aberto;
- links para o caso anterior e o seguinte.

Um caso inexistente MUST responder 404.

#### Scenario: Caso existente
- **WHEN** um visitante abre `/origem/atlas/st-paul`
- **THEN** a resposta é 200 com o nome "St. Paul UFO Landing Pad", o país, o grau "A/B", as fontes do caso e a navegação para o próximo caso

#### Scenario: Caso inexistente
- **WHEN** alguém abre `/origem/atlas/nao-existe`
- **THEN** a resposta é 404 com a página de erro do site

### Requirement: OVNIPORTO Lages sem exagero
No Atlas, o caso "OVNIPORTO Lages" MUST aparecer com o grau de confiança F e o estado real: projeto com meta 2028, ainda não construído, aprovado nem financiado. A página SHALL linkar para `/o-lugar` e MUST NOT descrever o lugar como existente.

#### Scenario: Caso de Lages
- **WHEN** o visitante abre o caso de Lages
- **THEN** vê o grau F, a etiqueta "em planejamento" e o link para o projeto, sem afirmações de que a pista existe

### Requirement: Mapas de localização identificados
Quando a imagem de um caso for um mapa de localização, e não uma foto da estrutura, a legenda MUST dizer "mapa de localização", e o texto alternativo MUST descrever um mapa.

#### Scenario: Caso ilustrado por mapa
- **WHEN** o caso Green River, Carbondale ou Lages é exibido
- **THEN** a imagem é legendada como mapa de localização, não como foto do lugar

### Requirement: Créditos de toda imagem de terceiros
Toda imagem de terceiros nas páginas de origem SHALL exibir, junto dela, o autor, a licença com link para o texto da licença e o link da página de origem. O Atlas SHALL reunir todos os créditos numa seção própria. Fotografias sem licença aberta MUST NOT ser publicadas e ficam apenas citadas como fonte.

#### Scenario: Legenda de foto licenciada
- **WHEN** uma foto do Wikimedia Commons aparece numa página de origem
- **THEN** a legenda mostra o autor, a licença (ex.: "CC BY 2.0") e o link "origem e licença"

### Requirement: Ilustrações conceito marcadas
As cenas ilustradas que ainda não existem SHALL aparecer como placeholders bonitos com a etiqueta "conceito em produção". Quando a imagem for adicionada, SHALL levar o selo "conceito". O sistema MUST NOT apresentar uma ilustração como foto real.

#### Scenario: Ilustração ainda não gerada
- **WHEN** a ilustração de uma seção ainda não existe nos assets
- **THEN** a seção mostra o placeholder com "conceito em produção" e a página não aparece quebrada

### Requirement: Vídeos só depois do clique
Os vídeos externos SHALL aparecer como cartão com capa local, título, autor e aviso de que o conteúdo vem do YouTube. Nenhuma requisição ao YouTube nem a terceiros MUST acontecer antes do clique. Depois do clique, o player SHALL carregar de `www.youtube-nocookie.com` no lugar do cartão, e o link "abrir no YouTube" SHALL continuar disponível.

#### Scenario: Antes do clique
- **WHEN** a página de Cachi carrega
- **THEN** nenhuma requisição é feita a domínios do YouTube ou do Google

#### Scenario: Depois do clique
- **WHEN** o visitante aciona "Assistir" num vídeo
- **THEN** o player do youtube-nocookie carrega no lugar do cartão, e o foco vai para o player

### Requirement: SEO e compartilhamento da origem
`/origem`, `/origem/cachi`, `/origem/atlas` e cada caso SHALL ter título, descrição, URL canônica e imagem de compartilhamento próprios, e SHALL constar no sitemap.

#### Scenario: Metadados de um caso
- **WHEN** o HTML de `/origem/atlas/cachi` é gerado
- **THEN** contém título e descrição do caso, `og:image` e `link rel="canonical"` para a própria URL
