# Spec Delta

## MODIFIED Requirements

### Requirement: Acesso por papel
O painel `/painel` SHALL ser restrito a papéis administrativos: `admin` acessa tudo, inclusive as Coordenadas; `moderator` só relatos e membros; `store` só pedidos e produtos. Membros comuns e visitantes MUST ser recusados.

#### Scenario: Moderador tenta abrir pedidos
- **WHEN** um `moderator` acessa `/painel/pedidos`
- **THEN** recebe 403

#### Scenario: Loja tenta abrir relatos
- **WHEN** um `store` acessa `/painel/relatos`
- **THEN** recebe 403

#### Scenario: Membro comum
- **WHEN** um `member` acessa `/painel`
- **THEN** recebe 403

#### Scenario: Só o admin abre as Coordenadas
- **WHEN** um `moderator` ou `store` acessa `/painel/coordenadas`
- **THEN** recebe 403
