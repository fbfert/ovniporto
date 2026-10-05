<?php

/**
 * One-off extraction of the "Atlas Mundial dos Ovnipuertos" research dossier (.docx) into
 * resources/content/origin/atlas.json plus its embedded images. The output is a draft: it is
 * reviewed by hand and committed; the .docx itself never enters the repository.
 *
 *   php scripts/origin/extract-atlas.php <dossier.docx> [--images=<folder>]
 */
if ($argc < 2) {
    fwrite(STDERR, "usage: php scripts/origin/extract-atlas.php <dossier.docx> [--images=<folder>]\n");
    exit(2);
}

$docx = $argv[1];
$imagesDir = null;
foreach (array_slice($argv, 2) as $arg) {
    if (str_starts_with($arg, '--images=')) {
        $imagesDir = substr($arg, 9);
    }
}

const W = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
const R = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
const A = 'http://schemas.openxmlformats.org/drawingml/2006/main';

/** Case slugs in dossier order (the "locais" field of each source uses the same ids). */
const SLUGS = ['st-paul', 'angelholm', 'ares', 'wycliffe-well', 'green-river', 'barra-do-garcas', 'lajas', 'el-enladrillado', 'cachi', 'emilcin', 'carbondale', 'lages'];

$zip = new ZipArchive;
if ($zip->open($docx) !== true) {
    fwrite(STDERR, "cannot open {$docx}\n");
    exit(1);
}

$relsXml = $zip->getFromName('word/_rels/document.xml.rels');
preg_match_all('#<Relationship [^>]*Id="(rId\d+)"[^>]*Target="([^"]+)"#', $relsXml, $m);
$rels = array_combine($m[1], array_map('html_entity_decode', $m[2]));

$dom = new DOMDocument;
$dom->loadXML($zip->getFromName('word/document.xml'));
$xp = new DOMXPath($dom);
$xp->registerNamespace('w', W);
$xp->registerNamespace('a', A);

/** @return list<array{kind: string, level?: int, text?: string, links?: list<array{text: string, url: string}>, image?: string, rows?: list<list<string>>}> */
function blocks(DOMXPath $xp, array $rels): array
{
    $out = [];
    foreach ($xp->query('//w:body/*') as $node) {
        if ($node->localName === 'tbl') {
            $rows = [];
            foreach ($xp->query('.//w:tr', $node) as $tr) {
                $rows[] = array_map(fn ($tc) => trim(preg_replace('/\s+/u', ' ', $tc->textContent)), iterator_to_array($xp->query('./w:tc', $tr)));
            }
            $out[] = ['kind' => 'table', 'rows' => $rows];

            continue;
        }
        $style = $xp->evaluate('string(./w:pPr/w:pStyle/@w:val)', $node);
        $text = '';
        $links = [];
        $image = null;
        foreach ($xp->query('.//w:t|.//a:blip|.//w:hyperlink|.//w:tab', $node) as $el) {
            if ($el->localName === 'blip') {
                $image = $rels[$el->getAttributeNS(R, 'embed')] ?? null;
            } elseif ($el->localName === 'hyperlink') {
                $id = $el->getAttributeNS(R, 'id');
                if ($id !== '' && isset($rels[$id])) {
                    $links[] = ['text' => trim($el->textContent), 'url' => $rels[$id]];
                }
            } elseif ($el->localName === 'tab') {
                $text .= "\t";
            } else {
                $text .= $el->textContent;
            }
        }
        $text = trim($text);
        if ($image !== null) {
            $out[] = ['kind' => 'image', 'image' => $image];
        }
        if ($text === '') {
            continue;
        }
        $level = preg_match('/(?:Heading|Ttulo)(\d)/i', $style, $h) ? (int) $h[1] : (stripos($style, 'Title') !== false ? 1 : 0);
        $out[] = ['kind' => $level > 0 ? 'heading' : 'p', 'level' => $level, 'text' => $text, 'links' => $links];
    }

    return $out;
}

$blocks = blocks($xp, $rels);

/** Splits the flat block list into top-level (level 1) sections. */
$sections = [];
$current = null;
foreach ($blocks as $b) {
    if ($b['kind'] === 'heading' && $b['level'] === 1) {
        $current = $b['text'];
        $sections[$current] = [];

        continue;
    }
    if ($current !== null) {
        $sections[$current][] = $b;
    }
}

