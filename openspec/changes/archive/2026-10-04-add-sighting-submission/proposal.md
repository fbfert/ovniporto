# Proposal

## Why

O "Livro de avistamentos" é o coração da comunidade. Quem relata está do lado de fora, à noite, com o celular numa mão; o envio precisa ser rápido e, acima de tudo, não pode vazar onde a pessoa mora pelos metadados das fotos.

## What Changes

- Assistente de 4 passos em `/relatar` (exige login): o que viu, fotos (até 3, opcionais), quando e onde (mapa, faixa ou hora exata, direção do olhar), revisar e enviar (Prompt 10).
- EXIF lido apenas no navegador para sugerir data/hora e ponto; o arquivo enviado é regravado sem metadados e o servidor remove metadados de novo (segunda camada).
- Consentimento explícito e separado por relato; apelido público por relato.
- Relato entra como `pending`; fotos pendentes ficam em armazenamento privado e só são servidas por URL assinada temporária.
- E-mails em fila: confirmação ao autor e aviso aos moderadores.

## Capabilities

### New Capabilities
- `sighting-submission`: envio de relatos pelo assistente, regras de validação, tratamento de fotos sem metadados e estado inicial de moderação.

### Modified Capabilities
<!-- Nenhuma. Depende de `member-accounts` (change `add-members-google-login`) e de `design-system`. -->

## Impact

- Módulo Sightings (tabelas de relatos e fotos), filas Redis/Horizon, disco privado de armazenamento, Leaflet + OSM, biblioteca de EXIF no cliente.
- Moderação e edição pós-ajuste vêm em `add-operations-panel`; exibição pública em `add-sightings-map`.
