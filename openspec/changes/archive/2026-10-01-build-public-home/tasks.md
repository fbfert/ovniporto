# Tasks

## 1. Fundação

- [x] 1.1 Integrar Inertia + React 19 + TypeScript + SSR no Laravel 13 (app.tsx, ssr.tsx, middleware HandleInertiaRequests, tsconfig) e verificar que `npm run build` gera os bundles de cliente e SSR
- [x] 1.2 Configurar Tailwind 4 com `@theme` lendo `resources/css/tokens.css`, `base.css` com reset, seleção beam e reduced-motion global, e as fontes com preconnect e `display=swap`; verificar no HTML renderizado
- [x] 1.3 Criar a estrutura Domain/Application/Infrastructure com o módulo Content (contrato de repositório, implementação Eloquent, UseCase GetHomeContent) e verificar com teste Pest de unidade
- [x] 1.4 Configurar Pest, Pint, Larastan nível 6, ESLint e Prettier; verificar que `composer lint` e `npm run lint` passam
- [x] 1.5 Criar `docker-compose.yml` (web, app, ssr, worker, mysql, redis), Dockerfiles, nginx, `.env.example` e Makefile (up, down, sh, test, lint, build, ssr-restart); verificar com `docker compose config`
- [x] 1.6 Escrever o README em português (subir, testar, estrutura, módulos, OpenSpec, skills) e verificar que os comandos documentados existem

## 2. Design system

- [x] 2.1 Criar tokens de movimento (curvas e durações) e os componentes Button, Eyebrow, Display, Badge, InfoCard, Section (com borda ondulada) e Reveal; verificar no styleguide
- [x] 2.2 Criar Starfield em canvas (3 camadas, cintilação, parallax, estrela cadente, pausa fora da viewport e com aba oculta, reduced-motion) e verificar no navegador que o rAF para fora da viewport
- [x] 2.3 Criar Seal (SVG inline provisório com brilho), Polaroid (fita, rotação, endireita no hover), TicketCard (picote), Marquee (bandeirinhas, pausa no hover, estático em reduced-motion) e ícones SVG; verificar no styleguide
- [x] 2.4 Criar Input, Checkbox, Toast acessíveis e `i18n/pt-BR.ts`; verificar navegação por teclado
- [x] 2.5 Criar a página `/dev/styleguide` só em ambiente local e verificar com teste de feature que responde 404 em produção

## 3. Layout público

- [x] 3.1 Criar PublicLayout com header em pílula (inverte sobre seções escuras, some ao descer), skip link e Lenis; verificar no navegador em 390, 768 e 1440 px
- [x] 3.2 Criar o menu mobile em tela cheia (clip-path circular, foco preso, Esc fecha) e verificar por teclado
- [x] 3.3 Criar o Footer (colunas, disco voador que cruza a cada 20 s, crédito Xiax) e o SeoHead; verificar metatags no HTML do servidor com teste de feature
- [x] 3.4 Criar páginas 404 e 500 temáticas e a página ComingSoon para as rotas futuras; verificar com testes de feature (404 e 200)

## 4. Home

- [x] 4.1 Criar migrations, models e seeds: content_blocks, place_spaces (9 espaços), sightings + sighting_photos, products, region_partners, members; verificar com `php artisan migrate:fresh --seed`
- [x] 4.2 Criar o UseCase GetHomeData (contadores, 4 relatos aprovados, 3 produtos, espaços, textos) com contratos no Domain e repositórios Eloquent; verificar com teste de unidade que relatos pendentes ficam de fora
- [x] 4.3 Construir a capa com a abdução guiada pelo scroll (serra, araucárias, carro, feixe, selo) e o estado estático em reduced-motion; verificar no navegador rolando para baixo e para cima
- [x] 4.4 Construir as seções 02 a 05 (boas-vindas com contador, Livro de polaroids com estado vazio, faixa, O lugar com trilha de espaços); verificar no navegador
- [x] 4.5 Construir as seções 06 a 09 (lembranças, lenda, região, comunidade e postal); verificar estados vazios no navegador
- [x] 4.6 Teste de feature: a home responde 200, contém "OVNIPORTO" e as seções na ordem, sem relatos pendentes

## 5. Avise-me

- [x] 5.1 Criar newsletter_subscribers, UseCase SubscribeToWaitlist (idempotente), rota com rate limit e e-mail de confirmação em fila com link assinado; verificar com testes de feature (válido, inválido, repetido, assinatura adulterada, 429)
- [x] 5.2 Ligar o formulário da home com Toast de retorno e verificar no navegador

## 6. Placeholders e verificação final

- [x] 6.1 Gerar placeholders "conceito" e a imagem OG padrão por código e o comando `dev:seed-demo` / `dev:clear-demo` bloqueado fora do ambiente local; verificar com teste
- [x] 6.2 Revisão visual (Prompt R) em 390 e 1440 px com capturas, corrigindo os problemas de "quebra" e "feio"
- [x] 6.3 Rodar `php artisan test`, lint e build; verificar que tudo passa
