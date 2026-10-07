# Tasks

## 1. Cadastro de produtos

- [x] 1.1 Botão **Salvar e voltar** em `/painel/produtos/novo` e redirecionamento para a lista, verificado pelo teste de feature "stays on the new product after Salvar and returns to the list after Salvar e voltar"
- [x] 1.2 Medidas com até duas casas (0,01 a 100 cm), mensagens em português e exibição com vírgula na loja, verificado pelo teste de feature "keeps package sides down to a tenth of a millimetre and refuses finer ones"
- [x] 1.3 Atualizar o manual do painel (`resources/content/manual/produtos.json`, seções "novo" e "medidas"), verificado por `php artisan test --filter=PanelManual`
- [x] 1.4 Rodar Pest, PHPStan, `npm run lint` e `npm test`, verificado tudo verde
