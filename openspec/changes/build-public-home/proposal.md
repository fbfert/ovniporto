# Proposal

## Why

O OVNIPORTO ainda não tem presença na web: a comunidade, o Livro de avistamentos e a loja dependem de um site que seja a primeira impressão da marca. A home precisa impressionar nos primeiros 2 segundos e parecer "um cartaz de cinema de uma noite na serra", não um template, e toda a base técnica (Laravel + Inertia + React com SSR) precisa existir antes de qualquer outro módulo.

## What Changes

- Fundação do projeto: Laravel 13 + Inertia + React 19 + TypeScript com SSR, Tailwind CSS 4 com os tokens da marca, fontes Unbounded/Caveat/Figtree, Clean Architecture (Domain/Application/Infrastructure) com o módulo Content como modelo, Docker Compose, Makefile e ferramentas de qualidade (Pest, Pint, Larastan, ESLint, Prettier).
- Design system com os elementos de assinatura: botão em pílula, sobretítulo cursivo, título display, seções clara/escura com borda ondulada, céu estrelado em canvas, polaroid com fita, cartão-ingresso com picote, faixa corrida com bandeirinhas, selo com brilho, revelação ao rolar. Styleguide em `/dev/styleguide` (só local).
- Layout público: menu em pílula flutuante que inverte em regiões escuras e some ao rolar para baixo, menu mobile em tela cheia aberto em círculo, rodapé noturno com disco voador que cruza, skip link, SEO (Open Graph/Twitter), páginas 404 e 500.
- Home completa em 10 seções (capa, boas-vindas, Livro de avistamentos, faixa, O lugar, lembranças, a lenda, região, comunidade e postal, rodapé) alimentada pelo UseCase GetHomeData, com estados vazios honestos para tudo que ainda não existe.
- Momento-assinatura: a capa mostra a serra à noite (araucárias no horizonte) e o carro amarelo estacionado; ao rolar, o feixe verde abre e abduz o carro. Tudo estático em `prefers-reduced-motion`.
- Cadastro "Avise-me da campanha" com consentimento explícito e confirmação por e-mail (double opt-in, em fila).
- Rotas ainda não construídas (`/mapa`, `/loja`, `/o-lugar` etc.) mostram uma página honesta "em construção pela torre" em vez de 404.
- Placeholders honestos (Prompt C): selo provisório gerado por código, imagens "conceito" marcadas, seeds de demonstração só em ambiente local.

## Capabilities

### New Capabilities
- `design-system`: tokens visuais, componentes de interface, comportamento de movimento e acessibilidade compartilhados por todas as páginas.
- `public-layout`: cabeçalho, navegação desktop/mobile, rodapé, SEO por página, páginas de erro e páginas "em construção".
- `home-page`: conteúdo, ordem, dados e estados vazios das 10 seções da home, incluindo o momento da abdução.
- `waitlist`: inscrição de e-mail "Avise-me da campanha" com consentimento e double opt-in.

### Modified Capabilities
- Nenhuma (primeira change do projeto).

## Impact

- Código novo em `app/Domain`, `app/Application`, `app/Infrastructure`, `app/Http`, `resources/js`, `resources/css`, `database/`, `routes/`, `tests/`.
- Dependências: inertiajs/inertia-laravel, tightenco/ziggy, pestphp/pest, larastan; react, @inertiajs/react, motion (Framer Motion), lenis, typescript.
- Infra: `docker-compose.yml` (web, app, ssr, worker, mysql, redis), `Makefile`, `.env.example`.
- Ambiente local roda com SQLite e fila `database`; produção com MySQL e Redis.
