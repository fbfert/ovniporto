<?php

namespace App\Infrastructure\Documents;

use App\Domain\Orders\Contracts\ProductionDocument;
use Barryvdh\DomPDF\Facade\Pdf;

final class DompdfProductionDocument implements ProductionDocument
{
    public function render(array $order): string
    {
        return Pdf::loadView('pdf.production-order', [
            'order' => $order,
            'seal' => 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path('brand/seal.png'))),
        ])->setPaper('a4')->output();
    }
}
