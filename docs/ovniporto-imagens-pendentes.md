# OVNIPORTO Lages — Imagens e ilustrações que faltam

> **Atualização 05/10/2026:** as 16 ilustrações (Parte 1 A e B e Parte 2 inteira) foram geradas e aplicadas no site. Faltam só as fotos reais (Parte 1 C) e a capa opcional `De Cachi a Lages.png`.

Levantamento de 05/10/2026, feito a partir do que o site mostra hoje. Quando gerar uma ilustração,
salve com o nome indicado em **Arquivo** e me avise a pasta: eu converto para AVIF/WebP
(`php artisan concept:import`) e troco o espaço reservado.

- **Parte 1:** o que falta no site agora (ilustrações e fotos reais).
- **Parte 2:** ilustrações novas para os 12 casos do Atlas.

---

## Estilo comum das ilustrações

- **Linguagem:** ilustração realista e pintada, crível, sem cara de desenho infantil. A mesma das ilustrações atuais do site.
- **Hora:** fim de tarde azulado ou noite com estrelas.
- **Cores do adesivo:** noite #061121 e azul-noite #142644 no céu; lilás #494383 na linha do horizonte; verde do feixe #54C933 só em luzes de óvni; amarelo do carro #FCB802 como único amarelo forte.
- **Evitar:**
  - rostos (pessoas de costas, de perfil distante ou em silhueta);
  - marcas e logotipos;
  - textos inventados (só as placas pedidas);
  - qualquer coisa que faça parecer que a pista de Lages já existe.
- **No site:** cada ilustração sai com selo. "conceito" quando mostra como o OVNIPORTO vai ficar; "ilustração" quando reconstitui um relato ou um lugar de outro país. Nunca passa por foto.

---

# Parte 1 — O que falta no site

## A. Espaços de /o-lugar sem ilustração

Hoje os dois aparecem com "Ilustração em produção" na seção "Os espaços, fase a fase".

### 1. Hangar (estacionamento)

- **Onde:** /o-lugar, espaço 04 (fase 1).
- **Arquivo:** `Hangar.png` → slug `hangar`
- **Formato:** 16:10 horizontal.

Fim de tarde azulado. Um estacionamento simples de chão batido e brita na beira do campo de altitude. Alguns carros comuns, sem marca, estacionados em fila, como "naves terrestres" pousadas. Uma placa baixa de madeira com uma seta indica o caminho de pedra até a pista. Balizas de luz vermelha baixa marcam as vagas. Araucárias e cerca de pedra ao fundo, primeiras estrelas.

