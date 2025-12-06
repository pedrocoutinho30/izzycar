<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\LegalizationController;
use App\Http\Controllers\Frontend\ImportController;
use App\Http\Controllers\Frontend\SellingController;
use App\Http\Controllers\Frontend\VehiclesController;
use App\Http\Controllers\Frontend\NewsController;
use App\Http\Controllers\Frontend\CostSimulatorController;
use App\Http\Controllers\Frontend\TradeInController;
use App\Http\Controllers\Api\ExtrasController;
use App\Http\Controllers\Api\BrandsModelsController;
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


// quero redirecionar todas estas rotas para uma rota

Route::post('/formulario-importacao', [ImportController::class, 'submitFormImport'])->name('frontend.import-submit');
Route::get('/formulario-importacao', [ContactController::class, 'importForm'])->name('frontend.form-import');
Route::get('/simulador-custos', [CostSimulatorController::class, 'index'])->name('frontend.cost-simulator');
Route::post('/simulador-custos', [CostSimulatorController::class, 'calculate'])->name('frontend.cost-simulator.calculate');

// API pública
Route::get('/extras', [ExtrasController::class, 'index'])->name('api.extras');
Route::get('/brands-models', [BrandsModelsController::class, 'index'])->name('api.brands-models');
Route::get('teste', function () {

    $convertedProposal = \App\Models\ConvertedProposal::first();
    $data = [
        'client_name' => "nome do cliente",
        'brand' => $convertedProposal->brand,
        'model' => $convertedProposal->modelCar,
        'version' => $convertedProposal->version,
        'car_image' => null,
    ];
    return view('emails.proposal_accepted', ['data' => $data]);
});
Route::middleware(['blockInProd'])->group(function () {
    Route::get('/sobre-nos', [PageController::class, 'aboutUs'])->name('frontend.about-us');
    Route::get('/servicos', [PageController::class, 'services'])->name('frontend.services');
    Route::get('/parceiros', [PageController::class, 'partners'])->name('frontend.partners');
    Route::get('/contactos', [PageController::class, 'contact'])->name('frontend.contact');
    Route::get('/politica-privacidade', [PageController::class, 'privacyPolicy'])->name('frontend.privacy-policy');
    Route::get('/termos-condicoes', [PageController::class, 'termsConditions'])->name('frontend.terms-conditions');
    Route::get('/cookies', [PageController::class, 'cookies'])->name('frontend.cookies');

    Route::get('/brevemente', [PageController::class, 'comingSoon'])->name('frontend.coming-soon');

    Route::get('/', [VehiclesController::class, 'index'])->name('frontend.home');
    Route::get('/viaturas', [VehiclesController::class, 'vehicles'])->name('vehicles.list');
    Route::get('/viaturas-filtradas', [VehiclesController::class, 'filteredVehicles']);
    Route::get('/viaturas/{brand}/{model}/{id}', [VehiclesController::class, 'vehicleDetails'])->name('vehicles.details');
    Route::post('/contact/vehicle', [ContactController::class, 'send'])->name('contact.vehicle');





    Route::get('/legalizacao', [LegalizationController::class, 'getLegalizationPage'])->name('frontend.legalization');
    Route::get('/importacao', [ImportController::class, 'getImportPage'])->name('frontend.import');
    Route::get('/retomas', [TradeInController::class, 'getTradeInPage'])->name('frontend.trade-in');
    Route::post('/retomas', [TradeInController::class, 'submitTradeInForm'])->name('frontend.trade-in-submit');

    Route::get('/venda', [SellingController::class, 'getSellingPage'])->name('frontend.selling');

    Route::get('/noticias', [NewsController::class, 'getNewsPage'])->name('frontend.news');

    Route::get('/noticias/{slug}', [NewsController::class, 'getNewsPageBySlug'])->name('frontend.news-details');

    Route::get('/politica-privacidade', function () {
        return view('frontend.privacy');
    })->name('frontend.privacy');
    Route::get('/termos-condicoes', function () {
        return view('frontend.terms');
    })->name('frontend.terms');

    Route::get('/load-service-cards/{id}', function ($id) {
        $serviceActive = \App\Models\Page::find($id)?->contents ?? collect();

        $subPages = $serviceActive->firstWhere('field_name', 'page_repeater')?->field_value ?? '[]';
        $subPages = json_decode($subPages, true);

        if (empty($subPages)) return response()->json([]);

        $pages = \App\Models\Page::whereIn('id', array_values($subPages))->get();

        $cards = $pages->map(function ($page) {
            $title = $page->contents->firstWhere('field_name', 'title')?->field_value ?? 'Sem título';
            $description = $page->contents->firstWhere('field_name', 'description')?->field_value ?? '';
            $image = $page->contents->firstWhere('field_name', 'image')?->field_value ?? 'img/logo-simples.png';
            return [
                'title' => $title,
                'description' => $description,
                'image' => $image
            ];
        });

        return response()->json($cards);
    });

    Route::get('/modelos-por-marca', [VehiclesController::class, 'modelsByBrand']);
    Route::get('/anos-por-marca', [VehiclesController::class, 'yearsByBrand']);
    Route::get('/anos-por-marca-modelo', [VehiclesController::class, 'yearsByBrandModel']);
    Route::get('/combustiveis-por-marca-modelo-ano', [VehiclesController::class, 'fuelsByBrandModelYear']);
    
    // Página para mockup redes sociais
    Route::get('/para-redes', [PageController::class, 'socialMediaMockup'])->name('frontend.social-mockup');
});
