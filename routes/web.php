<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\AdSearchController;
use App\Http\Controllers\VehicleAttributeController;
use App\Http\Controllers\AnuncioController;
use App\Http\Controllers\FrontendAnuncioController;
use App\Http\Controllers\PageTypeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PageContentController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ConvertedProposalController;
use App\Http\Controllers\Frontend\ImportController;
use App\Http\Controllers\Frontend\ImportSimulatorController;
use App\Models\ConvertedProposal;
use Sabberworm\CSS\Property\Import;
use UniSharp\LaravelFilemanager\Lfm;
use App\Http\Controllers\AttributeGroupController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });



// Route::get('simulador-importacao', [ImportSimulatorController::class, 'importSimulator'])->name('import-simulator');

// Route::get('proposta/{brand}/{model}/{version}/{id}', [ProposalController::class, 'detail'])->name('proposals.detail');
Route::get('proposta/{proposal_code}', [ProposalController::class, 'detail'])->name('proposals.detail');
Route::post('/proposals/{proposal}/accept', [ProposalController::class, 'accept'])
    ->name('proposals.accept');
Route::get('/proposta/{brand}/{model}/{version}/{id}/timeline', [ConvertedProposalController::class, 'detailTimeline'])
    ->name('converted-proposals.timeline');

Auth::routes();

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    Lfm::routes();
});

