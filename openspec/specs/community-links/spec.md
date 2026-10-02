# community-links Specification

## Purpose
Os canais da comunidade (WhatsApp, Instagram, e-mail) aparecem do mesmo jeito em qualquer página, vindos do conteúdo editável.

## Requirements

### Requirement: Links da comunidade em todas as páginas
O site SHALL disponibilizar os links de WhatsApp, Instagram e e-mail de contato a todas as páginas. Um link não cadastrado SHALL aparecer como "em breve", sem link quebrado.

#### Scenario: Página que não é a home
- **WHEN** o WhatsApp está cadastrado e a pessoa abre qualquer página
- **THEN** o rodapé e o menu mobile mostram o link do WhatsApp

#### Scenario: Instagram ainda não cadastrado
- **WHEN** o link do Instagram está vazio
- **THEN** aparece "Instagram · em breve" sem link
