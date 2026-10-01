# Design

## Context

Repositório novo. Stack fixada pelo CLAUDE.md (Laravel + Inertia + React/TS + SSR, Tailwind 4, Framer Motion). O Laravel instalado é a 13 (o CLAUDE.md pede "11+"). O Breeze não existe mais nas versões atuais, então a integração Inertia/React é montada à mão, sem kit de autenticação por senha (o login será só Google, em outra change).

## Goals / Non-Goals

**Goals:**
- Base técnica que todas as próximas changes reutilizam sem refatoração.
- Uma home que passe na revisão visual do Prompt R e no teste "isso parece template?".
- Um único momento de movimento memorável (a abdução) e o resto contido.

**Non-Goals:**
- Login, relatos, loja, painel (changes próprias em `openspec/changes/add-*`).
- Imagens reais: só placeholders marcados como "conceito".

## Decisions

1. **Motion (pacote `motion`, sucessor do Framer Motion) importado de `motion/react`.** Mesmo autor e API; o pacote `framer-motion` virou alias. GSAP descartado para não ter dois motores de animação.
2. **Movimento guiado pelas skills `frontend-design` (Anthropic) e `animate` (Emil Kowalski).** Curvas próprias como tokens (`--ease-out: cubic-bezier(0.23,1,0.32,1)`, `--ease-in-out: cubic-bezier(0.77,0,0.175,1)`), interface abaixo de 300 ms, nunca `scale(0)`, `:active` com `scale(0.97)`, hover só em `(hover:hover) and (pointer:fine)`, e `prefers-reduced-motion` em todo componente animado.
3. **A abdução é dirigida pelo scroll (`useScroll` + `useTransform` do Motion), não por tempo.** O usuário controla o momento, e o efeito é reversível e interrompível. A capa fica presa (sticky) por cerca de 1,6 viewport; o SSR entrega o quadro inicial estático, então não há salto de hidratação.
4. **Starfield em `<canvas>` com 3 camadas, pausado por IntersectionObserver e `visibilitychange`.** Cintilação limitada a ~30 fps, parallax pelo scroll (máx. 12 px), estrela cadente a cada 8–15 s. Só monta no cliente.
5. **Selo provisório em SVG inline (componente React)** com texto em caminho circular usando a própria Unbounded. Quando o adesivo real chegar em `public/brand/seal.svg`, o componente passa a usar o arquivo.
6. **Lenis para scroll suave**, desligado com reduced-motion e em dispositivos de toque (rolagem nativa é melhor no celular).
7. **Clean Architecture leve:** `Domain/<Modulo>/Contracts` (interfaces de repositório) e `Domain/<Modulo>/Data` (readonly classes), `Application/<Modulo>/UseCases`, `Infrastructure/Persistence/Eloquent` com os repositórios. Os models Eloquent ficam em `app/Models`, padrão do Laravel, e só a Infrastructure os toca.
8. **Strings de interface em `resources/js/i18n/pt-BR.ts`**, conteúdo editável em `content_blocks` (chave/valor) com seed.
9. **Rotas futuras com `ComingSoon`.** Os links do menu nunca levam a 404; cada página mostra em que fase do plano entra.
10. **SQLite local, MySQL em Docker/produção.** As migrations são compatíveis com os dois.

## Risks / Trade-offs

- [A capa presa por scroll pode irritar quem quer chegar rápido ao conteúdo] → no máximo 1,6 viewport, com "Role para explorar"; em reduced-motion a capa tem altura normal.
- [Canvas e animações pesam no celular] → pausa fora da viewport, menos estrelas no mobile, só `transform`/`opacity`, nenhum efeito de hover no toque.
- [Fontes do Google atrasam o LCP] → `preconnect`, `display=swap`, e a capa usa o selo em SVG (sem imagem raster no LCP).
- [Laravel 13 em vez de 11] → compatível com a exigência "11+".

## Migration Plan

Primeira entrega; não há o que migrar. `make up` sobe os containers; `php artisan migrate --seed` popula os textos e espaços.

11. **PHP 8.4 (não 8.3).** O `composer.lock` do Laravel 13 exige Symfony 8.1 e Pest 5, que pedem PHP >= 8.4.1. A imagem Docker usa `ARG PHP_VERSION=8.4`.
