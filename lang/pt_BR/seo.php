<?php

/*
 * Sharing metadata, read by App\Http\Seo. Visible page titles stay in
 * resources/js/i18n/pt-BR.ts; these are what search engines and link previews show.
 * Pages missing here get the default description and are kept out of the index.
 */

return [
    'site_name' => 'OVNIPORTO Lages',
    'home_title' => 'OVNIPORTO Lages · A pista de pouso do planalto',
    'default_description' => 'Astroturismo na Serra Catarinense: comunidade, Livro de avistamentos e lembranças do OVNIPORTO, a futura pista de pouso de Lages, SC.',

    'pages' => [
        'home' => ['title' => null],
        'legend' => [
            'title' => 'A lenda',
            'description' => 'Uma pista de pouso de verdade, com uma lenda inventada por cima. A gente conta qual é qual.',
        ],
        'faq' => [
            'title' => 'Perguntas frequentes',
            'description' => 'O OVNIPORTO já existe? É de graça? Meus dados ficam públicos? As respostas diretas.',
        ],
        'community' => [
            'title' => 'Comunidade',
            'description' => 'As regras de convivência da vigília, o grupo do WhatsApp, o Instagram e o Avise-me da campanha.',
        ],
        'privacy' => [
            'title' => 'Privacidade',
            'description' => 'Como o OVNIPORTO trata os seus dados. Texto em rascunho, a ser redigido pelo encarregado.',
        ],
        'terms' => [
            'title' => 'Termos de uso',
            'description' => 'As regras de uso do site, da comunidade e da loja. Texto em rascunho, a ser redigido pelo encarregado.',
        ],
        'place' => [
            'title' => 'O lugar',
            'description' => 'O projeto da pista de pouso do OVNIPORTO em Lages, SC: onde vai ficar, os espaços fase a fase e as regras do céu escuro.',
            'image' => '/concept/overview.jpg',
        ],
        'support' => [
            'title' => 'Apoie a pista',
            'description' => 'Como vai funcionar o apoio à pista de pouso do OVNIPORTO. Nenhuma arrecadação abre antes de existir orçamento.',
            'image' => '/concept/overview.jpg',
        ],
        'region' => [
            'title' => 'Conheça a região',
            'description' => 'Pousadas, passeios, produtores e comida da serra em volta do OVNIPORTO, em Lages, SC. Só com quem autorizou aparecer.',
        ],
        'diary' => [
            'title' => 'Diário da obra',
            'description' => 'O diário da construção da pista de pouso do OVNIPORTO, em Lages, SC.',
        ],
        'logbook' => [
            'title' => 'Livro de avistamentos',
            'description' => 'O mapa dos relatos aprovados pela torre de controle no céu da Serra Catarinense.',
        ],
        'store' => [
            'title' => 'Loja',
            'description' => 'Adesivo, camiseta, caneca e Kit Abdução do OVNIPORTO. Parte de cada venda vira pista.',
        ],
        'postcard' => [
            'title' => 'Mande um postal',
            'description' => 'Um postal do OVNIPORTO para mandar a quem precisa ver o céu de Lages. Guardei um lugar pra você.',
            'image' => '/brand/postal-og.jpg',
        ],

        // Private or transactional: titled, never indexed.
        'login' => ['title' => 'Entrar na comunidade', 'index' => false],
        'welcome' => ['title' => 'Seu apelido na vigília', 'index' => false],
        'account' => ['title' => 'Minha conta', 'index' => false],
        'report' => ['title' => 'Relatar avistamento', 'index' => false],
        'report.edit' => ['title' => 'Ajustar relato', 'index' => false],
        'report.sent' => ['title' => 'Relato na torre de controle', 'index' => false],
        'checkout' => ['title' => 'Finalizar compra', 'index' => false],
        'order.pay' => ['title' => 'Pagamento', 'index' => false],
        'waitlist.confirm' => ['title' => 'Inscrição confirmada', 'index' => false],
    ],

    'order_title' => 'Pedido :number',
    'pending_sighting_title' => 'Relato em análise',

    'sighting_types' => ['light' => 'Luz', 'object' => 'Objeto', 'trail' => 'Rastro', 'other' => 'Outro'],

    // Preview images (/og/*).
    'og' => [
        'sighting' => 'Alguém jurou ter visto',
        'product' => 'Lembranças de Lages',
        'partner' => 'Fique mais um dia',
        'post' => 'Diário da obra',
        'footer' => 'OVNIPORTO · LAGES · SC',
    ],
];
