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
use App\Http\Controllers\PageFieldController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PageContentController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuItemController;
use Spatie\Browsershot\Browsershot;
use App\Http\Controllers\UserController;
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



Auth::routes();

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
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


    Route::resource('proposals', ProposalController::class);
    Route::get('proposals/{id}/download-pdf', [ProposalController::class, 'generatePdf'])->name('proposals.downloadPdf');
    Route::get('proposals/{id}/sent-whatsapp', [ProposalController::class, 'sentWhatsapp'])->name('proposals.sent-whatsapp');




    Route::post('/proposals/{proposal}/duplicate', [ProposalController::class, 'duplicate'])->name('proposals.duplicate');

    Route::patch('proposals/{proposal}/update-status', [ProposalController::class, 'updateStatus'])->name('proposals.updateStatus');




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
    Route::get('/anuncios-frontend', [FrontendAnuncioController::class, 'index'])->name('anuncios-frontend.index');



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

    Route::get('news', [PageController::class, 'getNews'])->name('pages.news');
    Route::get('categories', [PageController::class, 'getCategories'])->name('pages.categories');
    Route::get('legalizations', [PageController::class, 'getLegalizations'])->name('pages.legalizations');
    Route::get('imports', [PageController::class, 'getImports'])->name('pages.imports');
    Route::get('selling', [PageController::class, 'getSelling'])->name('pages.selling');
});

    Route::get('proposta/{brand}/{model}/{version}/{id}', [ProposalController::class, 'show'])->name('proposals.show');
