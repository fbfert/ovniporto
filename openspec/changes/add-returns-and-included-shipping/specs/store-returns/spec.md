# Spec Delta

## Purpose

Permite que quem comprou na loja do OVNIPORTO peça devolução ou exerça o direito de arrependimento do CDC por um formulário público, e que a equipe atenda esses pedidos pelo painel com prazo, avisos e retenção ligados ao pedido.

## ADDED Requirements

### Requirement: Formulário público de devolução
A página `/devolucao` SHALL oferecer um formulário com número do pedido, e-mail da compra, nome, motivo (arrependimento, defeito, produto diferente do pedido, avaria no transporte ou outro) e mensagem de 10 a 2000 caracteres, aberto a visitantes e membros.

#### Scenario: Pedido de devolução aceito
- **WHEN** uma pessoa envia o formulário com o número e o e-mail de um pedido pago, nome, motivo "arrependimento" e mensagem válida
- **THEN** o pedido de devolução é gravado com um protocolo e a pessoa vê a confirmação com esse protocolo

#### Scenario: Campos inválidos
- **WHEN** a mensagem tem menos de 10 caracteres ou o motivo não é um dos da lista
- **THEN** o formulário é recusado com mensagens em português e nada é gravado

### Requirement: Conferência do pedido sem revelar dados
O pedido de devolução MUST ser aceito somente quando o número e o e-mail informados correspondem a um mesmo pedido que já foi pago; quando não correspondem, a resposta MUST ser a mesma mensagem genérica, sem indicar qual campo está errado.

#### Scenario: E-mail que não é o da compra
- **WHEN** o número existe mas o e-mail informado é diferente do e-mail do pedido
- **THEN** a pessoa vê "Não encontramos um pedido com esse número e esse e-mail." e nada é gravado

#### Scenario: Pedido não pago
- **WHEN** o número e o e-mail correspondem a um pedido em `pending_payment` ou `canceled`
- **THEN** a mesma mensagem genérica é mostrada e nada é gravado

### Requirement: Proteção contra abuso
O envio do formulário MUST ter limite de tentativas por IP e um campo-isca invisível; um envio com o campo-isca preenchido SHALL receber a mesma resposta de sucesso sem gravar nem enviar nada.

#### Scenario: Robô preenche o campo-isca
- **WHEN** o formulário chega com o campo-isca preenchido
- **THEN** a resposta é a confirmação de sempre e nenhum pedido de devolução nem e-mail é criado

#### Scenario: Muitas tentativas
- **WHEN** o mesmo IP envia mais tentativas do que o limite em um minuto
- **THEN** as tentativas excedentes são recusadas com status 429

### Requirement: Prazo de arrependimento marcado
Cada pedido de devolução SHALL ser marcado como dentro ou fora do prazo de arrependimento: dentro quando enviado até 7 dias corridos após a data em que o pedido foi marcado como entregue, ou quando o pedido ainda não foi entregue. Pedidos fora do prazo MUST ser aceitos e apenas marcados.

#### Scenario: Dentro do prazo
- **WHEN** o pedido foi entregue há 5 dias e a pessoa pede a devolução
- **THEN** o pedido de devolução é gravado como dentro do prazo de arrependimento

#### Scenario: Fora do prazo
- **WHEN** o pedido foi entregue há 20 dias e a pessoa pede a devolução por defeito
- **THEN** o pedido de devolução é gravado e marcado como fora do prazo de arrependimento

#### Scenario: Ainda não entregue
- **WHEN** o pedido está `shipped` e a pessoa pede a devolução
- **THEN** o pedido de devolução é marcado como dentro do prazo

### Requirement: Avisos por e-mail
Ao gravar um pedido de devolução, o sistema SHALL enfileirar um e-mail em português para o e-mail da compra, com o protocolo e o prazo de resposta, e um aviso para o endereço de devoluções configurado, contendo apenas o protocolo e o link do painel.

#### Scenario: Endereço de devoluções configurado
- **WHEN** o endereço de devoluções está definido em /painel/coordenadas e um pedido de devolução é gravado
- **THEN** o aviso da equipe vai para esse endereço e não contém nome, e-mail nem mensagem de quem pediu

#### Scenario: Sem configuração no painel
- **WHEN** o endereço de devoluções não foi definido no painel
- **THEN** o aviso vai para o valor do ambiente, que por padrão é `contato@tars.art.br`

### Requirement: Atendimento no painel
O painel SHALL listar os pedidos de devolução em `/painel/pedidos/devolucoes`, acessível a quem abre a área Pedidos, com protocolo, número do pedido, motivo, marcação de prazo, status e idade, além de busca e filtro por status refletidos na URL. A ficha SHALL mostrar os dados enviados e o link para o pedido.

#### Scenario: Papel da loja
- **WHEN** um membro com papel `store` abre a lista de devoluções
- **THEN** vê os pedidos de devolução e pode abrir a ficha de cada um

#### Scenario: Moderador
- **WHEN** um membro com papel `moderator` tenta abrir a lista de devoluções
- **THEN** o acesso é recusado

### Requirement: Status do pedido de devolução
O pedido de devolução SHALL seguir os status `received` → `in_progress` → `completed` ou `declined`, podendo ir de `received` direto para `completed` ou `declined`; cada mudança MUST exigir uma nota, registrar autor e data e gerar registro de auditoria. Transições fora dessa ordem MUST ser recusadas.

#### Scenario: Concluir com nota
- **WHEN** o operador marca como concluído um pedido de devolução em andamento, com a nota "Etiqueta reversa enviada e valor reembolsado"
- **THEN** o status vira `completed`, a nota fica no histórico e a auditoria registra a ação

#### Scenario: Transição inválida
- **WHEN** o operador tenta voltar um pedido de devolução `completed` para `in_progress`
- **THEN** a ação é recusada e o status não muda

### Requirement: Devoluções em "Precisa de você"
Pedidos de devolução em `received` SHALL aparecer na lista "Precisa de você" do início do painel para quem abre a área Pedidos.

#### Scenario: Devolução nova
- **WHEN** existe um pedido de devolução recebido e ainda não atendido
- **THEN** o início do painel de um membro `store` mostra esse item com link para a ficha

### Requirement: Caminhos até o formulário
O link para `/devolucao` SHALL aparecer no rodapé do site, na página do pedido e no e-mail de pedido entregue; a partir da página do pedido, o número do pedido MUST vir preenchido.

#### Scenario: A partir da página do pedido
- **WHEN** o cliente abre "Pedir devolução" na página do seu pedido
- **THEN** chega a `/devolucao` com o número do pedido já preenchido

### Requirement: Retenção junto com o pedido
Os dados de um pedido de devolução MUST seguir a retenção do pedido a que pertencem: quando os dados pessoais do pedido são apagados ou anonimizados, o nome, o e-mail e a mensagem do pedido de devolução MUST ser apagados junto, mantendo protocolo, motivo, status e datas; quando o pedido é apagado, seus pedidos de devolução também são.

#### Scenario: Conta excluída
- **WHEN** um membro com pedido pago e pedido de devolução exclui a conta
- **THEN** o pedido de devolução fica sem nome, e-mail e mensagem, mantendo protocolo, motivo, status e datas

#### Scenario: Pedido apagado ao fim do prazo
- **WHEN** a rotina de retenção apaga definitivamente um pedido
- **THEN** os pedidos de devolução desse pedido são apagados junto
