# OVNIPORTO Lages — Prompts para construção do site

Guia completo para construir o site com um assistente de código (Claude Code ou similar). São 26 prompts em 7 fases, na ordem em que devem ser executados. Cada prompt é autocontido, mas todos pressupõem o **Prompt 0 (contexto base)** salvo como `CLAUDE.md` na raiz do projeto.

**Como usar:**

1. Crie o repositório vazio e salve o Prompt 0 como `CLAUDE.md`.
2. Execute um prompt por vez, na ordem. Revise o resultado no navegador antes de passar ao próximo.
3. Depois de cada fase, rode o **Prompt de revisão visual (R)** no fim deste arquivo.
4. Conteúdo que ainda não existe (lenda, fotos, ilustrações, parceiros) entra como placeholder claramente marcado, nunca inventado.

**Regra de ouro do visual:** o site tem que parecer um cartaz de cinema de uma noite na serra, não um template. Toda seção precisa ter um motivo para existir e um detalhe que surpreenda (uma polaroid torta, uma faixa que corre, um brilho verde que pulsa).

---

## Prompt 0 — Contexto base (salvar como `CLAUDE.md`)

```markdown
# OVNIPORTO Lages — contexto do projeto

## O que é
Site real de uma marca e comunidade de Lages, SC, inspirada no Ovnipuerto de Cachi (Argentina).
Hoje: comunidade, "Livro de avistamentos" (relatos com mapa), loja de produtos.
Futuro: uma "pista de pouso" física na Localidade Pedras Brancas (Lages, SC), meta 2028.
Tom: astroturismo de verdade com uma camada de lenda e humor. A lenda é criada e o site não finge o contrário.
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
- A lenda: página mostra "aguardando conteúdo".
- Fotos reais do terreno, mapa 3D, ilustrações: placeholders com o aviso "conceito".
- Parceiros da região: lista vazia com estado vazio bonito.
- Orçamento da obra: "em planejamento".
```

---

# Fase 1 — Fundação

## Prompt 1 — Projeto, Docker e estrutura

```
Crie o projeto do OVNIPORTO do zero seguindo o CLAUDE.md.

1. Laravel 11 com Inertia + React + TypeScript + SSR (laravel/breeze --stack=react --typescript --ssr como base, depois remover auth por senha: só Socialite Google ficará).
2. Tailwind CSS 4 configurado com @theme lendo os tokens do CLAUDE.md como CSS custom properties em resources/css/tokens.css. Fontes Unbounded, Caveat e Figtree via Google Fonts com preconnect e font-display: swap.
3. Estrutura de pastas da Clean Architecture: app/Domain, app/Application, app/Infrastructure, com um módulo de exemplo "Content" completo (interface de repositório, implementação Eloquent, um UseCase GetHomeContent e seu teste Pest) para servir de modelo aos próximos.
4. Docker: docker-compose.yml com os serviços web (nginx), app (php-fpm 8.3), ssr (node 20 rodando o servidor SSR do Inertia), worker (Horizon), mysql 8, redis 7. Volumes nomeados para mysql e storage. Um .env.example completo. Um Makefile com alvos: up, down, sh, test, lint, build, ssr-restart.
5. Qualidade: Laravel Pint, PHPStan (larastan) nível 6, ESLint + Prettier para TS/React, Pest. Script "make lint" roda tudo.
6. Página inicial temporária em resources/js/Pages/Home.tsx renderizando "OVNIPORTO" com a fonte Unbounded sobre fundo --color-night, só para provar que SSR, Tailwind e as fontes funcionam.
7. README.md em português com: como subir, como rodar testes, estrutura de pastas e a lista de módulos.

Commits pequenos e em inglês. Ao final, rode make test e make lint e mostre a saída.
```

## Prompt 2 — Design system e componentes base

```
Construa o design system do OVNIPORTO em resources/js/Components/Ui, seguindo o CLAUDE.md. Cada componente em TypeScript, com props tipadas, variantes e um arquivo de histórias simples em resources/js/Pages/Dev/Styleguide.tsx (rota /dev/styleguide, só em ambiente local) mostrando todos os estados.

Componentes (todos responsivos, mobile first, com foco visível em --color-beam):

1. <Button variant="primary|secondary|ghost|car" size="sm|md|lg">: primary = fundo beam, texto night, bordas totalmente arredondadas (pílula), sombra suave verde no hover e leve scale 1.02; secondary = borda 1.5px moonlight/night conforme fundo; ghost = só texto com sublinhado animado; car = fundo car (amarelo) para loja e campanha. Estado loading com spinner. Suporta ícone à esquerda/direita.
2. <Eyebrow>: sobretítulo em Caveat 600, 1.5rem a 2rem, cor horizon em fundo claro e beam-glow em fundo escuro, com leve rotação -2deg.
3. <Display as="h1|h2|h3">: título em Unbounded 800 uppercase, tracking 0.04em, tamanhos fluidos com clamp(). Variante "outlined" (texto vazado com -webkit-text-stroke 1.5px) para os números 01/02/03.
4. <Section tone="light|dark" pattern="none|stars|grid">: espaçamento vertical fluido (clamp 4rem a 8rem), fundo moonlight ou night. Em tone=dark com pattern=stars, renderiza <Starfield/> atrás do conteúdo. A transição entre uma seção clara e uma escura tem uma borda ondulada suave (SVG mask) em vez de linha reta.
5. <Starfield density="low|medium" parallax>: canvas leve com 80 a 160 estrelas em 3 camadas, cintilação sutil, parallax de até 12px pelo scroll, uma "estrela cadente" a cada 8 a 15 s. Pausa quando fora da viewport. Desliga animação com prefers-reduced-motion. Nunca passa de 2% de CPU em idle.
6. <Polaroid src alt caption rotate={-4..4} tape="top|corner">: moldura branca com borda inferior maior, sombra difusa, fita adesiva semitransparente (CSS, sem imagem), rotação inicial e hover que endireita (rotate 0) com Framer Motion. A legenda em Caveat.
7. <TicketCard>: cartão com picote (bordas laterais com furos redondos via radial-gradient mask), linha tracejada separando a imagem do conteúdo, etiqueta no canto ("DA CASA", "PARA LEVAR"), preço em car.
8. <Marquee speed="slow|normal">: faixa corrida infinita, fundo horizon, texto moonlight em Unbounded 700, separadores com um pequeno disco voador SVG inline, borda superior com bandeirinhas triangulares (CSS). Pausa no hover. Em reduced-motion vira texto estático centralizado.
9. <InfoCard label value icon?>: cartão de informação curto (ONDE / CIDADE / RELATOS / PISTA) com label em Figtree 600 uppercase tracking 0.12em tamanho 0.7rem e valor em 1rem.
10. <Badge tone="beam|car|horizon|neutral">: etiquetas pequenas (fase 1, em planejamento, conceito, aguardando conteúdo).
11. <Seal size="sm|md|lg" glow>: o adesivo (public/brand/seal.png e seal.svg) com anel de brilho verde pulsando devagar (3 s) quando glow=true.
12. <Input>, <Textarea>, <Select>, <Checkbox>, <ChipGroup> (seleção única/múltipla em pílulas): tema claro e escuro, mensagens de erro em português, contador de caracteres opcional.
13. <Toast> e <Modal> acessíveis (foco preso, Esc fecha, aria corretos).
14. <Reveal>: wrapper Framer Motion que anima opacidade+translateY(16px) quando entra na viewport, uma vez, com stagger para filhos. Desligado em reduced-motion.

Também crie:
- resources/js/i18n/pt-BR.ts com todas as strings usadas.
- resources/css/base.css com reset, seleção de texto em beam, scrollbar discreta, e @media (prefers-reduced-motion) global.
- Um ícone set mínimo em SVG inline (disco voador, estrela, feixe, carro, pin, câmera, bússola, carimbo) em resources/js/Components/Icons.

Entregue com o styleguide funcionando e uma captura de tela de cada componente nos dois tons.
```

