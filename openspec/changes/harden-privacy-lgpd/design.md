# Design

## Context

Ver proposal.md. Depende de `member-accounts`, `sighting-submission`, `checkout` e `region-directory` já implementados.

## Goals / Non-Goals

**Goals:** cada garantia com ao menos um teste automatizado; consentimentos rastreáveis.

**Non-Goals:** redação jurídica final (encarregado); DPO externo.

## Decisions

- **Módulo Privacy com porta `ConsentLedger`** chamada em cada ponto de consentimento (boas-vindas, relato, Avise-me, parceiro). Versão dos termos em settings; middleware compara a última versão aceita.
- **Exclusão como orquestração** no Privacy, chamando portas de cada módulo (Sightings apaga, Orders anonimiza e define `retention_until = paid_at + 5 anos`). Comando agendado diário apaga vencidos.
- **Exportação** montada por "providers" de cada módulo que implementam uma interface comum; o job junta em JSON e envia por e-mail com links assinados.
- **Inspeção de metadados no teste** lendo os bytes dos arquivos salvos (EXIF/XMP/IPTC), não só a API da biblioteca.
- **Seção "O que fazemos na prática"** gerada de uma lista de garantias no código, para não divergir da implementação.
- **logrotate** no container web com 26 rotações semanais.
- **Versão dos termos = data "atualizado em" dos termos** (bloco `terms_updated_at`, preenchido no painel ao publicar o texto final). Enquanto não houver data, a versão é `inicial`; membros que aceitaram antes do registro de consentimentos entram no registro com essa versão.
- **Fotos na exportação:** aprovadas com o link público; pendentes com o link da página do relato, que emite na hora as URLs assinadas de 10 min para o autor logado. A regra dos 10 min não ganha exceção.
- **Consentimentos na exclusão de conta** ficam anonimizados: mantêm tipo, versão e data; perdem IP, navegador, e-mail e o vínculo com o membro.
- **Fotos sem metadados desde o envio:** o servidor recodifica a foto já no upload (antes de gravar), então nem o original temporário guarda EXIF/XMP/IPTC; arquivo que o GD não decodifica é recusado no envio.
- **Fotos pendentes** abrem para moderador e admin (não para o papel da loja), além do autor.

## Risks / Trade-offs

- [Prazo fiscal de 5 anos pode mudar] → valor em configuração; o encarregado confirma.
