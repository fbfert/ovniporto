# sighting-submission Specification

## Purpose
Permite que membros enviem relatos de avistamento em um assistente de 4 passos pensado para o celular à noite, com fotos sem metadados, ponto escolhido pela própria pessoa e consentimento explícito.

## Requirements

### Requirement: Relatar exige login
A rota `/relatar` MUST exigir um membro autenticado com perfil completo.

#### Scenario: Visitante sem login
- **WHEN** um visitante anônimo abre `/relatar`
- **THEN** é convidado a entrar com Google e não vê o assistente

### Requirement: Assistente em quatro passos
O assistente SHALL conduzir a pessoa por quatro passos, um por tela, com barra de progresso e botão principal fixo no rodapé: (1) tipo (Luz, Objeto, Rastro, Outro) e descrição de 20 a 1000 caracteres; (2) até 3 fotos opcionais com opção "Pular"; (3) data, faixa (Anoitecer, Noite, Madrugada) ou hora exata, ponto no mapa e direção do olhar opcional; (4) revisão e envio.

#### Scenario: Descrição curta
- **WHEN** a pessoa tenta avançar do passo 1 com descrição de 10 caracteres
- **THEN** o avanço é bloqueado e o contador indica o mínimo de 20

#### Scenario: Pular fotos
- **WHEN** a pessoa aciona "Pular" no passo 2
- **THEN** segue para o passo 3 sem fotos

### Requirement: Rascunho local
O assistente SHALL salvar o rascunho no próprio dispositivo a cada mudança e restaurá-lo quando a pessoa voltar a `/relatar`.

#### Scenario: Retomar rascunho
- **WHEN** a pessoa fecha a aba no passo 3 e reabre `/relatar`
- **THEN** os dados preenchidos são restaurados

### Requirement: EXIF só como sugestão no navegador
Quando uma foto tiver GPS ou data/hora no EXIF, o navegador SHALL usar esses dados apenas para pré-preencher o passo 3, marcados como "sugerido pela foto", e MUST enviar ao servidor um arquivo regravado sem nenhum metadado, com no máximo 2000 px no maior lado. Os avisos "O arquivo publicado vai sem esses dados." e "Sem rostos nem placas de carro." MUST ser exibidos.

#### Scenario: Foto com GPS
- **WHEN** a pessoa escolhe uma foto com coordenadas e data no EXIF
- **THEN** o passo 3 mostra o ponto e a data como "sugerido pela foto" e o arquivo enviado não contém EXIF

### Requirement: EXIF nunca persistido
O servidor MUST remover todo metadado (EXIF, XMP, IPTC) de cada foto recebida antes de armazená-la e MUST gerar as variantes 400, 800 e 1600 px em WebP também sem metadados, mesmo que o arquivo já chegue limpo. Arquivos aceitos: jpg, png e heic, até 8 MB cada.

#### Scenario: Arquivo com GPS enviado direto ao servidor
- **WHEN** um arquivo com GPS e DateTimeOriginal é enviado à rota de upload sem passar pelo navegador
- **THEN** nenhum arquivo armazenado (original ou variante) contém GPS ou outro metadado

#### Scenario: Arquivo grande demais
- **WHEN** um arquivo de 9 MB é enviado
- **THEN** o upload é recusado com mensagem de limite

### Requirement: Ponto escolhido pela pessoa
O ponto no mapa SHALL ser colocado ou confirmado pela pessoa; a localização do aparelho só é usada após permissão explícita e com explicação. O aviso "Marque de onde olhou o céu, não sua casa." MUST ficar visível. O ponto MUST estar a até 300 km de Lages.

#### Scenario: Ponto fora do raio
- **WHEN** o relato é enviado com ponto a 400 km de Lages
- **THEN** o envio falha com erro de validação e nada é gravado

### Requirement: Consentimento por relato
O envio MUST exigir a marcação explícita e separada de "Autorizo publicar este relato, as fotos e o ponto no mapa."; a data do consentimento SHALL ser registrada no relato. O apelido público é pré-preenchido do perfil e pode ser alterado só para este relato.

#### Scenario: Envio sem consentimento
- **WHEN** a pessoa envia o relato sem marcar a autorização
- **THEN** o envio falha e nenhum relato é criado

### Requirement: Relato entra em análise
Todo relato enviado SHALL ser criado com status `pending`, não aparecer publicamente e gerar e-mail de confirmação ao autor e aviso aos moderadores, ambos em segundo plano. A tela final SHALL mostrar "Relato na torre de controle, em análise."

#### Scenario: Envio bem-sucedido
- **WHEN** um relato válido com consentimento é enviado
- **THEN** ele fica `pending`, o autor vê a confirmação e os dois e-mails são enfileirados

### Requirement: Fotos pendentes privadas
Fotos de relatos não aprovados MUST ficar fora de qualquer URL pública e SHALL ser acessíveis apenas por URL assinada temporária, para o autor e moderadores.

#### Scenario: URL direta de foto pendente
- **WHEN** alguém tenta acessar o caminho de uma foto pendente sem assinatura válida
- **THEN** recebe 404 ou 403