## Prompt 3 — Layout público e navegação

```
Crie o layout público do OVNIPORTO em resources/js/Layouts/PublicLayout.tsx seguindo o CLAUDE.md e usando os componentes da Fase 1.

Header:
- Menu em pílula flutuante, fixo no topo com 16px de margem, fundo moonlight a 85% com backdrop-blur 12px e borda 1px night/10. Em regiões escuras da página (detectar via IntersectionObserver das <Section tone="dark">) ele inverte para night/85 com texto moonlight, com transição de 300ms.
- Esquerda: links "Conheça a região", "O lugar", "Livro de avistamentos", "Loja" em Figtree 500. Centro: o <Seal size="sm"/> que leva à home. Direita: <Button variant="primary" size="sm">Entrar na comunidade</Button> (abre o modal de login Google) ou, logado, avatar com menu (Minha conta, Meus relatos, Meus pedidos, Painel se tiver papel, Sair).
- Mobile (< 1024px): só o selo e um botão de menu; abre um painel em tela cheia fundo night com estrelas, links grandes em Unbounded, o botão principal e os links de WhatsApp/Instagram. Animação de abertura com Framer Motion (clip-path circle a partir do botão).
- O header some suavemente ao rolar para baixo e volta ao rolar para cima.

Footer (Section tone="dark" pattern="stars"):
- Linha 1: <Eyebrow>Guardei um lugar pra você.</Eyebrow> grande, centralizado.
- Linha 2: três colunas: "OVNIPORTO" (selo + "A pista de pouso do planalto" + "Lages · SC"), "Navegue" (todos os links), "Comunidade" (WhatsApp, Instagram, e-mail de contato).
- Linha 3: "Localidade Pedras Brancas · Lages, SC" com link para o mapa, links Privacidade e Termos, e o texto "Feito na serra por Xiax" com link para xiax.com.br.
- Um pequeno disco voador SVG cruza o footer da esquerda para a direita a cada 20 s (desligado em reduced-motion).

Também:
- Página 404 com o carro amarelo (ilustração placeholder) e o texto "Esse ponto do céu ainda não foi mapeado." e botão para a home.
- Página 500 no mesmo espírito.
- Componente <SeoHead title description image> que preenche title, meta description, Open Graph e Twitter Card, com imagem padrão public/og/default.jpg (1200×630).
- Skip link "Ir para o conteúdo" acessível.

Teste manual em 390px, 768px e 1440px e corrija qualquer quebra.
```

---

# Fase 2 — Páginas públicas

## Prompt 4 — Home, parte 1: capa, boas-vindas e Livro de avistamentos

```
Construa a home do OVNIPORTO (resources/js/Pages/Home.tsx) com os componentes existentes. Esta é a página mais importante do site: o visual tem que impressionar nos primeiros 2 segundos. Siga a aba Wireframes do plano: 10 seções em ordem. Neste prompt, faça as seções 01 a 03.

Dados: crie o UseCase GetHomeData (módulo Content) que retorna: contadores (membros, relatos aprovados), os 4 relatos aprovados mais recentes, 3 produtos em destaque, os espaços do lugar com status e os textos editáveis da home (tabela content_blocks com chave/valor, seed inicial com os textos abaixo).

Seção 01 — Capa (Section tone="dark", 100svh em mobile, min 640px):
- Fundo: ilustração da pista à noite (public/concept/cover.jpg; enquanto não existir, um gradiente radial night → night-blue com <Starfield density="medium" parallax/>). Sobre ela, um véu escuro de 30% para legibilidade.
- Centro: <Seal size="lg" glow/> com entrada animada (scale 0.8→1, opacity, 900ms, easing custom). Abaixo, em Caveat, "Vigília grátis" dentro de uma pílula moonlight/15 com borda moonlight/30.
- Rodapé da capa: texto "Role para explorar" em Caveat com uma seta que pulsa devagar.
- Um feixe verde vertical muito sutil (gradiente beam a 0→12%→0 de opacidade) desce do topo até o selo, animando em loop de 6 s.
- Em reduced-motion: tudo estático, sem feixe.

Seção 02 — Boas-vindas (Section tone="light"):
- <Eyebrow>Bem-vindo ao</Eyebrow> + <Display as="h1">OVNIPORTO</Display> + subtítulo "A pista de pouso do planalto" em Figtree 600 cor horizon, tudo centralizado.
- Parágrafo editável (content_blocks.home_intro), máximo 3 frases. Seed: "No alto da Serra Catarinense, na Localidade Pedras Brancas, uma comunidade mantém o céu sob vigilância. O Livro de avistamentos está aberto, a loja já vende lembranças e a pista de pouso tem meta: 2028. Quase toda noite alguém jura ter visto algo."
- Quatro <InfoCard>: ONDE "Pedras Brancas, Lages · SC" (link para o mapa), CIDADE "Lages, Santa Catarina", RELATOS "{n} no Livro" (contador animado de 0 ao valor quando entra na viewport), PISTA "Meta 2028" com <Badge tone="car">em planejamento</Badge>.
- Entre a seção 02 e 03, a borda ondulada do <Section>.

Seção 03 — Livro de avistamentos (Section tone="dark" pattern="stars"):
- <Eyebrow>Alguém jurou ter visto</Eyebrow> + <Display as="h2">Livro de avistamentos</Display> + texto curto "Os últimos relatos aprovados pela torre de controle."
- Quatro <Polaroid> dos relatos recentes, espalhadas numa grade "bagunçada" (rotações -4, 3, -2, 4; deslocamentos verticais diferentes em desktop), cada uma com a primeira foto do relato (ou, sem foto, um "céu" gerado por CSS: gradiente night-blue com 3 pontos de luz e o tipo do relato em Caveat), legenda "{tipo} · {cidade/região} · {data curta}" e link para /relatos/{id}.
- Estado vazio (zero relatos): 4 polaroids com "Seu relato aqui" e a mesma composição, para a seção nunca parecer quebrada.
- À direita (desktop) ou abaixo (mobile): <Button variant="primary">Relatar avistamento</Button> e <Button variant="secondary">Ver o mapa</Button>.
- Stagger de entrada das polaroids (80ms entre cada).

Use <Reveal> em todos os blocos. Garanta SSR sem erro de hidratação (o contador animado e o Starfield só rodam no cliente). Teste de feature: a home responde 200 e contém "OVNIPORTO".
```

## Prompt 5 — Home, parte 2: faixa, O lugar, loja, lenda, região, comunidade

