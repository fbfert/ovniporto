# Spec Delta

## Purpose

As Coordenadas do painel: o lugar onde o admin ajusta, sem acesso à VPS, as configurações operacionais do site (e-mail, alertas, frete) e confere o estado das integrações, sem nunca expor segredos.

## ADDED Requirements

### Requirement: Área Coordenadas
O painel SHALL ter a área **Coordenadas** em `/painel/coordenadas`, com item no menu e cartão no início do painel, aberta só ao `admin`. A tela SHALL agrupar as configurações em Correio, Alertas, Frete e Integrações, e cada grupo SHALL ser salvo separadamente.

#### Scenario: Admin abre as Coordenadas
- **WHEN** um `admin` acessa `/painel/coordenadas`
- **THEN** vê os grupos Correio, Alertas, Frete e Integrações, com os valores em vigor

#### Scenario: Outros papéis
- **WHEN** um `moderator` ou `store` acessa `/painel/coordenadas` ou envia um formulário dela
- **THEN** recebe 403 e nada é alterado

### Requirement: Prioridade sobre o .env
Um valor salvo nas Coordenadas SHALL valer no lugar do valor do `.env`. Um campo deixado vazio MUST voltar ao valor do `.env`. A tela SHALL indicar, campo a campo, se o valor em vigor vem do painel ou do `.env`.

#### Scenario: Nada salvo ainda
- **WHEN** nenhuma coordenada foi salva
- **THEN** o site usa exatamente os valores do `.env`, e cada campo indica "do .env"

#### Scenario: Campo esvaziado
- **WHEN** o admin apaga o servidor SMTP salvo no painel e salva
- **THEN** o site volta a usar o `MAIL_HOST` do `.env`

### Requirement: Vale sem reiniciar
Uma coordenada salva SHALL valer na próxima requisição do site e antes da próxima tarefa processada pelos workers, sem reiniciar containers nem limpar cache manualmente.

#### Scenario: E-mail em fila depois da troca
- **WHEN** o admin troca o servidor SMTP e, em seguida, um pedido gera um e-mail em fila
- **THEN** o worker envia esse e-mail pelo servidor novo

### Requirement: Correio (SMTP)
O grupo Correio SHALL permitir definir servidor, porta (1 a 65535), segurança (STARTTLS ou SSL/TLS), usuário, senha, endereço e nome do remetente. O endereço do remetente MUST ser um e-mail válido. Com servidor definido no painel, o site SHALL enviar por SMTP.

#### Scenario: Configuração válida
- **WHEN** o admin salva servidor, porta 587, STARTTLS, usuário, senha e remetente válidos
- **THEN** os próximos e-mails do site saem por esse servidor com esse remetente

#### Scenario: Porta inválida
- **WHEN** o admin informa a porta 70000
- **THEN** o salvamento é recusado com mensagem em português e nada muda

### Requirement: Segredos nunca voltam
A senha do SMTP MUST ser guardada cifrada e MUST NOT aparecer na tela, nas respostas ao navegador, na auditoria nem nos logs. A tela SHALL mostrar só "definida" ou "não definida". Deixar o campo de senha em branco ao salvar SHALL manter a senha atual. Apagar a senha SHALL exigir uma ação explícita.

#### Scenario: Salvar sem tocar na senha
- **WHEN** o admin muda só a porta e salva com o campo de senha em branco
- **THEN** a senha anterior continua valendo

#### Scenario: Página não carrega a senha
- **WHEN** o admin abre as Coordenadas com uma senha definida
- **THEN** a resposta enviada ao navegador não contém a senha, só a indicação "definida"

#### Scenario: Remover a senha
- **WHEN** o admin usa "Remover senha" e confirma
- **THEN** a senha do painel é apagada e volta a valer a do `.env`, se houver

### Requirement: E-mail de teste
O grupo Correio SHALL ter **Enviar e-mail de teste**, que envia na hora (sem fila) uma mensagem para o e-mail do admin com a configuração salva e mostra "enviado" ou o motivo da falha. O texto da falha MUST NOT conter a senha. O teste SHALL ser limitado a 5 por minuto por admin.

#### Scenario: Servidor recusa o login
- **WHEN** o servidor SMTP recusa o usuário e a senha
- **THEN** a tela mostra que o login foi recusado, com a resposta do servidor sem a senha

#### Scenario: Envio aceito
- **WHEN** o servidor aceita a mensagem
- **THEN** a tela mostra "enviado para <e-mail do admin>"

### Requirement: Alertas
O grupo Alertas SHALL definir o endereço que recebe "tarefa que falhou" e "fila esperando demais". O endereço MUST ser um e-mail válido. Vazio, vale a regra atual: falhas vão para todos os admins e a fila longa não avisa ninguém. A tela SHALL dizer isso quando o campo estiver vazio e SHALL ter um botão para enviar um alerta de teste.

#### Scenario: Endereço definido
- **WHEN** o admin salva um endereço de alertas
- **THEN** o próximo aviso de fila longa e o próximo de tarefa que falhou vão para esse endereço

#### Scenario: Campo vazio
- **WHEN** não há endereço de alertas no painel nem no `.env`
- **THEN** a tela avisa que a fila longa não avisa ninguém

### Requirement: Frete
O grupo Frete SHALL definir os dados do remetente das etiquetas (nome, telefone, e-mail, CPF ou CNPJ, rua, número, bairro, cidade, UF) e a embalagem padrão da cotação (comprimento, largura e altura em cm inteiros de 1 a 100). O CPF ou CNPJ MUST ser válido pelos dígitos verificadores e a UF MUST ser uma sigla brasileira.

#### Scenario: Etiqueta usa o remetente salvo
- **WHEN** o admin salva o remetente e depois gera uma etiqueta
- **THEN** a etiqueta sai com o remetente salvo no painel

#### Scenario: Documento inválido
- **WHEN** o admin informa um CNPJ com dígito verificador errado
- **THEN** o salvamento é recusado com mensagem em português

### Requirement: Integrações em leitura
O grupo Integrações SHALL mostrar, para Google, PayPal, Melhor Envio, Umami e geocodificação, se cada uma está configurada e em que modo (sandbox ou produção, quando houver). MUST NOT mostrar chaves, tokens ou segredos, nem permitir editá-los. A tela SHALL dizer que essas configurações ficam no `.env` da VPS.

#### Scenario: PayPal em sandbox na produção
- **WHEN** o site está em produção com o PayPal em modo sandbox
- **THEN** o PayPal aparece com o modo "sandbox" destacado como atenção

#### Scenario: Chave ausente
- **WHEN** o token do Melhor Envio está vazio
- **THEN** o Melhor Envio aparece como "não configurado", sem mostrar valor algum

### Requirement: Auditoria das Coordenadas
Todo salvamento nas Coordenadas MUST gerar registro de auditoria com autor, grupo e valores antes e depois. Para a senha, o registro MUST dizer só "alterada" ou "removida". Os testes de envio SHALL ser registrados com o resultado.

#### Scenario: Troca da senha auditada
- **WHEN** o admin troca a senha do SMTP
- **THEN** a auditoria mostra "senha: alterada", sem o valor antigo nem o novo
