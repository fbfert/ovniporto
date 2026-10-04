# Design

## Context

Ver proposal.md. Regras fixas de privacidade do CLAUDE.md: EXIF só no navegador, arquivo salvo sem EXIF, ponto escolhido pela pessoa, consentimento por relato, só apelido público, fotos pendentes por URL assinada.

## Goals / Non-Goals

**Goals:** duas camadas independentes de remoção de metadados; validação de domínio no servidor.

**Non-Goals:** moderação e edição após "ajuste pedido" (`add-operations-panel`); mapa público (`add-sightings-map`).

## Decisions

- **Camada 1 (cliente):** ler EXIF em memória para sugestão, redimensionar em canvas e exportar um novo blob (canvas não carrega metadados). Nada do EXIF é enviado como campo do formulário a menos que a pessoa confirme o ponto/data sugeridos.
- **Camada 2 (servidor):** job enfileirado reprocessa cada foto, descarta todos os perfis de metadados e gera variantes WebP. Processamento de imagem fica atrás de uma interface `ImageProcessor` no Domain; a implementação concreta fica em Infrastructure. HEIC é convertido no mesmo job.
- **Upload em duas etapas:** fotos sobem antes para área temporária privada e retornam id; o envio final referencia os ids. Temporários órfãos são apagados por rotina agendada (24 h).
- **Armazenamento atrás de interface** (`PhotoStorage`) com disco privado; URLs assinadas curtas para pendentes.
- **Validação de domínio no UseCase `SubmitSighting`:** consentimento, raio de 300 km (distância a partir de Lages), máximo 3 fotos, faixa xor hora exata.
- **E-mails** via interface de notificação, sempre em fila.

## Risks / Trade-offs

- [Navegador antigo sem canvas para HEIC] → aceita o arquivo original e confia na camada 2; o teste de servidor cobre esse caminho.
- [Ponto exato revela rotina da pessoa] → aviso fixo e ponto sempre escolhido manualmente.
