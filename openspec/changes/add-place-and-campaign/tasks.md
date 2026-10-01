# Tasks

## 1. Modelos e seeds

- [ ] 1.1 Criar migrations de `place_spaces`, `site_photos`, `construction_posts`, `campaign_settings`, `supporters` e `sponsors` e verificar com `php artisan migrate:fresh`
- [ ] 1.2 Criar seeds dos 9 espaços em 4 fases e da campanha `planning` e verificar com teste Pest que conta espaços por fase e o estado inicial

## 2. O lugar

- [ ] 2.1 Implementar UseCase e página `/o-lugar` (capa, mapa sob demanda, terreno x conceito, slot 3D, fases, céu escuro, Avise-me), verificado por teste de feature (200, título, "meta 2028", 9 espaços)
- [ ] 2.2 Garantir estados vazios de fotos e 3D, verificado por teste de feature sem fotos cadastradas

## 3. Apoie

- [ ] 3.1 Implementar serviço de Domain que monta a visão pública da campanha por estado, verificado por teste de unidade (`planning` sem URL/placar mesmo com URL preenchida)
- [ ] 3.2 Implementar `/apoie` nos modos `planning` e `open`, verificado por testes de feature: `planning` não contém placar nem link de pagamento; `open` lista só apoiadores com consentimento

## 4. Diário da obra

- [ ] 4.1 Implementar `/obra` e `/obra/{slug}` com filtro de publicação e estado vazio, verificado por testes de feature (vazio, agendado = 404)
- [ ] 4.2 Implementar `/obra.rss`, verificado por teste que valida o XML RSS 2.0 e exclui posts agendados
