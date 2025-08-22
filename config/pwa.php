<?php

return [
    'install-button' => true,

    'manifest'       => [
        'name'             => 'Vigilante',
        'short_name'       => 'Vigilante',
        'start_url'        => '/resumen?source=pwa',
        'scope'            => '/',
        'background_color' => '#ffffff',
        'theme_color'      => '#dc408a',
        'display'          => 'standalone',
        'orientation'      => 'portrait',
        'description'      => 'Administracion de entradas y salidas',
        'icons'            => [
            [
                'src'   => 'images/icons/icon-192x192.png',
                'sizes' => '192x192',
                'type'  => 'image/png',
            ],
            [
                'src'   => 'images/icons/icon-512x512.png',
                'sizes' => '512x512',
                'type'  => 'image/png',
            ],
        ],
    ],

    'debug'          => env('APP_DEBUG', false),
    'livewire-app'   => true,
];
