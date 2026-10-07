# Design

## Context

Toda a configuração operacional vem hoje de `env()` nos arquivos de `config/`. O `deploy.sh` roda `php artisan optimize`, então o `.env` é lido uma vez e fica no cache de configuração. Há três pontos que leem a configuração uma única vez e guardam o resultado:

- `HorizonServiceProvider::boot()` registra `routeMailNotificationsTo(ALERTS_EMAIL)` ao iniciar.
- `DomainServiceProvider::shippingProvider()` monta o `MelhorEnvioShippingProvider` com o remetente e a embalagem. É um singleton, então nos workers ele vive enquanto o processo viver.
- O `MailManager` do Laravel guarda a instância do mailer `smtp` depois do primeiro envio. Nos workers do Horizon, que ficam de pé por horas, isso congela o servidor antigo.

Já existe `SiteSettings` (blocos de conteúdo em `/painel/configuracoes`), mas ele guarda textos públicos sem cifra. Segredo não deve ir para lá. O `Auditor` grava antes e depois na mesma transação da mudança. `Cpf` já existe em `app/Domain/Orders/Cpf.php`.

## Goals / Non-Goals

**Goals:**
- Trocar SMTP, alertas e remetente do frete pelo painel, valendo sem reiniciar nada.
- Nenhum segredo sai do servidor depois de salvo.
- Com a tabela vazia, o comportamento é idêntico ao de hoje.

**Non-Goals:**
- Credenciais de Google, PayPal e Melhor Envio, `APP_KEY`, banco, Redis, Umami e o modo da CSP. Ficam no `.env` (ver proposal).
- Histórico com desfazer. A auditoria já registra antes e depois.
- Consertar o login recusado pelo Dovecot. Isso é configuração da caixa no servidor de e-mail. O que esta change entrega é o botão de teste, que mostra a resposta do servidor.

## Decisions

### 1. Catálogo fechado de chaves no domínio

`app/Domain/Settings/OperationalSetting.php` é um enum com cada chave (`mail.host`, `mail.port`, `mail.scheme`, `mail.username`, `mail.password`, `mail.from_address`, `mail.from_name`, `alerts.email`, `shipping.sender.*`, `shipping.package.*`). Cada caso diz:

- o grupo (`Mail`, `Alerts`, `Shipping`);
- a chave de `config()` que ele sobrescreve (`mail.mailers.smtp.host`, `ovniporto.alerts_email` etc.);
- se é segredo (só `mail.password`).

Só as chaves do enum são aceitas e aplicadas, então o painel nunca escreve em uma chave de config arbitrária.

*Alternativa recusada:* uma tabela chave-valor livre. Seria mais flexível, mas abriria espaço para um formulário adulterado sobrescrever `app.key` ou `database.*`.

### 2. Tabela própria, segredo cifrado

Migration `operational_settings`: `key` (único), `value` (text), `is_secret`, `updated_by` (member id, nulo), `timestamps`. O repositório cifra o valor de chave secreta com o `Encrypter` (a mesma `APP_KEY` que já cifra o CPF dos pedidos) antes de gravar. Não se usa o cast `encrypted` porque só uma das chaves da tabela é secreta.

Apagar a linha significa "volta ao `.env`". Por isso salvar um campo vazio apaga a linha, exceto para a senha. Para a senha, campo vazio significa "não mexer", e remover exige a ação explícita `DELETE /painel/coordenadas/senha-smtp`.

Repositório: `Domain/Settings/Contracts/OperationalSettingsRepository` (`values()`, `save(array $changes, ?int $actorId)`, em que `null` apaga a linha, `version()`, `has()`, `recordMailTest()` e `lastMailTest()`), com implementação Eloquent e cache.

### 3. Aplicação sobre `config()` em tempo de execução, com versão

O `OperationalSettingsApplier` (singleton) aplica os valores com `config()->set()` por cima da configuração em cache. Na primeira vez, ele guarda à parte os valores do `.env`, para voltar a eles quando um campo é esvaziado. Ele **nunca roda no boot dos providers**: `config:cache` monta um app novo e serializa o `config()` depois do boot, e uma aplicação nesse ponto congelaria as coordenadas (com a senha decifrada) em `bootstrap/cache/config.php`. Por isso são três gatilhos, todos fora do boot:

- **Web:** o middleware `ApplyOperationalSettings`, no grupo `web`, chama `refresh()`.
- **Workers:** um listener de `JobProcessing` chama `refresh()` antes de cada tarefa.
- **Mestre do Horizon:** um listener de `MasterSupervisorLooped` chama `refresh()` a cada volta. É o mestre que detecta a fila longa e envia esse aviso.

O `refresh()` compara a versão (um UUID trocado a cada salvamento, guardado no cache) com a da última aplicação. Se mudou, reaplica os valores, chama `purge('smtp')` no MailManager e esquece o singleton do frete (`forgetInstance(ShippingProvider::class)`). O custo é uma leitura no cache por requisição ou tarefa. Se o cache for apagado, a versão nova obriga todos a recarregar uma vez, o que é inofensivo. Qualquer falha (banco ou Redis fora) é registrada com `report()` e mantém o `.env` em vigor.

- **Alertas do Horizon:** `routeMailNotificationsTo` guarda uma string estática (`Horizon::$email`), lida no momento de cada aviso. Ele não aceita closure. O `HorizonServiceProvider` continua registrando o valor do `.env` no boot, e o aplicador troca `Horizon::$email` pelo endereço em vigor.
- **Mailer padrão:** com servidor definido no painel e `mail.default` igual a `log` no `.env`, o padrão passa a `smtp`. Outros valores (como o `array` dos testes) não mudam.