function paragraphs(array $blocks): array
{
    return array_values(array_map(fn ($b) => $b['text'], array_filter($blocks, fn ($b) => $b['kind'] === 'p')));
}

function subsections(array $blocks): array
{
    $subs = ['_' => []];
    $key = '_';
    foreach ($blocks as $b) {
        if ($b['kind'] === 'heading' && $b['level'] >= 2) {
            $key = $b['text'];
            $subs[$key] = [];

            continue;
        }
        $subs[$key][] = $b;
    }

    return $subs;
}

function grade(string $text): ?string
{
    return preg_match('/confiança\s+([A-F](?:\/[A-F])?)/u', $text, $m) ? $m[1] : null;
}

/** Parses the 5-paragraph source records ("id  Title" / meta / type line / note / link). */
function sources(array $blocks): array
{
    $out = [];
    $ps = array_values(array_filter($blocks, fn ($b) => $b['kind'] === 'p'));
    for ($i = 0; $i < count($ps); $i++) {
        if (! preg_match('/^([a-z0-9]+(?:-[a-z0-9]+)+)\s{1,}(.+)$/u', $ps[$i]['text'], $head) || ! isset($ps[$i + 3])) {
            continue;
        }
        $meta = array_map('trim', explode('|', $ps[$i + 1]['text']));
        $typeLine = $ps[$i + 2]['text'];
        if (! str_starts_with($typeLine, 'tipo ')) {
            continue;
        }
        $fields = [];
        foreach (array_map('trim', explode('|', $typeLine)) as $part) {
            [$k, $v] = array_pad(explode(' ', $part, 2), 2, '');
            $fields[$k] = $v;
        }
        $credits = array_values(array_filter($meta, fn ($p) => ! str_starts_with($p, 'confiança') && ! str_starts_with($p, 'acesso')));
        $date = null;
        foreach ($credits as $k => $p) {
            if (preg_match('/^\d{4}(-\d{2}(-\d{2})?)?$/', $p)) {
                $date = $p;
                unset($credits[$k]);
            }
        }
        $credits = array_values($credits);
        $url = null;
        $note = $ps[$i + 3]['text'];
        $linkPara = $ps[$i + 4] ?? null;
        if ($linkPara !== null && str_starts_with($linkPara['text'], 'Abrir fonte')) {
            $url = $linkPara['links'][0]['url'] ?? null;
        }
        $out[$head[1]] = [
            'id' => $head[1],
            'title' => trim($head[2]),
            'author' => count($credits) > 1 ? $credits[0] : null,
            'publisher' => $credits[count($credits) - 1] ?? null,
            'date' => $date,
            'grade' => grade($ps[$i + 1]['text']),
            'accessed' => preg_match('/acesso\s+(\S+)/', $ps[$i + 1]['text'], $a) ? $a[1] : null,
            'type' => $fields['tipo'] ?? null,
            'language' => $fields['idioma'] ?? null,
            'places' => array_values(array_filter(array_map('trim', explode(',', $fields['locais'] ?? '')))),
            'linkStatus' => $fields['link'] ?? null,
            'note' => $note,
            'url' => $url,
        ];
        $i += 4;
    }

    return $out;
}

function coordinates(string $value): ?array
{
    if (! preg_match('/(-?\d{1,3}\.\d+),\s*(-?\d{1,3}\.\d+)/', $value, $m)) {
        return null;
    }

    return [
        'lat' => (float) $m[1],
        'lng' => (float) $m[2],
        'approximate' => preg_match('/conferir|confirmar|aproximad|requer|derivad|colaborativ|OSM|OpenStreetMap|Wikimedia/iu', $value) === 1,
        'note' => $value,
    ];
}

