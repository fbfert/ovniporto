# Spec Delta

## ADDED Requirements

### Requirement: Página de Cachi nomeia o assunto
A página `/origem/cachi` SHALL ter título, descrição e H1 renderizados no servidor que nomeiam "Ovnipuerto de Cachi", e título ou descrição SHALL citar Werner Jaisli e a Estrella de la Esperanza.

#### Scenario: Robô sem JavaScript
- **WHEN** um robô busca `/origem/cachi` sem executar JavaScript
- **THEN** encontra "Ovnipuerto de Cachi" no `<title>`, na descrição e no `<h1>`

### Requirement: Resumo citável
A página SHALL mostrar uma seção "Em poucas palavras" com perguntas e respostas curtas tiradas do dossiê, e o JSON-LD SHALL conter um FAQPage com exatamente essas perguntas e respostas. As respostas MUST NOT afirmar nada que o dossiê não documente, e MUST NOT descrever o OVNIPORTO de Lages como um lugar que já funciona.

#### Scenario: Resumo e dados estruturados iguais
- **WHEN** a página é renderizada
- **THEN** cada pergunta do FAQPage aparece no HTML visível com a mesma resposta

### Requirement: Dados estruturados de referência
A página SHALL publicar um grafo JSON-LD com Article (datas de publicação e atualização, autor, `about`, `mentions`, `citation` com todas as fontes do capítulo de fontes), Place do Ovnipuerto de Cachi com nomes alternativos e endereço, Person de Werner Jaisli e BreadcrumbList. O Place MUST NOT ter coordenadas enquanto elas forem aproximadas.

#### Scenario: Fontes citadas
- **WHEN** o JSON-LD é lido
- **THEN** cada fonte listada na página aparece em `citation` com a mesma URL

### Requirement: Resumo para assistentes de IA
O sistema SHALL servir `/llms.txt` em texto simples com a descrição do site, o resumo do dossiê de Cachi, as perguntas e respostas e as fontes, todos gerados a partir de `resources/content/origin/cachi.json`.

#### Scenario: Assistente lê o resumo
- **WHEN** um cliente busca `/llms.txt`
- **THEN** recebe `text/plain` com o link canônico de `/origem/cachi`, as perguntas e as URLs das fontes
