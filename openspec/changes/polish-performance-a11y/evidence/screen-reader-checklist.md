# Roteiro de teste com leitor de tela (tarefa 2.4)

**Quem faz:** uma pessoa usando NVDA (Windows, gratuito: nvaccess.org) com Chrome ou Firefox, ou VoiceOver (iPhone/Mac) com Safari.
**Onde:** ambiente de homologação (ou `npm run e2e` não serve: aqui é uma pessoa navegando de verdade).
**Como anotar:** marque `[x]` no que funcionou; em cada problema, escreva o que o leitor disse e o que deveria ter dito. Os problemas encontrados precisam ser corrigidos antes do lançamento (a spec exige).

Atalhos úteis do NVDA: `Tab`/`Shift+Tab` (controles), `H` (próximo título), `D` (próxima região), `F` (próximo campo), `Insert+F7` (lista de links e títulos), `Insert+Espaço` (alterna modo foco/navegação).
VoiceOver no iPhone: deslizar para a direita/esquerda (próximo/anterior), toque duplo (ativar), rotor com dois dedos girando (títulos, links, campos).

## Antes de começar

- [ ] Ao abrir qualquer página, o leitor anuncia o título da página ("… · OVNIPORTO Lages").
- [ ] O primeiro `Tab` mostra e anuncia "Ir para o conteúdo"; ativá-lo pula o menu.
- [ ] O menu (celular) anuncia "Abrir menu, botão, recolhido"; aberto, o foco fica dentro dele; `Esc` fecha e volta ao botão.

## Fluxo 1: relatar um avistamento (`/relatar`, logado)

Passo 1, O que você viu?
- [ ] O título do passo é anunciado ao chegar ("O que você viu?") e o progresso "Passo 1 de 4".
- [ ] O grupo "Tipo" é anunciado como grupo de opções; cada opção diz o nome e se está marcada.
- [ ] O campo de descrição anuncia o rótulo e o limite de caracteres.
- [ ] "Continuar" com a descrição curta: o erro é anunciado ("Conte um pouco mais: pelo menos 20 caracteres.") e o foco vai para o campo com erro.

Passo 2, Fotos
- [ ] O botão de adicionar foto é anunciado com nome claro; depois de escolher, a miniatura tem texto alternativo.
- [ ] Remover uma foto é possível e anunciado.

Passo 3, Quando e de onde
- [ ] Ao avançar, o novo título é anunciado (o foco vai para ele).
- [ ] Data e faixa de horário têm rótulos.
- [ ] O mapa é anunciado como região "Mapa para marcar de onde você olhou o céu" e lê a instrução de teclado ("mova o mapa com as setas e aperte Enter para marcar o centro").
- [ ] Com o foco no mapa, as setas movem o mapa e `Enter` marca o ponto; algo confirma que o ponto foi marcado.
- [ ] "Usar minha localização atual" funciona como alternativa ao mapa.
- [ ] As sugestões vindas da foto ("A foto diz …") são lidas, com "Usar" e "Ignorar".

Passo 4, Revisão e autorização
- [ ] O resumo lê tipo, descrição, data, local e fotos.
- [ ] A caixa de autorização lê o texto inteiro ("Autorizo publicar este relato, as fotos e o ponto no mapa.").
- [ ] Enviar sem autorizar anuncia o erro.
- [ ] Depois de enviar, a página "Relato na torre de controle" é anunciada.

## Fluxo 2: comprar o adesivo com retirada em Lages (`/loja`)

- [ ] Na loja, cada produto é um link com nome e preço lidos.
- [ ] Na página do produto, variante e quantidade têm rótulos; "Adicionar ao carrinho" anuncia o resultado.
- [ ] O carrinho abre como diálogo ("Carrinho"), lê itens, quantidades e subtotal; `Esc` fecha e volta ao botão do carrinho.
- [ ] "Finalizar compra" leva ao checkout e o carrinho fecha.
- [ ] Checkout, etapa Você: cada campo (nome, e-mail, telefone, CPF) tem rótulo; erros são anunciados e levam ao campo.
- [ ] Etapa Entrega: as opções (retirada em Lages, envio) são um grupo de opções com nome e preço; o CEP anuncia erro de formato.
- [ ] Etapa Revisão: itens, entrega e total são lidos antes de confirmar.
- [ ] Pagamento: a página explica que os dados do cartão vão direto ao PayPal; os campos do PayPal são acessíveis (ou o Pix é uma alternativa clara).
- [ ] A página do pedido lê o número, o status ("Pago") e o diário do pedido.

## Resultado

| Data | Leitor e navegador | Quem testou | Problemas encontrados | Corrigido em |
| --- | --- | --- | --- | --- |
| | | | | |
