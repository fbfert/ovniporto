<?php

namespace Database\Seeders;

use App\Models\PlaceSpace;
use Illuminate\Database\Seeder;

class PlaceSpaceSeeder extends Seeder
{
    public function run(): void
    {
        $spaces = [
            ['pista-de-pouso', 'Pista de pouso', 'O coração do lugar: estrelas de pedra no chão, apontadas para o céu.', 1],
            ['area-de-vigilia', 'Área de vigília', 'Bancos baixos e luz vermelha para olhar o céu a noite inteira.', 1],
            ['carro-amarelo', 'Carro amarelo abduzido', 'A lenda em escala real, suspensa no meio do caminho.', 1],
            ['hangar', 'Hangar', 'Estacionamento para naves terrestres.', 1],
            ['museu-ao-ar-livre', 'Museu ao ar livre', 'Placas com QR ao longo da trilha contando as histórias do céu da serra.', 1],
            ['aduana-e-loja', 'Aduana interplanetária e loja', 'Carimbo no passaporte e lembranças para levar.', 2],
            ['lanchonete', 'Lanchonete', 'Café quente para as noites de vigília.', 3],
            ['torre-de-controle', 'Torre de controle', 'O mirante mais alto, de onde a comunidade vigia o céu.', 4],
            ['museu-coberto', 'Museu coberto', 'Acervo de relatos, objetos e a história de Cachi a Lages.', 4],
        ];

        foreach ($spaces as $order => [$slug, $name, $role, $phase]) {
            PlaceSpace::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'role' => $role,
                'phase' => $phase,
                'status' => 'planning',
                'sort_order' => $order,
            ]);
        }
    }
}
