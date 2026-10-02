<?php

use App\Domain\Members\Nickname;

it('accepts 3 to 20 lowercase letters, digits, "_" and "."', function (string $nickname, ?string $problem) {
    expect(Nickname::problem($nickname))->toBe($problem);
})->with([
    ['vigia_da.serra', null],
    ['Coruja', null], // normalized to lowercase
    ['ab', 'format'],
    ['com espaço', 'format'],
    [str_repeat('a', 21), 'format'],
    ['Torre', 'reserved'],
    ['admin', 'reserved'],
]);

it('suggests a free nickname from the first name', function () {
    $taken = ['ana', 'ana2'];

    expect(Nickname::suggest('Ana Clara Souza', fn ($c) => in_array($c, $taken, true)))->toBe('ana3')
        ->and(Nickname::suggest('João', fn () => false))->toBe('joao')
        ->and(Nickname::suggest('Li', fn () => false))->toBe('vigia')
        ->and(Nickname::suggest('Felipe Xavier', fn () => false))->toBe('felipe_vigia');
});
