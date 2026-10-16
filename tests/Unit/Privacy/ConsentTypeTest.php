<?php

use App\Domain\Privacy\ConsentType;

it('versions the minimum age and the purchase authorization by the date of their wording', function () {
    expect(ConsentType::MinimumAge->value)->toBe('idade_minima')
        ->and(ConsentType::MinimumAge->textVersion())->toBe('2026-10-16')
        ->and(ConsentType::PurchaseAuthorization->value)->toBe('compra_autorizada')
        ->and(ConsentType::PurchaseAuthorization->textVersion())->toBe('2026-10-16');
});

it('takes the terms version from the published texts, not from the enum', function () {
    ConsentType::Terms->textVersion();
})->throws(LogicException::class);
