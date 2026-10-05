<?php

/*
 * The nine spaces of the plan ("OVNIPORTO Lages — Planejamento do site", Espaços físicos), in visiting order.
 * Shared by PlaceSpaceSeeder and the migration that fills production, so both stay in step.
 * concept: slug of the concept illustration in public/concept, or null while none exists.
 */

return [
    [
        'slug' => 'pista-de-pouso',
        'name' => 'Pista de pouso',
        'role' => 'O símbolo principal, feito para ser visto do alto.',
        'description' => 'Um desenho no chão feito de pedras, com balizas de luz baixa e pedras gravadas com o nome de quem apoiar a obra.',
        'phase' => 1,
        'concept' => 'cover-alt',
    ],
    [
        'slug' => 'area-de-vigilia',
        'name' => 'Área de vigília',
        'role' => 'Onde se passa a noite olhando o céu.',
        'description' => 'Espreguiçadeiras, fogo de chão e cobertores, com luz só vermelha e baixa.',
        'phase' => 1,
        'concept' => 'vigil',
    ],
    [
        'slug' => 'carro-amarelo',
        'name' => 'Carro amarelo abduzido',
        'role' => 'Ponto de foto e prova do relato.',
        'description' => 'Um carro velho, amarelo, suspenso num feixe verde: escultura, não acidente.',
        'phase' => 1,
        'concept' => 'yellow-car-sculpture',
    ],
    [
        'slug' => 'hangar',
        'name' => 'Hangar',
        'role' => 'A chegada de carro.',
        'description' => 'Estacionamento para naves terrestres, só com sinalização.',
        'phase' => 1,
        'concept' => 'hangar',
    ],
    [
        'slug' => 'museu-ao-ar-livre',
        'name' => 'Museu ao ar livre',
        'role' => 'Um percurso entre os espaços.',
        'description' => 'Placas com QR code ao longo dos caminhos contando Cachi, o relato do carro amarelo e os relatos do Livro de avistamentos.',
        'phase' => 1,
        'concept' => 'museum-path',
    ],
    [
        'slug' => 'aduana-e-loja',
        'name' => 'Aduana interplanetária e loja',
        'role' => 'Entrada, recepção e Free Shop Interplanetário na saída.',
        'description' => 'Passaporte Interplanetário carimbado, check-in da vigília, os produtos da loja e banheiros.',
        'phase' => 2,
        'concept' => 'customs-shop',
    ],
    [
        'slug' => 'lanchonete',
        'name' => 'Lanchonete',
        'role' => 'O cardápio de bordo.',
        'description' => 'Disco Voador, Buraco Negro e Combustível de Foguete para as noites frias, com banheiros.',
        'phase' => 3,
        'concept' => 'snack-bar',
    ],
    [
        'slug' => 'torre-de-controle',
        'name' => 'Torre de controle',
        'role' => 'O mirante elevado e o coração do relato.',
        'description' => 'Rádio, antena, o Livro de avistamentos em papel e um telescópio no topo.',
        'phase' => 4,
        'concept' => 'tower',
    ],
    [
        'slug' => 'museu-coberto',
        'name' => 'Museu coberto',
        'role' => 'O acervo que precisa de abrigo.',
        'description' => 'Objetos, fotos e relatos impressos, de Cachi a Lages. Pode ficar na base da torre.',
        'phase' => 4,
        'concept' => 'museum-indoor',
    ],
];
