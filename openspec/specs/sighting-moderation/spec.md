# sighting-moderation Specification

## Purpose
Moderação dos relatos enviados pela comunidade antes de qualquer publicação, com comunicação ao autor e ciclo de ajuste e reenvio.

## Requirements

### Requirement: Fila de moderação
O painel SHALL apresentar os relatos em abas Pendentes (padrão), Ajuste pedido, Aprovados e Rejeitados, com miniatura, tipo, apelido, data observada, cidade aproximada e tempo desde o envio.

#### Scenario: Aba padrão
- **WHEN** um moderador abre `/painel/relatos`
- **THEN** vê os relatos `pending`, do mais antigo ao mais recente

### Requirement: Dados do autor só no painel
A tela de moderação SHALL exibir nome real e e-mail do autor apenas para moderadores e admin, e as fotos MUST ser servidas por URL assinada temporária.

#### Scenario: Fotos pendentes no painel
- **WHEN** o moderador abre um relato pendente
- **THEN** as fotos carregam por URLs assinadas e com validade curta

### Requirement: Aprovar
Aprovar SHALL publicar o relato (status `approved`, data de publicação), notificar o autor por e-mail e atualizar imediatamente o mapa, a API pública e a home.

#### Scenario: Aprovação publica
- **WHEN** o moderador aprova um relato pendente
- **THEN** o relato aparece na API pública e um e-mail de aprovação é enfileirado

### Requirement: Pedir ajuste
Pedir ajuste MUST exigir mensagem, mudar o status para `changes_requested`, retirar o relato de toda exibição pública e enviar ao autor e-mail com link para editar; ao reenviar pelo assistente com os dados carregados, o relato volta para `pending`.

#### Scenario: Ajuste some do público
- **WHEN** um relato aprovado recebe pedido de ajuste
- **THEN** ele deixa de aparecer na API pública e em `/relatos/{id}`

#### Scenario: Autor reenvia
- **WHEN** o autor edita o relato em ajuste e reenvia
- **THEN** o status volta a `pending` e ele reaparece na fila

### Requirement: Rejeitar com motivo
Rejeitar MUST exigir um motivo entre: foto com pessoa identificável, placa de carro, conteúdo ofensivo, não é um relato, ou outro com texto; o autor SHALL ser notificado por e-mail com o motivo.

#### Scenario: Rejeitar sem motivo
- **WHEN** o moderador tenta rejeitar sem escolher motivo
- **THEN** a rejeição é recusada

### Requirement: Despublicar
Um relato aprovado SHALL poder ser despublicado com nota, voltando a `pending` e saindo da exibição pública.

#### Scenario: Despublicação
- **WHEN** o moderador despublica um relato aprovado com nota
- **THEN** o relato volta a `pending` e a nota fica no histórico

### Requirement: Apoio à revisão
A tela SHALL oferecer atalhos de teclado (A aprovar, R rejeitar, J/K navegar) e um checklist de lembrete antes de aprovar: "Sem rostos identificáveis", "Sem placas", "Ponto não parece residência".

#### Scenario: Atalho de navegação
- **WHEN** o moderador pressiona J na fila
- **THEN** o foco vai para o próximo relato
