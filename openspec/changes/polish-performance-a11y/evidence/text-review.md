# Revisão dos textos da interface (tarefa 3.3)

- **Data:** 2026-10-04
- **Escopo:** todas as strings de `resources/js/i18n/pt-BR.ts` (≈1290 linhas), mais uma varredura de `resources/js/` atrás de textos em português fora do arquivo central.
- **Arquivo editado:** só `resources/js/i18n/pt-BR.ts` (apenas valores; nenhuma chave ou assinatura mudou).

## Critérios, nesta ordem

1. **Correção:** ortografia, acentos, crase, concordância, pontuação, aspas (o arquivo usa aspas retas `"…"`, mantidas), nada de inglês em texto de interface, sem espaço duplo, reticências `…` em textos de carregamento.
2. **Verdade (CLAUDE.md):** nada diz ou sugere que a pista/o lugar já existe ou funciona; nada pede dinheiro para a obra; a lenda é apresentada como lenda.
3. **Consistência de ações:** o mesmo verbo em botão, aviso e título; botões em caixa de frase; nada de "Clique aqui", "Submit", "OK".
4. **Clareza e tom:** direto, curto, coloquial; erro diz o que houve e como resolver; estado vazio convida a agir; vocabulário da marca (vigília, torre, Livro de avistamentos, postal, pista).
5. **Acessibilidade dos textos:** aria-labels e textos alternativos presentes e descritivos.
6. **Pessoas e privacidade:** nomes próprios expostos em público são sinalizados, não alterados.

Resultado geral: o arquivo já estava em bom estado (acentos, crase e reticências corretos; nenhum espaço duplo; nenhum "Clique aqui"/"OK"; caixa de frase nos botões). As mudanças abaixo corrigem problemas reais; texto bom não foi reescrito por gosto.

## Mudanças (24 valores)

| Chave | Antes | Depois | Motivo |
|---|---|---|---|
| `community.copied` | Link copiado | Link copiado. | Consistência: `sightingPage.copied` e `postcardPage.copied` usam "Link copiado." (e os testes de componente esperam essa forma). |
| `legendPage.lead` | Uma pista de pouso de verdade, com uma lenda inventada por cima. … | Uma pista de pouso que vai ser de verdade, com uma lenda inventada por cima. … | Verdade: o presente sugeria que a pista já existe. Alinha com `origin` ("A pista vai ser de verdade"). |
| `placePage.lead` | …no alto da serra, construída em fases. … | …no alto da serra, a ser construída em fases. … | Verdade: "construída" lia como obra pronta. |
| `placePage.skyRules[0].body` | Toda luz do chão fica baixa, quente ou vermelha… | Toda luz do chão vai ser baixa, quente ou vermelha… | Verdade: regra de projeto no futuro, não descrição de lugar em funcionamento. |
| `placePage.skyRules[2].body` | Cada espaço conta a sua história no celular… | Cada espaço vai contar a sua história no celular… | Idem. |
| `supportPage.how[0].body` | Uma campanha numa plataforma de crowdfunding, com meta e prazo claros. | Uma campanha numa plataforma de apoio coletivo, com meta e prazo claros. | Inglês em texto público. |
| `diaryPage.empty` | …O primeiro post será o dia em que a primeira pedra for colocada. | …O primeiro post sai no dia em que a primeira pedra for colocada. | Sentido: o post não "é" um dia. |
| `report.photos.full` | Já são 3 fotos. | Já são 3 fotos. Remova uma para trocar. | Erro diz como resolver. |
| `report.when.locationWhy` | Só para centralizar o mapa; o ponto continua sendo você quem escolhe. | Só para centralizar o mapa: quem escolhe o ponto continua sendo você. | Construção truncada ("sendo você quem escolhe"). |
| `report.sent.lead` | Relato na torre de controle, em análise. | Recebido. Agora ele fica em análise até a torre decidir. | Repetia o título (`report.sent.title`) palavra por palavra na mesma tela. |
| `errors.503.title` | Pista em manutenção. | Torre em manutenção. | Verdade: "pista em manutenção" sugere pista existente; a torre é a metáfora já usada para o site/equipe. |
| `errors.503.lead` | Voltamos em instantes. | O site volta em instantes. Recarregue daqui a pouco. | Erro diz o que fazer. |
| `postcardPage.frontAlt` | …ilustração conceito da pista… | …ilustração conceitual da pista… | Concordância e consistência com os demais alt ("Ilustração conceitual"). |
| `storePage.shippingError` | Não deu para calcular agora. Tente de novo. | Não deu para calcular o frete agora. Confira o CEP e tente de novo. | Erro diz o que houve e como resolver. |
| `panel.orders.actions.ship` | Marcar enviado | Marcar como enviado | Consistência com `shipTitle` ("Marcar como enviado") e auditoria ("marcou como enviado"). |
| `panel.orders.actions.deliver` | Marcar entregue | Marcar como entregue | Idem (auditoria: "marcou como entregue"). |
| `panel.home.goalsEmpty` | …em Conteúdo → Configurações. | …em Conteúdo → Configurações e textos. | O caminho citado não batia com o nome real da seção. |
| `panel.products.featuredLabel` | Destaque na home | Destaque na página inicial | Inglês na interface. |
| `panel.region.featured` | Destaque na home | Destaque na página inicial | Idem. |
| `panel.settings.blocks.home_intro` | Home: apresentação | Página inicial: apresentação | Idem. |
| `panel.settings.blocks.home_place` | Home: o lugar | Página inicial: o lugar | Idem. |
| `panel.settings.blocks.home_store` | Home: loja | Página inicial: loja | Idem. |
| `panel.settings.blocks.home_legend` | Home: a lenda | Página inicial: a lenda | Idem. |
| `panel.content.sections[0].body` | Links, metas, textos da home e das páginas legais, FAQ e regras. | Links, metas, textos da página inicial e das páginas legais, FAQ e regras. | Idem. |