```
Continue a home do OVNIPORTO com as seções 04 a 10, seguindo a aba Wireframes e o CLAUDE.md.

Seção 04 — Faixa:
- <Marquee> com "A PISTA DE POUSO DO PLANALTO ✦ LAGES · SC ✦ META 2028 ✦ VIGÍLIA GRÁTIS ✦" repetindo. Bandeirinhas no topo. Levemente inclinada (-1.5deg) e mais larga que a tela para não mostrar as pontas.

Seção 05 — O lugar (Section tone="light"):
- <Eyebrow>Em planejamento</Eyebrow> + <Display as="h2">O lugar</Display> + uma frase editável (content_blocks.home_place).
- Dois painéis lado a lado (empilhados no mobile): "O terreno hoje" (galeria das fotos reais do terreno; estado vazio: moldura tracejada com o texto "Fotos do terreno em breve") e "Como vai ficar" (ilustração conceitual com <Badge tone="horizon">conceito</Badge> no canto; placeholder com gradiente e o ícone de disco voador se não houver imagem). Ao passar o mouse, um leve zoom 1.03.
- Abaixo, os espaços como uma trilha horizontal rolável (scroll-snap) de cartões numerados: <Display variant="outlined">01</Display> + nome + <Badge> da fase + status. Dados vêm da tabela place_spaces (seed com os 9 espaços e fases do plano).
- Botão <Button variant="secondary">Conhecer o projeto</Button> → /o-lugar.

Seção 06 — Lembranças (Section tone="dark"):
- <Eyebrow>Lembranças de</Eyebrow> + <Display as="h2">OVNIPORTO</Display> + "Adesivo na mão, pista no céu." (editável).
- Três <TicketCard> dos produtos em destaque (imagem, nome, preço em car, etiqueta). Estado vazio bonito.
- <Button variant="car">Ver a loja</Button>.

Seção 07 — A lenda (Section tone="light"):
- Layout assimétrico: à esquerda uma <Polaroid> grande da ilustração do carro amarelo (placeholder se não existir) com rotação -3; à direita <Eyebrow>De Cachi a Lages</Eyebrow> + <Display as="h3">A lenda do carro amarelo</Display> + 2 frases editáveis + <Button variant="ghost">Ler a lenda →</Button>. Enquanto a lenda estiver "aguardando conteúdo", o botão leva a /lenda e a seção mostra <Badge tone="neutral">aguardando conteúdo</Badge>.

Seção 08 — Conheça a região (Section tone="light", fundo moonlight um pouco mais escuro via night/4):
- <Eyebrow>Fique mais um dia</Eyebrow> + <Display as="h3">Conheça a região</Display>.
- Três cartões horizontais (foto quadrada + nome + tipo + cidade) dos parceiros em destaque (tabela region_partners). Estado vazio: "Pousadas, trilhas e produtores da serra em breve." com botão "Quero aparecer aqui" (mailto).
- <Button variant="secondary">Ver tudo</Button> → /regiao.

Seção 09 — Comunidade e postal (Section tone="dark" pattern="stars"):
- <Eyebrow>Entre na vigília</Eyebrow> + <Display as="h2">Comunidade</Display>.
- Linha de botões: <Button variant="primary">Entrar com Google</Button>, <Button variant="secondary" icon=whatsapp>WhatsApp</Button>, <Button variant="secondary" icon=instagram>Instagram</Button> (links de settings).
- Formulário "Avise-me da campanha": e-mail + botão; salva em newsletter_subscribers com consentimento e data; feedback em <Toast>; double opt-in por e-mail (fila).
- Cartão "Mande um postal": prévia em formato de postal (imagem OG com o selo, borda branca, carimbo "LAGES · SC" em Caveat) e botões "WhatsApp" (link wa.me com texto pronto) e "Copiar link".

Seção 10 — Rodapé: já existe no layout.

Finalize: Lighthouse mobile ≥ 90 em performance e acessibilidade na home; corrija o que faltar. Mostre capturas em 390px e 1440px.
```

## Prompt 6 — A lenda, FAQ, Privacidade e Termos, Comunidade

```
Crie as páginas de conteúdo do OVNIPORTO, todas com SSR, <SeoHead> e textos vindos de content_blocks (editáveis no painel):

/lenda — A lenda:
- Capa curta (Section dark): <Eyebrow>Como tudo começou</Eyebrow> + <Display as="h1">A lenda</Display>.
- Bloco 1 "A origem real" (light): texto fixo sobre a visita do Felipe ao Ovnipuerto de Cachi, com 2 polaroids placeholder e um resumo da história de Cachi (Werner Jaisli, 2008, estrelas de pedra, desaparecimento em 2013). Link "Saiba mais sobre Cachi" para um bloco expansível com a linha do tempo (dados fixos em um JSON em resources/js/data/cachi.ts).
- Bloco 2 "O carro amarelo" (dark): se content_blocks.legend_body estiver vazio, renderizar um estado "aguardando conteúdo" bonito: a <Polaroid> da ilustração do carro (placeholder) com legenda em Caveat "Essa história ainda está sendo escrita." e um botão "Me avise quando sair" que usa o mesmo cadastro de e-mail da home. Se houver conteúdo, renderizar o markdown com tipografia cuidada (parágrafos 1.125rem, 70ch, capitular na primeira letra em Unbounded cor car).
- Fecho: botão "Relatar avistamento".

/faq — Perguntas frequentes: lista de pares pergunta/resposta (tabela faqs, seed com 10 perguntas: o lugar existe?, é de graça?, como relatar?, posso ir à noite?, como chego?, vendem camiseta?, quanto demora a entrega?, meus dados ficam públicos?, posso apagar meu relato?, como apoiar?). Acordeão acessível, uma aberta por vez, com animação de altura.

/comunidade: regras de convivência (texto editável, seed com 5 regras curtas: respeito, sem dados de terceiros, sem fotos de pessoas sem autorização, sem spam, humor sim e mentira não), links grandes para WhatsApp e Instagram, e o formulário Avise-me.

/privacidade e /termos: páginas de texto longo com índice lateral fixo (desktop), tipografia de leitura, data de atualização. Conteúdo inicial = placeholder marcado "RASCUNHO — texto final será redigido pelo encarregado (Felipe)" com a estrutura de tópicos pronta: dados coletados, finalidades, bases legais, compartilhamento (PayPal, Melhor Envio, Google), retenção, direitos do titular, contato do encarregado, cookies (nenhum de terceiros), alterações.

Todas com testes de feature (200 e título presente). Nenhum texto jurídico inventado como se fosse final.
```

## Prompt 7 — O lugar, Apoie a pista, Diário da obra

