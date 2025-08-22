<?php

return [

    'title'                                   => 'Vigilante 1.0 Beta',
    'title_prefix'                            => '',
    'title_postfix'                           => '',

    'use_ico_only'                            => false,
    'use_full_favicon'                        => false,

    'google_fonts'                            => [
        'allowed' => true,
    ],

    'logo'                                    => '<b>Vigilante</b>',                // Texto que se verá al lado del logo
    'logo_img'                                => 'images/brand/vigilante-mark.png', // Ruta dentro de /public
    'logo_img_class'                          => 'brand-image elevation-3',         // Sin "img-circle" para no recortar
    'logo_img_xl'                             => null,                              // O imagen más grande para top nav
    'logo_img_xl_class'                       => 'brand-image-xs',
    'logo_img_alt'                            => 'Vigilante Logo',

    'lang'                                    => 'es',
    'auth_logo'                               => [
        'enabled' => false,
        'img'     => [
            'path'   => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt'    => 'Auth Logo',
            'class'  => '',
            'width'  => 50,
            'height' => 50,
        ],
    ],

    'preloader'                               => [
        'enabled' => true,
        'img'     => [
            'path'   => '/images/brand/vigilante-mark.png', // tu logo
            'alt'    => 'Vigilante',
            'effect' => 'animation__shake', // o null para sin animación
            'width'  => 80,
            'height' => 80,
        ],
    ],

    'usermenu_enabled'                        => true,
    'usermenu_header'                         => false,
    'usermenu_header_class'                   => 'bg-primary',
    'usermenu_image'                          => false,
    'usermenu_desc'                           => false,
    'usermenu_profile_url'                    => false,

    'layout_topnav'                           => null,
    'layout_boxed'                            => null,
    'layout_fixed_sidebar'                    => null,
    'layout_fixed_navbar'                     => null,
    'layout_fixed_footer'                     => null,
    'layout_dark_mode'                        => null,

    'classes_auth_card'                       => 'card-outline card-primary',
    'classes_auth_header'                     => '',
    'classes_auth_body'                       => '',
    'classes_auth_footer'                     => '',
    'classes_auth_icon'                       => '',
    'classes_auth_btn'                        => 'btn-flat btn-primary',

    'classes_body'                            => '',
    'classes_brand'                           => '',
    'classes_brand_text'                      => '',
    'classes_content_wrapper'                 => '',
    'classes_content_header'                  => '',
    'classes_content'                         => '',
    'classes_sidebar'                         => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav'                     => '',
    'classes_topnav'                          => 'navbar-white navbar-light',
    'classes_topnav_nav'                      => 'navbar-expand',
    'classes_topnav_container'                => 'container',

    'sidebar_mini'                            => 'lg',
    'sidebar_collapse'                        => false,
    'sidebar_collapse_auto_size'              => false,
    'sidebar_collapse_remember'               => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme'                 => 'os-theme-light',
    'sidebar_scrollbar_auto_hide'             => 'l',
    'sidebar_nav_accordion'                   => true,
    'sidebar_nav_animation_speed'             => 300,

    'right_sidebar'                           => false,
    'right_sidebar_icon'                      => 'fas fa-cogs',
    'right_sidebar_theme'                     => 'dark',
    'right_sidebar_slide'                     => true,
    'right_sidebar_push'                      => true,
    'right_sidebar_scrollbar_theme'           => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide'       => 'l',

    'use_route_url'                           => true,
    'dashboard_url'                           => 'home',
    'logout_url'                              => 'logout',
    'login_url'                               => 'login',
    'register_url'                            => 'register',
    'password_reset_url'                      => 'password/reset',
    'password_email_url'                      => 'password/email',
    'profile_url'                             => false,
    'disable_darkmode_routes'                 => false,

    'laravel_asset_bundling'                  => false,
    'laravel_css_path'                        => 'css/app.css',
    'laravel_js_path'                         => 'js/app.js',

    'menu'                                    => [
        ['header' => 'VIGILANTE'],

        // 👉 Resumen (dashboard)
        ['text' => 'Resumen', 'route' => 'dashboard.summary', 'icon' => 'fas fa-tachometer-alt', 'can' => 'access.view'],

        // Accesos
        ['text' => 'Inicio', 'route' => 'access.index', 'icon' => 'fas fa-home', 'can' => 'access.view'],
        ['text' => 'Registrar entrada', 'route' => 'access.create', 'icon' => 'fas fa-sign-in-alt', 'can' => 'access.enter'],
        ['text' => 'Activos', 'route' => 'access.active', 'icon' => 'fas fa-user-check', 'can' => 'access.view.active'],
        ['text' => 'Registrar salida', 'route' => 'access.exit.form', 'icon' => 'fas fa-sign-out-alt', 'can' => 'access.exit'],
        ['text' => 'Reportes', 'route' => 'reports.index', 'icon' => 'fas fa-chart-bar', 'can' => 'reports.view'],

        // Admin
        ['header' => 'ADMIN', 'can' => 'roles.manage'],
        ['text' => 'Usuarios', 'route' => 'admin.users.index', 'icon' => 'fas fa-users', 'can' => 'users.manage'],
        ['text' => 'Roles', 'route' => 'admin.roles.index', 'icon' => 'fas fa-id-badge', 'can' => 'roles.manage'],

        // Header para sucursales
        ['header' => 'SUCURSALES', 'can' => 'branches.manage'],
        ['text' => 'Listado de Sucursales', 'route' => 'branches.index', 'icon' => 'fas fa-building', 'can' => 'branches.manage'],
        ['text' => 'Crear Sucursal', 'route' => 'branches.create', 'icon' => 'fas fa-plus-circle', 'can' => 'branches.manage'],

        // Header para patrullas
        ['header' => 'PATRULLAS'],

        [
            'text'  => 'Panel de Patrullas',
            'route' => 'admin.patrol.dashboard',
            'icon'  => 'fas fa-chart-line',
            'can'   => 'patrol.manage',
        ],
        [
            'text'  => 'Escanear Checkpoint',
            'route' => 'patrol.scan',
            'icon'  => 'fas fa-qrcode',
            'can'   => 'patrol.scan',
        ],
        [
            'text'    => 'Rutas de Patrulla',
            'icon'    => 'fas fa-route',
            'can'     => 'patrol.manage',
            'submenu' => [
                ['text' => 'Listado', 'route' => 'admin.patrol.routes.index', 'icon' => 'fas fa-list', 'can' => 'patrol.manage'],
                ['text' => 'Nueva Ruta', 'route' => 'admin.patrol.routes.create', 'icon' => 'fas fa-plus-circle', 'can' => 'patrol.manage'],
            ],
        ],
        [
            'text'  => 'Asignaciones',
            'route' => 'admin.patrol.assignments.index',
            'icon'  => 'fas fa-calendar-check',
            'can'   => 'patrol.manage',
        ],
        [
            'text'  => 'Mis Patrullas',
            'route' => 'patrol.index',
            'icon'  => 'fas fa-walking',
            'can'   => 'patrol.view', // o 'patrol.scan' si querés que sólo los que escanean lo vean
        ],
    ],

    'filters'                                 => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    'plugins'                                 => [
        'Datatables'  => [
            'active' => true,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2'     => [
            'active' => true,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs'     => [
            'active' => true,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => 'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files'  => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace'        => [
            'active' => false,
            'files'  => [
                [
                    'type'     => 'css',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    'iframe'                                  => [
        'default_tab' => [
            'url'   => null,
            'title' => null,
        ],
        'buttons'     => [
            'close'           => true,
            'close_all'       => true,
            'close_all_other' => true,
            'scroll_left'     => true,
            'scroll_right'    => true,
            'fullscreen'      => true,
        ],
        'options'     => [
            'loading_screen'    => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items'  => true,
        ],
    ],

    'livewire'                                => false,
];