O Redis é interno à rede do Docker e já guarda sessões, mas mesmo assim a senha não fica lá em texto puro: o cache leva as linhas exatamente como estão no banco (a senha cifrada), e a decifragem acontece em memória. Uma senha cifrada com outra `APP_KEY` (num restore em outro servidor) é ignorada com `report()`, e o `.env` volta a valer.

*Alternativa recusada:* reescrever o `.env` e rodar `config:cache`. Isso exige o processo web com escrita no `.env` e reinício dos workers, e mistura configuração de deploy com dado editável.

### 4. Testes de envio síncronos e sem vazar segredo

`TestCoordinates::mail()` usa o contrato `MailTester`, implementado por `SmtpMailTester`. Ele limpa o transporte `smtp`, envia na hora com o mailer `smtp`, sem fila, e captura qualquer exceção. O que vai para a tela:

- a classe do problema: `auth` (530/534/535), `connection`, `certificate`, `sender`, `other`;
- a resposta do servidor (até 300 caracteres), com o usuário e a senha, em texto puro e em base64, trocados por `***`.

O limite é `throttle:5,1` nos dois botões. `TestCoordinates::alert()` envia um e-mail de "alerta de teste" ao endereço de alertas em vigor. Sem endereço, responde `no-recipient` sem enviar nada. O resultado do último teste de e-mail fica no cache (`recordMailTest`) para o cartão do início do painel.

### 5. Validação no FormRequest, documento no domínio

- Porta de 1 a 65535. `scheme` em `{smtp, smtps}`, apresentado como "STARTTLS" e "SSL/TLS".
- E-mails com `email:rfc`. UF em lista fechada das 27 siglas. O CEP de origem (`MELHOR_ENVIO_FROM_POSTAL_CODE`) entra no bloco Frete, porque é o endereço do remetente.
- Embalagem com `integer` de 1 a 100, igual ao `.env` atual.
- CPF usa o `Cpf` existente. CNPJ ganha `app/Domain/Shipping/Cnpj.php` com os dígitos verificadores, e o campo aceita os dois formatos.

### 6. Integrações em leitura, sem valor

O contrato `IntegrationDirectory` (implementado por `ConfigIntegrationDirectory`, lido por `ShowCoordinates`) devolve só `{name, configured: bool, mode: ?string, attention: bool}` lendo `config()`. A atenção acende em produção quando PayPal ou Melhor Envio estão em `sandbox`, ou quando falta configuração. Nenhum valor de chave atravessa o caso de uso.

### 7. Tela

A página `Panel/Coordinates/Index.tsx` tem três seções de formulário (Correio, Alertas, Frete), cada uma com o próprio `useForm` e o botão **Salvar**, e o quadro de Integrações. Abaixo de 1280 px o quadro vem depois dos formulários; acima, fica numa coluna fixa ao lado:

- Cada campo traz uma marca discreta: "definido aqui" (losango lilás), "do servidor" (losango vazado) ou "não definido". Quando vem do `.env`, o campo mostra o valor do `.env` como placeholder, nunca no caso da senha.
- A senha é um input `password` vazio, com a legenda "definida" ou "não definida" e o botão "Remover senha", que pede confirmação no `ConfirmButton` existente (diálogo próprio, nunca o do navegador).
- O resultado do teste aparece num bloco logo abaixo do botão (verde-luz para enviado, amarelo-carro para falha), anunciado por `aria-live`.
- O quadro de Integrações é o detalhe da tela: uma faixa noturna como a de uma torre de controle, com uma luz por integração (verde-feixe quando está no ar, amarelo-carro quando pede atenção). Não há movimento decorativo.

O nome "Coordenadas" segue a metáfora da torre de controle. O menu ganha o item, e o início do painel ganha o cartão "Coordenadas", que fica destacado quando o SMTP falhou no último teste ou quando não há destino de alertas.

### 8. Manual

Novo capítulo `coordenadas.json` (grupo `backstage`, área `coordenadas`), com a seção `regra` apontando para `panel.coordinates` e o mapa: Painel ou servidor, Correio, E-mail de teste, Alertas, Frete, Integrações. O teste de cobertura do manual exige isso. `monitoramento` (alertas) e `problemas` (SMTP, remetente) passam a dizer onde se muda cada coisa; `papeis`, `primeiros-passos` e `inicio` ganham a área nova e o cartão. O capítulo `pedidos` não cita o remetente e ficou como estava.

## Risks / Trade-offs

- **Uma conta admin comprometida pode desviar o e-mail do site** (por exemplo, apontar o SMTP para outro servidor e ler as confirmações de pedido). → Mitigação: só `admin`, tudo auditado, segredo nunca exibido. As chaves de pagamento continuam fora do painel. O `DEPLOY.md` reforça a recomendação de dois admins e de revisar a auditoria.
- **Configuração errada derruba o e-mail.** → Mitigação: o botão de teste existe para isso, e esvaziar os campos volta ao `.env` na hora. O manual explica esse caminho de volta.
- **Redis fora do ar.** → Mitigação: o provider lê do banco. Com os dois fora, o site já está fora de qualquer forma, e o boot segue com o `.env`.
- **Custo por requisição.** → Uma leitura no Redis por requisição web e um inteiro por tarefa. É desprezível perto das leituras de sessão e de carrinho que já existem.
- **Workers com mailer em cache.** → `Mail::purge` na troca de versão. A implementação precisa provar isso com um teste que troca a configuração entre duas tarefas.