```
Crie as três páginas do projeto físico do OVNIPORTO (módulo Place e Campaign):

Modelos e seeds:
- place_spaces: id, slug, name, role, description, phase (1-4), status (planning|building|open), sort_order, concept_image_path (nullable). Seed com os 9 espaços do plano: Pista de pouso, Área de vigília, Carro amarelo abduzido, Hangar (estacionamento), Museu ao ar livre (fase 1); Aduana interplanetária + Loja (2); Lanchonete (3); Torre de controle, Museu coberto (4).
- site_photos: fotos reais do terreno (path, caption, taken_at, sort_order).
- construction_posts: diário da obra (title, body markdown, cover_path, published_at, phase).
- campaign_settings: status (planning|open|closed), goal_amount (nullable), raised_amount, crowdfunding_url (nullable), store_share_percent (nullable). Seed: status=planning, tudo o mais nulo.

/o-lugar:
- Capa dark com a ilustração da vista geral (placeholder) e <Display as="h1">O lugar</Display>, <Eyebrow>Na Localidade Pedras Brancas</Eyebrow>, <Badge tone="car">meta 2028</Badge>.
- Bloco "Onde": mapa Leaflet estático (sem interação até clicar, para performance) centrado em -27.85495, -50.21841 com um marcador customizado (disco voador SVG) e o botão "Abrir no Google Maps".
- Bloco "Hoje e amanhã": lado a lado "O terreno hoje" (galeria de site_photos com lightbox acessível; vazio = estado tracejado) e "Como vai ficar" (ilustração conceitual + badge conceito). Slot reservado para o mapa 3D: um <Section> com título "Mapa 3D" e placeholder "Em produção pela arquiteta do projeto".
- Bloco "Os espaços, fase a fase": linha do tempo vertical em 4 fases; dentro de cada fase, os espaços como cartões numerados com nome, descrição, status badge e a ilustração do espaço quando houver. A fase 1 destacada em beam.
- Bloco "Regras do céu escuro": três regras (luz baixa e vermelha, caminhos acessíveis, QR em cada placa) com ícones.
- Fecho: "Avise-me quando a campanha abrir" (mesmo cadastro).

/apoie — Apoie a pista:
- Se campaign_settings.status = planning (caso atual): capa dark <Display as="h1">Apoie a pista</Display> + <Badge tone="car">orçamento em planejamento</Badge>; texto explicando com honestidade: a pista será construída em fases, o orçamento está sendo feito, nenhuma arrecadação abre antes disso; três cartões "Como vai funcionar" (crowdfunding, parte das vendas da loja, patrocínio local); três cartões "O que o apoiador recebe" (pedra com nome, produtos, vigília de inauguração); formulário Avise-me em destaque; botão secundário "Enquanto isso, leve um adesivo" → /loja.
- Se status = open: adicionar placar (barra de progresso animada em beam com valor e meta em car), botão "Apoiar no {plataforma}" para crowdfunding_url, muro de apoiadores (tabela supporters com consentimento de publicação), faixa de patrocinadores (sponsors). Implementar já, mas sem dados.

/obra — Diário da obra:
- Lista de construction_posts em ordem cronológica inversa, cada post com capa, título, fase e data; página individual /obra/{slug} com markdown renderizado e galeria. Estado vazio: "A obra ainda não começou. O primeiro post será o dia em que a primeira pedra for colocada." com a ilustração da pista.
- Feed RSS em /obra.rss.

Testes de feature para as três rotas e para o estado planning de /apoie (não pode renderizar placar nem link de pagamento).
```

## Prompt 8 — Conheça a região

```
Crie a página /regiao (módulo Region) do OVNIPORTO:

Modelo region_partners: name, slug, type (inn|attraction|producer|restaurant|other), short_description, city, address, lat, lng, phone, whatsapp, instagram, website, cover_path, gallery (json), is_featured, consent_given_at (obrigatório para publicar), sort_order, published_at.

Página:
- Capa light com <Eyebrow>Fique mais um dia</Eyebrow> + <Display as="h1">Conheça a região</Display> + texto "Pousadas, trilhas, vinhos e gente da serra em volta do OVNIPORTO."
- Filtro por tipo em <ChipGroup> (Todos, Pousadas, Passeios, Produtores, Comida) e busca por texto, sem recarregar a página (Inertia partial reload).
- Grade de cartões com foto, nome, tipo, cidade e distância aproximada até o OVNIPORTO (calculada no servidor por Haversine a partir de -27.85495, -50.21841, arredondada em km).
- Alternância "Lista | Mapa": o mapa Leaflet mostra todos os parceiros com o marcador do OVNIPORTO destacado em beam.
- Página /regiao/{slug}: capa, galeria, descrição, contatos como botões (WhatsApp, Instagram, site, ligar), mini-mapa e "Como chegar" (link Google Maps com origem no OVNIPORTO).
- Estado vazio quando não há parceiros publicados: ilustração placeholder, texto "A lista está sendo montada com as pousadas e produtores da região." e botão "Quero aparecer aqui" (mailto para o contato).
- Só publicar parceiros com consent_given_at preenchido (regra no Domain, com teste).
```

---

# Fase 3 — Membros e relatos

## Prompt 9 — Login com Google e Minha conta

```
Implemente o módulo Members do OVNIPORTO:

- Login exclusivamente com Google via Laravel Socialite. Tabela members: google_id, name, email, avatar_url, nickname (único, público), city (nullable), role (member|moderator|store|admin), terms_accepted_at, created_at. Sem senha.
- Fluxo: botão "Entrar na comunidade" abre um <Modal> com o selo, a frase "Entre com sua conta Google. Só pedimos nome, e-mail e foto." e o botão Google. Após o retorno, se for o primeiro acesso, mostrar a tela de boas-vindas (Pages/Members/Welcome.tsx) pedindo: apelido público (pré-sugerido a partir do primeiro nome, validação de unicidade ao digitar), cidade (opcional), e a aceitação dos termos (checkbox obrigatório com link). Só então a conta fica ativa.
- Página /conta (Minha conta): abas "Meus relatos" (lista com status: em análise, aprovado, ajuste pedido, rejeitado, com o motivo quando houver, e ações despublicar/excluir), "Meus pedidos" (lista com status e rastreio), "Meus dados" (editar apelido e cidade; ver nome/e-mail do Google sem editar), e "Privacidade" com dois botões: "Baixar meus dados" (gera um JSON com tudo que temos e envia por e-mail via fila) e "Excluir minha conta" (modal de confirmação digitando o apelido; apaga perfil, relatos e fotos; anonimiza pedidos mantendo o necessário fiscal; envia e-mail de confirmação).
- Middleware EnsureProfileCompleted redireciona para Welcome quem não completou o apelido.
- Policies: um membro só vê/edita o que é dele.
- Testes Pest: login cria membro, primeiro acesso exige apelido, exclusão anonimiza pedidos e apaga relatos.
- Visual: a tela Welcome é dark com estrelas, o campo de apelido grande, e uma prévia ao vivo de como o apelido aparece numa polaroid.
```

## Prompt 10 — Formulário de relato em 4 passos