$sectionTitles = array_keys($sections);
$caseTitles = array_values(array_filter($sectionTitles, fn ($t) => preg_match('/^\d{1,2}\s/', $t)));
$atlasImages = [];
$cases = [];
foreach ($caseTitles as $index => $title) {
    preg_match('/^(\d{1,2})\s+(.+)$/u', $title, $tm);
    $subs = subsections($sections[$title]);
    $intro = $subs['_'];
    $paras = paragraphs($intro);
    $image = null;
    foreach ($intro as $b) {
        if ($b['kind'] === 'image') {
            $image = basename($b['image']);
        }
    }
    $facts = [];
    foreach ($subs['Dados documentais'] ?? [] as $b) {
        if ($b['kind'] === 'table') {
            foreach (array_slice($b['rows'], 1) as $row) {
                $facts[] = ['label' => $row[0], 'value' => $row[1] ?? ''];
            }
        }
    }
    $coords = null;
    $confidence = null;
    foreach ($facts as $fact) {
        if (str_starts_with($fact['label'], 'Coordenadas')) {
            $coords = coordinates($fact['value']);
        }
        if ($fact['label'] === 'Confiança') {
            $confidence = rtrim($fact['value'], '.');
        }
    }
    $claims = [];
    foreach ($subs['Afirmações estruturadas de Cachi'] ?? [] as $b) {
        if ($b['kind'] !== 'p') {
            continue;
        }
        if (preg_match('/^([A-F])\s+(documented fact|protagonist account|unconfirmed|local tradition|interpretation)\s+(.+)$/u', $b['text'], $cm)) {
            $claims[] = ['grade' => $cm[1], 'kind' => $cm[2], 'text' => $cm[3], 'sources' => []];
        } elseif (str_starts_with($b['text'], 'Fontes:') && $claims !== []) {
            $claims[count($claims) - 1]['sources'] = array_map('trim', explode(',', substr($b['text'], 7)));
        }
    }
    $chronology = [];
    foreach ($subs['Cronologia estruturada de Cachi'] ?? [] as $b) {
        if ($b['kind'] === 'table') {
            foreach (array_slice($b['rows'], 1) as $row) {
                [$class, $g] = array_map('trim', array_pad(explode('/', $row[2] ?? ''), 2, ''));
                $chronology[] = ['date' => $row[0], 'event' => $row[1], 'kind' => $class, 'grade' => $g];
            }
        }
    }
    $caption = $paras[1] ?? '';
    $credit = [];
    if (preg_match('/^(.*?)\s*Autor:\s*(.+?)(?:\s+Data:\s*(.+?))?\s+Licença:\s*(.+)$/u', $caption, $cm)) {
        $credit = ['caption' => trim($cm[1]), 'author' => trim($cm[2]), 'date' => isset($cm[3]) ? trim($cm[3]) : null, 'license' => trim($cm[4])];
    }
    $slug = SLUGS[$index] ?? 'caso-'.$tm[1];
    if ($image !== null) {
        $atlasImages[$image] = $slug;
    }
    $cases[] = [
        'slug' => $slug,
        'number' => (int) $tm[1],
        'name' => trim($tm[2]),
        'country' => $paras[0] ?? '',
        'image' => $image === null ? null : ['file' => "atlas-{$slug}", 'source' => $image] + $credit,
        'facts' => $facts,
        'confidence' => $confidence,
        // The short grade shown on the case's stamp: the first grade the dossier gives.
        'seal' => $confidence !== null && preg_match('#^([A-F](?:/[A-F])?)#', $confidence, $sm) ? $sm[1] : null,
        'coordinates' => $coords,
        'claims' => $claims,
        'chronology' => $chronology,
        'sources' => array_keys(sources($subs['Fontes deste caso'] ?? [])),
        'openQuestions' => paragraphs($subs['Questões em aberto'] ?? []),
    ];
}

$intro = subsections($sections[$sectionTitles[1]] ?? [])['_'];
$scope = paragraphs($sections['Escopo e conclusão principal'] ?? []);
$methodSubs = subsections($sections['Método de pesquisa'] ?? []);
$grades = [];
foreach ($methodSubs['Escala de confiança'] ?? [] as $b) {
    if ($b['kind'] === 'table') {
        foreach (array_slice($b['rows'], 1) as $row) {
            $grades[] = ['grade' => $row[0], 'use' => $row[1]];
        }
    }
}

$timeline = [];
foreach ($sections['Cronologia comparada'] ?? [] as $b) {
    if ($b['kind'] === 'table') {
        foreach (array_slice($b['rows'], 1) as $row) {
            $timeline[] = ['date' => $row[0], 'place' => $row[1], 'event' => $row[2]];
        }
    }
}

