# Tasks

## 1. Fotos e acesso

- [x] 1.1 Escrever teste de fluxo completo com foto contendo GPS e DateTimeOriginal que inspeciona os bytes de todos os arquivos salvos, verificado por `php artisan test` passando
- [x] 1.2 Ajustar URLs assinadas de fotos pendentes para 10 min e só moderador/admin/autor, verificado por testes (expirada negada; outro membro recusado; rota pública 404)
- [x] 1.3 Verificar escopos e campos do Google, verificado por teste que inspeciona a URL de autorização e as colunas persistidas

## 2. Consentimentos

- [x] 2.1 Criar tabela `consents` e porta `ConsentLedger` integrada aos quatro pontos de consentimento, verificado por testes de feature em cada ponto
- [x] 2.2 Implementar versão dos termos e novo aceite no login, verificado por teste de feature com troca de versão

## 3. Portabilidade e exclusão

- [x] 3.1 Implementar exportação com providers por módulo, verificado por teste que confere as cinco seções e ausência de dados de terceiros
- [x] 3.2 Implementar exclusão com anonimização e `retention_until`, verificado por teste de pedido "Titular excluído" e fotos/variantes apagadas do disco
- [x] 3.3 Implementar comando agendado de apagamento após retenção, verificado por teste com tempo simulado

## 4. Transparência e rastreamento

- [x] 4.1 Gerar seção "O que fazemos na prática" em `/privacidade` a partir da lista de garantias, verificado por teste de feature
- [x] 4.2 Configurar logrotate de 6 meses no container web, verificado por `logrotate -d` no container mostrando a política
- [x] 4.3 Escrever teste que carrega a home e confere só cookies de sessão e XSRF, verificado por `php artisan test` passando