```
Implemente o envio de relatos (módulo Sightings) do OVNIPORTO como um assistente de 4 passos em /relatar, exatamente como a aba Wireframes. Exige login. Mobile first: a pessoa está do lado de fora, à noite, com o celular numa mão.

Visual: tela inteira fundo night com estrelas discretas, barra de progresso de 4 segmentos em beam, um passo por tela, botão principal grande fixo no rodapé (56px de altura), tipografia 1.125rem, contraste alto, nada de branco puro. Transição entre passos com slide horizontal (Framer Motion). Rascunho salvo em localStorage a cada mudança e restaurado ao voltar.

Passo 1 — O que você viu?
- <ChipGroup> tipo: Luz, Objeto, Rastro, Outro (obrigatório).
- <Textarea> descrição, 20 a 1000 caracteres, contador.

Passo 2 — Fotos (até 3, opcionais):
- Input de arquivo com captura pela câmera no mobile. Prévia em miniaturas com remover.
- No navegador, antes de qualquer upload: ler EXIF (biblioteca exifr), extrair GPS e DateTimeOriginal se existirem, guardar em estado como "sugestão"; redimensionar a imagem para no máximo 2000px no maior lado com canvas; gerar um novo arquivo SEM metadados; é esse arquivo que sobe. Mostrar os avisos: "Se a foto tiver local e hora, usamos só para sugerir no próximo passo. O arquivo publicado vai sem esses dados." e "Sem rostos nem placas de carro."
- Upload direto para uma rota temporária (storage privado, pasta pending), devolvendo um id temporário. Limite 8 MB por arquivo, jpg/png/heic (converter heic no servidor via fila).
- Botão "Pular".

Passo 3 — Quando e onde?
- Data (padrão hoje), hora: toggle "Faixa" (Anoitecer / Noite / Madrugada em chips) ou "Hora exata" (input time). Se o EXIF trouxe data/hora, pré-preencher e marcar "sugerido pela foto".
- Mapa Leaflet em tela cheia dentro do passo, centrado em Lages (ou no ponto do EXIF, se houver, marcado como "ponto sugerido pela foto"). A pessoa toca para colocar/mover o marcador; botão "Usar minha localização atual" (geolocation, só com permissão, com explicação). Aviso fixo: "Marque de onde olhou o céu, não sua casa."
- Direção do olhar (opcional): <ChipGroup> N, NE, L, SE, S, SO, O, NO, com uma bússola SVG que gira conforme a escolha.

Passo 4 — Revisar e enviar:
- Resumo: tipo, descrição, miniaturas, data/hora, mini-mapa estático com o ponto, direção.
- Apelido público (pré-preenchido do perfil, editável só aqui para este relato).
- Checkbox obrigatório e separado: "Autorizo publicar este relato, as fotos e o ponto no mapa."
- Botão "Enviar para a torre".

Após enviar: tela de confirmação com animação do disco voador subindo, texto "Relato na torre de controle, em análise." e "Você recebe um e-mail quando for aprovado ou se precisar de ajuste.", botões "Ver meus relatos" e "Voltar ao início".

Servidor:
- Tabelas sightings (member_id, type, description, observed_date, observed_time_kind (range|exact), observed_time_range, observed_time, lat, lng, gaze_direction, public_nickname, consent_given_at, status (pending|approved|changes_requested|rejected), moderation_note, moderated_by, moderated_at, published_at) e sighting_photos (sighting_id, path, width, height, sort_order).
- UseCase SubmitSighting com validação de domínio (consentimento obrigatório, ponto dentro de um raio de 300 km de Lages, máximo 3 fotos).
- Job StripExifAndStorePhoto como segunda camada: mesmo recebendo o arquivo já limpo, o servidor reprocessa com Intervention Image removendo todo metadado e gerando variantes 400/800/1600 em WebP. Fotos de relatos pendentes ficam em disco privado e só são servidas por URL assinada temporária.
- E-mails em fila: confirmação ao autor; aviso aos moderadores.
- Testes: envio sem consentimento falha; ponto fora do raio falha; EXIF nunca permanece no arquivo salvo (teste lê o arquivo final e verifica ausência de GPS).
```

## Prompt 11 — Livro de avistamentos: mapa e página do relato

```
Implemente a parte pública dos relatos do OVNIPORTO:

/mapa — Livro de avistamentos:
- Capa curta dark: <Eyebrow>Céu sob vigilância</Eyebrow> + <Display as="h1">Livro de avistamentos</Display> + contador "{n} relatos aprovados".
- Mapa Leaflet ocupando a largura toda, altura 70svh, tiles escuros (CartoDB Dark Matter ou um estilo próprio via tile server OSM com filtro CSS), marcadores customizados: um ponto beam com halo pulsante; agrupamento (markercluster) com números em Unbounded. Clique abre um popup estilizado como mini-polaroid (foto, tipo, data, apelido, botão "Ver relato").
- Filtros em barra fixa sobre o mapa: período (Últimos 30 dias, 6 meses, 1 ano, Tudo), tipo (chips). Partial reload com Inertia; estado na URL.
- Abaixo do mapa, lista em grade de polaroids dos relatos filtrados, paginação "Carregar mais".
- Botão flutuante "Relatar" (beam) no canto inferior direito, só no mobile.
- Dados servidos por GET /api/sightings (JSON, cache 60 s, só aprovados, só campos públicos: id, type, lat, lng, date, nickname, thumb).

/relatos/{id}:
- Layout tipo "ficha da torre": à esquerda a galeria de fotos (lightbox), à direita um cartão night-blue com: tipo em Badge, data e hora (faixa ou exata), direção do olhar com a bússola, apelido, descrição com tipografia de leitura, mini-mapa com o ponto.
- Carimbo SVG "APROVADO PELA TORRE" em verde, leve rotação, com a data de publicação.
- Botão "Mande um postal": compartilhar no WhatsApp (texto: "Olha o que viram no céu de Lages: {url}") e copiar link. OG image dinâmica (rota /og/sightings/{id}.png gerada com Browsershot ou Intervention: a primeira foto em formato polaroid sobre fundo night com o selo e o tipo) com cache em disco.
- "Outros relatos perto daqui": 3 relatos num raio de 20 km.
- Se o relato não estiver aprovado: 404 para o público; o autor vê com uma faixa "Em análise".

Teste: relatos não aprovados não aparecem na API nem na página pública.
```

---

# Fase 4 — Loja

## Prompt 12 — Catálogo e página de produto

```
Implemente o catálogo da loja do OVNIPORTO (módulo Catalog):

Modelos: products (name, slug, description markdown, short_description, price_cents, compare_price_cents nullable, kind (stock|made_to_order), production_days, weight_grams, dimensions (json l/w/h cm), is_active, is_featured, label (nullable: "DA CASA", "PARA LEVAR"...), sort_order), product_variants (product_id, name ex. "P / Preta", sku, price_delta_cents, stock_qty nullable para stock, is_active), product_images (path, alt, sort_order). Seed: Adesivo OVNIPORTO (stock, 500 unidades, R$ 8), Camiseta (made_to_order, variantes P-GG, R$ 79), Caneca (made_to_order, R$ 49), Kit Abdução (made_to_order, R$ 189), todos is_active=false exceto o adesivo, para a loja abrir só com o que existe.

/loja:
- Capa light: <Eyebrow>Lembranças de</Eyebrow> + <Display as="h1">OVNIPORTO</Display> + "Tudo impresso depois do pedido, menos o adesivo, que já está pronto pra colar."
- Grade de <TicketCard> (2 colunas mobile, 3-4 desktop) com imagem, nome, preço em car (compare_price riscado quando houver), etiqueta, badge "pronta entrega" ou "feito sob pedido · {n} dias". Hover: a imagem troca para a segunda foto.
- Faixa <Marquee> no meio da página: "ADESIVO NA MÃO ✦ PISTA NO CÉU ✦".
- Bloco "Parte de cada venda vai virar pista": só aparece quando campaign_settings.store_share_percent não for nulo; hoje, oculto (teste).

/loja/{slug}:
- Galeria à esquerda (thumbs + imagem grande com zoom no hover), à direita: nome em Unbounded, preço grande em car, descrição curta, seletor de variantes em <ChipGroup> (desabilita variantes sem estoque), quantidade, botão <Button variant="car" size="lg">Adicionar ao carrinho</Button>, linha de prazo "Produção {n} dias + envio" ou "Pronta entrega", simulador de frete por CEP (chama a cotação do Melhor Envio, mostra opções e prazos), e um acordeão "Detalhes" com o markdown.
- Bloco "Combina com": 3 produtos relacionados.
- JSON-LD de Product para SEO.

Carrinho (estado no servidor, sessão para visitante e member_id quando logado): drawer lateral dark que abre ao adicionar, com itens, variantes, quantidades editáveis, subtotal, e botão "Finalizar compra". Ícone do carrinho no header com contador animado.
```

## Prompt 13 — Checkout com PayPal e frete

