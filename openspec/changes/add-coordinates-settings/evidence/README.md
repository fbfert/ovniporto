# Evidência: Coordenadas (2026-10-07)

Conferência no navegador (Playwright, Chromium) em `http://127.0.0.1:8077/painel/coordenadas`, como admin, com um SMTP falso local que aceita só a senha "certa" e recusa as outras com `535`.

| Passo | Resultado |
|---|---|
| Salvar Correio com senha errada | campo de senha volta vazio; legenda "Senha definida. Deixe em branco para manter." |
| Enviar e-mail de teste (senha errada) | "Não foi enviado. O servidor recusou o usuário ou a senha." com a resposta do servidor; usuário, senha e o pacote AUTH em base64 aparecem como `***` |
| Senha presente no HTML da página | não |
| Enviar e-mail de teste (senha certa) | "Enviado para <e-mail do admin>" |
| Alertas vazios | aviso amarelo "Sem endereço: ..." |
| Enviar alerta de teste | "Alerta de teste enviado para o endereço de alertas." |
| CNPJ com dígito errado | "CPF ou CNPJ inválido. Confira os números." |
| Início do painel | cartão "Coordenadas" |
| "Como funciona" | `/painel/manual/coordenadas#regra` |
| Remover senha (diálogo próprio) | "Nenhuma senha definida." |
| 1440 px e 390 px | rolagem horizontal 0, alvos menores que 44 px: 0, axe (WCAG 2.1 AA) sem violações |

Capturas:

- `coordenadas-teste-falhou-1440.png`: teste com login recusado.
- `coordenadas-1440.png`: tela inteira depois de salvar os três blocos.
- `coordenadas-390.png`: celular.

Ajustes feitos a partir desta conferência:

- Trechos em base64 retirados da resposta do servidor.
- Luz apagada para uma integração opcional desligada (antes ficava verde).
- Linhas do quadro de Integrações com o mesmo desenho em qualquer largura.
