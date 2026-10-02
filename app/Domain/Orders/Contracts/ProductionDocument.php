<?php

namespace App\Domain\Orders\Contracts;

/** The production order handed to whoever makes the items: seal, items and variants, no buyer data. */
interface ProductionDocument
{
    /**
     * @param  array<string, mixed>  $order  the order sheet
     * @return string PDF bytes
     */
    public function render(array $order): string;
}
