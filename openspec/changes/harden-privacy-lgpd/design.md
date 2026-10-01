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

## Risks / Trade-offs

- [Prazo fiscal de 5 anos pode mudar] → valor em configuração; o encarregado confirma.
