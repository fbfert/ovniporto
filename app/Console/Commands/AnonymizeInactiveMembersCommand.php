<?php

namespace App\Console\Commands;

use App\Application\Members\UseCases\AnonymizeInactiveMembers;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('members:anonymize-inactive')]
#[Description('Notice, then anonymize, the accounts without access for the inactivity term (panel accounts only alert the admins)')]
class AnonymizeInactiveMembersCommand extends Command
{
    public function handle(AnonymizeInactiveMembers $cycle): int
    {
        $result = $cycle->execute(
            now()->toDateTimeImmutable(),
            (int) config('privacy.inactive_account_years'),
            (int) config('privacy.inactivity_notice_days'),
        );
        $this->info("{$result['noticed']} aviso(s), {$result['anonymized']} conta(s) anonimizada(s), {$result['alerted']} alerta(s) de conta com papel.");

        return self::SUCCESS;
    }
}
