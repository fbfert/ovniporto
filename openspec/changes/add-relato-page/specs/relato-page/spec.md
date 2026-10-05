# Spec Delta

## ADDED Requirements

### Requirement: Página do relato
O sistema SHALL servir `/origem/relato` com o texto do bloco `legend_body` inteiro, apresentado como relato contado pelo Julean e nunca como documento. Parágrafos de até 12 caracteres SHALL ser marcados como pausas e exibidos em destaque. Enquanto o bloco estiver vazio, a página MUST mostrar o aviso de espera e o formulário Avise-me.

#### Scenario: Relato escrito
- **WHEN** o bloco `legend_body` tem texto
- **THEN** `/origem/relato` mostra o texto inteiro, com "Nada." marcado como pausa

#### Scenario: Relato vazio
- **WHEN** o bloco `legend_body` está vazio
- **THEN** `/origem/relato` mostra o aviso de espera e o Avise-me

### Requirement: Texto do relato em produção
Uma migration SHALL gravar o relato enviado pelo Julean no bloco `legend_body` somente quando ele estiver vazio, preservando qualquer texto já escrito no painel.

#### Scenario: Texto do painel preservado
- **WHEN** a migration roda com o bloco já preenchido no painel
- **THEN** o texto do painel continua igual

### Requirement: Chamadas para o relato
A home SHALL ter uma seção própria do carro amarelo com os três primeiros parágrafos do relato e um botão para `/origem/relato`, separada do cartão "A origem", que SHALL mostrar a foto aérea do Ovnipuerto de Cachi com autor, licença e link de origem visíveis e a legenda "Ovnipuerto Cachi". `/origem` SHALL mostrar a mesma abertura e o mesmo link.

#### Scenario: Home com relato
- **WHEN** alguém abre a home com o relato escrito
- **THEN** vê o cartão de Cachi com crédito da foto e, em outra seção, a abertura do relato com o botão "Ler o relato inteiro"

### Requirement: Relato encontrável
`/origem/relato` SHALL ter título e descrição próprios, JSON-LD `ShortStory` com autor Julean, e SHALL constar no `sitemap.xml` e no `/llms.txt`.

#### Scenario: Robô sem JavaScript
- **WHEN** um robô busca `/origem/relato`
- **THEN** encontra o título "O relato do carro amarelo do Julean" e o JSON-LD `ShortStory`