**Prompt (inglês):**
> Realistic painterly illustration at blue hour, a simple gravel parking area at the edge of a highland grass field in southern Brazil, a few ordinary unbranded cars parked in a row like landed earthbound ships, a low wooden sign with an arrow pointing to a stone path, small low red marker lights along the parking spaces, araucaria pines and a dry-stone wall behind, first stars in a deep navy sky (#061121) with a lilac horizon (#494383), only low warm or red lights, 16:10, no text, no logos, no faces.

### 2. Museu coberto

- **Onde:** /o-lugar, espaço 09 (fase 4).
- **Arquivo:** `Museu coberto.png` → slug `museum-indoor`
- **Formato:** 16:10 horizontal.

Interior pequeno e acolhedor na base da torre de controle: paredes de madeira e pedra, luz quente e baixa. Vitrines com objetos de relatos (uma lanterna antiga, um caderno aberto, polaroids de luzes no céu presas com fita). Numa mesa, uma maquete da estrela de pedras de Cachi. Uma janela mostra a noite e a pista com balizas vermelhas. Um visitante de costas lendo uma placa.

**Prompt (inglês):**
> Realistic painterly illustration, a small cozy museum room at the base of a wooden observation tower, wood and stone walls, warm low lighting, glass display cases with an old lantern, an open notebook and polaroid photos of lights in the night sky taped to a board, a table with a scale model of a large star shape made of pale stones, a window showing the night sky and a circular landing pad with red marker lights outside, one visitor seen from behind reading a panel, 16:10, no readable text, no logos, no faces.

## B. Cenas da origem ainda em produção

As descrições completas e os prompts prontos já estão em `docs/ovniporto-ilustracoes-origem.md`. Resumo do que ainda aparece como "Conceito em produção":

| # | Arquivo → slug | Onde aparece | A cena |
|---|---|---|---|
| 1 | `Estrella de la Esperanza.png` → `stone-star-night` | Porta para Cachi em /origem | A grande estrela de pedras de Cachi à noite, vista do alto, com a Via Láctea. **Serve também para o caso Cachi do Atlas.** |
| 2 | `Atlas dos Ovnipuertos.png` → `atlas-globe` | Porta para o Atlas em /origem | Um globo ou mapa antigo com pontos luminosos marcando os ovnipuertos do mundo. |
| 3 | `Casa-cueva.png` → `cachi-casa-cueva` | Capítulo "A casa-cueva" em /origem/cachi (hoje só tem o espaço reservado) | O poço circular de adobe e a porta escura onde Werner dormia, à noite, com a estrela ao fundo. **Prioridade alta:** o briefing foi refeito a partir das fotos de referência em `Dropbox/OVNIPORTO/casacueva`. |
| 4 | `Pedras e cordas.png` → `werner-stones` | Capítulo "Llamada al Cielo" em /origem/cachi | Mãos (sem rosto) esticando cordas entre pedras para riscar raios e ângulos no chão árido. |

Não precisam mais:
- `O carro amarelo do Julean.png` (`niva-abduction`): substituída pelas imagens `nivaamarelo.png` e `nivaamarelo2.png`.
- `De Cachi a Lages.png` (`origin-journey`): a capa de /origem hoje não usa imagem. Só gere se quiser uma capa ilustrada.

## C. Fotos reais (não são ilustração)

Estas precisam ser fotos de verdade, sobem pelo painel e não podem ser geradas:

| O quê | Onde aparece | Quem | Observação |
|---|---|---|---|
| Fotos do terreno hoje | /o-lugar, "Hoje e amanhã" → "O terreno hoje" (hoje: "Fotos do terreno em breve") | Rodrigo | Várias fotos horizontais: o campo, a vista, o céu à noite. Sem pessoas reconhecíveis. |
| Mapa 3D do lugar | /o-lugar, bloco "Mapa 3D" (hoje: "Em produção pela arquiteta do projeto") | Giovana | Entra como link de incorporação (iframe) no painel. |
| Fotos dos produtos | /loja e home ("Lembranças") | Rodrigo | Adesivo, camiseta, caneca e Kit Abdução. Fundo neutro, luz natural, 1 a 4 fotos por produto; a primeira quadrada. |
| Fotos dos parceiros | /regiao | Cada parceiro | Uma foto quadrada por parceiro (pousada, vinícola, trilha), com autorização. |

---

# Parte 2 — Ilustrações para os casos do Atlas

Hoje 9 casos têm foto de licença aberta e 3 têm só mapa de localização (Green River, Carbondale e Lages). A ideia é uma série de 12 ilustrações, uma por caso, para a capa de cada página `/origem/atlas/{caso}`. As fotos documentais continuam na página, com crédito.

**Regras da série (além do estilo comum):**
- **Mesmo enquadramento em todas:** formato 4:3 horizontal, hora azul ou noite, o lugar como ele é ou foi. Lado a lado, parecem uma coleção de cartões-postais.
- **Só o que está documentado:** a cena mostra o lugar e a obra. O que é alegação (seres, naves, abduções) aparece no máximo como luz distante ou sugestão, nunca como fato.
- **Se não foi construído, não aparece construído:** em Lajas não há pista; em Lages, nada existe ainda.
- **Sem textos inventados:** placas só onde está indicado; se a IA errar o texto, prefira sem texto.
- **Selo no site:** "ilustração".

### 1. St. Paul UFO Landing Pad — Canadá

- **Arquivo:** `Atlas St Paul.png` → slug `atlas-ill-st-paul`
- **Situação documentada:** plataforma pública construída para o centenário do Canadá, inaugurada em 3 de junho de 1967, numa cidade pequena das pradarias de Alberta. Centro de informação ao lado desde os anos 1990.

Noite fria de pradaria. No centro de uma praça gramada de cidade pequena, uma plataforma de concreto elevada, em forma de disco, sobre um pedestal, rodeada de mastros com bandeiras. Ao lado, uma construção baixa de visitantes com luz quente. Céu limpo com aurora boreal discreta.

> Realistic painterly illustration at night in a small prairie town in Alberta, Canada, a raised disc-shaped concrete landing platform on a pedestal in the middle of a grassy town square, surrounded by flagpoles with flags, a low visitor center building with warm windows beside it, flat prairie horizon, faint green aurora borealis and stars in a deep navy sky, calm and civic, 4:3, no text, no logos, no people.

### 2. Ängelholm UFO Memorial — Suécia

- **Arquivo:** `Atlas Angelholm.png` → slug `atlas-ill-angelholm`
- **Situação documentada:** memorial erguido em 1972 numa clareira de floresta, reproduzindo as marcas de um suposto pouso relatado por Gösta Carlsson em 1946. O relato tem inconsistências apontadas por investigação.

Fim de tarde na floresta de faias do sul da Suécia. Numa clareira, marcas reproduzidas no chão (pequenos círculos de concreto em padrão regular) e uma peça memorial discreta. Luz baixa entre as árvores, céu lilás. Nenhuma nave: só o memorial.

> Realistic painterly illustration at dusk in a beech forest in southern Sweden, a small clearing with a discreet memorial: a regular pattern of small round concrete markers set in the ground reproducing landing marks, soft low light between the tree trunks, mossy ground, lilac sky (#494383) fading to navy with first stars, quiet and mysterious, no spacecraft, 4:3, no text, no people.

### 3. Ovniport d'Arès — França

- **Arquivo:** `Atlas Ares.png` → slug `atlas-ill-ares`
- **Situação documentada:** área municipal oficialmente reservada em 1976 pelo prefeito Christian Raymond, na Gironda, perto da Bacia de Arcachon. Marcação física e estela vieram depois.

Hora azul no litoral da Gironda. Uma área gramada aberta entre pinheiros-marítimos, com uma estela de pedra e uma marcação circular no chão. Ao fundo, a água calma da bacia e o céu com as primeiras estrelas.

> Realistic painterly illustration at blue hour on the Atlantic coast of Gironde, France, an open grassy municipal area among maritime pines, a simple stone stele and a large circular marking on the ground, the calm water of the Arcachon bay in the distance, sandy paths, deep navy sky with first stars and a lilac horizon, 4:3, no text, no people.

### 4. Wycliffe Well UFO Landing Pad — Austrália

- **Arquivo:** `Atlas Wycliffe Well.png` → slug `atlas-ill-wycliffe-well`
- **Situação documentada:** parada de estrada privada no Outback (Território do Norte), transformada em atração ufológica no fim dos anos 1980, com uma pequena plataforma. Abandonada e vandalizada depois da enchente de 2022.

Noite no deserto vermelho do Outback, Via Láctea enorme. Uma parada de beira de estrada abandonada: estátuas de alienígenas desbotadas e tortas, uma plataforma pequena rachada, um posto de gasolina vazio, poças secas da enchente. Melancólico, não assustador.

> Realistic painterly illustration at night in the red desert of the Australian Outback, an abandoned roadside stop with faded and leaning alien statues, a small cracked landing platform, an empty fuel station with broken windows, dried flood marks on the red ground, spinifex grass, a huge Milky Way overhead, melancholic and quiet, 4:3, no readable text, no logos, no people.

### 5. Greater Green River Intergalactic Spaceport — Estados Unidos

- **Arquivo:** `Atlas Green River.png` → slug `atlas-ill-green-river`
- **Situação documentada:** aeroporto municipal comum (ativo desde 1963) que recebeu esse nome oficial por resolução da cidade em 1994. Não há estrutura especial.
- **Importante:** hoje este caso só tem mapa de localização.

Noite no planalto desértico do Wyoming, com morros planos (buttes) ao fundo. Uma pista pequena de aeroporto regional com luzes azuis de taxiamento, uma biruta, um hangar simples. Um avião pequeno parado. Tudo comum; o encanto está no nome, não na arquitetura.

> Realistic painterly illustration at night on the high desert of Wyoming, a small regional airstrip with blue taxiway lights, a windsock, a simple metal hangar and one small parked propeller plane, flat-topped buttes on the horizon, sagebrush, a clear starry sky in deep navy, ordinary and quiet, 4:3, no text, no logos, no people.

### 6. Discoporto de Barra do Garças — Brasil

- **Arquivo:** `Atlas Barra do Garcas.png` → slug `atlas-ill-barra-do-garcas`
- **Situação documentada:** área reservada por lei municipal de 1995 no Parque Estadual da Serra Azul, com painéis desde 1997 e revitalização em 2022.

Fim de tarde no alto da Serra Azul, cerrado e paredões de arenito. Uma área circular marcada no platô, com painéis baixos ao redor. Lá embaixo, as luzes da cidade e o rio Araguaia brilhando. Céu indo do lilás ao azul-noite.

> Realistic painterly illustration at dusk on top of a sandstone plateau in the Brazilian cerrado, a large circular marked area on the rocky ground with low panels around it, twisted cerrado trees, the lights of a town and a wide river (Araguaia) glowing in the valley below, sky fading from lilac (#494383) to deep navy with first stars, 4:3, no readable text, no people.

### 7. Ovnipuerto e Ruta Extraterrestre de Lajas — Porto Rico

- **Arquivo:** `Atlas Lajas.png` → slug `atlas-ill-lajas`
- **Situação documentada:** rota turística proclamada em 2005; o aeroporto para óvnis foi só proposto. Em 2010 havia apenas uma placa coberta de vegetação. **Não existe pista.**

Hora azul no vale de Lajas, sudoeste de Porto Rico, com a Sierra Bermeja ao fundo. Na beira de uma estrada rural, uma placa de rota tomada pelo mato. Campos planos e vazios, nenhuma construção. O vazio é o assunto.

> Realistic painterly illustration at blue hour in the Lajas valley in southwestern Puerto Rico, a rural road with a weathered route sign half covered by tropical vegetation, flat empty fields with no structures at all, low hills (Sierra Bermeja) on the horizon, a few palm trees, deep navy sky with first stars and a lilac horizon, quiet and slightly forgotten, 4:3, no readable text, no people.

### 8. El Enladrillado e Ruta Ufológica — Chile

- **Arquivo:** `Atlas El Enladrillado.png` → slug `atlas-ill-el-enladrillado`
- **Situação documentada:** formação natural de grandes blocos de rocha planos, na Reserva Nacional Altos de Lircay (Andes), que a tradição local trata como pista de pouso. Destino de uma rota turística municipal.

Noite clara nos Andes. Um platô de grandes lajes de pedra planas e encaixadas, como um piso gigante. Ao fundo, o vulcão Descabezado Grande com neve. Duas silhuetas de caminhantes com lanterna vermelha. Via Láctea sobre a cordilheira.

> Realistic painterly illustration at night in the Chilean Andes, a high plateau of huge flat interlocking stone slabs like a giant natural pavement, the snow-capped flat-topped volcano Descabezado Grande in the background, two distant hiker silhouettes with small red headlamps, the Milky Way over the mountains, deep navy and lilac palette, 4:3, no text.

### 9. Ovnipuerto de Cachi — Argentina

- **Arquivo:** reaproveitar `Estrella de la Esperanza.png` (`stone-star-night`, Parte 1 B).
- **Situação documentada:** Estrella de la Esperanza, 36 pontas e 48 m, construída por Werner Jaisli a partir de 2008 em Fuerte Alto.

Não gere uma imagem separada: a ilustração da porta para Cachi serve para este caso. Se quiser uma variação para o Atlas, faça a mesma estrela vista do chão, ao entardecer, com as montanhas secas dos Valles Calchaquíes e o Nevado de Cachi ao fundo.

### 10. Emilcin UFO Memorial — Polônia

- **Arquivo:** `Atlas Emilcin.png` → slug `atlas-ill-emilcin`
- **Situação documentada:** monumento erguido em 2005 numa vila rural, lembrando o relato de Jan Wolski em 1978. O encontro é alegação.

Fim de tarde numa vila do leste da Polônia: campos arados, uma estrada de terra, bétulas. Na beira do caminho, um pequeno monumento de pedra com flores ao pé. No céu, só uma luz distante e ambígua sobre a linha das árvores.

> Realistic painterly illustration at dusk in a rural village in eastern Poland, plowed fields, a dirt road lined with birch trees, a small stone monument by the roadside with a few flowers at its base, a single distant ambiguous light above the tree line, lilac horizon fading to navy, calm and pastoral, 4:3, no readable text, no people.

### 11. Carbondale UFO Incident — Estados Unidos

- **Arquivo:** `Atlas Carbondale.png` → slug `atlas-ill-carbondale`
- **Situação documentada:** em novembro de 1974, jovens relataram uma luz caindo num lago de rejeitos de carvão; a polícia recuperou um lampião ferroviário e houve acusação de fraude, contestada pelas testemunhas. Hoje há placa e festival.
- **Importante:** hoje este caso só tem mapa de localização.

Noite de novembro numa antiga cidade de carvão da Pensilvânia. Um lago escuro de rejeitos, com uma luz brilhando debaixo da água. Na margem, silhuetas de pessoas e um carro de polícia dos anos 1970 com o farol ligado. Colinas com árvores sem folhas. A cena é o mistério, sem resposta.

> Realistic painterly illustration on a cold November night in 1974 in a former coal town in Pennsylvania, a dark silt pond with a single glowing light under the water, silhouettes of onlookers and a 1970s police car with its headlights on at the edge, leafless trees on low hills, mining remnants, deep navy sky, ambiguous and suspenseful, 4:3, no text, no logos, no faces.

### 12. OVNIPORTO Lages — Brasil

- **Arquivo:** nenhum novo. Usar a ilustração conceitual que já existe (`overview` ou `cover`), com o selo "conceito".
- **Situação documentada:** projeto em planejamento, meta 2028. Não existe nada construído.

---

## Resumo do que gerar

| Prioridade | Arquivo | Para |
|---|---|---|
| Alta | `Estrella de la Esperanza.png` | /origem e caso Cachi do Atlas |
| Alta | `Atlas dos Ovnipuertos.png` | /origem e capa do Atlas |
| Alta | `Atlas Green River.png` e `Atlas Carbondale.png` | os 2 casos que só têm mapa |
| Média | `Hangar.png` e `Museu coberto.png` | /o-lugar |
| Média | `Casa-cueva.png` e `Pedras e cordas.png` | capítulos de Cachi |
| Média | as outras 8 do Atlas | capa de cada caso |
| Opcional | `De Cachi a Lages.png` | capa de /origem |

Fotos reais (Parte 1 C) dependem de quem tem acesso: terreno e produtos (Rodrigo), mapa 3D (Giovana), parceiros (cada um).
