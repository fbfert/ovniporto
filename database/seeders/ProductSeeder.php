<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /** The store opens only with what exists: the sticker. Everything else stays inactive. */
    public function run(): void
    {
        $products = [
            ['adesivo-ovniporto', 'Adesivo OVNIPORTO', 800, 'stock', 0, true, 'PARA LEVAR', 'O selo da pista, pronto pra colar.'],
            ['camiseta-ovniporto', 'Camiseta OVNIPORTO', 7900, 'made_to_order', 10, false, 'DA CASA', 'Estampa do selo, impressa depois do pedido.'],
            ['caneca-ovniporto', 'Caneca OVNIPORTO', 4900, 'made_to_order', 8, false, 'DA CASA', 'Para o café das noites de vigília.'],
            ['kit-abducao', 'Kit Abdução', 18900, 'made_to_order', 12, false, 'PARA LEVAR', 'Camiseta, caneca e adesivo numa caixa só.'],
        ];

        foreach ($products as $order => [$slug, $name, $price, $kind, $days, $active, $label, $short]) {
            $product = Product::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'price_cents' => $price,
                'kind' => $kind,
                'production_days' => $days,
                'is_active' => $active,
                'is_featured' => true,
                'label' => $label,
                'short_description' => $short,
                'sort_order' => $order,
            ]);

            if ($slug === 'adesivo-ovniporto') {
                // Packaging estimate for freight (envelope with the sticker); the admin adjusts it in the panel.
                $product->update(['weight_grams' => 20, 'dimensions' => ['length' => 16, 'width' => 11, 'height' => 1]]);
                $product->variants()->updateOrCreate(['sku' => 'OVP-ADESIVO'], [
                    'name' => 'Único',
                    'stock_qty' => 500,
                ]);
            }
        }
    }
}
