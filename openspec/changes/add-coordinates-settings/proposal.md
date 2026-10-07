# Proposal

## Why

Para trocar o servidor de e-mail, o endereço dos alertas ou o remetente do frete, hoje é preciso entrar na VPS, editar o `.env` e reiniciar os containers. O deploy de 07/10 mostrou o custo disso. O SMTP está parado (o login na caixa é recusado), `ALERTS_EMAIL` está vazio e nenhum alerta sai do servidor. Corrigir uma senha de e-mail não deveria depender de terminal.

## What Changes

- Nova área do painel **Coordenadas** (`/painel/coordenadas`), só para `admin`, com item próprio no menu e um cartão no início do painel. Nela o admin ajusta as configurações operacionais que hoje vivem no `.env`:
  - **Correio (SMTP)**: servidor, porta, segurança (STARTTLS ou SSL/TLS), usuário, senha, endereço e nome do remetente. O botão **Enviar e-mail de teste** manda uma mensagem na hora para o e-mail do próprio admin e mostra o resultado ou a resposta do servidor.
  - **Alertas**: o endereço que recebe "tarefa que falhou" e "fila esperando demais", com um botão de teste.
  - **Frete**: os dados do remetente das etiquetas (nome, telefone, e-mail, CPF/CNPJ, endereço) e a embalagem padrão da cotação.
  - **Integrações (só leitura)**: para Google, PayPal, Melhor Envio, Umami e geocodificação, mostra se cada uma está configurada e em que modo (sandbox ou produção), sem revelar valores.
- O valor salvo no painel tem prioridade. Campo vazio volta ao `.env`, então a produção continua igual até alguém salvar.
- A senha do SMTP fica cifrada com a `APP_KEY`, é só de escrita e nunca volta para a tela, para o navegador, para a auditoria ou para os logs. A tela mostra apenas "definida" ou "não definida".
- Toda alteração gera registro na auditoria, com antes e depois. Nos campos secretos, o registro diz só "alterada".
- As configurações valem sem reiniciar nada: o site lê o valor novo na próxima requisição, e os workers do Horizon antes da próxima tarefa.
- Continuam fora do painel, de propósito: `APP_KEY`, banco, Redis, as chaves do Google, do PayPal e do Melhor Envio, o Umami (o endereço do script entra na política de segurança de conteúdo) e o modo da CSP. Um erro nesses pontos derruba o login ou o pagamento, ou abre o site a script de terceiros. Por isso eles continuam exigindo acesso à VPS.

## Capabilities

### New Capabilities
- `operational-settings`: as Coordenadas, isto é, as configurações operacionais editáveis no painel (SMTP, alertas, remetente e embalagem do frete), a regra de prioridade sobre o `.env`, o tratamento dos segredos, os testes de envio e o quadro de integrações.

### Modified Capabilities
- `admin-access`: a regra de acesso por papel ganha a área Coordenadas, aberta só ao `admin`.

## Impact

- Domínio novo `app/Domain/Settings/` (catálogo das chaves, contrato do repositório) e casos de uso em `app/Application/Settings/`.
- Migration aditiva: tabela `operational_settings` (chave, valor, se é segredo, quem alterou, quando).
- Leitura em tempo de execução: o `OperationalSettingsApplier` aplica os valores sobre `config()` a cada requisição (middleware), antes de cada tarefa dos workers e a cada volta do mestre do Horizon, nunca no boot (para não entrar no `config:cache`). Ele também atualiza o destino dos alertas do Horizon e refaz o transporte SMTP e o singleton do Melhor Envio.
- `PanelArea::Coordinates`, rotas `panel.coordinates*`, `CoordinatesController`, página `resources/js/Pages/Panel/Coordinates/Index.tsx`, textos em `pt-BR.ts`.
- Manual: capítulo novo `coordenadas` (grupo "Bastidores"), e os capítulos `monitoramento`, `problemas`, `papeis`, `primeiros-passos` e `inicio` passam a apontar para ele.
- `DEPLOY.md`: o que mudou de lugar, a regra "painel tem prioridade, vazio volta ao `.env`" e o roteiro para o SMTP parado.
