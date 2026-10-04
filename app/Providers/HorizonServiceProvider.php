<?php

namespace App\Providers;

use App\Domain\Members\MemberRole;
use App\Models\Member;
use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

/**
 * Queues (Horizon, /painel/filas) and health (Pulse, /painel/saude): admin only, in every
 * environment. A queue that waits too long e-mails the alerts address (config/horizon.php
 * "waits"); jobs that fail for good are reported by JobFailureAlert.
 */
class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        // Horizon opens to everyone in the local environment by default; here the gate always decides.
        Horizon::auth(fn ($request) => Gate::check('viewHorizon', [$request->user()]));

        $alerts = config('ovniporto.alerts_email');
        if (filled($alerts)) {
            Horizon::routeMailNotificationsTo((string) $alerts);
        }
    }

    protected function gate(): void
    {
        $adminOnly = fn ($user = null) => $user instanceof Member && $user->role === MemberRole::Admin;

        Gate::define('viewHorizon', $adminOnly);
        Gate::define('viewPulse', $adminOnly);
    }
}
