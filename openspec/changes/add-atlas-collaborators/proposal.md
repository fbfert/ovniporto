# Proposal

## Why

O Atlas dos Ovnipuertos termina listando os limites da pesquisa: fontes que faltam, casos sem vistoria, traduções pendentes. Quem lê e conhece um desses lugares, lê o idioma de uma fonte ou tem fotos com licença aberta hoje não tem como oferecer ajuda. Queremos transformar a lista de lacunas em convite.

## What Changes

- Abaixo de "Limites desta pesquisa", em `/origem/atlas`, um link "Quero colaborar com a pesquisa" abre um formulário em modal.
- Campos: nome, e-mail, cidade e país, como pode ajudar (pesquisa e fontes, tradução, fotos com licença aberta, vistoria em campo, outra forma; uma ou mais), mensagem e consentimento explícito (LGPD).
- `POST /colaborar` guarda a oferta, registra o consentimento no livro de consentimentos (tipo `colaboracao`), envia e-mail de agradecimento à pessoa e um aviso aos administradores (só o número da oferta; os dados ficam atrás do login).
- Proteção contra abuso: limite de 5 envios a cada 10 minutos por IP e campo-armadilha (honeypot) que recebe a mesma resposta de uma pessoa sem guardar nada.
- `/painel/colaboradores` (área Conteúdo, só administradores): lista, exportação CSV e remoção auditada.
- "Baixar meus dados" e "Excluir conta" passam a incluir as ofertas enviadas com o e-mail da conta.

## Capabilities

### New Capabilities
- `research-collaborators`: ofertas de ajuda na pesquisa da origem, do formulário público ao painel.

### Modified Capabilities
<!-- Nenhuma: usa o livro de consentimentos e o painel de conteúdo existentes sem mudar seus requisitos. -->

## Impact

- Nova tabela `research_collaborators`; novo tipo de consentimento `colaboracao`.
- Rotas `POST /colaborar`, `/painel/colaboradores`, `/painel/colaboradores/exportar`, `DELETE /painel/colaboradores/{id}`.
- `Pages/Origin/Atlas.tsx`, novo `Components/Origin/CollaboratorForm.tsx`, nova `Pages/Panel/Content/Collaborators.tsx`, `i18n/pt-BR.ts` (bloco `collaborator` e `panel.collaborators`).
