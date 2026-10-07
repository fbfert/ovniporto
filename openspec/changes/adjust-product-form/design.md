# Design

## Decisões

- **O botão clicado decide o destino.** Os dois botões são `submit` do mesmo formulário; o front lê o `submitter` do evento e manda `returnToList: true` só no **Salvar e voltar**. O controller redireciona para `panel.products` ou para `panel.products.edit`. Na edição de um produto existente não há segundo botão: **Salvar** já mantém a pessoa na página.
- **Centímetros com duas casas, não milímetros.** A unidade continua sendo centímetro (é a do Melhor Envio e a dos produtos já cadastrados); o campo aceita passo 0,01. Validação `numeric`, `decimal:0,2`, `min:0.01`, `max:100`; o valor é guardado com `round(..., 2)`.
- **Sem migração.** `dimensions` é JSON; inteiros antigos e decimais novos convivem.
- **Fora do escopo:** usar as medidas de cada produto na cotação de frete (hoje é a embalagem padrão da configuração).
