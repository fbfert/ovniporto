**Para:** Felipe (encarregado de dados do OVNIPORTO)
**Assunto:** OVNIPORTO: privacidade pronta no site, preciso da sua revisão e de 3 confirmações

---

Oi, Felipe, tudo bem?

Terminamos a parte de privacidade e LGPD do site do OVNIPORTO (ovniporto.tars.art.br). Tudo o que está abaixo já está funcionando e coberto por testes automáticos. O site ainda não foi aberto ao público, então dá tempo de ajustar o que você achar necessário.

Preciso de você em três frentes: **confirmar três decisões**, **escrever os textos finais** de privacidade e termos e **revisar a lista "O que fazemos na prática"**.

## 1. O que o site já faz hoje

- **Fotos de relato sem metadados.** Toda foto enviada perde GPS, data, modelo da câmera e qualquer outro metadado (EXIF, XMP, IPTC) antes de ser gravada no servidor. O navegador só lê esses dados localmente para sugerir local e hora, e a pessoa confirma o ponto no mapa.
- **Relatos em análise ficam fechados.** As fotos de um relato ainda não aprovado só abrem por um link que vale 10 minutos, e só para o autor, a moderação e o admin. A equipe da loja não vê.
- **Mínimo do Google.** Do login com Google guardamos só nome, e-mail, foto de perfil e o identificador da conta, e pedimos apenas as permissões básicas (openid, email, profile). Em público aparece só o apelido.
- **Cartão nunca passa por nós.** Os dados de cartão vão do navegador direto para o PayPal.
- **Registro de consentimentos.** Cada "sim" fica registrado com tipo, versão do texto, data, IP e navegador:
  - aceite dos termos;
  - autorização para publicar cada relato (registrada de novo se o relato for reenviado);
  - inscrição no Avise-me da campanha;
  - autorização dos parceiros da região para aparecer no site (com o comprovante guardado em área privada).
- **Novo aceite quando os termos mudam.** Quando um texto final de termos ou de privacidade é publicado no painel, cada membro precisa aceitar de novo antes de continuar usando a conta.
- **Baixar meus dados.** Em "Minha conta" a pessoa recebe por e-mail um arquivo JSON com perfil, relatos (com links das fotos), pedidos (com CPF mascarado), inscrições e consentimentos. Só os dados dela.
- **Excluir conta.** Ao excluir a conta:
  - relatos, fotos e todas as cópias das fotos são apagados na hora;
  - pedidos pagos ficam guardados sem nome, e-mail, telefone nem endereço (só cidade e UF), com o cliente marcado como "Titular excluído", mantendo número, itens, valores e CPF criptografado, por causa da obrigação fiscal;
  - uma rotina diária apaga esses pedidos de vez quando o prazo de guarda vence;
  - pedidos não pagos não têm obrigação fiscal e são apagados na próxima execução dessa rotina;
  - os registros de consentimento ficam anonimizados: guardamos tipo, versão e data, sem nada que aponte para a pessoa;
  - a inscrição no Avise-me com o e-mail da conta também é apagada.
- **Sem rastreamento.** Nenhum cookie de terceiros, nenhum anúncio, nenhum pixel. O site só cria o cookie de sessão e o de proteção de formulários. A contagem de visitas usa um sistema próprio (Umami, no nosso servidor), sem cookie e respeitando o "Não rastrear" do navegador. Até as fontes do site são servidas pelo nosso servidor, então abrir uma página não envia o IP do visitante a nenhuma empresa de fora.
- **Logs do servidor.** Os registros de acesso, que contêm IP, são apagados depois de no máximo 6 meses.
- **Dados no Brasil.** Tudo roda numa VPS no Brasil.

## 2. Preciso que você confirme

1. **Prazo de guarda fiscal dos pedidos: 5 anos após o pagamento.** Usei o prazo do CTN (arts. 173 e 174). Se o correto for outro prazo ou outro marco inicial (por exemplo, a partir do ano seguinte ao da venda), é uma mudança simples de configuração.
2. **Retenção dos logs de acesso: 6 meses.** Ficou alinhado ao Marco Civil da Internet (art. 15). Se preferir menos, ajusto.
3. **Consentimentos anonimizados após a exclusão da conta.** Optamos por manter tipo, versão e data, sem IP, navegador, e-mail ou vínculo com a pessoa. Se entender que devem ser apagados por completo, ou guardados identificados por algum prazo para defesa em eventual processo, me diga.

## 3. Textos finais de privacidade e termos

As páginas /privacidade e /termos estão com o aviso de rascunho e com a estrutura de tópicos pronta para você escrever. A política de privacidade tem 9 tópicos:

- Dados coletados
- Finalidades
- Bases legais
- Compartilhamento
- Retenção
- Direitos do titular
- Contato do encarregado
- Cookies
- Alterações

Você edita direto no painel, em Configurações. Quando marcar o texto como **final**:

- o aviso de rascunho some;
- a data de publicação vira a nova versão dos termos;
- todos os membros precisarão aceitar de novo no próximo acesso.

Por isso, sugiro publicar os dois textos (privacidade e termos) juntos, para pedir um único novo aceite.

Para os tópicos de compartilhamento: os serviços externos que recebem dados são o Google (login), o PayPal (pagamento), o Melhor Envio (frete e etiqueta, com nome, endereço e CPF de quem compra), o ViaCEP (consulta de endereço pelo CEP), o OpenStreetMap/Nominatim (mapa e busca de endereço dos parceiros) e o provedor de e-mail transacional.

## 4. Revisão de "O que fazemos na prática"

No topo de /privacidade aparece uma lista com as garantias acima, em linguagem simples. Os números dela (10 minutos, 5 anos, 6 meses) saem direto do código, então nunca ficam diferentes do que o sistema faz. Se quiser mudar alguma redação, me mande o texto e eu atualizo.

Se for mais fácil, posso te dar acesso ao ambiente de homologação para ver tudo funcionando antes do lançamento.

Obrigado!

[seu nome]
OVNIPORTO Lages

---

## Resposta do Felipe (recebida em 2026-10-16)

1. Prazo de guarda fiscal: 5 anos. **Confirmado**, sem mudança (`PRIVACY_FISCAL_RETENTION_YEARS=5`, contado a partir do pagamento).
2. Logs de acesso: 6 meses. **Confirmado**, sem mudança (`docker/nginx/logrotate.conf`, 26 arquivos semanais).
3. Consentimentos anonimizados após a exclusão: **ok**, sem mudança.
4. Fontes: liberou o Google Fonts. Desde o commit c111c9f as fontes já são servidas pelo nosso servidor, o que protege mais a privacidade, então **mantivemos assim**.

Ainda pendente: os textos finais de /privacidade e /termos (seção 3) e a revisão da lista "O que fazemos na prática" (seção 4).
