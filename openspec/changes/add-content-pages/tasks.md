# Tasks

## 1. Conteúdo e dados

- [ ] 1.1 Criar migrations e modelos de `content_blocks`, `faqs` e `community_rules` no módulo Content e verificar com `php artisan migrate:fresh` sem erro
- [ ] 1.2 Criar seeds (10 perguntas, 5 regras, blocos vazios da lenda, rascunhos de privacidade/termos) e verificar com teste Pest que conta os registros
- [ ] 1.3 Implementar UseCase de leitura de bloco com cache e sanitização de markdown, verificado por teste de unidade (vazio retorna estado vazio; HTML bruto é removido)

## 2. Páginas

- [ ] 2.1 Implementar `/lenda` com origem real, linha do tempo de Cachi e estado "aguardando conteúdo", verificado por testes de feature para bloco vazio e preenchido
- [ ] 2.2 Implementar `/faq` com acordeão de uma resposta aberta por vez, verificado por teste de feature (200 + título) e teste de componente do `aria-expanded`
- [ ] 2.3 Implementar `/comunidade` com regras, links e formulário Avise-me, verificado por teste de feature (200 + título + links presentes)
- [ ] 2.4 Implementar `/privacidade` e `/termos` com índice lateral, data de atualização e aviso de RASCUNHO, verificado por teste de feature que procura o aviso e os 9 tópicos
- [ ] 2.5 Centralizar todos os textos em `resources/js/i18n/pt-BR.ts` e verificar com `npm run typecheck` sem erros
