# Tasks

## 1. Domain e dados

- [x] 1.1 Criar migrations `sightings` e `sighting_photos` com status e `consent_given_at`, verificado por `php artisan migrate:fresh`
- [x] 1.2 Implementar UseCase `SubmitSighting` com validação (consentimento, raio de 300 km, até 3 fotos, faixa xor hora), verificado por testes de unidade: sem consentimento falha; ponto fora do raio falha; 4 fotos falha

## 2. Fotos e privacidade

- [x] 2.1 Definir interfaces `ImageProcessor` e `PhotoStorage` no Domain com implementações em Infrastructure, verificado por teste de unidade com fakes
- [x] 2.2 Implementar rota de upload temporário privado (8 MB, jpg/png/heic) e limpeza de órfãos, verificado por testes de feature (9 MB recusado; arquivo não acessível por URL pública)
- [x] 2.3 Implementar job de remoção de metadados e variantes 400/800/1600 WebP, verificado por teste que envia foto com GPS e lê os arquivos finais confirmando ausência de EXIF
- [x] 2.4 Implementar URL assinada temporária para fotos pendentes (autor e moderadores), verificado por teste de feature (sem assinatura = 404/403)

## 3. Assistente

- [x] 3.1 Implementar passos 1 e 2 (tipo, descrição, fotos com leitura de EXIF e regravação sem metadados no cliente), verificado por teste de componente que confirma que o blob enviado não tem EXIF
- [x] 3.2 Implementar passo 3 (data, faixa/hora, mapa, geolocalização com permissão, direção com bússola), verificado por teste de componente da sugestão "sugerido pela foto"
- [x] 3.3 Implementar passo 4, envio e tela de confirmação, verificado por teste de feature (relato criado `pending`; e-mails enfileirados com `Mail::fake`)
- [x] 3.4 Implementar rascunho local e restauração, verificado por teste de componente que recarrega o estado
