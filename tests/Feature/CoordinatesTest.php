<?php

use App\Domain\Settings\Contracts\OperationalSettingsRepository;
use App\Domain\Shipping\Contracts\ShippingProvider;
use App\Infrastructure\Settings\OperationalSettingsApplier;
use App\Models\AuditLog;
use App\Models\Member;
use App\Models\OperationalSettingRow;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Horizon\Horizon;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

const SMTP_SECRET = 's3gr3d0-da-torre';

beforeEach(function () {
    $this->admin = Member::factory()->role('admin')->create(['email' => 'torre@ovniporto.test']);
    config([
        'mail.mailers.smtp.host' => 'env-smtp.ovniporto.test',
        'mail.mailers.smtp.port' => 2525,
        'mail.mailers.smtp.password' => null,
        'ovniporto.alerts_email' => null,
    ]);
});

function smtpHost(): string
{
    /** @var EsmtpTransport $transport */
    $transport = app(MailManager::class)->mailer('smtp')->getSymfonyTransport();

    return $transport->getStream()->getHost();
}

it('opens the Coordenadas to the admin only, for reading and for every write', function () {
    $this->actingAs($this->admin)->get('/painel/coordenadas')->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Panel/Coordinates/Index')->has('groups.correio')->has('integrations', 5));

    foreach (['moderator', 'store'] as $role) {
        $this->actingAs(Member::factory()->role($role)->create());
        $this->get('/painel/coordenadas')->assertForbidden();
        $this->put('/painel/coordenadas/correio', ['host' => 'mau.example.com'])->assertForbidden();
        $this->put('/painel/coordenadas/alertas', ['email' => 'mau@example.com'])->assertForbidden();
        $this->put('/painel/coordenadas/frete', ['name' => 'Mau'])->assertForbidden();
        $this->delete('/painel/coordenadas/senha-smtp')->assertForbidden();
        $this->post('/painel/coordenadas/teste-email')->assertForbidden();
        $this->post('/painel/coordenadas/teste-alerta')->assertForbidden();
    }
    expect(OperationalSettingRow::query()->count())->toBe(0);
});

it('refuses an impossible port and a CNPJ with a wrong check digit, in Portuguese', function () {
    $this->actingAs($this->admin)->from('/painel/coordenadas')
        ->put('/painel/coordenadas/correio', ['port' => 70000])
        ->assertSessionHasErrors(['port' => 'A porta vai de 1 a 65535 (as comuns são 587 e 465).']);
    $this->put('/painel/coordenadas/frete', ['document' => '11.222.333/0001-82'])
        ->assertSessionHasErrors(['document' => 'CPF ou CNPJ inválido. Confira os números.']);
    $this->put('/painel/coordenadas/frete', ['document' => '11.222.333/0001-81', 'state' => 'XX'])
        ->assertSessionHasErrors(['state']);

    expect(OperationalSettingRow::query()->count())->toBe(0);
});

it('stores the password encrypted, never sends it to the browser and audits it as "alterada"', function () {
    $this->actingAs($this->admin)->put('/painel/coordenadas/correio', [
        'host' => 'smtp.ovniporto.test', 'port' => 587, 'scheme' => 'smtp', 'username' => 'torre', 'password' => SMTP_SECRET,
    ])->assertSessionHasNoErrors();

    $row = OperationalSettingRow::query()->where('key', 'mail.password')->firstOrFail();
    expect($row->value)->not->toContain(SMTP_SECRET)
        ->and(decrypt($row->value, false))->toBe(SMTP_SECRET)
        ->and($row->is_secret)->toBeTrue()
        ->and(app(OperationalSettingsRepository::class)->values()['mail.password'])->toBe(SMTP_SECRET);

    $html = $this->get('/painel/coordenadas')->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('groups.correio.password.set', true)->where('groups.correio.host.fromPanel', true))
        ->getContent();
    expect($html)->not->toContain(SMTP_SECRET);

    $log = AuditLog::query()->where('action', 'coordinates.updated')->firstOrFail();
    expect(json_encode([$log->before, $log->after, $log->context]))->not->toContain(SMTP_SECRET)
        ->and($log->context['password'])->toBe('alterada');
});

it('keeps the password when the field comes back blank, and removes it only on request', function () {
    $this->actingAs($this->admin)->put('/painel/coordenadas/correio', ['host' => 'smtp.ovniporto.test', 'password' => SMTP_SECRET]);
    $this->put('/painel/coordenadas/correio', ['host' => 'smtp.ovniporto.test', 'port' => 465, 'password' => '']);
    expect(app(OperationalSettingsRepository::class)->values()['mail.password'])->toBe(SMTP_SECRET);

    $this->delete('/painel/coordenadas/senha-smtp')->assertRedirect();
    expect(app(OperationalSettingsRepository::class)->values())->not->toHaveKey('mail.password')
        ->and(AuditLog::query()->where('action', 'coordinates.password_removed')->exists())->toBeTrue();
});

it('lays the panel over the .env, and an emptied field goes back to the .env', function () {
    $applier = app(OperationalSettingsApplier::class);
    $settings = app(OperationalSettingsRepository::class);

    $applier->refresh();
    expect(config('mail.mailers.smtp.host'))->toBe('env-smtp.ovniporto.test');

    $settings->save(['mail.host' => 'smtp.ovniporto.test', 'mail.port' => '587', 'shipping.package.length' => '20'], $this->admin->id);
    $applier->refresh();
    expect(config('mail.mailers.smtp.host'))->toBe('smtp.ovniporto.test')
        ->and(config('mail.mailers.smtp.port'))->toBe(587)
        ->and(config('ovniporto.shipping.package.length'))->toBe(20);

    $settings->save(['mail.host' => null], $this->admin->id);
    $applier->refresh();
    expect(config('mail.mailers.smtp.host'))->toBe('env-smtp.ovniporto.test')
        ->and(config('mail.mailers.smtp.port'))->toBe(587);
});