```
Implemente o checkout da loja do OVNIPORTO (módulos Orders, Payments, Shipping), seguindo o CLAUDE.md. Pagamento pelo PayPal Complete Payments (cartão e Pix) e frete pelo Melhor Envio. Os dados de cartão nunca tocam o nosso servidor.

Fluxo em /checkout (3 etapas numa página só, com resumo fixo à direita no desktop):
1. Identificação: se não logado, oferecer "Entrar com Google" ou continuar como visitante com nome e e-mail. Telefone (WhatsApp) obrigatório para contato de entrega. CPF obrigatório para emissão de nota (validar dígitos; armazenar criptografado com Laravel encrypted cast; mostrar por que pedimos).
2. Entrega: CEP com busca automática de endereço (ViaCEP), número, complemento. Cotação de frete no Melhor Envio com as opções (Correios PAC/SEDEX e transportadoras) mostrando preço e prazo; selecionar uma. Opção "Retirar em Lages" (combinar pelo WhatsApp) com frete zero.
3. Pagamento: botões do PayPal JS SDK (cartão e Pix) renderizados no cliente com client-id; o servidor cria a ordem no PayPal (POST /v2/checkout/orders) com o total; após aprovação, captura no servidor e confirma via webhook (PAYMENT.CAPTURE.COMPLETED) com validação de assinatura. Idempotência por paypal_order_id.

Modelos: orders (number legível tipo OVP-2026-000123, member_id nullable, customer json, shipping_address json, shipping_method, shipping_cents, subtotal_cents, total_cents, status: pending_payment|paid|in_production|shipped|delivered|canceled|refunded, paypal_order_id, paid_at, tracking_code, tracking_url, melhorenvio_order_id, notes), order_items (snapshot de produto/variante/preço), order_events (log de mudanças de status com ator).

Interfaces no Domain: PaymentGateway (createOrder, capture, verifyWebhook) e ShippingProvider (quote, createLabel, track); implementações PayPalGateway e MelhorEnvioProvider em Infrastructure; fakes para testes.

E-mails (fila, templates bonitos em dark com o selo, em português): pedido recebido, pagamento confirmado, em produção, enviado com rastreio, entregue. Todos com o fecho "Guardei um lugar pra você."

Página /pedido/{number}?token=...: status em linha do tempo (recebido → pago → produção → enviado → entregue) com ícones, itens, endereço, rastreio. Acesso por token assinado no e-mail ou pelo membro dono.

Regras: estoque do adesivo decrementa só após pagamento confirmado; pedido pendente por mais de 2 h é cancelado por um comando agendado; carrinho esvazia após pagamento. Valores sempre em centavos no servidor. Testes: cálculo de total com frete, webhook inválido rejeitado, captura idempotente, cancelamento automático.

Modo sandbox do PayPal e do Melhor Envio controlado por .env; nunca commitar credenciais.
```

---

# Fase 5 — Painel de operação

## Prompt 14 — Painel: base, papéis e dashboard

```
Crie o painel de operação do OVNIPORTO em /painel (rota protegida por papel: admin tudo; moderator só relatos e membros; store só pedidos e produtos). Layout próprio resources/js/Layouts/AdminLayout.tsx: barra lateral escura (night) com o selo pequeno, nome "Torre de controle" em Caveat, menu por área com contadores (relatos pendentes, pedidos pagos a produzir), e conteúdo claro. Visual sóbrio, denso e rápido; nada de animações decorativas aqui. Tabelas com ordenação, busca, paginação e filtros na URL.

Dashboard (/painel):
- Cartões: membros (total e últimos 7 dias), relatos (pendentes, aprovados no mês), pedidos (pagos no mês, a produzir, a enviar), faturamento do mês, e-mails de Avise-me.
- Metas de 6 meses: barra de progresso para cada meta configurada em settings (membros 300, WhatsApp 200 (campo manual), relatos 30, avise-me 150, pedidos 50) a partir da data de lançamento em settings.launch_date.
- Gráfico simples de linha (Recharts) de relatos e pedidos por semana nas últimas 12 semanas.
- Lista "Precisa de você": relatos pendentes há mais de 48 h, pedidos pagos há mais de 2 dias sem produção, pedidos sem rastreio há mais de 7 dias.

Membros (/painel/membros): lista com busca, papel, data; ações: alterar papel (só admin), bloquear (impede relatos e pedidos; registra motivo), atender pedido de exclusão.

Auditoria: tabela audit_logs (actor_id, action, subject_type, subject_id, before json, after json, created_at) preenchida por todo UseCase do painel. Tela /painel/auditoria só para admin.

Testes: moderator não acessa pedidos; store não acessa relatos; toda ação gera audit_log.
```

## Prompt 15 — Painel: moderação de relatos

```
Implemente a moderação de relatos em /painel/relatos no painel do OVNIPORTO:

- Fila com abas: Pendentes (padrão), Ajuste pedido, Aprovados, Rejeitados. Cada linha: miniatura, tipo, apelido, data observada, cidade aproximada (reverse geocode cacheado via Nominatim, com rate limit e atribuição), enviado há X.
- Tela do relato: fotos grandes (servidas por URL assinada), descrição, data/hora, direção, mapa com o ponto, dados do autor (nome real e e-mail só aqui, nunca públicos), histórico de moderação.
- Ações: Aprovar (publica, dispara e-mail ao autor, invalida cache da API e da home); Pedir ajuste (campo de mensagem obrigatório, e-mail ao autor com link para editar o relato; o autor edita e reenvia para a fila); Rejeitar (motivo obrigatório entre opções: foto com pessoa identificável, placa de carro, conteúdo ofensivo, não é um relato, outro + texto; e-mail ao autor); Despublicar (um aprovado volta a pendente com nota).
- Atalhos de teclado: A aprovar, R rejeitar, J/K navegar.
- Checklist visual antes de aprovar: "Sem rostos identificáveis", "Sem placas", "Ponto não parece residência" (marcação manual, só lembrete).
- Edição pelo autor em /relatar/{id}/editar reaproveita o assistente de 4 passos com os dados carregados.
- Tudo registrado em audit_logs. Testes: aprovar publica e envia e-mail; rejeitar exige motivo; relato em ajuste some do público.
```

## Prompt 16 — Painel: pedidos e produtos

```
Implemente no painel do OVNIPORTO as áreas de loja (papel store e admin):

/painel/pedidos:
- Abas por status; linha com número, cliente, itens, total, método de pagamento, status, idade.
- Tela do pedido: itens com variante e quantidade, dados do cliente e endereço (CPF mascarado, revelar com clique e registro em auditoria), linha do tempo de eventos, pagamento (id PayPal, link para o painel do PayPal), e ações por estado:
  - Pago → "Marcar em produção" (abre modal com o resumo do que pedir ao fornecedor e um botão "Copiar resumo" formatado para colar no chat do Mercado Livre; registra fornecedor e data).
  - Em produção → "Gerar etiqueta" (chama Melhor Envio: cria envio, compra etiqueta, salva tracking_code e tracking_url, oferece PDF da etiqueta) e "Marcar enviado" (e-mail ao cliente com rastreio).
  - Enviado → "Marcar entregue" (manual ou por job que consulta o rastreio diariamente).
  - Qualquer → "Cancelar" com motivo e, se pago, "Reembolsar" via PayPal (refund da captura) com confirmação.
- Impressão de "ordem de produção" em PDF (dompdf) com o selo, itens e variantes.
- Exportação CSV do período.

/painel/produtos:
- CRUD de produtos, variantes e imagens (upload com corte quadrado, reordenar por arrastar). Prévia do <TicketCard> ao vivo enquanto edita. Ativar/desativar. Controle de estoque do adesivo com histórico de ajustes.

/painel/configuracoes (admin): links de WhatsApp e Instagram, e-mail de contato, data de lançamento, metas, percentual da loja para a pista (nulo = oculto), status da campanha, textos de content_blocks com editor markdown e prévia, FAQ, regras da comunidade.

Testes: transições de status inválidas falham; etiqueta só é gerada em pedido pago; reembolso registra evento.
```

