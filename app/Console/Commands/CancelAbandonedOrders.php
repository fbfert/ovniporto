<?php

namespace App\Console\Commands;

use App\Application\Orders\UseCases\ChangeOrderStatus;
use Illuminate\Console\Command;

class CancelAbandonedOrders extends Command
{
    protected $signature = 'orders:cancel-abandoned';

    protected $description = 'Cancel orders still waiting for payment after 2 hours';

    public function handle(ChangeOrderStatus $orders): int
    {
        $this->info($orders->cancelAbandoned(now()->toDateTimeImmutable()).' pedido(s) cancelado(s).');

        return self::SUCCESS;
    }
}
