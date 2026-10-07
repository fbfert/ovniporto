# Achados ao escrever o manual do painel (2026-10-07)

Para escrever o manual (`/painel/manual`), cada tela foi lida contra o código. O manual descreve o que o código **faz hoje**. Esta lista guarda o que pareceu defeito, risco ou incoerência, para virar changes OpenSpec. Nada aqui foi corrigido, exceto o que está marcado como feito.

Prioridade: **A** = dinheiro, dados pessoais ou ação que não se desfaz; **B** = operador recebe informação errada; **C** = acabamento.

## A. Dinheiro, LGPD e ações sem volta

1. **Etiqueta pode ser comprada duas vezes.** `MelhorEnvioShippingProvider::createLabel` paga antes de gerar a etiqueta; se a geração falha depois do pagamento, nada é salvo no pedido e **Gerar etiqueta** continua visível (`OperateOrders::actionsFor` só olha se há código de rastreio). Outro clique compra de novo. O manual manda conferir a conta do Melhor Envio antes de repetir.
2. **Pix que chega depois do cancelamento não tem reembolso pelo painel.** A nota diz "reembolsar", mas pedido cancelado não tem ações e não pode passar a reembolsado. Hoje só pelo PayPal, à mão.
3. **Remover do Avise-me ou dos colaboradores deixa o e-mail no livro de consentimentos.** `ManageWaitlist::remove` e `ManageCollaborators::remove` apagam a linha, mas o registro em `consents` (e-mail, IP, navegador) fica. Só a exclusão de conta anonimiza (`DatabaseConsentLedger::anonymize`).
4. **Membro bloqueado pode ganhar papel de painel.** O acesso ao painel não olha `blocked_at`; as regras só impedem bloquear quem já tem papel.
5. **Reembolso grava o status depois de devolver o dinheiro.** O ID de requisição do PayPal torna a chamada repetível, mas nada amarra as duas etapas se a segunda falhar.
6. **Campanha pode ser posta como "Aberta" sem trava no servidor.** Só o aviso amarelo do painel segura, e o CLAUDE.md proíbe arrecadar antes do orçamento.

## B. Informação errada para quem opera ou para o cliente

7. **Cliente não recebe e-mail de cancelamento**, nem manual nem automático (2 h sem pagamento). `MailOrderNotifier` devolve null para Canceled.
8. **Pedido de retirada recebe "A caminho / Seu pedido saiu de Lages"** ao ser marcado como enviado.
9. **Erros de etiqueta mostram o texto do cliente** ("cálculo de frete está fora do ar... retirar em Lages"), escondendo causas como saldo do Melhor Envio.
10. **Mudança de status recusada mostra códigos crus**: `Um pedido "pending_payment" não pode passar para "refunded"`.
11. **Spec x código no cancelamento:** `store-operations` diz "qualquer → Cancelar"; o código só cancela enquanto espera pagamento.
12. **Aviso de fila longa não vai para ninguém com `ALERTS_EMAIL` vazio.** O comentário em `config/ovniporto.php` diz que vai para todos os admins; isso só vale para tarefa que falhou. *(O `DEPLOY.md` foi corrigido nesta entrega.)*
13. **Markdown não é renderizado em todo lugar que o editor oferece.** Os blocos da home (`home_intro`, `home_place`, `home_store`, `home_legend`) e as regras da comunidade saem como texto puro; `**` e `##` aparecem literalmente.
14. **Rótulo "vazio: o site mostra aguardando conteúdo"** não corresponde: bloco da home some, `legend_body` mostra o aviso do relato, texto legal mostra o aviso de rascunho.
15. **Fase 5 some da linha do tempo.** O painel oferece Fase 1–5 (o servidor aceita até 9), mas `/o-lugar` só desenha 1–4.
16. **Erros de upload ficam mudos:** ilustração "conceito" dos espaços (`router.post` fora do `useForm`) e comprovante de consentimento de parceiro (`form.errors.consentProof` não é exibido).
17. **"Achar no mapa" diz "Endereço não encontrado."** também quando bate o limite de 30/min ou o Nominatim cai.
18. **"Publicar" parceiro só lê a data de consentimento salva**; digitar a data sem salvar deixa o botão desligado sem explicação. Apagar a data tira o parceiro do site sem aviso.
19. **Aviso "Produto criado (inativo até você ativar)"** aparece mesmo quando a caixa **Ativo** foi marcada e o produto já entrou na loja.
20. **Início mostra gráfico e metas de todas as áreas para todo papel** (moderação vê pedidos; loja vê relatos). Os contadores e "Precisa de você" já filtram.
21. **Metas contam totais de sempre**, não o que entrou depois de `launch_date`.
22. **Relato despublicado volta à fila já "atrasado"** (mantém o `submitted_at` antigo).
23. **Agendamentos em UTC:** rastreio "07:00" roda às 04:00 de Brasília; o faturado do mês vira às 21h do último dia.
24. **Loja não recebe e-mail de pedido pago comum**; só quando o pagamento pede conferência.
25. **Avise-me com link vencido (7 dias) não tem como confirmar**: nova inscrição com o mesmo e-mail não reenvia.
26. **Auditoria:** produto, lugar, FAQ/regras, apoiadores e CPF não guardam antes/depois; criações aparecem como "#0"; `collaborator.removed` não tem rótulo em `pt-BR.ts`; pessoa excluída vira "sistema".
27. **Motivos de rejeição:** o chip diz "Placa de carro"; o e-mail ao autor diz "Foto com placa de carro".

## C. Acabamento

28. CSV de pedidos usa ponto decimal (`45.90`) com separador `;` (Excel pt-BR pode ler errado) e status em código interno.
29. Link do PDF de produção some em pedidos cancelados ou reembolsados (a URL continua funcionando).
30. E-mail "Em produção" diz "feito à mão" mesmo para itens de pronta entrega.
31. Pílula de 48 h aparece também nas abas Aprovados e Rejeitados.
32. Capa do diário e ilustração de espaço só podem ser trocadas, não removidas.
33. Endereço público de parceiro e de post não muda ao renomear (documentado como comportamento).
34. Importar o mesmo CSV de apoiadores duas vezes duplica tudo.
35. Strings sem uso: `t.panel.home.openQueue`, `t.panel.home.approved`, `t.panel.audit.when/who/what/subject/change`.
36. Specs desatualizadas: `content-admin` cita "percentual da loja" em Configurações (não existe); `content-pages` ainda fala em `/lenda`.
37. Filas e Saúde não estão no menu do painel (só pelo endereço).
38. Aviso no Pest: `BuildSitemap.php:40` lê `reference` inexistente (2 warnings em toda execução da suíte).
39. Promoção do primeiro admin por SQL não passa pela auditoria (documentado; recomendação: ter dois admins).
