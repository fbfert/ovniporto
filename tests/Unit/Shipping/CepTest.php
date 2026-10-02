<?php

use App\Domain\Shipping\Cep;

it('accepts 8 digits with or without the dash', function (string $input) {
    expect(Cep::from($input)->formatted())->toBe('88501-000');
})->with(['88501-000', '88501000', ' 88.501-000 ']);

it('refuses anything that is not 8 digits', function (string $input) {
    expect(fn () => Cep::from($input))->toThrow(InvalidArgumentException::class, 'Digite um CEP com 8 números.');
})->with(['88501', '885010000', '', 'abcdefgh', '00000-000']);
