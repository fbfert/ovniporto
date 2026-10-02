<?php

namespace App\Domain\Orders\Contracts;

use App\Domain\Orders\OrderStatus;

/** E-mails to the buyer, always queued, always signed off "Guardei um lugar pra você." */
interface OrderNotifier
{
    public function received(string $orderNumber): void;

    /** Paid, in production, shipped (with tracking) and delivered; other statuses send nothing. */
    public function statusChanged(string $orderNumber, OrderStatus $status): void;

    /** Something needs a human (e.g. a payment arrived for a canceled order): tells the store role. */
    public function alertStore(string $orderNumber, string $message): void;
}
