# Spec Delta

## Purpose

Define os critérios mínimos de qualidade que o OVNIPORTO precisa atender antes do lançamento: performance em rede móvel, estados de interface completos, instalação básica e fluxos críticos cobertos por testes de ponta a ponta.

## ADDED Requirements

### Requirement: Meta de performance móvel
As rotas `/`, `/mapa`, `/loja`, `/o-lugar` e `/relatar` SHALL obter nota Lighthouse mobile ≥ 90 em todas as categorias e LCP abaixo de 2,5 s em perfil 4G.

#### Scenario: Auditoria antes do lançamento
- **WHEN** o Lighthouse mobile roda nessas rotas em ambiente de produção
- **THEN** todas as categorias marcam 90 ou mais e os relatórios são anexados

### Requirement: Imagens responsivas
Imagens de conteúdo SHALL ser servidas em AVIF e WebP em larguras 400, 800, 1200 e 1600 com `srcset`, carregamento tardio fora da capa e placeholder borrado enquanto carregam.

#### Scenario: Imagem abaixo da dobra
- **WHEN** a página carrega
- **THEN** imagens fora da primeira tela não são baixadas até se aproximarem da área visível

### Requirement: Carregamento sob demanda
Bibliotecas de mapa e de animação MUST ser carregadas apenas nas páginas que as usam.

#### Scenario: Página sem mapa
- **WHEN** o visitante abre `/faq`
- **THEN** nenhum código de mapa é baixado

### Requirement: Cache HTTP
Respostas públicas SHALL enviar `Cache-Control` e `ETag`, e respostas em cache MUST ser invalidadas quando o conteúdo de origem muda.

#### Scenario: Revalidação
- **WHEN** o navegador repete uma requisição com `If-None-Match` de conteúdo inalterado
- **THEN** recebe 304

### Requirement: Estados de interface completos
Toda lista do site SHALL ter estado vazio, de carregamento (no tom da seção) e de erro com mensagem em português.

#### Scenario: Falha ao carregar
- **WHEN** a API de relatos falha
- **THEN** a lista mostra o estado de erro em vez de ficar em branco

### Requirement: Instalação básica e offline
O site SHALL ter favicon e ícones nos tamanhos padrão, manifest com cor de tema night e, sem conexão, MUST mostrar a página "Sem sinal da torre".

#### Scenario: Sem rede
- **WHEN** o visitante instalado abre o site sem conexão
- **THEN** vê a página "Sem sinal da torre"

### Requirement: Testes de ponta a ponta dos fluxos críticos
O projeto SHALL ter testes de ponta a ponta executáveis por `make e2e` cobrindo: navegação e compartilhamento de relato; envio de relato com foto com EXIF e aprovação; compra do adesivo com retirada em Lages e avanço do pedido; exportação e exclusão de conta; axe-core sem violações críticas; e regressão visual da home em 390 px e 1440 px com tolerância de 0,5%.

#### Scenario: Regressão visual
- **WHEN** uma mudança altera mais de 0,5% de uma captura de referência da home
- **THEN** o teste de regressão visual falha

#### Scenario: Relato some após exclusão
- **WHEN** o teste exclui a conta de um membro com relato aprovado
- **THEN** o relato não aparece mais no mapa
