<?php

namespace App\Console\Commands;

use App\Application\Orders\UseCases\ChangeOrderStatus;
use Illuminate\Console\Command;

class TrackShipments extends Command
{
    protected $signature = 'orders:track-shipments';

    protected $description = 'Ask the shipping provider which shipped orders were delivered';

    public function handle(ChangeOrderStatus $orders): int
    {
        $this->info($orders->markDelivered().' pedido(s) entregue(s).');

        return self::SUCCESS;
    }
}
