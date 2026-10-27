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
                    'routes' => ['admin.v2.leads.*', 'admin.v2.pre-leads.*', 'admin.v2.api.new-leads', 'admin.v2.export.leads', 'admin.mobile.dashboard', 'admin.mobile.search',
                        // Mobile sem filtro por dono: criar, eliminar e atribuir dono (update.all) exigem "todas".
                        'admin.mobile.leads.create', 'admin.mobile.leads.store', 'admin.mobile.leads.destroy', 'admin.mobile.leads.owner'],
                    // Rotas mobile que verificam o dono registo a registo (applyScope/allows). Nomes explícitos:
                    // uma rota nova não herda o acesso "próprias" sem ser listada aqui.
                    'own_routes' => ['admin.angariador.dashboard', 'admin.angariador.leads', 'admin.angariador.leads.*',
                        'admin.mobile.leads.index', 'admin.mobile.leads.show', 'admin.mobile.leads.status',
                        'admin.mobile.leads.convert', 'admin.mobile.leads.followup', 'admin.mobile.leads.activity'],
                ],
                'clients' => [
                    'label' => 'Clientes',
                    'actions' => ['view' => ['all', 'own'], 'create' => [], 'update' => ['all', 'own'], 'delete' => []],
                    'owner' => 'owner_id',
                    // Mobile: criar (create/store) exige "criar"; sem filtro por dono.
                    'routes' => ['admin.v2.clients.*', 'admin.v2.export.clients', 'admin.mobile.clients.create', 'admin.mobile.clients.store'],
                    // Rotas mobile que verificam o dono registo a registo (applyScope/allows). Nomes explícitos:
                    // uma rota nova não herda o acesso "próprios" sem ser listada aqui.
                    'own_routes' => ['admin.mobile.clients.index', 'admin.mobile.clients.show', 'admin.mobile.clients.edit',
                        'admin.mobile.clients.update', 'admin.mobile.clients.followup', 'admin.mobile.clients.activity'],
                ],
                'form-proposals' => [
                    'label' => 'Formulários',
                    // Pedidos de importação: chegam pelo site ou são criados à mão
                    // dentro da lead/cliente. As Oportunidades vivem dentro de
                    // cada pedido (editar).
                    'actions' => ['view' => ['all', 'own'], 'create' => [], 'update' => [], 'delete' => []],
                    'owner' => 'client.owner_id',
                    // Mobile: criar, mudar o estado e tudo o que mexe nas oportunidades
                    // (editar, sem âmbito) segue o desktop. As oportunidades (vendedor,
                    // preço de compra) exigem ver "todos", como no desktop.
                    'routes' => ['admin.v2.form-proposals.*', 'admin.v2.consignment-evaluations.*', 'admin.mobile.form-proposals.*'],
                    // Rotas mobile que verificam o dono do cliente registo a registo
                    // (applyScope/allows). Nomes explícitos: uma rota nova não herda "próprios".
                    'own_routes' => ['admin.angariador.formularios',
                        'admin.mobile.form-proposals.index', 'admin.mobile.form-proposals.show'],
                ],
                'proposals' => [
                    'label' => 'Cotações',
                    'actions' => ['view' => ['all', 'own'], 'create' => [], 'update' => ['all', 'own'], 'delete' => []],
                    'owner' => 'client.owner_id',
                    // Mobile: criar (create/store) exige "criar"; editar, mudar o estado e
                    // aceitar exigem editar "todas", como as rotas do desktop.
                    'routes' => ['admin.v2.proposals.*', 'admin.v2.car-candidates.*', 'admin.v2.export.proposals', 'isv.*', 'admin.mobile.proposals.*'],
                    // Rotas mobile que verificam o dono do cliente registo a registo
                    // (applyScope/allows). Nomes explícitos: uma rota nova não herda "próprias".
                    'own_routes' => ['admin.angariador.propostas', 'admin.angariador.cotacoes',
                        'admin.mobile.proposals.index', 'admin.mobile.proposals.show'],
                ],
                'converted-proposals' => [
                    'label' => 'Cotações convertidas',
                    'actions' => ['view' => [], 'create' => [], 'update' => [], 'delete' => []],
                    // Mobile: mudar o estado exige editar, como a rota update-status do desktop.
                    // Ver (index/show) está em "shared": o controller filtra registo a registo.
                    'routes' => ['admin.v2.converted-proposals.*', 'admin.mobile.converted-proposals.status'],
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
                    'own_routes' => ['admin.angariador.comissoes', 'admin.angariador.comissoes.comprovativo'],
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
                'brands' => ['label' => 'Marcas e logos', 'routes' => ['admin.v2.brands.*']],
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
        'admin.mobile.leads.owner' => 'update.all',
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
        // Ficheiros privados (retomas, consignação, comprovativos, anexos): qualquer perfil
        // de backoffice autenticado; o angariador puro é barrado pelo restrictAngariador.
        'admin.v2.files.show',
        // Cada utilizador só vê o seu próprio contrato assinado.
        'admin.angariador.contrato',
        // Cotações convertidas do angariador: só as dos seus clientes (filtradas no controller).
        'admin.angariador.convertidas',
        'admin.angariador.convertidas.show',
        // O mesmo no mobile: quem vê cotações convertidas vê todas; quem só vê cotações,
        // as dos seus clientes (ConvertedProposalService::viewScope, no controller).
        'admin.mobile.converted-proposals.index',
        'admin.mobile.converted-proposals.show',
        'admin.angariador.stop-impersonating',
    ],

    /* Só admin: backoffice antigo (V1), CMS de páginas e ferramentas sensíveis. */
    'admin_only' => [
        'admin.v2.email-preview.*',
        'admin.v2.financial.seed',
        'admin.v2.angariadores.impersonate',
        'ad-searches.*',
        'anuncios.*',
        'brands.*',
        'clients.*',
        'menu-items.*',
        'menus.*',
        'page-contents.*',
        'page-types.*',
        'pages.*',
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