## Prompt 17 — Painel: O lugar, obra, região, campanha

```
Complete o painel do OVNIPORTO com as áreas de conteúdo do projeto físico:

/painel/lugar: editar os place_spaces (nome, descrição, fase, status, ilustração conceitual com upload), reordenar; subir fotos reais do terreno (site_photos) com legenda e data; subir ilustrações conceituais por espaço; campo para o embed do mapa 3D quando existir (URL de iframe permitida só de domínios configurados).

/painel/obra: CRUD de construction_posts com editor markdown, galeria, fase, agendamento de publicação. Prévia antes de publicar.

/painel/regiao: CRUD de region_partners com todos os campos, geocodificação do endereço (Nominatim) com ajuste manual no mapa, upload de capa e galeria, e o campo obrigatório "consentimento recebido em" com upload opcional do comprovante (mensagem, e-mail). Sem consentimento, o botão Publicar fica desabilitado com explicação.

/painel/campanha (admin): status, meta, valor arrecadado (manual, somando as fontes), URL do crowdfunding, percentual da loja; apoiadores (nome, valor, recompensa, consentiu publicação do nome) com importação por CSV; patrocinadores (nome, cota, logo, link). Aviso fixo na tela: "Não abra a campanha sem orçamento aprovado."

/painel/avise-me: lista de inscritos, status do double opt-in, exportação CSV, remoção.

Testes de autorização e de que parceiro sem consentimento não publica.
```

---

# Fase 6 — Qualidade, SEO, LGPD e polimento

## Prompt 18 — SEO, Open Graph e compartilhamento

```
Faça o SEO e o compartilhamento do OVNIPORTO ficarem impecáveis:

- <SeoHead> em toda página com título no padrão "{Página} · OVNIPORTO Lages", descrição única, canonical, OG e Twitter Card. Imagem OG padrão 1200×630 gerada a partir do selo sobre a capa (public/og/default.jpg; gerar um placeholder bonito por código se a ilustração não existir).
- OG dinâmicas: relatos (polaroid), produtos (ticket), parceiros (capa), posts da obra (capa) via rota /og/... com cache em disco e invalidação ao editar.
- sitemap.xml automático (spatie/laravel-sitemap) e robots.txt; /painel e /conta bloqueados.
- JSON-LD: Organization na home, Product nos produtos, Article nos posts da obra, Place no /o-lugar com as coordenadas, FAQPage no /faq.
- Prévia no WhatsApp testada: título, descrição e imagem aparecem (validar com um comando artisan og:check {url} que busca e imprime as metatags).
- Página /postal: gera a imagem de postal do site (selo + "Lages · SC" + frase) para download e um botão de compartilhar nativo (Web Share API) com fallback para WhatsApp e copiar link.
- Métrica sem cookie: Umami self-hosted no docker-compose (serviço umami + postgres) com o script carregado só em produção; eventos: entrar_comunidade, relatar_iniciado, relatar_enviado, adicionar_carrinho, compra_concluida, avise_me.
```

## Prompt 19 — LGPD na prática

```
Implemente as garantias de privacidade do OVNIPORTO previstas no CLAUDE.md e deixe tudo verificável por teste:

- Fotos: teste automatizado que envia uma foto com GPS e DateTimeOriginal no EXIF pelo fluxo completo e verifica que todos os arquivos salvos (original e variantes) não contêm nenhum metadado EXIF/XMP/IPTC.
- Fotos pendentes: rota pública para o arquivo retorna 404; só URL assinada com validade de 10 min funciona, e só para moderadores ou o autor.
- Dados do Google: só google_id, name, email, avatar_url são salvos; nenhum escopo além de openid, email, profile.
- "Baixar meus dados": job que monta um JSON legível (perfil, relatos com fotos em links assinados, pedidos, inscrições, consentimentos com datas) e envia por e-mail; teste do conteúdo.
- "Excluir minha conta": apaga membro, relatos e fotos (inclusive variantes); pedidos ficam com customer substituído por "Titular excluído" mantendo número, itens, valores e CPF criptografado até o prazo fiscal (campo retention_until = paid_at + 5 anos) e um comando agendado que apaga de vez após a data. Teste.
- Registro de consentimentos: tabela consents (member_id, kind: terms|sighting_publication|newsletter|partner_listing, version, given_at, ip, user_agent) alimentada em cada ponto de consentimento. Versão dos termos em settings; mudança de versão exige novo aceite no próximo login.
- Página /privacidade ganha uma seção "O que fazemos na prática" gerada do código: lista das garantias acima em linguagem simples, para o encarregado revisar.
- Logs de acesso do nginx com retenção de 6 meses (logrotate no container web).
- Sem cookies de terceiros: teste que carrega a home e verifica que só existem cookies de sessão e XSRF.
```

## Prompt 20 — Performance, acessibilidade e polimento visual

```
Faça a passagem final de qualidade no OVNIPORTO:

Performance:
- Imagens: pipeline que gera AVIF e WebP em 400/800/1200/1600 e um componente <Picture> com srcset, sizes e lazy loading (eager só na capa). LQIP (blur placeholder de 20px) para toda imagem de conteúdo.
- Fontes: subset latin, preload das duas principais, font-display swap. Verificar que não há flash de layout.
- Código: code-splitting por página (Inertia + Vite), Leaflet e Framer Motion carregados só onde usados, Starfield em idle callback.
- Cache: respostas públicas com Cache-Control e ETag; cache de fragmentos para home e mapa (invalidado por eventos de domínio).
- Meta: Lighthouse mobile ≥ 90 em todas as categorias nas rotas /, /mapa, /loja, /o-lugar, /relatar. Mostre os relatórios.

Acessibilidade:
- Navegação completa por teclado em menu, modais, assistente de relato, carrinho e lightbox. Ordem de foco lógica. Foco visível em beam em todo elemento interativo.
- Contraste: verificar cada combinação de cor dos tokens; ajustar opacidades, nunca inventar cores.
- Textos alternativos em todas as imagens de conteúdo (campo alt obrigatório no painel).
- Mapa com alternativa em lista. Marquee pausável. Nada pisca mais de 3 vezes por segundo.
- Testar com leitor de tela (VoiceOver ou NVDA) os fluxos de relato e compra e corrigir.

Polimento visual (passe por cada página e aplique):
- Ritmo vertical consistente (escala de 8px); títulos nunca "colados" nas bordas; largura de leitura 65 a 75 caracteres.
- Micro-interações: botões com feedback de pressão (scale 0.98), links com sublinhado que cresce, cartões com elevação suave no hover, ícones que reagem.
- Transições de página com Inertia: fade curto (150ms) e restauração de scroll correta.
- Estados vazios, de carregamento (skeletons no tom da seção) e de erro em TODAS as listas.
- Favicon (selo simplificado) em SVG, PNG 32/180/512, manifest.webmanifest com theme-color night; instalável como PWA básica (offline mostra uma página "Sem sinal da torre").
- Revisar todos os textos de interface: português correto, tom direto, humor leve, sem jargão.
```

---

# Fase 7 — Deploy

## Prompt 21 — Deploy em produção na VPS

