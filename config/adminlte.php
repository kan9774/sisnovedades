<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Novedades',
    'title_prefix' => 'B.Com.N°1 | ',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => true,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>SIS</b>-Novedades',
    'logo_img' => 'image/logo/Heraldica.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'image/logo/Heraldica.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'image/logo/Heraldica.png',
            'alt' => 'Novedades Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'admin',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        [
            'type'         => 'fullscreen-widget',
            'topnav_right' => true,
        ],
        ['header' => 'I N I C I O'],
        [
            'text' => 'Inicio',
            'url' => 'admin',
            'icon' => 'fa-solid fa-house-chimney',
            'active' => ['admin']
        ],
        // Guardias — todos pueden ver
        [
            'text' => 'Guardias y Novedades',
            'icon' => 'fa-solid fa-person-military-pointing',
            'can' => 'view_guardias',
            'submenu' => [
                [
                    'text'   => 'Guardias',
                    'url'    => 'admin/guardias',
                    'icon'   => 'fa-solid fa-person-rifle',
                    'active' => ['admin/guardias*'],
                    'can'    => 'view_guardias',
                ],
                [
                    'text' => 'Novedades del Día',
                    'icon' => 'fa-solid fa-newspaper',
                    'url'  => 'admin/novedades',
                    'active' => ['admin/novedades*'],
                ],
                [
                    'text'   => 'Destinatarios PDF',
                    'url'    => 'admin/pdf-destinatarios',
                    'icon'   => 'fa-solid fa-address-book',
                    'active' => ['admin/pdf-destinatarios*'],
                    'can'    => 'ver_destinatarios_pdf',
                ],
            ],
        ],

        // Usuarios — solo admin
        [
            'text'  => 'Usuarios',
            'icon'  => 'fa-solid fa-user-gear',
            'can'     => 'viewAny-user',
            'submenu' => [
                [
                    'text'    => 'Usuarios',
                    'url'     => 'admin/users',
                    'icon'    => 'fa-solid fa-users',
                    'active'  => ['admin/users*'],
                    'can'     => 'viewAny-user',
                ],
                                [
                    'text'   => 'Unidades Ámbito',
                    'url'    => 'admin/unidades',
                    'icon'   => 'fas fa-building',
                    'active' => ['admin/unidades*'],
                    'can'    => 'viewAny-user', // solo admin
                ],
                [
                    'text'   => 'Oficinas',
                    'url'    => 'admin/oficinas',
                    'icon'   => 'fas fa-building',
                    'active' => ['admin/oficinas*'],
                    'can'    => 'viewAny-oficina',
                ],
                                [
                    'text'   => 'Unidades Ejército',
                    'url'    => 'admin/organismos',
                    'icon'   => 'fas fa-landmark',
                    'active' => ['admin/organismos*'],
                    'can'    => 'viewAny-user', // solo admin
                ],
            ],
        ],
        // Vehículos
        [
            'text'   => 'Parque Vehículos',
            'icon'   => 'fa-solid fa-car',
            'can'    => 'viewAny-vehiculo', // Necesitas crear este Gate o usar otro
            'submenu' => [
                [
                    'text'   => 'Vehículos',
                    'url'    => 'admin/vehiculos',
                    'icon'   => 'fa-solid fa-truck-monster',
                    'active' => ['admin/vehiculos*'],
                    'can'    => 'viewAny-vehiculo',
                ],
                [
                    'text'   => 'Conductores',
                    'url'    => 'admin/conductores',
                    'icon'   => 'fa-solid fa-wheelchair-move',
                    'active' => ['admin/conductores*'],
                    'can'    => 'viewAny-conductor',
                ],
                [
                    'text'   => 'Tipos de Vehículo',
                    'url'    => 'admin/vehiculos/tipos',
                    'icon'   => 'fa-solid fa-shapes',
                    'active' => ['admin/vehiculos/tipos*'],
                    'can'    => 'viewAny-tipos-vehiculo',
                ],
            ],
        ],

        // Roles y Permisos — solo admin
        [
            'text'    => 'Permisos de Usuarios',
            'icon'   => 'fa-solid fa-user-lock',
            'can'    => 'viewAny-rol',
            'submenu' => [
                [
                    'text'    => 'Roles',
                    'url'     => 'admin/roles',
                    'icon'    => 'fas fa-key',
                    'active'  => ['admin/roles*'],
                    'can'     => 'viewAny-rol',
                ],
                [
                    'text'   => 'Permisos',
                    'url'    => 'admin/permisos',
                    'icon'   => 'fas fa-shield-alt',
                    'active' => ['admin/permisos*'],
                    'can'    => 'viewAny-user',
                ],
            ]
        ],
        // Palomar
        [
            'text'    => 'Palomar Militar',
            'icon'    => 'fas fa-dove',
            'can'     => 'viewAny-palomar',
            'submenu' => [
                [
                    'text'   => 'Palomares',
                    'url'    => 'admin/palomar/palomares',
                    'icon'   => 'fas fa-home',
                    'active' => ['admin/palomar/palomares*'],
                    'can'    => 'viewAny-palomar',
                ],
                [
                    'text'   => 'Palomas',
                    'url'    => 'admin/palomar/palomas',
                    'icon'   => 'fas fa-feather-alt',
                    'active' => ['admin/palomar/palomas*'],
                    'can'    => 'viewAny-palomar',
                ],
                [
                    'text'   => 'Vuelos',
                    'url'    => 'admin/palomar/vuelos',
                    'icon'   => 'fa-solid fa-plane-departure',
                    'active' => ['admin/palomar/vuelos*'],
                    'can'    => 'viewAny-palomar',
                ],
                [
                    'text'   => 'Estados',
                    'url'    => 'admin/palomar/estados-paloma',
                    'icon'   => 'fas fa-tags',
                    'active' => ['admin/palomar/estados-paloma*'],
                    'can'    => 'viewAny-palomar',
                ],
            ],
        ],
        // Documentos
        [
            'text' => 'Manuales',
            'icon' => 'fa-solid fa-folder-tree',
            'submenu' => [

                [
                    'text'   => 'Documentos',
                    'url'    => 'admin/documentos',
                    'icon'   => 'fas fa-file-alt',
                    'active' => ['admin/documentos'],
                    'can'    => 'viewAny-documento',
                ],
                [
                    'text'   => 'Categorías',
                    'url'    => 'admin/documentos/categorias',
                    'icon'   => 'fa-solid fa-list',
                    'active' => ['admin/documentos/categorias'],
                    'can'    => 'viewAny-documento',
                ],
            ]
        ],
        // Auditoría
        [
            'header' => 'A U D I T O R Í A',
            'can'    => 'viewAny-log',
        ],
        [
            'text'   => 'Log de Actividad',
            'url'    => 'admin/logs',
            'icon'   => 'fas fa-history',
            'active' => ['admin/logs*'],
            'can'    => 'viewAny-log',
        ],
        [
            'text'   => 'Backups',
            'url'    => 'admin/backup',
            'icon'   => 'fas fa-database',
            'active' => ['admin/backup*'],
            'can'    => 'viewAny-log',
        ],
        // Configuración
        // ['header' => 'C U E N T A'],
        // [
        //     'text' => 'Perfil',
        //     'url'  => 'admin/settings',
        //     'icon' => 'fas fa-fw fa-user',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => true,
];