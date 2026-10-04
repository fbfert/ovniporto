# OVNIPORTO Lages

> A pista de pouso do planalto. Comunidade, Livro de avistamentos e lembranças de uma futura pista de pouso para discos voadores ao lado da Hospedaria Vila das Pedras, em Lages, SC.

Laravel 13 · Inertia v3 · React 19 · TypeScript · SSR · Tailwind CSS 4 · Motion (Framer Motion) · Lenis · Pest

---

## O que já está pronto

| Área | Estado |
| --- | --- |
| Fundação (Laravel + Inertia + React + SSR, Clean Architecture, Docker) | pronto |
| Design system (tokens, botões, polaroid, ingresso, faixa, selo, céu estrelado) | pronto (`/dev/styleguide`, só local) |
| Layout público (menu em pílula, menu mobile, rodapé, SEO, 404/500) | pronto |
| Home com as 10 seções e a **abdução guiada pelo scroll** | pronto |
| Avise-me da campanha (consentimento + double opt-in) | pronto |
| Demais páginas do menu | página honesta "em construção pela torre" |
| Fases 2 a 7 do plano | especificadas em `openspec/changes/add-*` |

### O momento-assinatura

A capa é uma noite na Serra Catarinense: céu em três camadas de estrelas, névoa lilás no horizonte, araucárias desenhadas por código e o carro amarelo estacionado. Ao rolar, o selo sobe, o disco voador desce pelo feixe que já estava lá, a luz verde abre e o carro é abduzido. No fim, "Mais um pro Livro de avistamentos." Rolando de volta, tudo se desfaz. Com `prefers-reduced-motion`, a capa vira uma cena parada.

---

## Como subir

### Com Docker (recomendado)

```bash
cp .env.example .env
make up            # web (nginx), app (php-fpm), ssr (node), worker, mysql, redis
make migrate       # migrations + textos iniciais, os 9 espaços e o adesivo
make seed-demo     # opcional: 12 relatos e 3 parceiros [EXEMPLO] (só local)
```

Abra http://localhost:8080.

### Sem Docker (PHP 8.3+, Composer, Node 22+)

```bash
composer install && npm install
cp .env.example .env    # troque DB_CONNECTION para sqlite e crie database/database.sqlite
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan dev:seed-demo    # opcional
npm run build                # gera o cliente e o bundle SSR
node bootstrap/ssr/ssr.js &  # servidor SSR (porta 13714)
php artisan serve
```

Para desenvolver com hot reload: `npm run dev` + `php artisan serve` (o SSR cai para renderização no cliente se o servidor SSR não estiver no ar).

---

## Testes e qualidade

```bash
make test        # Pest (unidade + feature)
make lint        # Pint, Larastan nível 6, ESLint, tsc e Prettier
```

Sem Docker: `php artisan test`, `composer lint` e `npm run lint`.

### Ponta a ponta (Playwright)

```bash
npx playwright install chromium   # uma vez
make e2e                          # ou npm run e2e (Windows sem make)
npm run e2e:update                # regrava as capturas de referência da home
```

- Roda contra os assets compilados (`npm run build:client`) no servidor embutido do PHP, na porta 8091, com um banco SQLite próprio (`database/e2e.sqlite`, recriado a cada execução com os seeds base, o conteúdo de demonstração e os membros de `E2eSeeder`). O banco de desenvolvimento não é tocado.
- O login com Google é substituído pela rota local `/dev/entrar-como/{id}`; pagamento e frete usam os provedores simulados (sem credenciais).
- Cobre os fluxos críticos, teclado, movimento reduzido, modo offline, axe-core nas páginas principais e regressão visual da home em 390 e 1440 px (tolerância de 0,5%).
- As capturas de referência ficam em `e2e/*-snapshots/` com o sistema no nome do arquivo: cada sistema operacional gera e versiona as suas.
- Relatório HTML de cada execução: `storage/e2e-report/` (`npx playwright show-report storage/e2e-report`).

