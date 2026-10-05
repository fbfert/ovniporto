# Revisão visual das páginas da origem (2026-10-05)

## Como foi feita

Capturas de página inteira em 390 e 1440 px, com movimento reduzido, de:
- `/origem`;
- `/origem/cachi`;
- `/origem/atlas`;
- `/origem/atlas/st-paul`, `/origem/atlas/lages`, `/origem/atlas/green-river` e `/origem/atlas/cachi`.

O site rodava localmente com dados de demonstração. As capturas finais estão em `screens/`, reduzidas para 900 px de largura.

## Problemas encontrados e corrigidos

| Gravidade | Página | Problema | Correção |
|---|---|---|---|
| quebra | `/origem` | O placeholder "conceito em produção" saía como retângulo preto vazio no polaroid do carro e nas duas portas. `relative` fixo vencia o `absolute inset-0` do chamador, e o bloco sem conteúdo colapsava. | `OriginConcept` recebe o posicionamento só pelo `className` (`relative` por padrão). |
| feio | `/origem/atlas` | O rótulo "CONFIANÇA" escapava do selo pequeno dos carimbos. | O selo pequeno mostra só o grau; o grande mantém o rótulo, menor. O grau completo é lido pelo `aria-label`. |
| feio | `/origem/atlas` | "data não localizada" e "2026 em diante" apareciam no estilo vazado gigante dos anos. | Na linha do tempo, só datas numéricas ficam vazadas; frases ficam em texto sólido. |
| feio | `/origem/atlas` | Os créditos usavam o nome do arquivo ("UFO Landing Pad St. Paul.jpg") como legenda. | A legenda passou a ser o nome do caso. |
| feio | `/origem/atlas` | No celular, os créditos em uma coluna deixavam a página com quase 20 mil px. | Duas colunas também no celular. |
| detalhe | `/origem/atlas` | "1 fontes". | Singular e plural. |
| feio | `/origem/cachi` | Uma foto única na largura do texto, em 4:3, ficava com ~730 px de altura. | Foto única em 16:9; pares continuam em 4:3. |
| detalhe | casos do Atlas | A linha "Fontes-chave" mostrava identificadores crus e "Confiança" repetia o selo. | As duas linhas saem da tabela, porque as fontes estão listadas ao lado com título e link. |

## Conferido e mantido

- **Cachi:** capa com a foto aérea e crédito no canto, índice numerado fixo, relatos em cartões-ingresso com o tipo de fonte escrito, linha do tempo vazada, galeria com crédito em cada foto. Os vídeos do YouTube carregam só no clique; Dailymotion e Vimeo ficam como cartões com link.
- **Atlas:** mapa-múndi com Lages como o disco do site, carimbos com o grau, escala A–F, candidatos e catálogo de fontes em acordeão.
- **Lages:** grau F, "em planejamento" e o quadro "Do lado de cá" com o link para `/o-lugar`; nada descreve a pista como existente.
- **Acessibilidade:** o axe-core não encontra violação crítica em `/origem`, `/origem/cachi`, `/origem/atlas` e `/origem/atlas/lages` (`e2e/quality.spec.ts`).
