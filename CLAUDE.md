# OVNIPORTO Lages — contexto do projeto

## O que é
Site real de uma marca e comunidade de Lages, SC, inspirada no Ovnipuerto de Cachi (Argentina).
Hoje: comunidade, "Livro de avistamentos" (relatos com mapa), loja de produtos.
Futuro: uma "pista de pouso" física na Localidade Pedras Brancas (Lages, SC), meta 2028.
Tom: astroturismo de verdade com uma camada de relato e humor. O relato do carro amarelo (o Lada Niva do Julean) é contado como relato ("segundo contam..."), nunca como documento.
Vocabulário: o site não usa a palavra "lenda" nos próprios textos. A página é "A origem" (/origem) e a história do carro é "o relato". A palavra só aparece nos dados históricos de Cachi e do Atlas, onde descreve a lenda local desses lugares.
O lugar físico ainda NÃO existe: o site nunca fala dele como se já funcionasse.
Nenhuma arrecadação de dinheiro para a obra antes de existir orçamento: /apoie fica em modo "em planejamento".

## Stack (não mudar)
- Laravel 11+ (PHP 8.3), Inertia.js, React 18+ com TypeScript, SSR ativo.
- Tailwind CSS 4 + CSS custom properties para tokens. Framer Motion para animação.
- MySQL 8, Redis (filas e cache), Laravel Horizon para workers.
- Mapa: Leaflet + OpenStreetMap (sem Google Maps).
- Login: Laravel Socialite (Google) somente.
- Pagamento: PayPal Complete Payments (cartão + Pix). Dados de cartão nunca passam pelo nosso servidor.
- Frete: Melhor Envio (cotação e etiqueta).
- E-mail: SMTP transacional via Laravel Mail, filas.
- Docker: containers web (nginx), app (php-fpm), ssr (node), worker (horizon), mysql, redis.
- Domínio: ovniporto.tars.art.br. Dados em VPS no Brasil.

## Arquitetura
- Clean Architecture dentro do Laravel:
  - app/Domain/<Modulo>/ (Entities, ValueObjects, Repositories interfaces, Services de regra)
  - app/Application/<Modulo>/ (UseCases, DTOs)
  - app/Infrastructure/ (Eloquent repositories, integrações externas: Google, PayPal, MelhorEnvio, Mail, Storage)
  - app/Http/ (Controllers finos, FormRequests, Resources, Middleware)
- Módulos: Members, Sightings, Map, Catalog, Orders, Payments, Shipping, Place (espaços e obra), Campaign, Region, Privacy, Content.
- Toda integração externa fica atrás de uma interface no Domain e é trocável.
- Controllers só validam, chamam um UseCase e retornam Inertia::render ou JSON.
- Testes: Pest. Todo UseCase tem teste de unidade; toda rota pública tem teste de feature.

## Código
- Código, comentários, nomes de variáveis, funções, classes, commits e migrations em INGLÊS.
- Textos de interface em português do Brasil, centralizados em resources/js/i18n/pt-BR.ts.
- Clean Code: funções pequenas, nomes claros, sem duplicação. Sem "any" no TypeScript.
- Componentes React em resources/js/Components/<Area>/<Name>.tsx, páginas em resources/js/Pages/.
- Commits em inglês, imperativo, curtos: "Add sighting submission wizard".

