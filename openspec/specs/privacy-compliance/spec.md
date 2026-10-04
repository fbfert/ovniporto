# privacy-compliance Specification

## Purpose
Consolida as garantias de privacidade e LGPD do OVNIPORTO em comportamentos verificáveis: fotos sem metadados, acesso restrito a conteúdo pendente, dados mínimos, consentimentos registrados, portabilidade, exclusão com retenção fiscal e ausência de rastreamento.

## Requirements

### Requirement: Nenhum metadado em fotos armazenadas
Nenhum arquivo de foto armazenado pelo sistema (original ou variante) MUST conter metadados EXIF, XMP ou IPTC, qualquer que seja o caminho de envio.

#### Scenario: Fluxo completo com foto geolocalizada
- **WHEN** uma foto com GPS e DateTimeOriginal percorre todo o fluxo de relato
- **THEN** todos os arquivos salvos estão sem qualquer bloco de metadados

### Requirement: Acesso a fotos pendentes
Fotos de relatos não aprovados MUST responder 404 em qualquer rota pública e SHALL ser acessíveis somente por URL assinada válida por 10 minutos, emitida apenas para moderadores, admin ou o autor.

#### Scenario: URL assinada expirada
- **WHEN** alguém usa uma URL assinada emitida há 11 minutos
- **THEN** o acesso é negado

#### Scenario: Outro membro pede a URL
- **WHEN** um membro que não é o autor tenta obter URL assinada de foto pendente
- **THEN** a emissão é recusada

### Requirement: Dados mínimos do Google
O sistema MUST persistir do Google apenas id, nome, e-mail e avatar, e MUST NOT solicitar escopos além de `openid`, `email` e `profile`.

#### Scenario: Redirecionamento de login
- **WHEN** a pessoa inicia o login
- **THEN** a URL de autorização contém somente os três escopos permitidos

### Requirement: Registro de consentimentos
Cada consentimento (termos, publicação de relato, newsletter, listagem de parceiro) SHALL ser registrado com tipo, versão, data, IP e agente do navegador. Quando a versão dos termos mudar, o membro MUST aceitar a nova versão no próximo acesso antes de continuar.

#### Scenario: Nova versão dos termos
- **WHEN** a versão dos termos é atualizada e um membro faz login
- **THEN** ele precisa aceitar a nova versão e um novo consentimento é registrado

### Requirement: Conteúdo da exportação
A exportação "Baixar meus dados" SHALL conter, em JSON legível: perfil, relatos com fotos em links assinados, pedidos, inscrições e consentimentos com datas.

#### Scenario: Exportação completa
- **WHEN** um membro com relato, pedido e inscrição pede a exportação
- **THEN** o JSON enviado contém as cinco seções com os dados desse membro e de mais ninguém

### Requirement: Exclusão com retenção fiscal
A exclusão de conta MUST apagar o membro, seus relatos e todas as fotos e variantes; pedidos SHALL manter número, itens, valores e CPF criptografado com o cliente substituído por "Titular excluído" até 5 anos após o pagamento, quando uma rotina agendada MUST apagá-los definitivamente.

#### Scenario: Pedido após a exclusão
- **WHEN** um membro com pedido pago exclui a conta
- **THEN** o pedido continua existindo com cliente "Titular excluído" e data de retenção de 5 anos após o pagamento

#### Scenario: Fim da retenção
- **WHEN** a rotina roda após a data de retenção de um pedido anonimizado
- **THEN** o pedido e seus dados fiscais são apagados

### Requirement: Sem cookies de terceiros
As páginas públicas MUST definir apenas os cookies de sessão e de proteção contra falsificação de requisição, sem cookies ou scripts de rastreamento de terceiros.

#### Scenario: Home
- **WHEN** um visitante novo carrega a home
- **THEN** só existem os cookies de sessão e XSRF

### Requirement: Transparência e retenção de logs
A página `/privacidade` SHALL incluir a seção "O que fazemos na prática" listando estas garantias em linguagem simples, e os logs de acesso do servidor web MUST ser retidos por no máximo 6 meses.

#### Scenario: Seção de garantias
- **WHEN** o visitante abre `/privacidade`
- **THEN** encontra a seção "O que fazemos na prática" com as garantias vigentes
