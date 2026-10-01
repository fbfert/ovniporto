# Tasks

## 1. Acesso, dashboard e auditoria

- [ ] 1.1 Criar layout do painel e gates por área e papel, verificado por testes de feature (moderator não acessa pedidos; store não acessa relatos; member recebe 403)
- [ ] 1.2 Criar `audit_logs` e decorador de auditoria dos UseCases do painel, verificado por teste que executa cada ação e confere o registro
- [ ] 1.3 Implementar dashboard com contagens, metas, gráfico de 12 semanas e "Precisa de você", verificado por teste de feature com dados fabricados nos limites (48 h, 2 dias, 7 dias)
- [ ] 1.4 Implementar `/painel/membros` (papel só por admin, bloqueio com motivo, exclusão) e `/painel/auditoria`, verificado por testes de feature de autorização e de bloqueio impedindo relato

## 2. Moderação de relatos

- [ ] 2.1 Implementar fila com abas e cidade aproximada via porta `Geocoder` cacheada, verificado por teste de feature e teste de unidade do cache
- [ ] 2.2 Implementar aprovar, pedir ajuste, rejeitar e despublicar com e-mails e invalidação de cache, verificado por testes: aprovar publica e envia e-mail; rejeitar exige motivo; ajuste some do público
- [ ] 2.3 Implementar edição pelo autor em `/relatar/{id}/editar` reaproveitando o assistente, verificado por teste de feature (reenvio volta a `pending`; outro membro recebe 403)
- [ ] 2.4 Implementar atalhos de teclado e checklist, verificado por teste de componente dos atalhos

## 3. Loja

- [ ] 3.1 Implementar lista e ficha de pedidos com CPF mascarado e revelação auditada, verificado por teste de feature
- [ ] 3.2 Implementar ações por status, etiqueta e "Marcar enviado", verificado por testes: transição inválida falha; etiqueta só em pedido pago
- [ ] 3.3 Implementar reembolso via `PaymentGateway`, verificado por teste que confirma o evento de reembolso
- [ ] 3.4 Implementar ordem de produção em PDF e exportação CSV, verificado por teste que gera os arquivos e confere conteúdo
- [ ] 3.5 Implementar CRUD de produtos, variantes, imagens (alt obrigatório) e histórico de estoque, verificado por testes de feature

## 4. Conteúdo e campanha

- [ ] 4.1 Implementar `/painel/configuracoes` (links, metas, percentual, blocos markdown com prévia, FAQ, regras), verificado por teste de feature só-admin
- [ ] 4.2 Implementar `/painel/lugar` e `/painel/obra` com whitelist de domínio do 3D e agendamento, verificado por testes (domínio fora da lista recusado; post futuro invisível)
- [ ] 4.3 Implementar `/painel/regiao` com geocodificação e consentimento obrigatório, verificado por teste de que parceiro sem consentimento não publica
- [ ] 4.4 Implementar `/painel/campanha` com aviso fixo, apoiadores (CSV) e patrocinadores, e `/painel/avise-me` com exportação e remoção, verificado por testes de autorização e de importação CSV