Mantidos de propósito: "post", "feed RSS", "SKU", "CSV", "FAQ", "Markdown", "Admin", "Logo" (termos correntes no Brasil ou técnicos do painel); "Me avise" (botão) e "Avise-me" (nome do recurso).

## Textos em português fora de `pt-BR.ts` (não corrigidos)

| Arquivo:linha | Texto | Observação |
|---|---|---|
| `resources/js/Components/Layout/nav.ts:12-17` | Relatar avistamento, A lenda, Apoie a pista, Diário da obra, Comunidade, Perguntas frequentes | Já existem equivalentes (`t.report.title`, `t.legendPage.title`, `t.supportPage.title`, `t.diaryPage.title`, `t.community.title`, `t.faqPage.title`). |
| `resources/js/Components/Home/SouvenirsSection.tsx:80` | Próxima lembrança em produção | Igual a `t.storePage.nextStub`. |
| `resources/js/Components/Store/ShippingQuote.tsx:56` | Digite um CEP com 8 números. | Mostrado em qualquer falha (inclusive rede). |
| `resources/js/Pages/Checkout/Checkout.tsx:135` | Digite um CEP com 8 números. | Mesmo texto, no `catch` da busca: falha de rede vira "CEP inválido". |
| `resources/js/Pages/Checkout/Checkout.tsx:146` | Escolha como receber. / Diga o número. | Validação. |
| `resources/js/Pages/Sightings/Report.tsx:70` | Escolha o que você viu. | Validação do passo 1. |
| `resources/js/Pages/Sightings/Report.tsx:72` | Conte um pouco mais: pelo menos 20 caracteres. | O número está fixo no texto, mas o limite vem de `limits.descriptionMin`. |
| `resources/js/Pages/Sightings/Report.tsx:73` | No máximo 1000 caracteres. | Idem com `limits.descriptionMax`. |
| `resources/js/Pages/Sightings/Report.tsx:77-78` | Escolha a faixa de horário. / Diga a hora. | Validação do passo 3. |
| `resources/js/Pages/Sightings/Report.tsx:82-83` | Use de 3 a 20 letras, números, "_" ou ".". / Marque a autorização para publicar o relato. | Validação do passo 4. |
| `resources/js/Components/Sightings/SightingPolaroid.tsx:25` | `${type} vista por ${nickname}` | **Erro de concordância**: "Objeto vista", "Rastro vista", "Outro vista". Sugestão: "Relato de ${type} por ${nickname}" ou "${type}, relato de ${nickname}". |
| `resources/js/Components/Report/StepReview.tsx:55` | `Foto ${i + 1}` | Existe `t.placePage.photoOf`/`t.panel.review.photoAlt`; um alt "Foto 1 do seu relato" seria mais descritivo. |
| `resources/js/Components/Postcard/PostcardBack.tsx:52` | Selo OVNIPORTO no postal | Título do SVG. |
| `resources/js/Pages/Content/Legend.tsx:143`, `resources/js/Pages/Region/Index.tsx:41` | label "Cachi", "Serra" | Rótulos das ilustrações placeholder. |
| `resources/js/app.tsx:13`, `resources/js/ssr.tsx:10` | OVNIPORTO Lages · A pista de pouso do planalto | Título padrão duplicado; podia usar `t.brand.tagline`. |
| `resources/js/data/cachi.ts:13-25` | Linha do tempo de Cachi | Conteúdo, não interface; ver decisões abaixo. |
| `resources/js/Pages/Dev/Styleguide.tsx` (várias) | Textos de exemplo | Página só de desenvolvimento; pode ficar. |