$candidates = [];
$candidateBlocks = $sections['Casos candidatos'] ?? [];
$cand = null;
$field = null;
foreach ($candidateBlocks as $b) {
    if ($b['kind'] === 'heading' && $b['level'] === 2) {
        if ($cand !== null) {
            $candidates[] = $cand;
        }
        $cand = ['name' => $b['text'], 'facts' => [], 'description' => '', 'sources' => [], 'reason' => '', 'reliability' => '', 'decision' => ''];
        $field = null;

        continue;
    }
    if ($cand === null) {
        continue;
    }
    if ($b['kind'] === 'heading' && $b['level'] === 3) {
        $field = ['Descrição' => 'description', 'Fonte' => 'sources', 'Fontes' => 'sources', 'Razão para possível inclusão' => 'reason', 'Confiabilidade documental' => 'reliability', 'Decisão' => 'decision'][$b['text']] ?? null;

        continue;
    }
    if ($b['kind'] !== 'p') {
        continue;
    }
    if ($field === null && preg_match('/^([^:]{3,40}):\s*(.+)$/u', $b['text'], $fm)) {
        $cand['facts'][] = ['label' => $fm[1], 'value' => $fm[2]];
    } elseif ($field === 'sources') {
        $cand['sources'][] = $b['text'];
    } elseif ($field !== null) {
        $cand[$field] = trim($cand[$field].' '.$b['text']);
    }
}
if ($cand !== null) {
    $candidates[] = $cand;
}

$credits = [];
$creditSubs = subsections($sections['Créditos e licenças das imagens'] ?? []);
foreach ($creditSubs as $title => $bs) {
    if ($title === '_' || ! preg_match('/^(\d{1,2})\s/', $title, $cm)) {
        continue;
    }
    $entry = ['case' => SLUGS[(int) $cm[1] - 1] ?? null];
    foreach ($bs as $b) {
        if ($b['kind'] !== 'p') {
            continue;
        }
        if (preg_match('/^(Arquivo|Autor|Licença):\s*(.+)$/u', $b['text'], $fm)) {
            $entry[['Arquivo' => 'file', 'Autor' => 'author', 'Licença' => 'license'][$fm[1]]] = trim(preg_replace('/\s+Texto da licença$/u', '', $fm[2]));
        }
        foreach ($b['links'] as $link) {
            if (str_contains($link['url'], 'creativecommons.org')) {
                $entry['licenseUrl'] = $link['url'];
            } elseif (str_contains($link['url'], 'wikimedia.org') || str_contains($link['url'], 'wikipedia.org')) {
                $entry['sourceUrl'] = $link['url'];
            }
        }
    }
    $credits[] = $entry;
}

$atlas = [
    'title' => $sectionTitles[0] ?? 'Atlas Mundial dos Ovnipuertos',
    'subtitle' => $sectionTitles[1] ?? '',
    'summary' => trim(preg_replace('/Revisão de .*$/u', '', paragraphs($intro)[0] ?? '')),
    'revision' => preg_match('/Revisão de (.+)$/u', paragraphs($intro)[0] ?? '', $rm) ? trim($rm[1]) : null,
    'scope' => $scope,
    'method' => paragraphs($methodSubs['_']),
    'grades' => $grades,
    'cases' => $cases,
    'comparativeTimeline' => $timeline,
    'timelineNote' => paragraphs($sections['Cronologia comparada'] ?? [])[0] ?? '',
    'crossQuestions' => paragraphs($sections['Questões transversais'] ?? []),
    'candidatesNote' => paragraphs(subsections($candidateBlocks)['_'])[0] ?? '',
    'candidates' => $candidates,
    'sources' => array_values(sources($sections['Catálogo completo de fontes'] ?? [])),
    'credits' => $credits,
    'limitations' => paragraphs($sections['Limitações documentais'] ?? []),
];

$target = __DIR__.'/../../resources/content/origin/atlas.json';
@mkdir(dirname($target), 0777, true);
file_put_contents($target, json_encode($atlas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");

if ($imagesDir !== null) {
    @mkdir($imagesDir, 0777, true);
    foreach ($atlasImages as $file => $slug) {
        $bytes = $zip->getFromName('word/media/'.$file);
        file_put_contents($imagesDir.'/atlas-'.$slug.'.'.pathinfo($file, PATHINFO_EXTENSION), $bytes);
    }
}

printf(
    "%d casos, %d candidatos, %d fontes, %d graus, %d créditos, %d imagens\n",
    count($cases),
    count($candidates),
    count($atlas['sources']),
    count($grades),
    count($credits),
    count($atlasImages),
);