it('switches a "log" mailer to SMTP only when the panel has a server', function () {
    config(['mail.default' => 'log']);
    $applier = app(OperationalSettingsApplier::class);
    $applier->refresh();
    expect(config('mail.default'))->toBe('log');

    app(OperationalSettingsRepository::class)->save(['mail.host' => 'smtp.ovniporto.test'], null);
    $applier->refresh();
    expect(config('mail.default'))->toBe('smtp');
});

it('sends the next queued job through the new server, without a restart', function () {
    $applier = app(OperationalSettingsApplier::class);
    $applier->refresh();
    expect(smtpHost())->toBe('env-smtp.ovniporto.test');
    $before = app(ShippingProvider::class);

    // Saved by the admin while this "worker" is up.
    app(OperationalSettingsRepository::class)->save([
        'mail.host' => 'smtp.ovniporto.test',
        'alerts.email' => 'alertas@ovniporto.test',
    ], $this->admin->id);

    dispatch(fn () => cache()->put('seen-host', smtpHost()));

    expect(cache('seen-host'))->toBe('smtp.ovniporto.test')
        ->and(Horizon::$email)->toBe('alertas@ovniporto.test')
        ->and(app(ShippingProvider::class))->not->toBe($before);
});

it('shows the server answer of a failed test without the user or the password, and logs nothing secret', function () {
    app(MailManager::class)->extend('smtp', fn () => new class extends AbstractTransport
    {
        protected function doSend(SentMessage $message): void
        {
            throw new TransportException('Failed to authenticate on SMTP server with username "torre" using the following authenticators: "LOGIN". Authenticator "LOGIN" returned "Expected response code "235" but got code "535", with message "535 5.7.8 Error: authentication failed: '.SMTP_SECRET.' '.base64_encode("\0torre\0".SMTP_SECRET).'".');
        }

        public function __toString(): string
        {
            return 'failing';
        }
    });
    Log::spy();

    $this->actingAs($this->admin)->put('/painel/coordenadas/correio', ['username' => 'torre', 'password' => SMTP_SECRET]);
    $this->from('/painel/coordenadas')->post('/painel/coordenadas/teste-email')->assertRedirect('/painel/coordenadas');

    $page = $this->get('/painel/coordenadas');
    $page->assertInertia(fn (Assert $p) => $p
        ->where('test.kind', 'mail')
        ->where('test.sent', false)
        ->where('test.problem', 'auth')
        ->where('test.to', 'torre@ovniporto.test')
        ->where('test.detail', fn (string $detail) => str_contains($detail, '535') && str_contains($detail, 'username "***"') && ! str_contains($detail, SMTP_SECRET) && ! str_contains($detail, substr(base64_encode("\0torre\0".SMTP_SECRET), 0, 12))));
    expect($page->getContent())->not->toContain(SMTP_SECRET)
        ->and(json_encode(AuditLog::query()->get()->toArray()))->not->toContain(SMTP_SECRET);
    Log::shouldNotHaveReceived('error');

    // The panel home now points at the broken mailer.
    $this->get('/painel')->assertInertia(fn (Assert $p) => $p->where('coordinates.mailBroken', true)->where('coordinates.alertsMissing', true));
});

it('limits the test buttons to five a minute', function () {
    $this->actingAs($this->admin);
    foreach (range(1, 5) as $_) {
        $this->post('/painel/coordenadas/teste-alerta')->assertRedirect();
    }
    $this->post('/painel/coordenadas/teste-alerta')->assertStatus(429);
});

it('describes the integrations without a single key', function () {
    config(['services.paypal.client_id' => 'paypal-client-xyz', 'services.paypal.client_secret' => 'paypal-secret-xyz', 'services.paypal.webhook_id' => 'wh-xyz', 'services.melhor_envio.token' => null]);

    $html = $this->actingAs($this->admin)->get('/painel/coordenadas')
        ->assertInertia(fn (Assert $page) => $page
            ->where('integrations.1', ['name' => 'paypal', 'configured' => true, 'mode' => 'sandbox', 'attention' => false])
            ->where('integrations.2.configured', false)
            ->where('integrations.2.attention', true))
        ->getContent();
    expect($html)->not->toContain('paypal-client-xyz')->not->toContain('paypal-secret-xyz')->not->toContain('wh-xyz');
});

it('gives the panel home card to the admin only', function () {
    $this->actingAs(Member::factory()->role('store')->create())->get('/painel')
        ->assertInertia(fn (Assert $page) => $page->where('coordinates', null));
});

it('falls back to the .env when the password was encrypted with another APP_KEY', function () {
    OperationalSettingRow::query()->create(['key' => 'mail.password', 'value' => 'eyJpdiI6ImZvcmVpZ24ifQ==', 'is_secret' => true]);

    expect(app(OperationalSettingsRepository::class)->values())->not->toHaveKey('mail.password');
});

it('keeps the coordinates out of the config cache', function () {
    app(OperationalSettingsRepository::class)->save(['mail.host' => 'smtp.ovniporto.test', 'mail.password' => SMTP_SECRET], null);

    // What `config:cache` serializes: a freshly booted app, before any request or job.
    $fresh = require base_path('bootstrap/app.php');
    $fresh->make(Kernel::class)->bootstrap();

    expect($fresh['config']->get('mail.mailers.smtp.host'))->not->toBe('smtp.ovniporto.test')
        ->and(json_encode($fresh['config']->all()))->not->toContain(SMTP_SECRET);
});