```
Prepare o deploy do OVNIPORTO em produção na VPS (ovniporto.tars.art.br), em Docker, seguindo o CLAUDE.md:

- docker-compose.prod.yml: web (nginx com gzip/brotli, HTTP/2, cache de estáticos 1 ano com hash nos nomes), app (php-fpm com opcache e JIT), ssr, worker (Horizon), scheduler (cron do Laravel), mysql 8 (volume), redis, umami + postgres. Proxy reverso Caddy (ou Traefik, conforme já usado na VPS: verificar e adaptar) com HTTPS automático e redirecionamento www.
- Dockerfile multi-stage: build dos assets com Vite (SSR e cliente), composer install --no-dev, imagem final enxuta sem node no app.
- Script deploy.sh idempotente: git pull, build, migrate --force, cache de config/rotas/views/eventos, restart do ssr e horizon, health check em /up, rollback simples se falhar.
- Backups: script diário (cron no host) que faz dump do MySQL e tar do storage, criptografa com age ou gpg e envia para um bucket S3-compatível fora da VPS; retenção 30 dias; comando de restauração documentado e testado.
- Monitoramento: Horizon protegido por papel admin; Laravel Pulse em /painel/pulse; alertas por e-mail quando a fila acumula ou um job falha 3 vezes; uptime externo (UptimeRobot ou similar) em /up.
- Segurança: headers (CSP compatível com PayPal SDK, Leaflet tiles e Umami; HSTS; X-Frame-Options), rate limit em login, relato, checkout e API; .env fora do repositório; segredos só por variáveis; usuário não-root nos containers.
- Checklist de go-live em DEPLOY.md: DNS, certificados, webhooks do PayPal apontando para produção, chaves do Melhor Envio, SMTP, Google OAuth com a URL de produção, Umami, backup testado, 404/500 testadas, Lighthouse final.
```

---

# Prompts auxiliares

## Prompt R — Revisão visual (rodar ao fim de cada fase)

```
Faça uma revisão visual rigorosa do OVNIPORTO como um diretor de arte exigente. Abra cada página pronta em 390px, 768px e 1440px (use o navegador headless do Playwright já instalado, tire capturas de tela e olhe para elas) e responda, página por página:

1. O primeiro segundo impressiona? Se não, o que falta (contraste, escala do título, imagem, movimento)?
2. A hierarquia está clara: um título, um sobretítulo, uma ação principal por seção?
3. As cores seguem só os tokens do CLAUDE.md? Algum branco puro ou cor inventada?
4. Tipografia: Unbounded só em títulos, Caveat só em sobretítulos e legendas, Figtree no resto? Tamanhos fluidos? Linhas de 65 a 75 caracteres?
5. Espaçamento: escala de 8px, respiro entre seções, nada colado na borda?
6. Elementos de assinatura presentes e bem feitos: pílula do menu, selo, polaroids, faixa, ingressos, 01/02/03?
7. Movimento: sutil, com propósito, desligado em reduced-motion, sem travar?
8. Estados vazios, loading e erro bonitos?
9. Mobile: nada corta, alvos de toque ≥ 44px, botão principal alcançável com o polegar?
10. Algo parece template genérico? O que trocar para parecer feito à mão?

Liste os problemas por gravidade (quebra, feio, detalhe) com o arquivo e a linha, corrija todos os de "quebra" e "feio" agora, e me mostre as capturas antes e depois.
```

## Prompt C — Conteúdo placeholder honesto

```
Crie os placeholders visuais do OVNIPORTO para tudo que ainda não existe, sem inventar conteúdo real:

- public/concept/*.jpg: para cada ilustração prevista (cover, vigil, yellow-car, customs-shop, tower, snack-bar, overview, museum-path), gere por código (Node + sharp ou canvas) uma imagem no tamanho certo com gradiente night → night-blue, estrelas aleatórias, um brilho verde e o nome da ilustração em Unbounded pequeno no canto com o selo "CONCEITO EM PRODUÇÃO". Bonito o bastante para o site não parecer quebrado.
- public/brand/seal.png e seal.svg: a partir do arquivo do adesivo que vou colocar em resources/brand/ (se não houver, gere um selo circular temporário com "OVNIPORTO · LAGES · SC" em Unbounded sobre night com anel beam).
- public/og/default.jpg a partir do selo sobre a cover.
- Seeds de desenvolvimento (só em local): 12 relatos aprovados espalhados num raio de 30 km de Lages com fotos geradas (céu noturno por código), 3 parceiros fictícios claramente marcados "[EXEMPLO]", 2 posts de obra "[EXEMPLO]". Um comando artisan dev:seed-demo e dev:clear-demo. Nunca rodar em produção (guard por APP_ENV).
```

## Prompt T — Testes de ponta a ponta

```
Escreva testes end-to-end com Playwright para os fluxos críticos do OVNIPORTO, rodando contra o ambiente local com os seeds de demonstração:

1. Visitante abre a home, rola até o fim, clica em "Ver o mapa", filtra por "Luz", abre um relato, compartilha (verifica o link gerado).
2. Membro (login Google mockado via Socialite fake) envia um relato completo em 4 passos com uma foto que tem EXIF; verifica a tela de confirmação; moderador aprova no painel; o relato aparece no mapa e na home.
3. Visitante compra um adesivo: adiciona ao carrinho, checkout com frete "Retirar em Lages", pagamento no PayPal sandbox (ou gateway fake), recebe a página do pedido; store marca em produção e enviado.
4. Membro baixa seus dados e exclui a conta; verifica que o relato sumiu do mapa.
5. Acessibilidade: axe-core sem violações críticas em /, /mapa, /loja, /relatar, /o-lugar.
6. Regressão visual: capturas de referência das 10 seções da home em 390px e 1440px; o teste falha se mudar mais de 0,5%.

Integre no Makefile (make e2e) e documente no README.
```

---

# Ordem de execução resumida

| # | Prompt | Resultado |
| --- | --- | --- |
| 0 | Contexto base | `CLAUDE.md` |
| 1 | Projeto, Docker e estrutura | Projeto rodando |
| 2 | Design system | Componentes e styleguide |
| 3 | Layout público | Header, footer, 404, SEO |
| R | Revisão visual | Fase 1 aprovada |
| 4 | Home parte 1 | Capa, boas-vindas, Livro |
| 5 | Home parte 2 | Faixa, lugar, loja, lenda, região, comunidade |
| 6 | Lenda, FAQ, privacidade, comunidade | Páginas de conteúdo |
| 7 | O lugar, Apoie, Obra | Projeto físico |
| 8 | Conheça a região | Parceiros |
| C | Placeholders honestos | Site nunca parece quebrado |
| R | Revisão visual | Fase 2 aprovada |
| 9 | Login Google e Minha conta | Membros |
| 10 | Relato em 4 passos | Envio com privacidade |
| 11 | Mapa e página do relato | Livro público |
| R | Revisão visual | Fase 3 aprovada |
| 12 | Catálogo e produto | Loja |
| 13 | Checkout PayPal e frete | Vendas |
| 14–17 | Painel | Operação pelo Felipe e amigos |
| 18 | SEO e compartilhamento | Prévias bonitas |
| 19 | LGPD na prática | Garantias testadas |
| 20 | Performance, acessibilidade, polimento | Lighthouse ≥ 90 |
| T | Testes de ponta a ponta | Fluxos críticos cobertos |
| 21 | Deploy | No ar em ovniporto.tars.art.br |
| R | Revisão visual final | Lançamento |
