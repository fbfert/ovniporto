# Design

## Context

Ver proposal.md. O `SeoHead` básico vem de `public-layout`; aqui ele ganha dados por página e imagens dinâmicas.

## Goals / Non-Goals

**Goals:** prévias corretas sem JavaScript; zero cookies de métrica.

**Non-Goals:** anúncios, pixels de redes sociais, Google Analytics.

## Decisions

- **Metadados nascem no servidor.** Cada controller entrega um objeto `Seo` (título, descrição, imagem, tipo, JSON-LD) junto da página Inertia. O `app.blade.php` imprime as tags no `<head>` a partir dele, então a prévia existe mesmo com o SSR fora do ar e os testes Pest conferem o HTML real. O `SeoHead` do React só lê essa prop para manter o `<head>` certo na navegação pelo cliente. Textos de SEO das páginas fixas ficam em `lang/pt_BR/seo.php`; títulos visíveis continuam no `pt-BR.ts`.
- **Gerador de OG atrás de interface** (`OgImageRenderer`) no Domain; implementação inicial com biblioteca de imagem no servidor (sem navegador headless, mais leve na VPS). Cache em disco por chave de conteúdo + versão; eventos de edição apagam a entrada.
- **Sitemap gerado por comando agendado** e servido estático, a partir das mesmas consultas "publicados" de cada módulo.
- **JSON-LD montado no servidor** a partir dos Resources públicos, sem campos privados.
- **Umami self-hosted** no compose; script incluído só quando `APP_ENV=production`; eventos disparados por um wrapper tipado no front.
- **Privacidade:** nenhuma imagem OG expõe apelido de relato não aprovado nem dados do autor além do apelido.

## Risks / Trade-offs

- [Cache de prévia do WhatsApp guarda imagem antiga] → URL da imagem inclui hash da versão.
