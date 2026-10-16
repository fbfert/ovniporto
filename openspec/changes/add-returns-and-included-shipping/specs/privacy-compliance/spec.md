# Spec Delta

## MODIFIED Requirements

### Requirement: Conteúdo da exportação
A exportação "Baixar meus dados" SHALL conter, em JSON legível: perfil, relatos com fotos em links assinados, pedidos, pedidos de devolução, inscrições e consentimentos com datas.

#### Scenario: Exportação completa
- **WHEN** um membro com relato, pedido, pedido de devolução e inscrição pede a exportação
- **THEN** o JSON enviado contém as seis seções com os dados desse membro e de mais ninguém
