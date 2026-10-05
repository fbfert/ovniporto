# Tasks

## 1. Domínio e dados

- [x] 1.1 Criar `CollaborationArea`, `CollaboratorRepository`, `CollaboratorNotifier`, o tipo de consentimento `colaboracao` e a tabela `research_collaborators`
- [x] 1.2 Implementar `ApplyAsCollaborator` e `ManageCollaborators`, verificados por testes de unidade

## 2. Rotas e e-mails

- [x] 2.1 `POST /colaborar` com FormRequest, limite e honeypot, e-mails na fila, verificado por teste de feature
- [x] 2.2 `/painel/colaboradores` com lista, CSV e remoção auditada, verificado por teste de feature (admin sim, moderador 403)
- [x] 2.3 Incluir as ofertas em "Baixar meus dados" e "Excluir conta"

## 3. Interface

- [x] 3.1 Link e modal com o formulário abaixo de "Limites desta pesquisa", verificado por teste de componente
- [x] 3.2 Tela do painel e atalho no hub de Conteúdo
- [ ] 3.3 Conferir no navegador em 390px e no desktop (foco, Esc, alvos de 44px) e o e-mail real em staging
