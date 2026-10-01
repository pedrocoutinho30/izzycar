<?php

/*
|--------------------------------------------------------------------------
| Catálogo de permissões do backoffice
|--------------------------------------------------------------------------
|
| Fonte única da estrutura Categoria → Objeto → Ações → Âmbito. As permissões
| do Spatie são geradas a partir daqui (php artisan permissions:sync):
|
|   <objeto>.<ação>            ex. clients.create
|   <objeto>.<ação>.<âmbito>   ex. leads.view.own
|
| Para acrescentar um objeto basta uma entrada em "modules" e correr o sync;
| o perfil admin tem acesso a tudo sem precisar de lista (Gate::before).
|
| Rotas: cada rota de /gestao tem de pertencer a um objeto, à lista "shared"
| ou à lista "admin_only" (o teste PermissionCatalogTest garante-o). A ação é
| deduzida do nome da rota: index/show/… → view, create/store → create,
| edit/update/… → update, destroy/delete → delete; sub-recursos (ex.
| vehicles.photos.cover) contam como editar o objeto. Nos objetos com âmbito,
| as rotas normais exigem "todos"; só as de "own_routes" (que já filtram pelo
| dono) aceitam "próprios".
|
*/

return [

    /*
     | Enquanto false, o middleware só REGISTA o que bloquearia (canal de log
     | "permissions", ver php artisan permissions:report) e deixa passar.
     */
    'enforce' => env('PERMISSIONS_ENFORCE', false),

    'actions' => [
        'view' => 'Ver',
        'create' => 'Criar',
        'update' => 'Editar',
        'delete' => 'Eliminar',
    ],

    'scopes' => [
        'all' => 'Todos',
        'own' => 'Próprios',
    ],

    'modules' => [

        'funil' => [
            'label' => 'Funil de vendas',
            'resources' => [
                'leads' => [
                    'label' => 'Leads',
                    'actions' => ['view' => ['all', 'own'], 'create' => [], 'update' => ['all', 'own'], 'delete' => []],
                    'owner' => 'owner_id',
                    'routes' => ['admin.v2.leads.*', 'admin.v2.pre-leads.*', 'admin.v2.api.new-leads', 'admin.v2.export.leads'],
                    'own_routes' => ['admin.angariador.dashboard', 'admin.angariador.leads', 'admin.angariador.leads.*'],
                ],
                'clients' => [
                    'label' => 'Clientes',
                    'actions' => ['view' => ['all', 'own'], 'create' => [], 'update' => ['all', 'own'], 'delete' => []],
                    'owner' => 'owner_id',
                    'routes' => ['admin.v2.clients.*', 'admin.v2.export.clients'],
                ],
                'form-proposals' => [
                    'label' => 'Formulários',
                    // Pedidos de importação: chegam pelo site ou são criados à mão
                    // dentro da lead/cliente. As Oportunidades vivem dentro de
                    // cada pedido (editar).
                    'actions' => ['view' => ['all', 'own'], 'create' => [], 'update' => [], 'delete' => []],
                    'owner' => 'client.owner_id',
                    'routes' => ['admin.v2.form-proposals.*', 'admin.v2.consignment-evaluations.*'],
                    'own_routes' => ['admin.angariador.formularios'],
                ],
                'proposals' => [
                    'label' => 'Cotações',
                    'actions' => ['view' => ['all', 'own'], 'create' => [], 'update' => ['all', 'own'], 'delete' => []],
                    'owner' => 'client.owner_id',
                    'routes' => ['admin.v2.proposals.*', 'admin.v2.car-candidates.*', 'admin.v2.export.proposals', 'isv.*'],
                    'own_routes' => ['admin.angariador.propostas'],
                ],
                'converted-proposals' => [
                    'label' => 'Cotações convertidas',
                    'actions' => ['view' => [], 'create' => [], 'update' => [], 'delete' => []],
                    'routes' => ['admin.v2.converted-proposals.*', 'converted-proposals.updateStatus'],
                ],
                'cost-simulators' => [
                    'label' => 'Simulador de custos',
                    // As simulações são feitas no site.
                    'actions' => ['view' => [], 'delete' => []],
                    'routes' => ['admin.v2.cost-simulators.*'],
                ],
            ],
        ],

        'operacoes' => [
            'label' => 'Operações',
            'resources' => [
                'legalizations' => ['label' => 'Legalizações', 'routes' => ['admin.legalizations.*']],
                'vehicles' => ['label' => 'Viaturas', 'routes' => ['admin.v3.vehicles.*', 'admin.v2.vehicles.*']],
                'tasks' => ['label' => 'Tarefas', 'routes' => ['admin.tasks.*']],
                'movements' => ['label' => 'Movimentos', 'routes' => ['admin.v2.movements.*', 'admin.v2.expenses.*']],
                'sales' => ['label' => 'Vendas', 'routes' => ['admin.v2.sales.*']],
                'inspections' => ['label' => 'Inspeções', 'routes' => ['admin.v3.inspections.*']],
                'transports' => ['label' => 'Transportes', 'routes' => ['admin.transport-quotes.*']],
            ],
        ],

        'rede' => [
            'label' => 'Angariadores e parceiros',
            'resources' => [
                'angariadores' => [
                    'label' => 'Angariadores',
                    // Criar = candidatura pública; aprovar/rejeitar = editar.
                    'actions' => ['view' => [], 'update' => [], 'delete' => []],
                    'routes' => ['admin.v2.angariadores.*'],
                ],
                'commissions' => [
                    'label' => 'Comissões',
                    'actions' => ['view' => ['all', 'own'], 'update' => []],
                    'owner' => 'owner_id',
                    'routes' => ['admin.v2.angariadores.comissoes', 'admin.v2.angariadores.toggle-paid', 'admin.v2.angariadores.upload-receipt'],
                    'own_routes' => ['admin.angariador.comissoes'],
                ],
                'sellers' => ['label' => 'Vendedores', 'routes' => ['admin.v2.sellers.*']],
                'suppliers' => ['label' => 'Fornecedores', 'routes' => ['admin.v2.suppliers.*']],
                'partners' => ['label' => 'Parceiros', 'routes' => ['admin.v2.partners.*']],
            ],
        ],

        'analise' => [
            'label' => 'Análise & Ferramentas',
            'resources' => [
                // Ferramentas: usar = ver (gerar um relatório, correr uma análise).
                'reports' => ['label' => 'Relatórios', 'actions' => ['view' => []], 'map_all_to' => 'view', 'routes' => ['admin.v2.reports.*', 'admin.v2.financial.dashboard']],
                'profit-calculator' => ['label' => 'Calculadora de lucro', 'actions' => ['view' => []], 'map_all_to' => 'view', 'routes' => ['calculator.profit']],
                'radar' => ['label' => 'Radar', 'routes' => ['admin.v2.radar.*', 'admin.v2.radar-equipment.*']],
                'comparator' => ['label' => 'Comparador de veículos', 'actions' => ['view' => []], 'map_all_to' => 'view', 'routes' => ['admin.v2.comparator.*']],
                'car-analysis' => ['label' => 'Análise de carros', 'actions' => ['view' => []], 'map_all_to' => 'view', 'routes' => ['car-analysis.*']],
            ],
        ],

        'conteudo' => [
            'label' => 'Conteúdo do site',
            'resources' => [
                'news' => ['label' => 'Notícias', 'routes' => ['admin.news.*']],
                'testimonials' => ['label' => 'Testemunhos', 'routes' => ['admin.testimonials.*']],
                'newsletter' => ['label' => 'Newsletter', 'routes' => ['admin.v2.newsletter-management.*']],
                'menus' => ['label' => 'Menus', 'routes' => ['admin.v2.menus.*']],
                'social-posts' => ['label' => 'Criador de posts', 'routes' => ['admin.v2.social-posts.*']],
            ],
        ],

        'config' => [
            'label' => 'Configurações',
            'resources' => [
                'attribute-groups' => ['label' => 'Grupos de atributos', 'routes' => ['admin.v2.attribute-groups.*']],
                'vehicle-attributes' => ['label' => 'Atributos de veículos', 'routes' => ['admin.v2.vehicle-attributes.*']],
                'settings' => ['label' => 'Configurações', 'routes' => ['admin.v2.settings.*']],
            ],
        ],

        'sistema' => [
            'label' => 'Sistema',
            'resources' => [
                'users' => ['label' => 'Utilizadores', 'routes' => ['admin.v2.users.*']],
                'roles' => ['label' => 'Perfis', 'routes' => ['admin.v2.roles.*']],
                // O catálogo vem deste ficheiro: no backoffice só se consulta.
                'permissions' => ['label' => 'Permissões', 'actions' => ['view' => []], 'routes' => ['admin.v2.permissions.*']],
                'audit-log' => ['label' => 'Log de auditoria', 'actions' => ['view' => []], 'routes' => ['admin.v2.audit-log']],
                'manual' => ['label' => 'Manual de utilizador', 'actions' => ['view' => []], 'routes' => ['admin.v2.manual']],
            ],
        ],
    ],

    /*
     | Ações que não se deduzem do nome da rota. Valor: ação, opcionalmente
     | com o âmbito obrigatório (ex. "update.all").
     */
    'route_actions' => [
        'admin.v2.leads.assign-owner' => 'update.all',
        'admin.v2.pre-leads.approve' => 'create',
        'admin.v2.proposals.createFromForm' => 'create',
        'admin.v2.proposals.createFromOpportunity' => 'create',
        'admin.v2.proposals.importFromListing' => 'create',
        'isv.*' => 'view',
    ],

    /* Qualquer utilizador com perfil de backoffice. */
    'shared' => [
        'home',
        'about',
        'profile',
        'profile.update',
        'admin.v2.dashboard',
        'admin.v2.dashboard.*',
        'admin.v2.push-subscriptions.*',
        // A pesquisa filtra cada secção pelas permissões do utilizador.
        'admin.v2.search',
        'admin.v2.search.results',
        'admin.angariador.manual',
        'admin.angariador.faq',
        'admin.angariador.stop-impersonating',
    ],

    /* Só admin: backoffice antigo (V1), CMS de páginas e ferramentas sensíveis. */
    'admin_only' => [
        'admin.v2.email-preview.*',
        'admin.v2.financial.seed',
        'admin.v2.angariadores.impersonate',
        'ad-searches.*',
        'anuncios.*',
        'attribute-groups.*',
        'brands.*',
        'clients.*',
        'converted-proposals.*',
        'expenses.*',
        'form_proposals.*',
        'menu-items.*',
        'menus.*',
        'page-contents.*',
        'page-types.*',
        'pages.*',
        'partners.*',
        'permissions.*',
        'proposals.*',
        'roles.*',
        'sales.*',
        'settings.*',
        'suppliers.*',
        'users.*',
        'vehicle-attributes.*',
    ],

    /*
     | Perfis iniciais (migration e seeder). "*" = todas as ações com o âmbito
     | mais abrangente. O admin não precisa de lista (Gate::before).
     */
    'profiles' => [
        'Importador' => [
            'leads' => '*',
            'clients' => '*',
            'form-proposals' => '*',
            'proposals' => '*',
            'converted-proposals' => '*',
            'cost-simulators' => '*',
            'legalizations' => '*',
            'vehicles' => '*',
            'tasks' => '*',
            'movements' => '*',
            'transports' => '*',
            'reports' => '*',
            'profit-calculator' => '*',
            'radar' => '*',
            // O seletor de vendedor das Oportunidades e da compra de viaturas usa esta área.
            'sellers' => '*',
        ],
        'angariador' => [
            'leads' => ['view' => 'own', 'create' => true, 'update' => 'own'],
            'proposals' => ['view' => 'own', 'update' => 'own'],
            'form-proposals' => ['view' => 'own'],
            'commissions' => ['view' => 'own'],
        ],
        'cms' => [
            'news' => '*',
            'testimonials' => '*',
            'newsletter' => '*',
            'menus' => '*',
            'social-posts' => '*',
        ],
    ],
];
