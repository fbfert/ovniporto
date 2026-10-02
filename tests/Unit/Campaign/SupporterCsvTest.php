<?php

use App\Domain\Campaign\SupporterCsv;

it('reads the semicolon spreadsheet the platforms export, with Brazilian money and dates', function () {
    $entries = SupporterCsv::parse("\u{FEFF}nome;valor;recompensa;publicar_nome;data\nAna;R$ 1.234,56;Adesivo;Sim;01/03/2027\n");

    expect($entries)->toHaveCount(1)
        ->and($entries[0]->name)->toBe('Ana')
        ->and($entries[0]->amountCents)->toBe(123456)
        ->and($entries[0]->reward)->toBe('Adesivo')
        ->and($entries[0]->publishName)->toBeTrue()
        ->and($entries[0]->supportedAt?->format('Y-m-d'))->toBe('2027-03-01');
});

it('keeps a name private unless the column says yes', function () {
    $entries = SupporterCsv::parse("nome,publicar_nome\nAna,\nBruno,não\nCarla,sim\n");

    expect(array_map(fn ($e) => $e->publishName, $entries))->toBe([false, false, true]);
});

it('points at the line that is wrong', function (string $csv, string $message) {
    expect(fn () => SupporterCsv::parse($csv))->toThrow(InvalidArgumentException::class, $message);
})->with([
    'no name column' => ["valor\n10\n", 'A primeira linha precisa ter a coluna "nome".'],
    'missing name' => ["nome,valor\nAna,10\n,5\n", 'Linha 3: falta o nome.'],
    'bad amount' => ["nome,valor\nAna,dez\n", 'Linha 2: valor inválido.'],
    'bad date' => ["nome,data\nAna,31-31-2027\n", 'Linha 2: data inválida'],
    'empty' => ['', 'O arquivo está vazio.'],
]);
