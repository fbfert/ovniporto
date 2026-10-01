# Tasks

## 1. Consulta pública e API

- [ ] 1.1 Implementar UseCase de relatos públicos com filtros de período e tipo, verificado por teste de unidade (só aprovados)
- [ ] 1.2 Implementar `GET /api/sightings` com Resource de campos públicos e cache de 60 s invalidado por evento, verificado por testes de feature (não aprovados ausentes; só os 7 campos)

## 2. Página /mapa

- [ ] 2.1 Implementar capa com contador, mapa com marcadores agrupados e popup polaroid, verificado por teste de feature (200, contador correto)
- [ ] 2.2 Implementar filtros com estado na URL e partial reload, verificado por teste de feature com query string
- [ ] 2.3 Implementar grade de polaroids com "Carregar mais" e botão flutuante "Relatar" no mobile, verificado por teste de componente da paginação

## 3. Página do relato

- [ ] 3.1 Implementar `/relatos/{id}` com galeria, ficha, carimbo e mini-mapa, verificado por testes de feature (aprovado 200; não aprovado 404 ao público; autor vê "Em análise")
- [ ] 3.2 Implementar "Mande um postal" (WhatsApp e copiar link), verificado por teste de componente do texto e URL gerados
- [ ] 3.3 Implementar "Outros relatos perto daqui" (20 km, até 3), verificado por teste de unidade da consulta por raio
