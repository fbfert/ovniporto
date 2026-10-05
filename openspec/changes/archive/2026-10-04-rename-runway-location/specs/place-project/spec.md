# Spec Delta

## MODIFIED Requirements

### Requirement: Lugar descrito como projeto
A página `/o-lugar` MUST descrever o lugar físico como planejado (meta 2028) e MUST NOT usar linguagem, horários, preços de entrada ou chamadas que indiquem que ele já existe ou está aberto ao público.

#### Scenario: Selo de meta
- **WHEN** o visitante abre `/o-lugar`
- **THEN** a capa mostra o selo "meta 2028" e a referência "Na Localidade Pedras Brancas"

#### Scenario: Nenhum espaço aparece como aberto antes da hora
- **WHEN** nenhum espaço tem status `open`
- **THEN** nenhum cartão de espaço exibe o selo de aberto e todos mostram o status de planejamento ou obra
