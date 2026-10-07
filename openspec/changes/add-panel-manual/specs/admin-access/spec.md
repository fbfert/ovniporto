# Spec Delta

## MODIFIED Requirements

### Requirement: Acesso por papel
O painel `/painel` SHALL ser restrito a papéis administrativos: `admin` acessa tudo; `moderator` só relatos, membros e o manual; `store` só pedidos, produtos e o manual. Membros comuns e visitantes MUST ser recusados.

#### Scenario: Moderador tenta abrir pedidos
- **WHEN** um `moderator` acessa `/painel/pedidos`
- **THEN** recebe 403

#### Scenario: Loja tenta abrir relatos
- **WHEN** um `store` acessa `/painel/relatos`
- **THEN** recebe 403

#### Scenario: Membro comum
- **WHEN** um `member` acessa `/painel`
- **THEN** recebe 403

#### Scenario: Todo papel administrativo abre o manual
- **WHEN** um `admin`, `moderator` ou `store` acessa `/painel/manual`
- **THEN** a página abre

#### Scenario: Membro comum não abre o manual
- **WHEN** um `member` acessa `/painel/manual`
- **THEN** recebe 403
