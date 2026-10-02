<?php

return [
    // The future runway, next to Hospedaria Vila das Pedras (Lages, SC). Origin for partner distances.
    'location' => [
        'lat' => -27.85495,
        'lng' => -50.21841,
    ],

    // 3D viewers the place page may embed (the panel refuses any other host).
    'embed_hosts' => array_values(array_filter(array_map('trim', explode(',', (string) env(
        'OVNIPORTO_EMBED_HOSTS',
        'sketchfab.com,my.matterport.com,kuula.co,momento360.com',
    ))))),
];
