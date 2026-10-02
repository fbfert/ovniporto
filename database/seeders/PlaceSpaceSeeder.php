<?php

namespace Database\Seeders;

use App\Models\PlaceSpace;
use Illuminate\Database\Seeder;

class PlaceSpaceSeeder extends Seeder
{
    public function run(): void
    {
        $spaces = [
            // [slug, name, role, phase, concept illustration slug (public/concept) or null]
            ['pista-de-pouso', 'Pista de pouso', 'O coração do lugar: estrelas de pedra no chão, apontadas para o céu.', 1, 'cover-alt'],
            ['area-de-vigilia', 'Área de vigília', 'Bancos baixos e luz vermelha para olhar o céu a noite inteira.', 1, 'vigil'],
            ['carro-amarelo', 'Carro amarelo abduzido', 'A lenda em escala real, suspensa no meio do caminho.', 1, 'yellow-car-sculpture'],
            ['hangar', 'Hangar', 'Estacionamento para naves terrestres.', 1, null],
            ['museu-ao-ar-livre', 'Museu ao ar livre', 'Placas com QR ao longo da trilha contando as histórias do céu da serra.', 1, 'museum-path'],
            ['aduana-e-loja', 'Aduana interplanetária e loja', 'Carimbo no passaporte e lembranças para levar.', 2, 'customs-shop'],
            ['lanchonete', 'Lanchonete', 'Café quente para as noites de vigília.', 3, 'snack-bar'],
            ['torre-de-controle', 'Torre de controle', 'O mirante mais alto, de onde a comunidade vigia o céu.', 4, 'tower'],
            ['museu-coberto', 'Museu coberto', 'Acervo de relatos, objetos e a história de Cachi a Lages.', 4, null],
        ];

        foreach ($spaces as $order => [$slug, $name, $role, $phase, $concept]) {
            PlaceSpace::query()->firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'role' => $role,
                'phase' => $phase,
                'status' => 'planning',
                'sort_order' => $order,
                'concept_image_path' => $concept,
            ]);
        }
    }
}