---

## Estrutura

```
app/
  Domain/<Modulo>/          regras e contratos (interfaces), sem framework
    Contracts/              ex.: SightingReadRepository, WaitlistNotifier
    Data/                   objetos imutáveis (SightingCard, ProductCard...)
  Application/<Modulo>/
    UseCases/               GetHomeContent, GetHomeData, SubscribeToWaitlist...
  Infrastructure/
    Persistence/Eloquent/   implementações dos contratos
    Mail/                   envio de e-mails (sempre em fila)
    Brand/                  gerador de imagens da marca (OG, céus de demonstração)
  Http/                     controllers finos, FormRequests
  Providers/DomainServiceProvider.php   liga cada contrato à sua implementação
resources/
  css/tokens.css            as 7 cores, 3 fontes e curvas de movimento
  js/
    Components/Ui           design system
    Components/Scene        céu, araucárias, carro, disco voador (SVG/canvas)
    Components/Home         as seções da home
    Components/Layout       header, menu mobile, footer, SEO, scroll suave
    Pages/                  páginas Inertia
    i18n/pt-BR.ts           todos os textos de interface
openspec/                   specs e changes (fluxo spec-driven)
.claude/skills/             skills de design e animação (ver README da pasta)
```

### Módulos

Members · Sightings · Map · Catalog · Orders · Payments · Shipping · Place · Campaign · Region · Privacy · Content.
Hoje existem Content, Sightings (leitura), Catalog (leitura), Place, Region (leitura), Members (contagem) e Campaign (Avise-me).

---

## Fluxo de trabalho com OpenSpec

Cada mudança nasce como uma change em `openspec/changes/<nome>/` com `proposal.md`, `specs/`, `design.md` e `tasks.md`.

```bash
openspec list                     # changes em andamento
openspec show add-sightings-map   # ver uma change
openspec validate --all --strict  # validar tudo
```

No Claude Code: `/opsx:explore`, `/opsx:propose`, `/opsx:apply <change>` e `/opsx:archive <change>`.

Roadmap já especificado (na ordem do plano):
`add-content-pages` → `add-place-and-campaign` → `add-region-partners` → `add-members-google-login` → `add-sighting-submission` → `add-sightings-map` → `add-store-catalog` → `add-checkout-payments` → `add-operations-panel` → `add-seo-and-sharing` → `harden-privacy-lgpd` → `polish-performance-a11y` → `add-production-deploy`.

---

## Comandos úteis

| Comando | O que faz |
| --- | --- |
| `php artisan brand:seal "<adesivo.png>"` | Recorta o adesivo redondo em `public/brand/seal-*.{webp,avif}` e `seal.png` (centro e raio ajustáveis por `--cx --cy --r`) |
| `php artisan brand:og` | Gera `public/og/default.jpg` (1200×630) com o adesivo sobre a ilustração da capa (ou a cena desenhada, se ela não existir) |
| `php artisan dev:seed-demo` | Relatos e parceiros de demonstração (bloqueado fora do ambiente local) |
| `php artisan dev:clear-demo` | Remove tudo que é de demonstração |
| `php artisan concept:import "<pasta>"` | Gera AVIF/WebP/JPEG e o manifesto das ilustrações conceituais a partir dos originais (ver `ImportConceptIllustrations::FILES`); depois rode `php artisan brand:og` e `php artisan db:seed --class=PlaceSpaceSeeder` |

## Conteúdo que ainda não existe

Nada é inventado: a lenda mostra "aguardando conteúdo", as ilustrações conceituais (em `public/concept`) sempre levam a etiqueta "conceito", fotos do terreno aparecem como moldura tracejada e parceiros só aparecem com consentimento registrado. O selo é o adesivo impresso, recortado por `php artisan brand:seal "<adesivo.png>"` em `public/brand`.

---

Feito na serra por [Xiax](https://xiax.com.br). Guardei um lugar pra você.