## Pontos para decisão humana

1. **Nome do encarregado em público.** `legalPage.draft` = "RASCUNHO — texto final será redigido pelo encarregado (Felipe)" aparece nas páginas públicas de Privacidade e Termos; `legalPage.draftLead` diz "para ele escrever". Manter o nome, trocar por "pelo encarregado" ou pelo canal de contato?
2. **Nome do fundador na história.** `legendPage.origin[0]`: "O Felipe visitou o Ovnipuerto de Cachi…". Provavelmente intencional (história de origem), mas confirmar autorização.
3. **Frete "para todo o Brasil".** `storePage.strip` promete "Envio para todo o Brasil" enquanto `checkout.shippingOff` diz que o envio pelos Correios "ainda não está ligado" e `storePage.shippingSimulated` diz que os valores são simulados. Enquanto o Melhor Envio não estiver ativo, a faixa promete algo que o checkout não entrega.
4. **"Parte de cada venda vira pista".** `storePage.description` ("vira pista", presente), `storePage.strip` ("Parte vira pista") e `storePage.runwayTitle` ("vai virar pista") afirmam que vendas da loja já financiam a obra. CLAUDE.md proíbe arrecadar para a obra antes do orçamento; `supportPage` trata isso como algo "quando abrir". Decidir se a destinação já vale (e então informar o percentual) ou se esses textos passam para o futuro/condicional.
5. **"Vigília grátis".** `hero.freeVigil` e `strip` anunciam uma vigília gratuita. Se não há vigílias acontecendo hoje, o texto sugere um lugar/evento em funcionamento. Confirmar se existem vigílias reais (fora do terreno) ou trocar por algo como "Vigília grátis, meta 2028".
6. **Status "aberto".** `place.status.open` = "aberto" ainda existe no dicionário (o painel não oferece essa opção). Pode ficar para o futuro; só registrar.
7. **"A loja abre com o adesivo."** `members.noOrders` sugere que a loja não está aberta; se a loja já vende, trocar por "Nenhum pedido ainda. O adesivo está esperando." (mesmo tom de `cart.empty`).
8. **Fatos de Cachi.** `resources/js/data/cachi.ts` afirma fatos históricos (Werner Jaisli, "Jaisli desaparece"). Conferir fontes antes do lançamento, já que o site promete separar fato de lenda.
9. **"Apoie a pista" no menu.** Com /apoie em "em planejamento", o imperativo no menu (`nav.ts:14`) pode soar como pedido de dinheiro. Alternativa: "Como apoiar".

## Verificação

- `npx prettier --write resources/js/i18n/pt-BR.ts` — sem alterações de formato.
- `npx tsc --noEmit -p .` — sem erros.
- `npx vitest run` — 15 arquivos, 42 testes passando. (Uma primeira execução teve 1 falha intermitente que não se repetiu em duas execuções seguintes; nenhum teste depende das strings alteradas além de "Link copiado.", que foi mantido.)

## Correções feitas depois da revisão (sessão principal)

- Textos fixos levados para `pt-BR.ts`: menu completo (`nav.ts`), validações do assistente de relato (agora usam os limites reais do servidor em vez de 20/1000 fixos), erros do checkout, "Próxima lembrança em produção" (reusa `storePage.nextStub`), alt das fotos na revisão do relato.
- Bug de concordância no alt dos polaroides corrigido com `logbook.photoAlt`: "Luz vista", "Objeto visto", "Rastro visto", "Algo visto".
- Checkout: falha de rede ao consultar o CEP agora diz isso ("Não deu para consultar o CEP agora…"), em vez de acusar CEP inválido; `postJson` passou a expor o status HTTP (`HttpError`).
- Novos textos de acessibilidade: botão de pausar a faixa corrida (`marquee.pause`) e instrução de teclado do mapa do relato (`report.when.mapKeyboardHint`).
- Ficaram de fora, por serem nomes próprios em arte de placeholder ou conteúdo, não interface: rótulos "Cachi"/"Serra" das artes, o título do selo no verso do postal, a linha do tempo de `data/cachi.ts` e os textos da página de estilos (só em desenvolvimento).
- Pontos que dependem de decisão do responsável pelo projeto continuam listados acima.
