<?php

use App\Mail\JobFailedAlertMail;
use App\Models\Member;
use App\Providers\HorizonServiceProvider;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Mail;
use Laravel\Horizon\Horizon;

function failedJob(int $attempts): JobFailed
{
    $job = Mockery::mock(Job::class)->shouldIgnoreMissing(); // Horizon's own listeners read other fields
    $job->allows('attempts')->andReturn($attempts);
    $job->allows('resolveName')->andReturn('App\Jobs\ProcessSightingPhoto');

    return new JobFailed('redis', $job, new RuntimeException('GD could not decode the photo'));
}

it('opens the queue and health panels to admins only', function (string $path) {
    $this->get($path)->assertForbidden();
    $this->actingAs(Member::factory()->create())->get($path)->assertForbidden();
    $this->actingAs(Member::factory()->role('moderator')->create())->get($path)->assertForbidden();
    $this->actingAs(Member::factory()->role('store')->create())->get($path)->assertForbidden();
    $this->actingAs(Member::factory()->role('admin')->create())->get($path)->assertOk();
})->with(['/painel/filas', '/painel/saude']);

it('e-mails the admins when a job fails for the third time', function () {
    Mail::fake();
    $admin = Member::factory()->role('admin')->create(['email' => 'torre@ovniporto.test']);
    Member::factory()->role('moderator')->create();

    event(failedJob(2));
    Mail::assertNothingSent();

    event(failedJob(3));
    Mail::assertSent(JobFailedAlertMail::class, fn (JobFailedAlertMail $mail) => $mail->hasTo($admin->email)
        && $mail->job === 'ProcessSightingPhoto'
        && $mail->attempts === 3
        && str_contains($mail->error, 'GD could not decode'));
    Mail::assertSentCount(1);
});

it('sends the alert to the operations address when one is set', function () {
    Mail::fake();
    config(['ovniporto.alerts_email' => 'alertas@ovniporto.test']);
    Member::factory()->role('admin')->create();

    event(failedJob(3));

    Mail::assertSent(JobFailedAlertMail::class, fn ($mail) => $mail->hasTo('alertas@ovniporto.test'));
});

it('routes Horizon long-wait notifications to the alerts address', function () {
    config(['ovniporto.alerts_email' => 'alertas@ovniporto.test']);
    (new HorizonServiceProvider(app()))->boot();

    expect(Horizon::$email)->toBe('alertas@ovniporto.test')
        ->and(config('horizon.waits'))->toHaveKey('redis:default');
});
