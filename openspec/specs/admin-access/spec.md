# admin-access Specification

## Purpose
Acesso ao painel de operação do OVNIPORTO por papéis, visão geral do dia a dia, gestão de membros e trilha de auditoria de toda ação administrativa.

## Requirements

### Requirement: Acesso por papel
O painel `/painel` SHALL ser restrito a papéis administrativos: `admin` acessa tudo; `moderator` só relatos e membros; `store` só pedidos e produtos. Membros comuns e visitantes MUST ser recusados.

#### Scenario: Moderador tenta abrir pedidos
- **WHEN** um `moderator` acessa `/painel/pedidos`
- **THEN** recebe 403

#### Scenario: Loja tenta abrir relatos
- **WHEN** um `store` acessa `/painel/relatos`
- **THEN** recebe 403

#### Scenario: Membro comum
- **WHEN** um `member` acessa `/painel`
- **THEN** recebe 403

### Requirement: Dashboard
O dashboard SHALL mostrar contagens de membros, relatos, pedidos, faturamento do mês e inscritos do Avise-me, o progresso das metas de 6 meses a partir da data de lançamento configurada, um gráfico semanal de relatos e pedidos das últimas 12 semanas e a lista "Precisa de você".

#### Scenario: Itens que precisam de atenção
- **WHEN** existe relato pendente há mais de 48 h, pedido pago há mais de 2 dias sem produção ou pedido sem rastreio há mais de 7 dias
- **THEN** cada um aparece em "Precisa de você" com link para a ação

### Requirement: Tabelas operáveis
As listas do painel SHALL oferecer ordenação, busca, paginação e filtros refletidos na URL.

#### Scenario: Filtro compartilhável
- **WHEN** um operador copia a URL de uma lista filtrada
- **THEN** outra pessoa com o mesmo papel abre a mesma lista filtrada

### Requirement: Gestão de membros
O painel SHALL listar membros com busca e permitir: alterar papel (somente admin), bloquear com motivo registrado (impede novos relatos e pedidos) e atender pedidos de exclusão.

#### Scenario: Moderador tenta mudar papel
- **WHEN** um `moderator` tenta promover um membro a `admin`
- **THEN** a operação é recusada

#### Scenario: Membro bloqueado tenta relatar
- **WHEN** um membro bloqueado envia um relato
- **THEN** o envio é recusado

### Requirement: Auditoria de ações
Toda ação do painel que altera dados MUST gerar um registro de auditoria com autor, ação, objeto, valores antes e depois e data; a tela de auditoria SHALL ser visível apenas para `admin`.

#### Scenario: Ação gera registro
- **WHEN** um operador executa qualquer ação de alteração no painel
- **THEN** existe um registro de auditoria correspondente com autor e valores antes/depois
