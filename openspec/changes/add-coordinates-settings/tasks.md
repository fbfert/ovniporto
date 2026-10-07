# Tasks

## 1. Domínio e persistência

- [x] 1.1 Enum `App\Domain\Settings\OperationalSetting` (chave, grupo, chave de `config()` sobrescrita, se é segredo) e `SettingsGroup`, verificado por teste de unidade que cobre todas as chaves e recusa chave desconhecida
- [x] 1.2 `App\Domain\Shipping\Cnpj` com dígitos verificadores, verificado por teste de unidade com CNPJs válidos e inválidos
- [x] 1.3 Migration aditiva `operational_settings` (key única, value text, cifrado pelo repositório quando é segredo, is_secret, updated_by, timestamps) e o modelo `OperationalSettingRow`, verificado por `php artisan migrate` e por teste que confirma a senha cifrada no banco
- [x] 1.4 Contrato `OperationalSettingsRepository` e implementação Eloquent com cache versionado no Redis, onde o valor secreto continua cifrado no cache, verificado por teste de feature

## 2. Aplicação em tempo de execução

- [x] 2.1 `OperationalSettingsApplier` + middleware `ApplyOperationalSettings`: aplica as coordenadas sobre `config()` por requisição (nunca no boot, para não entrar no `config:cache`), segue com o `.env` se banco e Redis estiverem fora, e muda `mail.default` para `smtp` quando há servidor no painel. Verificado por teste: tabela vazia mantém o `.env`; valor salvo prevalece; campo apagado volta ao `.env`; um app recém-iniciado (o que o `config:cache` serializa) não contém as coordenadas
- [x] 2.2 Listeners de `JobProcessing` e `MasterSupervisorLooped` que, ao mudar a versão, reaplica, faz `Mail::purge('smtp')` e esquece o singleton do frete. Verificado por teste que troca o SMTP entre duas tarefas e confere o servidor usado na segunda
- [x] 2.3 Alertas em vigor: `JobFailureAlert` lê o `config()` reaplicado; `routeMailNotificationsTo` guarda uma string estática (sem closure), então o aplicador troca `Horizon::$email`. Verificado por teste com o destino trocado depois do boot

## 3. Casos de uso

- [x] 3.1 `ShowCoordinates`: valores em vigor e origem de cada um (painel ou `.env`), senha só como definida/não definida. Verificado por teste de unidade que confirma que a senha não está no retorno
- [x] 3.2 `SaveCoordinates::execute` por grupo (só os campos enviados), com auditoria de antes e depois e a senha registrada como "alterada"/"removida". Campo vazio apaga a linha, exceto a senha, que é mantida. Verificado por teste de unidade
- [x] 3.3 `SaveCoordinates::removePassword`, auditado, verificado por teste de unidade
- [x] 3.4 `TestCoordinates::mail` e `TestCoordinates::alert` (sobre o contrato `MailTester`, implementado por `SmtpMailTester`): síncronos, com a falha classificada (login, conexão, certificado, remetente), a resposta do servidor sem usuário nem senha e o resultado auditado. Verificado por teste de unidade com transporte falso que lança 535 contendo a senha
- [x] 3.5 Contrato `IntegrationDirectory` (`ConfigIntegrationDirectory`), exposto por `ShowCoordinates`: Google, PayPal, Melhor Envio, Umami e geocodificação, com configurado, modo e atenção, sem valor algum. Verificado por teste de feature que procura as chaves de teste no HTML da página

## 4. Painel

- [x] 4.1 `PanelArea::Coordinates = 'coordenadas'` (só admin), middleware nas rotas `panel.coordinates`, `.mail`, `.alerts`, `.shipping`, `.password.destroy`, `.test-mail` e `.test-alert` (as de teste com `throttle:5,1`). Verificado por teste de feature: admin 200; moderador e loja 403 em GET e em todos os envios; atualizar `PanelAreaTest`
- [x] 4.2 FormRequest `SaveCoordinatesRequest` com mensagens em pt-BR: porta, segurança, e-mails, CPF/CNPJ, UF, embalagem. Verificado por teste de feature com porta 70000 e CNPJ inválido
- [x] 4.3 `CoordinatesController`, fino: valida, chama o caso de uso e redireciona com toast
- [x] 4.4 Página `resources/js/Pages/Panel/Coordinates/Index.tsx` com as quatro seções, a origem por campo, a senha só de escrita com "Remover senha" no `ConfirmButton` e o resultado do teste em `aria-live`. Textos em `pt-BR.ts` (`panel.areas.coordinates`, `panel.coordinates.*`). Antes de mexer na UI, carregar as skills de design do `.claude/skills/README.md`. Verificado por teste Vitest de render e da senha nunca preenchida
- [x] 4.5 Item "Coordenadas" no menu do `PanelLayout` e cartão no início do painel, destacado quando o último teste de e-mail falhou ou falta destino de alertas. Verificado por teste de feature das props e por Vitest
- [x] 4.6 Teste de feature ponta a ponta: o HTML e as props da página nunca contêm a senha salva, e os logs da requisição de teste falho também não

## 5. Manual e documentação

- [x] 5.1 Atualizar o manual do painel: capítulo novo `resources/content/manual/coordenadas.json` (grupo backstage, área coordenadas, mapa, seções com `screen: panel.coordinates`, rotas citadas) e os capítulos `monitoramento`, `problemas`, `papeis`, `primeiros-passos` e `inicio` (texto, mapa, rotas e reviewedAt; `pedidos` não cita o remetente). Verificado por `php artisan test --filter=PanelManual`
- [x] 5.2 `DEPLOY.md`: o que saiu do `.env` para as Coordenadas, a regra "painel tem prioridade, vazio volta ao `.env`", o roteiro do SMTP parado (teste no painel e leitura da resposta) e a recomendação de dois admins
- [x] 5.3 Rodar Pest, PHPStan, `npm run lint`, `npm test`, `npm run build` e `openspec validate --all --strict`, verificado tudo verde
- [x] 5.4 Conferir no navegador, em 1440 e 390 px: salvar cada grupo, teste de e-mail com servidor falso (Mailpit local) e com senha errada, nenhuma rolagem horizontal, axe sem violações. Capturas em `evidence/`
