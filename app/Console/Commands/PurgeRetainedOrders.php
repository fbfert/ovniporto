<?php

namespace App\Console\Commands;

use App\Domain\Orders\Contracts\OrderRepository;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('privacy:purge-orders')]
#[Description('Anonymize the paid orders whose tax retention ended and delete those of deleted accounts')]
class PurgeRetainedOrders extends Command
{
    public function handle(OrderRepository $orders): int
    {
        $cutoff = now()->subYears((int) config('privacy.fiscal_retention_years'));
        $anonymized = $orders->anonymizePaidBefore($cutoff);
        $purged = $orders->purgeRetainedUntil(now());
        $this->info("{$anonymized} pedido(s) anonimizado(s) e {$purged} apagado(s) após o fim da retenção fiscal.");

        return self::SUCCESS;
    }
}
