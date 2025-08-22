<?php

return [
    // === ACCESOS ===
    'access.view'        => [
        'label' => 'Ver todos los registros',
        'group' => 'ACCESOS',
        'desc'  => 'Lista general de entradas/salidas.',
    ],
    'access.view.active' => [
        'label' => 'Ver activos (dentro)',
        'group' => 'ACCESOS',
        'desc'  => 'Ver quiénes están dentro actualmente.',
    ],
    'access.enter'       => [
        'label' => 'Registrar entradas',
        'group' => 'ACCESOS',
        'desc'  => 'Crear registros de entrada (vehículo o a pie).',
    ],
    'access.exit'        => [
        'label' => 'Registrar salidas',
        'group' => 'ACCESOS',
        'desc'  => 'Cerrar registros (salida total o parcial).',
    ],
    'access.show'        => [
        'label' => 'Ver detalle de registro',
        'group' => 'ACCESOS',
        'desc'  => 'Ver información detallada de un acceso.',
    ],

    // === REPORTES ===
    'reports.view'       => [
        'label' => 'Ver reportes y estadísticas',
        'group' => 'REPORTES',
        'desc'  => 'Acceso a la pantalla de reportes/estadísticas.',
    ],

    // === USUARIOS ===
    'users.manage'       => [
        'label' => 'Gestionar usuarios',
        'group' => 'USUARIOS',
        'desc'  => 'Crear/editar usuarios, activar/desactivar.',
    ],

    // === ROLES ===
    'roles.manage'       => [
        'label' => 'Gestionar roles y permisos',
        'group' => 'ROLES',
        'desc'  => 'Asignar y configurar permisos de cada rol.',
    ],

    // === SUCURSALES ===
    'branches.manage'    => [
        'label' => 'Gestionar sucursales',
        'group' => 'SUCURSALES',
        'desc'  => 'Crear, editar y eliminar sucursales.',
    ],

    // === PATRULLAS ===
    'patrol.manage'      => [
        'label' => 'Gestionar patrullas (rutas, checkpoints, asignaciones)',
        'group' => 'PATRULLAS',
        'desc'  => 'Acceso a módulo de administración de patrullas.',
    ],
    'patrol.view'        => [
        'label' => 'Ver módulo de patrullas (guardia)',
        'group' => 'PATRULLAS',
        'desc'  => 'Acceso a Mis Patrullas.',
    ],
    'patrol.scan'        => [
        'label' => 'Escanear checkpoints (guardia)',
        'group' => 'PATRULLAS',
        'desc'  => 'Permite escanear y registrar checkpoints.',
    ],

];
