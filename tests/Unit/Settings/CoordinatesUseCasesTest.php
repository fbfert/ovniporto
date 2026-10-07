<?php

use App\Application\Settings\UseCases\SaveCoordinates;
use App\Application\Settings\UseCases\ShowCoordinates;
use App\Application\Settings\UseCases\TestCoordinates;
use App\Domain\Settings\Contracts\IntegrationDirectory;
use App\Domain\Settings\Contracts\MailTester;
use App\Domain\Settings\Contracts\SettingsBaseline;
use App\Domain\Settings\Data\MailTestResult;
use App\Domain\Settings\OperationalSetting;
use App\Domain\Settings\SettingsGroup;
use Tests\Support\InMemoryOperationalSettings;
use Tests\Support\SnapshotAuditor;

/** @param array<string, mixed> $env config key => .env value */
function envBaseline(array $env = []): SettingsBaseline
{
    return new class($env) implements SettingsBaseline
    {
        /** @param array<string, mixed> $env */
        public function __construct(private array $env) {}

        public function envValue(OperationalSetting $setting): mixed
        {
            return $this->env[$setting->configKey()] ?? null;
        }
    };
}

function noIntegrations(): IntegrationDirectory
{
    return new class implements IntegrationDirectory
    {
        public function describe(): array
        {
            return [];
        }
    };
}

function scriptedTester(MailTestResult $result): MailTester
{
    return new class($result) implements MailTester
    {
        /** @var list<array{to: list<string>, subject: string}> */
        public array $sent = [];

        public function __construct(private MailTestResult $result) {}

        public function send(array $to, string $subject, string $body): MailTestResult
        {
            $this->sent[] = ['to' => $to, 'subject' => $subject];

            return $this->result;
        }
    };
}

it('shows values in force and where they come from, the password only as set', function () {
    $settings = new InMemoryOperationalSettings([
        'mail.host' => 'smtp.ovniporto.test',
        'mail.password' => 'segredo-do-painel',
    ]);
    $page = (new ShowCoordinates($settings, envBaseline(['mail.mailers.smtp.port' => 587]), noIntegrations()))->execute();

    expect($page['groups']['correio']['host'])->toBe(['value' => 'smtp.ovniporto.test', 'env' => null, 'fromPanel' => true])
        ->and($page['groups']['correio']['port'])->toBe(['value' => null, 'env' => '587', 'fromPanel' => false])
        ->and($page['groups']['correio']['password'])->toBe(['value' => null, 'env' => null, 'fromPanel' => true, 'set' => true])
        ->and(json_encode($page))->not->toContain('segredo-do-painel');
});

it('flags alerts that reach nobody and a mailer that failed its last test', function () {
    $settings = new InMemoryOperationalSettings;
    $show = new ShowCoordinates($settings, envBaseline(), noIntegrations());
    expect($show->health())->toBe(['alertsMissing' => true, 'mailBroken' => false]);

    $settings->values['alerts.email'] = 'torre@ovniporto.test';
    $settings->lastTest = false;
    expect($show->health())->toBe(['alertsMissing' => false, 'mailBroken' => true]);
});

it('saves a block, empties back to the .env and keeps the password when its field is blank', function () {
    $settings = new InMemoryOperationalSettings(['mail.password' => 'antiga', 'mail.username' => 'velho']);
    $auditor = new SnapshotAuditor;
    (new SaveCoordinates($settings, $auditor))->execute(7, SettingsGroup::Mail, [
        'host' => ' smtp.ovniporto.test ',
        'port' => '587',
        'username' => '',
        'password' => '',
        'fromAddress' => 'Torre@OVNIPORTO.test',
    ]);

    expect($settings->values)->toBe([
        'mail.password' => 'antiga',
        'mail.host' => 'smtp.ovniporto.test',
        'mail.port' => '587',
        'mail.from_address' => 'torre@ovniporto.test',
    ])
        ->and($auditor->records[0]['entry']->action)->toBe('coordinates.updated')
        ->and($auditor->records[0]['entry']->context)->toBe(['group' => 'correio'])
        ->and($auditor->records[0]['before']['username'])->toBe('velho')
        ->and($auditor->records[0]['after']['username'])->toBeNull();
});

it('audits a new password only as "alterada"', function () {
    $settings = new InMemoryOperationalSettings(['mail.password' => 'antiga']);
    $auditor = new SnapshotAuditor;
    $save = new SaveCoordinates($settings, $auditor);
    $save->execute(7, SettingsGroup::Mail, ['password' => 'nova-senha-123']);

    $record = $auditor->records[0];
    expect($settings->values['mail.password'])->toBe('nova-senha-123')
        ->and($record['entry']->context['password'])->toBe('alterada')
        ->and($record['before']['password'])->toBe('definida')
        ->and(json_encode($auditor->records))->not->toContain('antiga')->not->toContain('nova-senha-123');

    $save->removePassword(7);
    expect($settings->values)->not->toHaveKey('mail.password')
        ->and($auditor->records[1]['entry']->action)->toBe('coordinates.password_removed')
        ->and($auditor->records[1]['after']['password'])->toBe('não definida');
});

it('keeps only digits in the document and the postal code, and upper-cases the state', function () {
    $settings = new InMemoryOperationalSettings;
    (new SaveCoordinates($settings, new SnapshotAuditor))->execute(1, SettingsGroup::Shipping, [
        'document' => '11.222.333/0001-81',
        'postalCode' => '88500-000',
        'state' => 'sc',
        'packageLength' => '16',
    ]);

    expect($settings->values)->toBe([
        'shipping.sender.document' => '11222333000181',
        'shipping.sender.postal_code' => '88500000',
        'shipping.sender.state' => 'SC',
        'shipping.package.length' => '16',
    ]);
});

it('sends the test mail to the admin, remembers the result and audits it', function () {
    $settings = new InMemoryOperationalSettings;
    $auditor = new SnapshotAuditor;
    $tester = scriptedTester(new MailTestResult(false, 'auth', '535 5.7.8 Error: authentication failed'));
    $result = (new TestCoordinates($tester, $settings, envBaseline(), $auditor))->mail(7, 'admin@ovniporto.test');

    expect($result->problem)->toBe('auth')
        ->and($tester->sent[0]['to'])->toBe(['admin@ovniporto.test'])
        ->and($settings->lastTest)->toBeFalse()
        ->and($auditor->records[0]['entry']->action)->toBe('coordinates.mail_tested')
        ->and($auditor->records[0]['entry']->context)->toBe(['sent' => false, 'problem' => 'auth']);
});

it('sends the test alert to the alerts address in force, or says there is none', function () {
    $tester = scriptedTester(new MailTestResult(true));
    $none = (new TestCoordinates($tester, new InMemoryOperationalSettings, envBaseline(), new SnapshotAuditor))->alert(7);
    expect($none->problem)->toBe('no-recipient')->and($tester->sent)->toBe([]);

    $fromEnv = new TestCoordinates($tester, new InMemoryOperationalSettings, envBaseline(['ovniporto.alerts_email' => 'env@ovniporto.test']), new SnapshotAuditor);
    expect($fromEnv->alert(7)->sent)->toBeTrue()->and($tester->sent[0]['to'])->toBe(['env@ovniporto.test']);
});
