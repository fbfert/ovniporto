# Tasks

## 1. Identidade

- [ ] 1.1 Criar migration `members` (google_id, name, email, avatar_url, nickname único, city, role, terms_accepted_at) e verificar com `php artisan migrate:fresh`
- [ ] 1.2 Definir interface `IdentityProvider` no Domain, implementação Google via Socialite e fake, verificado por teste de unidade do UseCase de login com o fake
- [ ] 1.3 Implementar login, callback e modal "Entrar na comunidade", verificado por testes de feature (primeiro login cria membro; segundo não duplica; escopos pedidos são só openid/email/profile)

## 2. Boas-vindas e perfil

- [ ] 2.1 Implementar tela de boas-vindas com sugestão e checagem de apelido e aceite dos termos, verificado por testes de feature (apelido duplicado falha; sem aceite falha)
- [ ] 2.2 Implementar middleware de perfil completo, verificado por teste de feature que redireciona membro incompleto
- [ ] 2.3 Implementar policies de propriedade, verificado por teste de feature de acesso cruzado retornando 403/404

## 3. Minha conta

- [ ] 3.1 Implementar `/conta` com as quatro abas e edição de apelido/cidade, verificado por teste de feature (200 para membro; redireciona visitante)
- [ ] 3.2 Implementar UseCase e job "Baixar meus dados" com e-mail em fila, verificado por teste que usa `Queue::fake` e `Mail::fake`
- [ ] 3.3 Implementar "Excluir minha conta" com confirmação por apelido, verificado por testes Pest (confirmação errada não exclui; exclusão apaga relatos e anonimiza pedidos)
