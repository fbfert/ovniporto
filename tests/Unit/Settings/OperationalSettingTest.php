<?php

use App\Domain\Settings\OperationalSetting;
use App\Domain\Settings\SettingsGroup;
use App\Domain\Shipping\Cnpj;

it('maps every setting to one config key, one group and one form field', function () {
    $configKeys = array_map(fn (OperationalSetting $s) => $s->configKey(), OperationalSetting::cases());
    expect(array_unique($configKeys))->toHaveCount(count(OperationalSetting::cases()));

    foreach (SettingsGroup::cases() as $group) {
        $fields = array_map(fn (OperationalSetting $s) => $s->field(), $group->settings());
        expect(array_unique($fields))->toHaveCount(count($fields));
        foreach ($group->settings() as $setting) {
            expect(OperationalSetting::forField($group, $setting->field()))->toBe($setting);
        }
    }
});

it('never reaches app, database or payment keys', function () {
    foreach (OperationalSetting::cases() as $setting) {
        expect($setting->configKey())->toMatch('/^(mail\.|ovniporto\.(alerts_email|shipping\.)|services\.melhor_envio\.from_postal_code$)/');
    }
});

it('names the form fields the panel sends', function () {
    expect(OperationalSetting::MailFromAddress->field())->toBe('fromAddress')
        ->and(OperationalSetting::MailHost->configKey())->toBe('mail.mailers.smtp.host')
        ->and(OperationalSetting::PackageLength->field())->toBe('packageLength')
        ->and(OperationalSetting::SenderPostalCode->field())->toBe('postalCode')
        ->and(OperationalSetting::forField(SettingsGroup::Mail, 'appKey'))->toBeNull()
        ->and(OperationalSetting::tryFrom('app.key'))->toBeNull();
});

it('keeps only the SMTP password secret', function () {
    $secrets = array_filter(OperationalSetting::cases(), fn (OperationalSetting $s) => $s->isSecret());
    expect(array_values($secrets))->toBe([OperationalSetting::MailPassword]);
});

it('checks CNPJ digits', function (string $cnpj, bool $valid) {
    expect(Cnpj::isValid($cnpj))->toBe($valid);
})->with([
    'valid' => ['11222333000181', true],
    'another valid' => ['11444777000161', true],
    'wrong last digit' => ['11222333000182', false],
    'repeated' => ['11111111111111', false],
    'short' => ['1122233300018', false],
]);
