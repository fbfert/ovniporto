# Proposal

## Why

A referência de localização da futura pista mudou: os fundadores passam a chamá-la de "Localidade Pedras Brancas" (Lages, SC), e não mais "ao lado da Hospedaria Vila das Pedras".

## What Changes

- Todas as menções a "Vila das Pedras" nos textos do site, nos textos iniciais editáveis, nos dados de demonstração, no CLAUDE.md e no README passam a dizer "Localidade Pedras Brancas", com a gramática ajustada ("na Localidade Pedras Brancas").
- O ponto no mapa e as coordenadas não mudam.

## Capabilities

### New Capabilities

### Modified Capabilities
- `place-project`: a capa de `/o-lugar` passa a mostrar "Na Localidade Pedras Brancas".
- `public-layout`: o rodapé passa a mostrar "Localidade Pedras Brancas · Lages, SC".

## Impact

`resources/js/i18n/pt-BR.ts`, `database/seeders/ContentBlockSeeder.php`, `database/seeders/FaqSeeder.php`, `app/Console/Commands/SeedDemoData.php`, `resources/js/Pages/Dev/Styleguide.tsx`, `config/ovniporto.php` (comentário), `CLAUDE.md`, `README.md`, `docs/ovniporto-prompts-construcao-site.md`. Bancos já semeados mantêm o texto antigo nos blocos editáveis até alguém editar no painel; produção ainda não foi semeada.
