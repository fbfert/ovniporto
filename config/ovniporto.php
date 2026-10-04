<?php

return [
    // The future runway, next to Hospedaria Vila das Pedras (Lages, SC). Origin for partner distances.
    'location' => [
        'lat' => -27.85495,
        'lng' => -50.21841,
    ],

    // "nominatim" (OpenStreetMap, cached) or "offline" (no network: e2e suite, offline development).
    'geocoder' => env('GEOCODER', 'nominatim'),

    'shipping' => [
        // Standard box used for quotes and labels (cm); weights come from the products.
        'package' => [
            'length' => (int) env('SHIPPING_PACKAGE_LENGTH', 16),
            'width' => (int) env('SHIPPING_PACKAGE_WIDTH', 11),
            'height' => (int) env('SHIPPING_PACKAGE_HEIGHT', 2),
        ],
        'sender' => [
            'name' => env('SHIPPING_SENDER_NAME', ''),
            'phone' => env('SHIPPING_SENDER_PHONE', ''),
            'email' => env('SHIPPING_SENDER_EMAIL', ''),
            'document' => env('SHIPPING_SENDER_DOCUMENT', ''),
            'street' => env('SHIPPING_SENDER_STREET', ''),
            'number' => env('SHIPPING_SENDER_NUMBER', ''),
            'district' => env('SHIPPING_SENDER_DISTRICT', ''),
            'city' => env('SHIPPING_SENDER_CITY', 'Lages'),
            'state' => env('SHIPPING_SENDER_STATE', 'SC'),
        ],
    ],

    // 3D viewers the place page may embed (the panel refuses any other host).
    'embed_hosts' => array_values(array_filter(array_map('trim', explode(',', (string) env(
        'OVNIPORTO_EMBED_HOSTS',
        'sketchfab.com,my.matterport.com,kuula.co,momento360.com',
    ))))),
];