## Identidade visual (fonte da verdade)
Cores (tiradas do adesivo impresso):
- --color-moonlight: #F4F5E8 (fundo principal claro, texto sobre escuro)
- --color-night: #061121 (regiões escuras, texto sobre claro)
- --color-night-blue: #142644 (cartões em regiões escuras)
- --color-horizon: #494383 (lilás: faixas, etiquetas, títulos de destaque)
- --color-beam: #54C933 (verde do feixe: botão principal, luzes de óvni)
- --color-beam-glow: #ADDBA1 (verde-luz: brilhos, hover suave)
- --color-car: #FCB802 (amarelo do carro: loja, preços, campanha; único amarelo forte)
- Tons de apoio derivados por opacidade, nunca novas cores.
Base clara (moonlight) como a página Ovni Porto do Saltopia, com regiões escuras (night) de noite.
Tipografia (Google Fonts):
- Display: "Unbounded" (largo e geométrico, como o OVNIPORTO do adesivo), pesos 700/800, uppercase, tracking +0.04em.
- Cursiva: "Caveat" 600, nos sobretítulos ("Bem-vindo ao", "Abra o apetite").
- Texto: "Figtree" 400/500/600.
Elementos de assinatura (do Saltopia, reinterpretados):
- Menu em pílula flutuante; selo redondo (o adesivo) na capa; sobretítulo cursivo + título display;
- polaroids com fita adesiva e leve rotação; faixa corrida com bandeirinhas; cartões-ingresso com picote;
- blocos numerados 01/02/03; "Mande um postal"; fecho "Guardei um lugar pra você."
Luz: nas regiões escuras, estrelas sutis e brilho verde do feixe. Nada de branco estourado.
Movimento: entradas suaves ao rolar (Framer Motion), parallax leve nas estrelas, marquee contínuo,
polaroids que endireitam no hover. Tudo respeita prefers-reduced-motion.
Acessibilidade: contraste AA mínimo; foco visível em verde; alvos de toque ≥ 44px; alt em toda imagem.
Performance: LCP < 2,5 s no 4G; imagens em AVIF/WebP com srcset; fontes com font-display: swap; SSR em toda página pública.
Mobile first: tudo é desenhado primeiro para 390px de largura.

## Privacidade (regras fixas)
- Relato: fotos opcionais; local e hora do EXIF lidos SÓ no navegador para sugerir; arquivo salvo sem EXIF.
- Ponto no mapa é escolhido/confirmado pela pessoa; consentimento explícito por relato; só apelido público.
- Fotos pendentes nunca ficam em URL pública; servidas por URL assinada.
- Minha conta: baixar meus dados e excluir conta. Sem cookies de terceiros. Métrica sem cookie (Umami self-hosted).

## Conteúdo ainda inexistente (usar placeholder marcado, nunca inventar)
- O relato do carro amarelo: o texto enviado pelo Julean está em /origem/relato (bloco `legend_body`, editável no painel); a home e /origem mostram só a abertura e levam para lá. Se o bloco ficar vazio, volta o aviso "Ainda estamos capturando o relato do Julean que foi abduzido." com o Avise-me.
- A origem (/origem, /origem/cachi, /origem/atlas): conteúdo documentado em resources/content/origin/*.json. Fotos de terceiros só com licença aberta e crédito visível; mapas de localização sempre identificados como mapa; cada informação com o tipo de fonte ou o grau de confiança (A–F) do dossiê.
- Fotos reais do terreno, mapa 3D, ilustrações: placeholders com o aviso "conceito".
- Toda ilustração leva selo visível: "conceito" quando mostra como o OVNIPORTO vai ficar; "ilustração" quando reconstitui um relato, a origem ou um lugar do Atlas. Fotos de terceiros sem licença servem só de referência para ilustrar, nunca são publicadas.
- Parceiros da região: lista vazia com estado vazio bonito.
- Orçamento da obra: "em planejamento".

## Fluxo de trabalho (OpenSpec)
- Toda mudança de comportamento nasce como uma change em `openspec/changes/<nome>/` (proposal, specs, design, tasks).
- Comandos: `/opsx:explore`, `/opsx:propose`, `/opsx:apply`, `/opsx:archive`. CLI: `openspec list`, `openspec validate --strict`.
- O plano completo em 26 prompts está em `docs/ovniporto-prompts-construcao-site.md`; o roadmap por fase está em `openspec/changes/`.
- Manual do painel (`/painel/manual`, conteúdo em `resources/content/manual/*.json`): toda mudança que toca o painel (rotas, telas, ações, e-mails, papéis) atualiza o capítulo da área e o `reviewedAt` no mesmo commit, escrito a partir do código real. O `PanelManualTest` falha se uma rota ou tela do painel ficar sem capítulo; o hook `.claude/hooks/manual-reminder.mjs` lembra qual capítulo revisar a cada edição e antes de encerrar.

## Skills de design e animação instaladas (.claude/skills)
Antes de criar ou mexer em UI, carregue as skills de design/animação listadas em `.claude/skills/README.md`.
A regra anti-slop vale para tudo: nada de gradiente roxo genérico, nada de cartões com ícone + título + texto em grade 3x,
nada de "glassmorphism" sem motivo, nada de emoji como ícone. Cada seção tem um motivo e um detalhe que surpreenda.
