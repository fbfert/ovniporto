<?php

use App\Domain\Origin\ConfidenceGrade;
use App\Domain\Origin\InvalidOriginData;
use App\Domain\Origin\SourceKind;

it('parses single and mixed confidence seals', function () {
    expect(ConfidenceGrade::parseSeal('A'))->toBe([ConfidenceGrade::A])
        ->and(ConfidenceGrade::parseSeal('a/b'))->toBe([ConfidenceGrade::A, ConfidenceGrade::B])
        ->and(ConfidenceGrade::parseSeal(' B/C '))->toBe([ConfidenceGrade::B, ConfidenceGrade::C]);
});

it('rejects a seal outside the A–F scale', function () {
    ConfidenceGrade::parseSeal('A/G');
})->throws(InvalidOriginData::class, 'A/G');

it('words every grade as the dossier does', function () {
    expect(ConfidenceGrade::A->meaning())->toBe('fonte primária ou ato oficial')
        ->and(ConfidenceGrade::F->meaning())->toBe('informação não confirmada')
        ->and(array_map(fn ($g) => $g->meaning(), ConfidenceGrade::cases()))->each->not->toBeEmpty();
});

it('labels every source kind in words', function () {
    expect(SourceKind::Report->label())->toBe('relato de fenômeno')
        ->and(SourceKind::Ordinary->label())->toBe('hipótese ordinária')
        ->and(array_map(fn ($k) => $k->label(), SourceKind::cases()))->each->not->toBeEmpty();
});
