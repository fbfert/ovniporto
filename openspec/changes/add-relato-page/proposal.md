# Proposal

## Why

O Julean enviou o relato do carro amarelo. Até agora ele tinha só um espaço reservado dentro de /origem, e na home o carro amarelo dividia o mesmo cartão com a origem em Cachi. São duas histórias diferentes: a origem real (a viagem a Cachi) e o relato (a noite do Niva). Cada uma merece seu lugar.

## What Changes

- Nova página `/origem/relato`, "O relato do carro amarelo do Julean": capa noturna, aviso de que é relato e não documento, o texto inteiro com as pausas curtas em destaque e um convite para relatar um avistamento.
- O texto entra no bloco editável `legend_body` por uma migration que só escreve se o bloco estiver vazio (produção não roda seeders); o seeder usa o mesmo arquivo `database/data/relato-carro-amarelo.md`.
- Home: o cartão "A origem" passa a mostrar a foto aérea do Ovnipuerto de Cachi, com crédito visível e legenda "Ovnipuerto Cachi", levando a /origem/cachi.
- Home: nova seção "O carro amarelo do Julean" com a abertura do relato e o botão "Ler o relato inteiro".
- /origem: o bloco do relato mostra a abertura e leva à página nova.
- SEO: título e descrição próprios, JSON-LD `ShortStory` com autor Julean, página no sitemap e no `/llms.txt`.

## Capabilities

### New Capabilities
- `relato-page`: a página do relato do carro amarelo e as chamadas para ela.

### Modified Capabilities
<!-- Nenhuma: o requisito de espera honesta enquanto o texto não existe continua valendo. -->

## Impact

- `GetRelato`, `GetCachiCover`, `GetOriginHub`, `HomeController`, `RelatoController`, rota `origin.relato`, `ContentSeo`/`StructuredData`, `BuildSitemap`, `BuildLlmsText`, migration `2026_10_05_130000_fill_yellow_car_relato`, `Pages/Origin/Relato.tsx`, `Components/Home/RelatoSection.tsx`, `Components/Home/OriginSection.tsx`, `Pages/Origin/Hub.tsx`, `i18n/pt-BR.ts`, CLAUDE.md.
