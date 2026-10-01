# Design

## Context

Ver proposal.md. O `SeoHead` básico vem de `public-layout`; aqui ele ganha dados por página e imagens dinâmicas.

## Goals / Non-Goals

**Goals:** prévias corretas sem JavaScript; zero cookies de métrica.

**Non-Goals:** anúncios, pixels de redes sociais, Google Analytics.

## Decisions

- **Gerador de OG atrás de interface** (`OgImageRenderer`) no Domain; implementação inicial com biblioteca de imagem no servidor (sem navegador headless, mais leve na VPS). Cache em disco por chave de conteúdo + versão; eventos de edição apagam a entrada.
- **Sitemap gerado por comando agendado** e servido estático, a partir das mesmas consultas "publicados" de cada módulo.
- **JSON-LD montado no servidor** a partir dos Resources públicos, sem campos privados.
- **Umami self-hosted** no compose; script incluído só quando `APP_ENV=production`; eventos disparados por um wrapper tipado no front.
- **Privacidade:** nenhuma imagem OG expõe apelido de relato não aprovado nem dados do autor além do apelido.

## Risks / Trade-offs

- [Cache de prévia do WhatsApp guarda imagem antiga] → URL da imagem inclui hash da versão.
