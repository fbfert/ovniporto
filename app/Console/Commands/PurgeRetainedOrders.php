<?php

namespace App\Console\Commands;

use App\Domain\Orders\Contracts\OrderRepository;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('privacy:purge-orders')]
#[Description('Delete the anonymized orders of deleted accounts whose tax retention has ended')]
class PurgeRetainedOrders extends Command
{
    public function handle(OrderRepository $orders): int
    {
        $count = $orders->purgeRetainedUntil(now());
        $this->info("{$count} pedido(s) apagado(s) após o fim da retenção fiscal.");

        return self::SUCCESS;
    }
}