Route::prefix('gestao')->middleware(['auth'])->group(function () {


    Route::get('/simulador-isv', [ImportSimulatorController::class, 'index'])->name('isv.simulator');
    Route::post('/simulador-isv', [ImportSimulatorController::class, 'calcular'])->name('isv.calcular');

    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('settings', SettingController::class);

    Route::resource('permissions', PermissionController::class);
    Route::get('/', 'HomeController@index')->name('home');

    Route::get('/profile', 'ProfileController@index')->name('profile');
    Route::put('/profile', 'ProfileController@update')->name('profile.update');

    Route::get('/about', function () {
        return view('about');
    })->name('about');

    Route::resource('clients', ClientController::class);
    Route::get('clients/{client}/contrato_servico', [ClientController::class, 'contractService'])->name('clients.contractService');
    Route::resource('suppliers', SupplierController::class);
    Route::resource('partners', PartnerController::class);

    Route::resource('vehicles', VehicleController::class);
    Route::resource('vehicle-attributes', VehicleAttributeController::class);
    Route::patch('/vehicle-attributes/{id}/update-group', [VehicleAttributeController::class, 'updateGroup'])->name('vehicle-attributes.update-group');
    Route::post('/vehicle-attributes/sort', [VehicleAttributeController::class, 'sort'])->name('vehicle-attributes.sort');
    Route::get('vehicles/{vehicle}/profit', 'VehicleController@profit')->name('vehicles.profit');



    Route::resource('expenses', ExpenseController::class);

    Route::resource('sales', SaleController::class);

    Route::resource('attribute-groups', AttributeGroupController::class);

    // ============================================================
    // PROPOSALS V1 (Sistema Antigo - Manter para compatibilidade)
    // ============================================================
    Route::resource('proposals', ProposalController::class);
    Route::post('/proposals/create_by_form', [ProposalController::class, 'create_by_form'])->name('proposals.create_by_form');
    Route::get('proposals/{id}/download-pdf', [ProposalController::class, 'generatePdf'])->name('proposals.downloadPdf');
    Route::get('proposals/{id}/sent-whatsapp', [ProposalController::class, 'sentWhatsapp'])->name('proposals.sent-whatsapp');
    Route::post('/proposals/{proposal}/duplicate', [ProposalController::class, 'duplicate'])->name('proposals.duplicate');
    Route::patch('proposals/{proposal}/update-status', [ProposalController::class, 'updateStatus'])->name('proposals.updateStatus');

    // ============================================================
    // PROPOSALS V2 (Sistema Novo - Moderno e Mobile-First)
    // ============================================================
    // ============================================================
    // ADMIN DASHBOARD V2
    // ============================================================
    Route::get('v2/dashboard', [App\Http\Controllers\Admin\DashboardV2Controller::class, 'index'])->name('admin.v2.dashboard');

    Route::prefix('v2/proposals')->name('admin.v2.proposals.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // CLIENTES V2
    // ============================================================
    Route::prefix('v2/clients')->name('admin.v2.clients.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ClientV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\ClientV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\ClientV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\ClientV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\ClientV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\ClientV2Controller::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // FORMULÁRIOS DE PROPOSTA V2
    // ============================================================
    Route::prefix('v2/form-proposals')->name('admin.v2.form-proposals.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\FormProposalV2Controller::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\FormProposalV2Controller::class, 'show'])->name('show');
        Route::patch('/{id}/status', [App\Http\Controllers\Admin\FormProposalV2Controller::class, 'updateStatus'])->name('update-status');
    });

    Route::prefix('v2/cost-simulators')->name('admin.v2.cost-simulators.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CostSimulatorController::class, 'index'])->name('index');
        // Route::get('/create', [App\Http\Controllers\Admin\CostSimulatorController::class, 'create'])->name('create');
        // Route::post('/', [App\Http\Controllers\Admin\CostSimulatorController::class, 'store'])->name('store');
        // Route::get('/{id}/edit', [App\Http\Controllers\Admin\CostSimulatorController::class, 'edit'])->name('edit');
        // Route::put('/{id}', [App\Http\Controllers\Admin\CostSimulatorController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\CostSimulatorController::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // VEÍCULOS V2
    // ============================================================
    Route::prefix('v2/vehicles')->name('admin.v2.vehicles.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\VehicleV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\VehicleV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\VehicleV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\VehicleV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\VehicleV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\VehicleV2Controller::class, 'destroy'])->name('destroy');
        Route::get('list', [App\Http\Controllers\Admin\VehicleV2Controller::class, 'list'])->name('list');
    });

    // ============================================================
    // VENDAS V2
    // ============================================================
    Route::prefix('v2/sales')->name('admin.v2.sales.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SaleV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\SaleV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\SaleV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\SaleV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\SaleV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\SaleV2Controller::class, 'destroy'])->name('destroy');
        
    });

    // ============================================================
    // DESPESAS V2
    // ============================================================
    Route::prefix('v2/expenses')->name('admin.v2.expenses.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ExpenseV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\ExpenseV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\ExpenseV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\ExpenseV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\ExpenseV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\ExpenseV2Controller::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // FORNECEDORES V2
    // ============================================================
    Route::prefix('v2/suppliers')->name('admin.v2.suppliers.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SupplierV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\SupplierV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\SupplierV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\SupplierV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\SupplierV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\SupplierV2Controller::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // PARCEIROS V2
    // ============================================================
    Route::prefix('v2/partners')->name('admin.v2.partners.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PartnerV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\PartnerV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\PartnerV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\PartnerV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\PartnerV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\PartnerV2Controller::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // PROPOSTAS CONVERTIDAS V2
    // ============================================================
    Route::prefix('v2/converted-proposals')->name('admin.v2.converted-proposals.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // GRUPOS DE ATRIBUTOS V2
    // ============================================================
    Route::prefix('v2/attribute-groups')->name('admin.v2.attribute-groups.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AttributeGroupV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\AttributeGroupV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\AttributeGroupV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\AttributeGroupV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\AttributeGroupV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\AttributeGroupV2Controller::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // ATRIBUTOS DE VEÍCULOS V2
    // ============================================================
    Route::prefix('v2/vehicle-attributes')->name('admin.v2.vehicle-attributes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\VehicleAttributeV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\VehicleAttributeV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\VehicleAttributeV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\VehicleAttributeV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\VehicleAttributeV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\VehicleAttributeV2Controller::class, 'destroy'])->name('destroy');
        Route::post('/sort', [App\Http\Controllers\Admin\VehicleAttributeV2Controller::class, 'sort'])->name('sort');
    });

    // ============================================================
    // CONFIGURAÇÕES V2
    // ============================================================
    Route::prefix('v2/settings')->name('admin.v2.settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SettingV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\SettingV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\SettingV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\SettingV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\SettingV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\SettingV2Controller::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // UTILIZADORES V2
    // ============================================================
    Route::prefix('v2/users')->name('admin.v2.users.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\UserV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\UserV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\UserV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\UserV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\UserV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\UserV2Controller::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // PERFIS V2
    // ============================================================
    Route::prefix('v2/roles')->name('admin.v2.roles.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RoleV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\RoleV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\RoleV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\RoleV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\RoleV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\RoleV2Controller::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // PERMISSÕES V2
    // ============================================================
    Route::prefix('v2/permissions')->name('admin.v2.permissions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PermissionV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\PermissionV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\PermissionV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\PermissionV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\PermissionV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\PermissionV2Controller::class, 'destroy'])->name('destroy');
    });

    Route::resource('form_proposals', \App\Http\Controllers\FormProposalController::class)->names('form_proposals');

    Route::patch('/converted-proposals/{id}/update-status', [ConvertedProposalController::class, 'updateStatus'])->name('converted-proposals.updateStatus');

    // Route::get('/converted-proposals', [ConvertedProposalController::class, 'index'])
    //     ->name('converted-proposals.index');

    // Route::put('/converted-proposals/{convertedProposal}', [ConvertedProposalController::class, 'update'])
    //     ->name('converted-proposals.update');

    // Route::get('/converted-proposals/{convertedProposal}/edit', [ConvertedProposalController::class, 'edit'])
    //     ->name('converted-proposals.edit');

    Route::resource('converted-proposals', ConvertedProposalController::class);






    // Rotas para Brand
    Route::resource('brands', BrandController::class);


    Route::get('/anuncios', [AnuncioController::class, 'index'])->name('anuncios.index');
    Route::post('/anuncios/importar', [AnuncioController::class, 'importar'])->name('anuncios.importar');



    Route::get('/ad-searches', [AdSearchController::class, 'index'])->name('ad-searches.index');
    Route::get('/ad-searches/new-form', [AdSearchController::class, 'form'])->name('ad-searches.form');
    Route::post('/ad-searches/new', [AdSearchController::class, 'submit'])->name('ad-searches.submit');

    Route::get('/ad-searches/{adSearch}', [AdSearchController::class, 'show'])->name('ad-searches.show');
    Route::get('/ad-searches/{listing}/detail', [AdSearchController::class, 'detail'])->name('ad-searches.detail');

    Route::delete('/ad-searches/{adSearch}', [AdSearchController::class, 'destroy'])->name('ad-searches.destroy');
    Route::get('importar-anuncios', [AdSearchController::class, 'importarAnuncios'])->name('ad-searches.importarAnuncios');


    //frontend routes
    // Route::get('/anuncios-frontend', [FrontendAnuncioController::class, 'index'])->name('anuncios-frontend.index');



    // Tipos de Página
    Route::resource('page-types', PageTypeController::class)->names('page-types');
    Route::post('/page-types/{pageType}/duplicate', [PageTypeController::class, 'duplicate'])->name('page-types.duplicate');

    // Páginas
    Route::resource('pages', PageController::class)->names('pages');

    // Conteúdo das Páginas
    Route::resource('page-contents', PageContentController::class)->only(['store', 'update', 'destroy'])->names('page-contents');

    // Menus
    Route::resource('menus', MenuController::class)->names('menus');

    // Itens de Menu
    Route::resource('menu-items', MenuItemController::class)->only(['store', 'update', 'destroy'])->names('menu-items');

    Route::get('homepage', [PageController::class, 'getHomepage'])->name('pages.homepage');
    Route::get('news', [PageController::class, 'getNews'])->name('pages.news');
    Route::get('categories', [PageController::class, 'getCategories'])->name('pages.categories');
    Route::get('legalizations', [PageController::class, 'getLegalizations'])->name('pages.legalizations');
    Route::get('imports', [PageController::class, 'getImports'])->name('pages.imports');
    Route::get('selling', [PageController::class, 'getSelling'])->name('pages.selling');
});
