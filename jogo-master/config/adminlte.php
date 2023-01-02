<?php

return [

    /*Rótulos
    Aqui segem os rótulos usados no site de forma dinamica
    */ 
    'title_adm_geral' => 'SUPER BETS - Área administrativia - Sistema de Apostas Esportivas',
    'title_adm_login' => 'SUPER BETS - Login - Sistema de Apostas Esportivas',
    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | The default title of your admin panel, this goes into the title tag
    | of your page. You can override it per page with the title section.
    | You can optionally also specify a title prefix and/or postfix.
    |
    */

    'title'             => 'STAR BETSS - Sistema de Aposta Esportivas',

    'title_prefix'      => 'STAR BETSS- Sistema de Aposta Esportivas',

    'title_postfix'     => 'STAR BETSS- Sistema de Aposta Esportivas',

    'version_system'    => '2.0.0',

    'name_site'   => 'S B',

    'url_system'         => 'starbetss.net.br',

    'year_system'       => '2021',
 

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    |
    | This logo is displayed at the upper left corner of your admin panel.
    | You can use basic HTML here if you want. The logo has also a mini
    | variant, used for the mini sides bar. Make it 3 letters or so
    |
    */

    'logo' => '<b>STAR  </b> BETSS',

    'logo_mini' => '<b>STAR </b>BETSS',

    /*
    |--------------------------------------------------------------------------
    | Skin Color
    |--------------------------------------------------------------------------
    |
    | Choose a skin color for your admin panel. The available skin colors:
    | blue, black, purple, yellow, red, and green. Each skin also has a
    | ligth variant: blue-light, purple-light, purple-light, etc.
    |
    */

    'skin' => 'green',

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Choose a layout for your admin panel. The available layout options:
    | null, 'boxed', 'fixed', 'top-nav'. null is the default, top-nav
    | removes the sidebar and places your menu in the top navbar
    |
    */

    'layout' => null,

    /*
    |--------------------------------------------------------------------------
    | Collapse Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we choose and option to be able to start with a collapsed side
    | bar. To adjust your sidebar layout simply set this  either true
    | this is compatible with layouts except top-nav layout option
    |
    */

    'collapse_sidebar' => false,

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Register here your dashboard, logout, login and register URLs. The
    | logout URL automatically sends a POST request in Laravel 5.3 or higher.
    | You can set the request to a GET or POST with logout_method.
    | Set register_url to null if you don't want a register link.
    |
    */

    'dashboard_url' => 'admin/home',

    'logout_url' => 'logout',

    'logout_method' => null,

    'login_url' => 'login',

    'register_url' => 'register',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Specify your menu items to display in the left sidebar. Each menu item
    | should have a text and and a URL. You can also specify an icon from
    | Font Awesome. A string instead of an array represents a header in sidebar
    | layout. The 'can' is a filter on Laravel's built in Gate functionality.
    |
    */

    'menu' => [
        'PAINEL NAVEGAÇÃO',

            [
                'text' => 'Página Inicial',
                'url'  => 'admin/home',
                'icon' => 'dashboard',
            ],
               
            [
                'text' => 'Gerentes',
                'url'  => 'admin/gerentes',
                'icon' => 'users',
                'can'  => 'adm'
            ],
            [
                'text' => 'Cambistas',
                'url'  => 'admin/cambistas',
                'icon' => 'users',
            ],
                   
            [
                'text' => 'Caixa Gerente',
                'url'  => 'admin/caixa-adm-gerente',
                'icon' => 'bank',
                'can'  => 'adm',
                
            ],

            [
                'text' => 'Caixa Cambista',
                'url'  => 'admin/caixa-adm-cambista',
                'icon' => 'bank',
                'can'  => 'adm',
                
            ],

            [
                'text' => 'Relatório Cambista',
                'url'  => 'admin/relatorio-cambista',
                'icon' => 'money',
                
            ],

            [
                'text' => 'Relatório Gerente',
                'url'  => 'admin/relatorio-gerente',
                'icon' => 'money',
                'can'  => 'adm',
                
            ],

           
            [
                'text' => 'Financeiro',
                'url'  => 'admin/financeiro-gerente',
                'icon' => 'money',
                'can'  => 'gerente',
            ],

            [
                'text' => 'Lançamentos (Cambistas)',
                'url'  => 'admin/lancamentos',
                'icon' => 'cloud-upload',
                'can'  => 'adm',
            ],
          
            [
                'text' => 'Bilhetes',
                'url'  => 'admin/bilhetes',
                'icon' => 'tags',
            ],
            [
                'text' => 'Cotações Loto (Quina)',
                'url'  => 'admin/cotacao-loto-quina',
                'icon' => 'qrcode',
                'can'  => 'adm'
            ],
            [
                'text' => 'Cotações Loto (Sena)',
                'url'  => 'admin/cotacao-loto-sena',
                'icon' => 'qrcode',
                'can'  => 'adm'
            ],
            [
                'text' => 'Configurações',
                'url'  => 'admin/configuracoes',
                'icon' => 'cogs',
                'can'  => 'adm'
            ],

            [
                'text' => 'Gerenc. Risco',
                'url'  => 'admin/gerenciaento-riscos',
                'icon' => 'balance-scale',
                'can'  => 'adm'
            ],
     
          
            [
                'text' => 'Mapa de Apostas',
                'url'  => 'admin/mapa-apostas',
                'icon' => 'line-chart',
                'can'  => 'adm'
            ],
            [
                'text' => 'Ger. Mercados Cambista',
                'url'  => 'admin/mercados-user',
                'icon' => 'sort-amount-asc',
                'can'  => 'adm'
            ],
            [
                'text' => 'Ger. Mercados Geral',
                'url'  => 'admin/mercados',
                'icon' => 'sort-amount-asc',
                'can'  => 'adm'
            ],
            [
                'text' => 'Ger. Odds Cambista',
                'url'  => 'admin/odds-user',
                'icon' => 'sort-amount-asc',
                'can'  => 'adm'
            ],
            [
                'text' => 'Ger. Odds',
                'url'  => 'admin/odds',
                'icon' => 'sort-amount-asc',
                'can'  => 'adm'
            ],
           
            [
                'text' => 'Regulamentos',
                'url'  => 'admin/regulamento',
                'icon' => 'map-o',
                'can'  => 'adm'
            ],
           

            [
                'text' => 'Gerenciar Ligas',
                'url'  => 'admin/adm-ligas-list',
                'icon' => 'trophy',
                'can'  => 'adm'
            ],

            [
                'text' => 'Ligas (Principais) ',
                'url'  => 'admin/gerenciar-ligas-principais',
                'icon' => 'trophy',
                'can'  => 'adm'
            ],

            [
                'text' => 'Ligas (Bloqueadas) ',
                'url'  => 'admin/gerenciar-ligas',
                'icon' => 'trophy',
                'can'  => 'adm'
            ],
            [
                'text' => 'Confrontos (Bloqueados) ',
                'url'  => 'admin/gerenciar-matchs',
                'icon' => 'futbol-o',
                'can'  => 'adm'
            ],
            
            [
                'text' => 'Gerenciar Confrontos (Ao-Vivo)',
                'url'  => 'admin/confrontos-aovivo',
                'icon' => 'futbol-o',
                'can'  => 'adm'
            ],
            [
                'text' => 'Gerenciar Confrontos',
                'url'  => 'admin/confrontos',
                'icon' => 'futbol-o',
                'can'  => 'adm'
            ],



  

],
    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Choose what filters you want to include for rendering the menu.
    | You can add your own filters to this array after you've created them.
    | You can comment out the GateFilter if you don't want to use Laravel's
    | built in Gate functionality
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SubmenuFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Choose which JavaScript plugins should be included. At this moment,
    | only DataTables is supported as a plugin. Set the value to true
    | to include the JavaScript file from a CDN via a script tag.
    |
    */

    'plugins' => [
        'datatables' => true,
        'select2'    => true,
        'chartjs'    => true,
    ],
];
