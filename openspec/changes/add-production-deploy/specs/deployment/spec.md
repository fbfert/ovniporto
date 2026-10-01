# Spec Delta

## Purpose

Mantém o OVNIPORTO em produção na VPS de forma repetível, segura e recuperável: deploy com verificação e rollback, backups externos, monitoramento e proteções de borda.

## ADDED Requirements

### Requirement: HTTPS e domínio canônico
O site SHALL ser servido somente por HTTPS em `ovniporto.tars.art.br`, com certificado renovado automaticamente, redirecionamento de `www` e de HTTP para o domínio canônico e cabeçalho HSTS.

#### Scenario: Acesso por HTTP
- **WHEN** alguém acessa `http://www.ovniporto.tars.art.br`
- **THEN** é redirecionado permanentemente para `https://ovniporto.tars.art.br`

### Requirement: Deploy idempotente com rollback
O deploy SHALL ser executado por um único script idempotente que atualiza o código, constrói as imagens, roda migrações, aquece caches, reinicia SSR e workers e verifica `/up`; se a verificação falhar, MUST voltar à versão anterior.

#### Scenario: Health check falha
- **WHEN** `/up` não responde com sucesso após o deploy
- **THEN** o script restaura a versão anterior e termina com erro

#### Scenario: Rodar duas vezes
- **WHEN** o script é executado duas vezes seguidas sem mudanças
- **THEN** a segunda execução termina com sucesso sem alterar o estado

### Requirement: Backups externos criptografados
Um backup diário do banco e dos arquivos SHALL ser criptografado e enviado a armazenamento fora da VPS, com retenção de 30 dias, e o procedimento de restauração MUST estar documentado e testado.

#### Scenario: Teste de restauração
- **WHEN** o backup mais recente é restaurado em ambiente limpo seguindo a documentação
- **THEN** a aplicação sobe com os dados do dia do backup

### Requirement: Monitoramento e alertas
Os painéis de filas e de saúde SHALL ser acessíveis apenas a `admin`; o sistema SHALL enviar alerta por e-mail quando a fila acumular ou um job falhar 3 vezes, e um serviço externo SHALL monitorar `/up`.

#### Scenario: Job com falhas repetidas
- **WHEN** um job falha pela terceira vez
- **THEN** um alerta por e-mail é enviado ao admin

### Requirement: Proteções de borda
As respostas SHALL incluir CSP que permita apenas as origens necessárias (PayPal, tiles do mapa, métrica própria), `X-Frame-Options` e HSTS, e login, envio de relato, checkout e API MUST ter limite de requisições.

#### Scenario: Excesso de envios
- **WHEN** um mesmo cliente excede o limite de envios de relato
- **THEN** recebe 429

### Requirement: Segredos fora do repositório
Credenciais e chaves MUST vir somente de variáveis de ambiente, nunca do repositório, e os containers MUST rodar com usuário não-root.

#### Scenario: Varredura do repositório
- **WHEN** a varredura de segredos roda sobre o repositório
- **THEN** nenhuma credencial é encontrada

### Requirement: Checklist de go-live
O lançamento SHALL seguir um checklist documentado cobrindo DNS, certificados, webhooks de pagamento de produção, chaves de frete, SMTP, OAuth do Google com URL de produção, métrica, backup testado, páginas 404/500 e Lighthouse final.

#### Scenario: Antes de abrir ao público
- **WHEN** o responsável prepara o lançamento
- **THEN** todos os itens do checklist estão marcados com evidência
