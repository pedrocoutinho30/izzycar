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
use App\Http\Controllers\CarAnalysisController;
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



// Relatórios de violação da Content-Security-Policy (sem auth/CSRF; ver docs/seguranca-csp.md).
Route::post('csp-report', \App\Http\Controllers\CspReportController::class)
    ->middleware('throttle:30,1')
    ->name('csp.report');

// Route::get('simulador-importacao', [ImportSimulatorController::class, 'importSimulator'])->name('import-simulator');

// Route::get('proposta/{brand}/{model}/{version}/{id}', [ProposalController::class, 'detail'])->name('proposals.detail');
Route::get('proposta/{proposal_code}', [ProposalController::class, 'detail'])->name('proposals.detail');
// O cliente pede cotação de uma das "outras opções" da cotação (envia email à equipa).
Route::post('proposta/{proposal_code}/opcoes/{opportunity}/pedir-cotacao', [ProposalController::class, 'requestAlternativeQuote'])
    ->middleware('throttle:10,1')
    ->name('proposals.request-alternative');
// Aceitação pública: pelo código da cotação (não pelo id), com limite de pedidos.
Route::post('proposta/{proposal_code}/aceitar', [ProposalController::class, 'accept'])
    ->middleware('throttle:5,1')
    ->name('proposals.accept');
Route::get('/proposta/{brand}/{model}/{version}/{code}/timeline', [ConvertedProposalController::class, 'detailTimeline'])
    ->name('converted-proposals.timeline');

// Sem registo público: as contas de backoffice são criadas pela administração
// e os angariadores candidatam-se em /registo-angariador.
Auth::routes(['register' => false]);

Route::get('/definir-password/{token}', [App\Http\Controllers\Auth\SetPasswordController::class, 'show'])
    ->name('password.setup');
Route::post('/definir-password', [App\Http\Controllers\Auth\SetPasswordController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('password.setup.store');

// O manual é só para quem tem conta: sem sessão, redireciona para o login.
Route::get('/manual-angariador', [App\Http\Controllers\Frontend\AngariadorManualController::class, 'index'])
    ->middleware('auth')
    ->name('public.manual.angariador');

Route::get('/seja-angariador', [App\Http\Controllers\Frontend\AngariadorManualController::class, 'landing'])
    ->name('public.angariador.landing');

Route::get('/registo-angariador', [App\Http\Controllers\Frontend\AngariadorRegistrationController::class, 'create'])
    ->name('public.angariador.register');
Route::post('/registo-angariador', [App\Http\Controllers\Frontend\AngariadorRegistrationController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('public.angariador.register.store');

Route::get('/newsletter/unsubscribe', [App\Http\Controllers\Admin\NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe');

Route::post('/newsletter/subscribe', [App\Http\Controllers\Admin\NewsletterController::class, 'subscribe'])
    ->middleware(['throttle:5,1', 'antispam'])
    ->name('newsletter.subscribe');

Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    Lfm::routes();
});

Route::prefix('gestao')->middleware(['auth', 'restrictAngariador', 'authorizeResource'])->group(function () {


    Route::get('/simulador-isv', [ImportSimulatorController::class, 'index'])->name('isv.simulator');
    Route::post('/simulador-isv', [ImportSimulatorController::class, 'calcular'])->name('isv.calcular');

    // Análise de carros usados (JSON)
    Route::get('/analise-carros', [CarAnalysisController::class, 'index'])->name('car-analysis.index');
    Route::post('/analise-carros', [CarAnalysisController::class, 'upload'])->name('car-analysis.upload');
    Route::post('/analise-carros/recalcular', [CarAnalysisController::class, 'recalculate'])->name('car-analysis.recalculate');

    // Calculadora de Lucro
    Route::get('/calculadora-lucro', function () {
        return view('admin.v2.calculator.profit');
    })->name('calculator.profit');

    // ============================================================
    // LEGALIZAÇÕES
    // ============================================================
    Route::prefix('legalizacoes')->name('admin.legalizations.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\LegalizationController::class, 'index'])->name('index');
        Route::get('/kanban', [App\Http\Controllers\Admin\LegalizationController::class, 'kanban'])->name('kanban');
        // Exportação XLSX (antes de /{legalization}); a ação "export" conta como ver.
        Route::get('/export', [App\Http\Controllers\Admin\ExportXlsxController::class, 'download'])->name('export')->defaults('tipo', 'legalizations');
        Route::get('/criar', [App\Http\Controllers\Admin\LegalizationController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\LegalizationController::class, 'store'])->name('store');
        Route::get('/{legalization}', [App\Http\Controllers\Admin\LegalizationController::class, 'show'])->name('show');
        Route::get('/{legalization}/editar', [App\Http\Controllers\Admin\LegalizationController::class, 'edit'])->name('edit');
        Route::put('/{legalization}', [App\Http\Controllers\Admin\LegalizationController::class, 'update'])->name('update');
        Route::delete('/{legalization}', [App\Http\Controllers\Admin\LegalizationController::class, 'destroy'])->name('destroy');
        Route::post('/{legalization}/mover', [App\Http\Controllers\Admin\LegalizationController::class, 'move'])->name('move');
        Route::post('/{legalization}/passo', [App\Http\Controllers\Admin\LegalizationController::class, 'toggleStep'])->name('toggle-step');
        Route::post('/{legalization}/documentos', [App\Http\Controllers\Admin\LegalizationController::class, 'uploadDocument'])->name('upload-document');
        Route::get('/{legalization}/documentos/{document}/download', [App\Http\Controllers\Admin\LegalizationController::class, 'downloadDocument'])->name('download-document');
        Route::delete('/{legalization}/documentos/{document}', [App\Http\Controllers\Admin\LegalizationController::class, 'deleteDocument'])->name('delete-document');
        Route::post('/{legalization}/fatura', [App\Http\Controllers\Admin\LegalizationController::class, 'uploadInvoice'])->name('upload-invoice');
        Route::get('/{legalization}/fatura/download', [App\Http\Controllers\Admin\LegalizationController::class, 'downloadInvoice'])->name('download-invoice');
        Route::delete('/{legalization}/fatura', [App\Http\Controllers\Admin\LegalizationController::class, 'deleteInvoice'])->name('delete-invoice');
        Route::get('/{legalization}/modelo9', [App\Http\Controllers\Admin\LegalizationController::class, 'generateModelo9'])->name('modelo9');
        Route::post('/{legalization}/modelo9', [App\Http\Controllers\Admin\LegalizationController::class, 'saveAndGenerateModelo9'])->name('modelo9.save-and-generate');
        Route::get('/{legalization}/modelo1460', [App\Http\Controllers\Admin\LegalizationController::class, 'generateModelo1460'])->name('modelo1460');
        Route::post('/{legalization}/modelo1460', [App\Http\Controllers\Admin\LegalizationController::class, 'saveAndGenerateModelo1460'])->name('modelo1460.save-and-generate');
        Route::get('/{legalization}/modelo1ra', [App\Http\Controllers\Admin\LegalizationController::class, 'generateModelo1Ra'])->name('modelo1ra');
    });

    // ============================================================
    // TESTEMUNHOS
    // ============================================================
    Route::prefix('testemunhos')->name('admin.testimonials.')->group(function () {
        Route::get('/',                 [App\Http\Controllers\Admin\TestimonialController::class, 'index'])->name('index');
        Route::get('/criar',            [App\Http\Controllers\Admin\TestimonialController::class, 'create'])->name('create');
        Route::post('/',                [App\Http\Controllers\Admin\TestimonialController::class, 'store'])->name('store');
        Route::get('/{testimonial}/editar', [App\Http\Controllers\Admin\TestimonialController::class, 'edit'])->name('edit');
        Route::put('/{testimonial}',    [App\Http\Controllers\Admin\TestimonialController::class, 'update'])->name('update');
        Route::delete('/{testimonial}', [App\Http\Controllers\Admin\TestimonialController::class, 'destroy'])->name('destroy');
        Route::post('/{testimonial}/toggle', [App\Http\Controllers\Admin\TestimonialController::class, 'togglePublished'])->name('toggle');
    });

    // Backoffice antigo (V1) de utilizadores, perfis, permissões, configurações,
    // clientes, fornecedores, parceiros, atributos, despesas, vendas, pedidos,
    // cotações e cotações convertidas: desligado — o V2/V3 substitui-o.
    Route::get('/', 'HomeController@index')->name('home');

    Route::get('/profile', 'ProfileController@index')->name('profile');
    Route::put('/profile', 'ProfileController@update')->name('profile.update');

    Route::get('/about', function () {
        return view('about');
    })->name('about');

    // Contrato de prestação de serviços de um cliente (PDF) — a única rota
    // do antigo CRUD de clientes sem equivalente no V2.
    Route::get('clients/{client}/contrato_servico', [ClientController::class, 'contractService'])->name('clients.contractService');

    // ============================================================
    // PROPOSALS V2 (Sistema Novo - Moderno e Mobile-First)
    // ============================================================
    // ============================================================
    // COMPARADOR DE VEÍCULOS V2
    // ============================================================
    Route::prefix('v2/comparator')->name('admin.v2.comparator.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CarComparatorController::class, 'index'])->name('index');
        Route::post('/analyze', [App\Http\Controllers\Admin\CarComparatorController::class, 'analyze'])->name('analyze');
    });

    // ============================================================
    // ADMIN DASHBOARD V2
    // ============================================================
    Route::get('v2/dashboard', [App\Http\Controllers\Admin\DashboardV2Controller::class, 'index'])->name('admin.v2.dashboard');
    Route::get('v2/dashboard/chart-data', [App\Http\Controllers\Admin\DashboardV2Controller::class, 'getChartData'])->name('admin.v2.dashboard.chart-data');
    Route::get('v2/api/new-leads', [App\Http\Controllers\Admin\DashboardV2Controller::class, 'newLeadsApi'])->name('admin.v2.api.new-leads');
    Route::get('v2/search', [App\Http\Controllers\Admin\GlobalSearchController::class, 'index'])->name('admin.v2.search');
    Route::get('v2/search/resultados', [App\Http\Controllers\Admin\GlobalSearchController::class, 'results'])->name('admin.v2.search.results');

    // ============================================================
    // PUSH NOTIFICATIONS (PWA)
    // ============================================================
    Route::post('v2/push-subscriptions', [App\Http\Controllers\Admin\PushSubscriptionController::class, 'store'])->name('admin.v2.push-subscriptions.store');
    Route::delete('v2/push-subscriptions', [App\Http\Controllers\Admin\PushSubscriptionController::class, 'destroy'])->name('admin.v2.push-subscriptions.destroy');

    // ============================================================
    // DASHBOARD FINANCEIRO
    // ============================================================
    Route::get('v2/financial', [App\Http\Controllers\Admin\FinancialDashboardController::class, 'index'])->name('admin.v2.financial.dashboard');
    // Apaga e regenera movimentos financeiros — só admin, e nunca por GET.
    Route::post('v2/financial/seed', [App\Http\Controllers\Admin\FinancialDashboardController::class, 'seedExisting'])->middleware('role:admin')->name('admin.v2.financial.seed');

    // ============================================================
    // RADAR DE PREÇOS AUTOSCOUT24
    // ============================================================
    Route::prefix('v2/radar')->name('admin.v2.radar.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RadarController::class, 'index'])->name('index');
        Route::get('/nova', [App\Http\Controllers\Admin\RadarController::class, 'create'])->name('create');
        Route::post('/nova', [App\Http\Controllers\Admin\RadarController::class, 'store'])->name('store');
        Route::get('/modelos', [App\Http\Controllers\Admin\RadarController::class, 'models'])->name('models');
        Route::get('/submodelos', [App\Http\Controllers\Admin\RadarController::class, 'submodels'])->name('submodels');
        Route::get('/pt-modelos', [App\Http\Controllers\Admin\RadarController::class, 'ptModels'])->name('pt-models');
        Route::get('/carmine-modelos', [App\Http\Controllers\Admin\RadarController::class, 'carmineModels'])->name('carmine-models');
        Route::post('/{radarSearch}/correr', [App\Http\Controllers\Admin\RadarController::class, 'run'])->name('run');
        Route::get('/{radarSearch}/editar', [App\Http\Controllers\Admin\RadarController::class, 'edit'])->name('edit');
        Route::put('/{radarSearch}', [App\Http\Controllers\Admin\RadarController::class, 'update'])->name('update');
        Route::patch('/{radarSearch}/custo-importacao', [App\Http\Controllers\Admin\RadarController::class, 'updateImportCost'])->name('update-import-cost');
        Route::patch('/{radarSearch}/ativo', [App\Http\Controllers\Admin\RadarController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('/{radarSearch}', [App\Http\Controllers\Admin\RadarController::class, 'destroy'])->name('destroy');
        Route::get('/{radarSearch}', [App\Http\Controllers\Admin\RadarController::class, 'show'])->name('show');
    });

    Route::patch('/v2/radar-listings/{radarListing}/media', [App\Http\Controllers\Admin\RadarController::class, 'toggleAverage'])->name('admin.v2.radar.toggle-average');

    Route::prefix('v2/radar-equipamento')->name('admin.v2.radar-equipment.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RadarEquipmentController::class, 'index'])->name('index');
        Route::patch('/{radarEquipment}/filtro', [App\Http\Controllers\Admin\RadarEquipmentController::class, 'toggleFilter'])->name('toggle-filter');
        Route::put('/{radarEquipment}', [App\Http\Controllers\Admin\RadarEquipmentController::class, 'update'])->name('update');
        Route::post('/{radarEquipment}/fundir', [App\Http\Controllers\Admin\RadarEquipmentController::class, 'merge'])->name('merge');
        Route::post('/aliases/{radarEquipmentAlias}/desacoplar', [App\Http\Controllers\Admin\RadarEquipmentController::class, 'detachAlias'])->name('detach-alias');
        Route::delete('/{radarEquipment}', [App\Http\Controllers\Admin\RadarEquipmentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('v2/social-posts')->name('admin.v2.social-posts.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SocialPostController::class, 'index'])->name('index');
        Route::get('/recomendacao', [App\Http\Controllers\Admin\SocialPostController::class, 'recommendation'])->name('recommendation');
        Route::post('/recomendacao', [App\Http\Controllers\Admin\SocialPostController::class, 'store'])->name('store');
        Route::get('/recomendacao/{post}/editar', [App\Http\Controllers\Admin\SocialPostController::class, 'edit'])->name('edit');
        Route::delete('/{post}', [App\Http\Controllers\Admin\SocialPostController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('v2/proposals')->name('admin.v2.proposals.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'index'])->name('index');
        Route::get('/kanban', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'kanban'])->name('kanban');
        Route::get('/create', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'create'])->name('create');
        Route::get('/create-from-form/{formProposalId}', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'createFromForm'])->name('createFromForm');
        Route::get('/create-from-opportunity/{opportunity}', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'createFromOpportunity'])->name('createFromOpportunity');
        Route::post('/', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'store'])->name('store');
        Route::post('/import-from-listing', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'importFromListing'])->name('importFromListing');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/status', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'updateStatus'])->name('updateStatus');
        Route::post('/{id}/accept', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'accept'])->name('accept');
        Route::post('/bulk-reject', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'bulkReject'])->name('bulkReject');
        Route::post('/bulk-status', [App\Http\Controllers\Admin\ProposalV2Controller::class, 'bulkUpdateStatus'])->name('bulkUpdateStatus');
    });

    // ============================================================
    // CLIENTES V2
    // ============================================================
    // ============================================================
    // LEADS V2
    // ============================================================
    // Log de auditoria
    Route::get('v2/audit-log', [App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('admin.v2.audit-log');
    Route::get('v2/manual', [App\Http\Controllers\Admin\ManualController::class, 'index'])->name('admin.v2.manual');

    // Exportações CSV
    Route::prefix('v2/export')->name('admin.v2.export.')->group(function () {
        Route::get('/leads',     [App\Http\Controllers\Admin\ExportController::class, 'leads'])->name('leads');
        Route::get('/clients',   [App\Http\Controllers\Admin\ExportController::class, 'clients'])->name('clients');
        Route::get('/proposals', [App\Http\Controllers\Admin\ExportController::class, 'proposals'])->name('proposals');
    });

    Route::prefix('v2/leads')->name('admin.v2.leads.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\LeadV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\LeadV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\LeadV2Controller::class, 'store'])->name('store');
        Route::get('/kanban', [App\Http\Controllers\Admin\LeadV2Controller::class, 'kanban'])->name('kanban');
        // Exportação XLSX (antes de /{id}); a ação "export" conta como ver.
        Route::get('/export', [App\Http\Controllers\Admin\ExportXlsxController::class, 'download'])->name('export')->defaults('tipo', 'leads');
        Route::get('/{id}', [App\Http\Controllers\Admin\LeadV2Controller::class, 'show'])->name('show');
        Route::post('/{id}/convert', [App\Http\Controllers\Admin\LeadV2Controller::class, 'convert'])->name('convert');
        Route::post('/{id}/status', [App\Http\Controllers\Admin\LeadV2Controller::class, 'updateStatus'])->name('status');
        Route::post('/{id}/activity', [App\Http\Controllers\Admin\LeadV2Controller::class, 'storeActivity'])->name('activity');
        Route::post('/{id}/followup', [App\Http\Controllers\Admin\LeadV2Controller::class, 'saveFollowup'])->name('followup');
        Route::post('/{id}/owner', [App\Http\Controllers\Admin\LeadV2Controller::class, 'assignOwner'])->name('assign-owner');
        Route::delete('/{id}', [App\Http\Controllers\Admin\LeadV2Controller::class, 'destroy'])->name('destroy');
    });

    Route::prefix('v2/pre-leads')->name('admin.v2.pre-leads.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PreLeadController::class, 'index'])->name('index');
        Route::post('/{preLead}/approve', [App\Http\Controllers\Admin\PreLeadController::class, 'approve'])->name('approve');
        Route::delete('/{preLead}', [App\Http\Controllers\Admin\PreLeadController::class, 'reject'])->name('reject');
    });

    Route::prefix('v2/clients')->name('admin.v2.clients.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ClientV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\ClientV2Controller::class, 'create'])->name('create');
        // Exportação XLSX (antes de /{id}); a ação "export" conta como ver.
        Route::get('/export', [App\Http\Controllers\Admin\ExportXlsxController::class, 'download'])->name('export')->defaults('tipo', 'clients');
        Route::post('/', [App\Http\Controllers\Admin\ClientV2Controller::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Admin\ClientV2Controller::class, 'show'])->name('show');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\ClientV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\ClientV2Controller::class, 'update'])->name('update');
        Route::post('/{id}/followup', [App\Http\Controllers\Admin\ClientV2Controller::class, 'saveFollowup'])->name('followup');
        Route::post('/{id}/activity', [App\Http\Controllers\Admin\ClientV2Controller::class, 'storeActivity'])->name('activity');
        Route::delete('/{id}', [App\Http\Controllers\Admin\ClientV2Controller::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // CARROS EM ANÁLISE (usado nas páginas de Cliente e de Lead — o
    // mesmo modelo Client, só páginas de detalhe diferentes)
    // ============================================================
    Route::prefix('v2/clients/{client}/car-candidates')->name('admin.v2.car-candidates.')->group(function () {
        Route::post('/', [App\Http\Controllers\Admin\CarCandidateController::class, 'store'])->name('store');
        Route::put('/{carCandidate}', [App\Http\Controllers\Admin\CarCandidateController::class, 'update'])->name('update');
        Route::delete('/{carCandidate}', [App\Http\Controllers\Admin\CarCandidateController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [App\Http\Controllers\Admin\CarCandidateController::class, 'reorder'])->name('reorder');
    });

    // ============================================================
    // FORMULÁRIOS DE PROPOSTA V2
    // ============================================================
    Route::prefix('v2/consignment-evaluations')->name('admin.v2.consignment-evaluations.')->group(function () {
        Route::get('/',                [App\Http\Controllers\Admin\ConsignmentEvaluationController::class, 'index'])->name('index');
        Route::get('/{id}',            [App\Http\Controllers\Admin\ConsignmentEvaluationController::class, 'show'])->name('show');
        Route::patch('/{id}/status',   [App\Http\Controllers\Admin\ConsignmentEvaluationController::class, 'updateStatus'])->name('update-status');
        Route::patch('/{id}/notes',    [App\Http\Controllers\Admin\ConsignmentEvaluationController::class, 'updateNotes'])->name('update-notes');
        Route::delete('/{id}',         [App\Http\Controllers\Admin\ConsignmentEvaluationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('v2/form-proposals')->name('admin.v2.form-proposals.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\FormProposalV2Controller::class, 'index'])->name('index');
        Route::post('/bulk-status', [App\Http\Controllers\Admin\FormProposalV2Controller::class, 'bulkUpdateStatus'])->name('bulk-status');
        Route::post('/', [App\Http\Controllers\Admin\FormProposalV2Controller::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Admin\FormProposalV2Controller::class, 'show'])->name('show');
        Route::put('/{id}', [App\Http\Controllers\Admin\FormProposalV2Controller::class, 'update'])->name('update');
        Route::patch('/{id}/status', [App\Http\Controllers\Admin\FormProposalV2Controller::class, 'updateStatus'])->name('update-status');
        Route::delete('/{id}', [App\Http\Controllers\Admin\FormProposalV2Controller::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // OPORTUNIDADES DE UM PEDIDO DE IMPORTAÇÃO
    // ============================================================
    Route::prefix('v2/form-proposals/{formProposal}/oportunidades')->name('admin.v2.form-proposals.opportunities.')->scopeBindings()->group(function () {
        Route::post('/', [App\Http\Controllers\Admin\ImportOpportunityController::class, 'store'])->name('store');
        Route::get('/{opportunity}', [App\Http\Controllers\Admin\ImportOpportunityController::class, 'show'])->name('show');
        Route::put('/{opportunity}', [App\Http\Controllers\Admin\ImportOpportunityController::class, 'update'])->name('update');
        Route::delete('/{opportunity}', [App\Http\Controllers\Admin\ImportOpportunityController::class, 'destroy'])->name('destroy');
        Route::patch('/{opportunity}/estado', [App\Http\Controllers\Admin\ImportOpportunityController::class, 'updateStatus'])->name('update-status');
        Route::patch('/{opportunity}/checklist', [App\Http\Controllers\Admin\ImportOpportunityController::class, 'updateChecklist'])->name('update-checklist');
        Route::patch('/{opportunity}/notas', [App\Http\Controllers\Admin\ImportOpportunityController::class, 'updateNotes'])->name('update-notes');
        Route::post('/{opportunity}/contactos', [App\Http\Controllers\Admin\ImportOpportunityContactController::class, 'store'])->name('contacts.store');
        Route::delete('/{opportunity}/contactos/{contact}', [App\Http\Controllers\Admin\ImportOpportunityContactController::class, 'destroy'])->name('contacts.destroy');
    });

    // ============================================================
    // NEWSLETTER
    // ============================================================
    Route::get('/v2/email-preview/newsletter-custos-importacao', fn() => view('emails.newsletter-custos-importacao'))
        ->name('admin.v2.email-preview.newsletter-custos-importacao');

    // Pré-visualização do email de confirmação de pedido de importação
    Route::get('/v2/email-preview/import-form-confirmation', function () {
        $client = new \App\Models\Client(['name' => 'Sofia Almeida']);
        $proposal = new \App\Models\FormProposal([
            'brand' => 'Mercedes-Benz',
            'model' => 'Classe A',
            'version' => '200d',
            'fuel' => 'Gasolina',
            'budget' => 28000,
            'estimated_purchase_date' => '1_3_meses',
            'payment_type' => 'pronto_pagamento',
        ]);

        return view('emails.import_form_confirmation', compact('client', 'proposal'));
    })->name('admin.v2.email-preview.import-form-confirmation');

    // Pré-visualização do email de aceitação de proposta — ?angariador=1 mostra a versão enviada ao angariador
    Route::get('/v2/email-preview/proposal-accepted', function (\Illuminate\Http\Request $request) {
        $data = [
            'client_name' => 'Beatriz Ferreira',
            'brand' => 'BMW',
            'model' => 'Série 3',
            'version' => '320d',
            'car_image' => null,
            'tracking_url' => url('/acompanhamento/bmw/serie-3/320d/1'),
        ];

        return view('emails.proposal_accepted', [
            'data' => $data,
            'tracking_url' => $data['tracking_url'],
            'forAngariador' => $request->boolean('angariador'),
        ]);
    })->name('admin.v2.email-preview.proposal-accepted');

    // Route::post('v2/newsletter/send', [App\Http\Controllers\Admin\NewsletterController::class, 'send'])->name('admin.v2.newsletter.send');
    
    // Newsletter Management (Unified)
    Route::prefix('v2/newsletter-management')->name('admin.v2.newsletter-management.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'store'])->name('store');
        Route::get('/{id}', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'show'])->name('show');
        Route::get('/{id}/preview', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'preview'])->name('preview');
        Route::get('/{id}/send', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'sendPage'])->name('send');
        Route::post('/{id}/send', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'doSend'])->name('doSend');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'destroy'])->name('destroy');
        
        // Offers within newsletter
        Route::get('/{newsletterId}/offers/create', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'createOffer'])->name('offers.create');
        Route::post('/{newsletterId}/offers', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'storeOffer'])->name('offers.store');
        Route::get('/{newsletterId}/offers/{offerId}/edit', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'editOffer'])->name('offers.edit');
        Route::put('/{newsletterId}/offers/{offerId}', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'updateOffer'])->name('offers.update');
        Route::delete('/{newsletterId}/offers/{offerId}', [App\Http\Controllers\Admin\NewsletterManagementController::class, 'destroyOffer'])->name('offers.destroy');
    });

    Route::prefix('v2/cost-simulators')->name('admin.v2.cost-simulators.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CostSimulatorController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\CostSimulatorController::class, 'show'])->name('show');
        // Route::get('/create', [App\Http\Controllers\Admin\CostSimulatorController::class, 'create'])->name('create');
        // Route::post('/', [App\Http\Controllers\Admin\CostSimulatorController::class, 'store'])->name('store');
        // Route::get('/{id}/edit', [App\Http\Controllers\Admin\CostSimulatorController::class, 'edit'])->name('edit');
        // Route::put('/{id}', [App\Http\Controllers\Admin\CostSimulatorController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\CostSimulatorController::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // NOTÍCIAS (novo módulo independente)
    // ============================================================
    Route::prefix('news')->name('admin.news.')->group(function () {
        Route::get('/',                [App\Http\Controllers\Admin\NewsArticleController::class, 'index'])->name('index');
        Route::get('/create',          [App\Http\Controllers\Admin\NewsArticleController::class, 'create'])->name('create');
        Route::post('/',               [App\Http\Controllers\Admin\NewsArticleController::class, 'store'])->name('store');
        Route::get('/{id}/edit',       [App\Http\Controllers\Admin\NewsArticleController::class, 'edit'])->name('edit');
        Route::put('/{id}',            [App\Http\Controllers\Admin\NewsArticleController::class, 'update'])->name('update');
        Route::delete('/{id}',         [App\Http\Controllers\Admin\NewsArticleController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status',  [App\Http\Controllers\Admin\NewsArticleController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{id}/gallery-image', [App\Http\Controllers\Admin\NewsArticleController::class, 'deleteGalleryImage'])->name('gallery-image.delete');
    });

    // ============================================================
    // TRANSPORTES
    // ============================================================
    Route::prefix('v2/transport-quotes')->name('admin.transport-quotes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\TransportQuoteController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\TransportQuoteController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\TransportQuoteController::class, 'store'])->name('store');
        Route::get('/map', [App\Http\Controllers\Admin\TransportQuoteController::class, 'map'])->name('map');
        Route::get('/map-data', [App\Http\Controllers\Admin\TransportQuoteController::class, 'getMapData'])->name('map-data');
        Route::post('/geocode', [App\Http\Controllers\Admin\TransportQuoteController::class, 'geocode'])->name('geocode');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\TransportQuoteController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\TransportQuoteController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\TransportQuoteController::class, 'destroy'])->name('destroy');
    });

    // ============================================================
    // VEÍCULOS V2 → redireccionamento para V3 (sistema unificado)
    // ============================================================
    Route::prefix('v2/vehicles')->name('admin.v2.vehicles.')->group(function () {
        Route::get('/{any?}', fn() => redirect()->route('admin.v3.vehicles.index'))->name('index')->where('any', '.*');
        // Aliases de named routes usados em views antigas
        Route::get('/create', fn() => redirect()->route('admin.v3.vehicles.create'))->name('create');
        Route::get('/list',   fn() => redirect()->route('admin.v3.vehicles.index'))->name('list');
        Route::get('/{id}/edit', fn($id) => redirect()->route('admin.v3.vehicles.edit', $id))->name('edit');
    });

    // ============================================================
    // VEÍCULOS V3
    // ============================================================
    Route::prefix('v3/vehicles')->name('admin.v3.vehicles.')->group(function () {
        Route::get('/',       [App\Http\Controllers\Admin\V3VehicleController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\V3VehicleController::class, 'create'])->name('create');
        // Exportação XLSX (antes das rotas com {id}); a ação "export" conta como ver.
        Route::get('/export', [App\Http\Controllers\Admin\ExportXlsxController::class, 'download'])->name('export')->defaults('tipo', 'vehicles');
        Route::post('/',      [App\Http\Controllers\Admin\V3VehicleController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\V3VehicleController::class, 'edit'])->name('edit');
        Route::delete('/{id}',   [App\Http\Controllers\Admin\V3VehicleController::class, 'destroy'])->name('destroy');

        // Auto-save (AJAX)
        Route::post('/{id}/save-general',    [App\Http\Controllers\Admin\V3VehicleController::class, 'saveGeneral'])->name('save-general');
        Route::post('/{id}/save-purchase',   [App\Http\Controllers\Admin\V3VehicleController::class, 'savePurchase'])->name('save-purchase');
        Route::post('/{id}/save-equipment',  [App\Http\Controllers\Admin\V3VehicleController::class, 'saveEquipment'])->name('save-equipment');

        // Expenses
        Route::post('/{id}/expenses',                 [App\Http\Controllers\Admin\V3VehicleController::class, 'storeExpense'])->name('expenses.store');
        Route::delete('/{vehicleId}/expenses/{expenseId}', [App\Http\Controllers\Admin\V3VehicleController::class, 'destroyExpense'])->name('expenses.destroy');

        // Documents
        Route::post('/{id}/documents',                      [App\Http\Controllers\Admin\V3VehicleController::class, 'uploadDocument'])->name('documents.upload');
        Route::get('/{vehicleId}/documents/{documentId}/download', [App\Http\Controllers\Admin\V3VehicleController::class, 'downloadDocument'])->name('documents.download');
        Route::delete('/{vehicleId}/documents/{documentId}',       [App\Http\Controllers\Admin\V3VehicleController::class, 'destroyDocument'])->name('documents.destroy');

        // Photos
        Route::post('/{id}/photos',                    [App\Http\Controllers\Admin\V3VehicleController::class, 'uploadPhoto'])->name('photos.upload');
        Route::delete('/{vehicleId}/photos/{photoId}', [App\Http\Controllers\Admin\V3VehicleController::class, 'destroyPhoto'])->name('photos.destroy');
        Route::post('/{vehicleId}/photos/{photoId}/cover', [App\Http\Controllers\Admin\V3VehicleController::class, 'setCoverPhoto'])->name('photos.cover');
        Route::post('/{vehicleId}/photos/{photoId}/focal', [App\Http\Controllers\Admin\V3VehicleController::class, 'setFocalPoint'])->name('photos.focal');
        Route::post('/{vehicleId}/photos/{photoId}/crop',    [App\Http\Controllers\Admin\V3VehicleController::class, 'cropPhoto'])->name('photos.crop');
        Route::post('/{vehicleId}/photos/{photoId}/restore', [App\Http\Controllers\Admin\V3VehicleController::class, 'restorePhoto'])->name('photos.restore');
        Route::post('/{id}/photos/reorder',            [App\Http\Controllers\Admin\V3VehicleController::class, 'reorderPhotos'])->name('photos.reorder');

        // Sale
        Route::post('/{id}/sale',                   [App\Http\Controllers\Admin\V3VehicleController::class, 'storeSale'])->name('sale.store');
        Route::delete('/{vehicleId}/sale/{saleId}', [App\Http\Controllers\Admin\V3VehicleController::class, 'destroySale'])->name('sale.destroy');
        Route::post('/{id}/sale/preview',           [App\Http\Controllers\Admin\V3VehicleController::class, 'calculatePreview'])->name('sale.preview');

        // Legalization
        Route::post('/{id}/legalization/create',    [App\Http\Controllers\Admin\V3VehicleController::class, 'createLegalization'])->name('legalization.create');
        Route::post('/{id}/legalization/save',      [App\Http\Controllers\Admin\V3VehicleController::class, 'saveLegalization'])->name('legalization.save');
        Route::post('/{id}/legalization/step',      [App\Http\Controllers\Admin\V3VehicleController::class, 'toggleLegalizationStep'])->name('legalization.toggle-step');
        Route::post('/{id}/legalization/documents', [App\Http\Controllers\Admin\V3VehicleController::class, 'uploadLegalizationDocument'])->name('legalization.upload-document');
        Route::get('/{vehicleId}/legalization/documents/{documentId}/download', [App\Http\Controllers\Admin\V3VehicleController::class, 'downloadLegalizationDocument'])->name('legalization.download-document');
        Route::delete('/{vehicleId}/legalization/documents/{documentId}',        [App\Http\Controllers\Admin\V3VehicleController::class, 'deleteLegalizationDocument'])->name('legalization.delete-document');

        // Gerar anúncio
        Route::post('/{id}/generate-ad', [App\Http\Controllers\Admin\V3VehicleController::class, 'generateAdText'])->name('generate-ad');
        Route::post('/{id}/save-ad',     [App\Http\Controllers\Admin\V3VehicleController::class, 'saveAdText'])->name('save-ad');
    });

    // ============================================================
    // CHECKLIST / INSPEÇÕES V3
    // ============================================================
    Route::prefix('v3/inspections')->name('admin.v3.inspections.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'store'])->name('store');
        Route::post('/{inspection}/duplicate', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'duplicate'])->name('duplicate');
        Route::get('/{inspection}/report', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'report'])->name('report');
        Route::get('/{inspection}/report/pdf', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'downloadPdf'])->name('report.pdf');
        Route::get('/{inspection}/edit', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'edit'])->name('edit');
        Route::put('/{inspection}', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'update'])->name('update');
        Route::post('/{inspection}/convert', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'convert'])->name('convert');

        Route::post('/{inspection}/entries/{entry}/media', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'uploadMedia'])->name('media.store');
        Route::post('/{inspection}/entries/{entry}/media/reorder', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'reorderMedia'])->name('media.reorder');
        Route::delete('/{inspection}/entries/{entry}/media/{media}', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'destroyMedia'])->name('media.destroy');

        Route::post('/{inspection}/general-media', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'uploadGeneralMedia'])->name('general-media.store');
        Route::delete('/{inspection}/general-media/{media}', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'destroyGeneralMedia'])->name('general-media.destroy');
        Route::post('/{inspection}/destroy', [App\Http\Controllers\Admin\VehicleInspectionController::class, 'destroy'])->name('destroy');
        
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
    // TAREFAS
    // ============================================================
    Route::prefix('tasks')->name('admin.tasks.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\TaskController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\TaskController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\TaskController::class, 'store'])->name('store');
        Route::get('/{task}', [App\Http\Controllers\Admin\TaskController::class, 'show'])->name('show');
        Route::get('/{task}/edit', [App\Http\Controllers\Admin\TaskController::class, 'edit'])->name('edit');
        Route::put('/{task}', [App\Http\Controllers\Admin\TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [App\Http\Controllers\Admin\TaskController::class, 'destroy'])->name('destroy');
        Route::patch('/{task}/status', [App\Http\Controllers\Admin\TaskController::class, 'updateStatus'])->name('update-status');
        Route::get('/json/tasks', [App\Http\Controllers\Admin\TaskController::class, 'getTasksJson'])->name('json');
    });

    // ============================================================
    // MOVIMENTOS V2 (antigo: Despesas)
    // ============================================================
    Route::prefix('v2/movements')->name('admin.v2.movements.')->group(function () {
        Route::get('/',           [App\Http\Controllers\Admin\MovementV2Controller::class, 'index'])->name('index');
        // Exportação XLSX; a ação "export" conta como ver.
        Route::get('/export',     [App\Http\Controllers\Admin\ExportXlsxController::class, 'download'])->name('export')->defaults('tipo', 'movements');
        Route::get('/create',     [App\Http\Controllers\Admin\MovementV2Controller::class, 'create'])->name('create');
        Route::post('/',          [App\Http\Controllers\Admin\MovementV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit',  [App\Http\Controllers\Admin\MovementV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}',       [App\Http\Controllers\Admin\MovementV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}',    [App\Http\Controllers\Admin\MovementV2Controller::class, 'destroy'])->name('destroy');
    });

    // Backward compatibility – redirect old expense URLs to new movement URLs
    Route::prefix('v2/expenses')->name('admin.v2.expenses.')->group(function () {
        Route::get('/',           fn () => redirect()->route('admin.v2.movements.index'))->name('index');
        Route::get('/create',     fn () => redirect()->route('admin.v2.movements.create'))->name('create');
        Route::get('/{id}/edit',  fn ($id) => redirect()->route('admin.v2.movements.edit', $id))->name('edit');
        Route::post('/',          [App\Http\Controllers\Admin\MovementV2Controller::class, 'store'])->name('store');
        Route::put('/{id}',       [App\Http\Controllers\Admin\MovementV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}',    [App\Http\Controllers\Admin\MovementV2Controller::class, 'destroy'])->name('destroy');
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
    // VENDEDORES (stands/empresas) E CONTACTOS
    // ============================================================
    Route::prefix('v2/vendedores')->name('admin.v2.sellers.')->scopeBindings()->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SellerController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\SellerController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\SellerController::class, 'store'])->name('store');
        Route::get('/pesquisa', [App\Http\Controllers\Admin\SellerController::class, 'search'])->name('search');
        Route::post('/verificar-duplicados', [App\Http\Controllers\Admin\SellerController::class, 'checkDuplicates'])->name('check-duplicates');
        Route::get('/{seller}', [App\Http\Controllers\Admin\SellerController::class, 'show'])->name('show');
        Route::get('/{seller}/edit', [App\Http\Controllers\Admin\SellerController::class, 'edit'])->name('edit');
        Route::put('/{seller}', [App\Http\Controllers\Admin\SellerController::class, 'update'])->name('update');
        Route::patch('/{seller}/estado', [App\Http\Controllers\Admin\SellerController::class, 'toggleActive'])->name('toggle-active');
        Route::delete('/{seller}', [App\Http\Controllers\Admin\SellerController::class, 'destroy'])->name('destroy');
        Route::get('/{seller}/contactos', [App\Http\Controllers\Admin\SellerController::class, 'contacts'])->name('contacts.index');
        Route::post('/{seller}/contactos', [App\Http\Controllers\Admin\SellerContactController::class, 'store'])->name('contacts.store');
        Route::put('/{seller}/contactos/{contact}', [App\Http\Controllers\Admin\SellerContactController::class, 'update'])->name('contacts.update');
        Route::patch('/{seller}/contactos/{contact}/principal', [App\Http\Controllers\Admin\SellerContactController::class, 'setPrimary'])->name('contacts.primary');
        Route::patch('/{seller}/contactos/{contact}/estado', [App\Http\Controllers\Admin\SellerContactController::class, 'toggleActive'])->name('contacts.toggle-active');
    });

    // ============================================================
    // MENUS V2
    // ============================================================
    Route::prefix('v2/menus')->name('admin.v2.menus.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\MenuV2Controller::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\MenuV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\MenuV2Controller::class, 'store'])->name('store');
        Route::get('/{menu}/edit', [App\Http\Controllers\Admin\MenuV2Controller::class, 'edit'])->name('edit');
        Route::put('/{menu}', [App\Http\Controllers\Admin\MenuV2Controller::class, 'update'])->name('update');
        Route::delete('/{menu}', [App\Http\Controllers\Admin\MenuV2Controller::class, 'destroy'])->name('destroy');
        Route::post('/{menu}/toggle', [App\Http\Controllers\Admin\MenuV2Controller::class, 'toggle'])->name('toggle');
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
        Route::get('/kanban', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'kanban'])->name('kanban');
        Route::get('/create', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'destroy'])->name('destroy');
        Route::post('/{id}/documents/signed', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'uploadSignedDocument'])->name('documents.uploadSigned');
        Route::get('/{id}/documents/{document}/download', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'downloadDocument'])->name('documents.download');
        Route::get('/{id}/contract', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'contract'])->name('contract');
        Route::delete('/{id}/documents/{document}', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'deleteDocument'])->name('documents.destroy');
        Route::post('/{id}/viatura', [App\Http\Controllers\Admin\ConvertedProposalV2Controller::class, 'createVehicle'])->name('create-vehicle');
    });

    // ============================================================
    // GESTÃO MOBILE (iPhone) — só para quem tem access_mobile
    // ============================================================
    Route::prefix('mobile')->name('admin.mobile.')->middleware('mobile.access')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\Mobile\DashboardController::class, 'index'])->name('dashboard');

        // Pesquisa: cada secção é filtrada pelas permissões do utilizador no controller.
        Route::get('/pesquisa', [App\Http\Controllers\Admin\Mobile\SearchController::class, 'index'])->name('search');

        // Leads ("/leads/nova" tem de ficar antes de "/leads/{id}").
        Route::get('/leads', [App\Http\Controllers\Admin\Mobile\LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/nova', [App\Http\Controllers\Admin\Mobile\LeadController::class, 'create'])->name('leads.create');
        Route::post('/leads', [App\Http\Controllers\Admin\Mobile\LeadController::class, 'store'])->name('leads.store');
        Route::get('/leads/{id}', [App\Http\Controllers\Admin\Mobile\LeadController::class, 'show'])->name('leads.show')->whereNumber('id');
        Route::post('/leads/{id}/estado', [App\Http\Controllers\Admin\Mobile\LeadController::class, 'status'])->name('leads.status')->whereNumber('id');
        Route::post('/leads/{id}/converter', [App\Http\Controllers\Admin\Mobile\LeadController::class, 'convert'])->name('leads.convert')->whereNumber('id');
        Route::post('/leads/{id}/followup', [App\Http\Controllers\Admin\Mobile\LeadController::class, 'followup'])->name('leads.followup')->whereNumber('id');
        Route::post('/leads/{id}/atividade', [App\Http\Controllers\Admin\Mobile\LeadController::class, 'activity'])->name('leads.activity')->whereNumber('id');
        Route::post('/leads/{id}/dono', [App\Http\Controllers\Admin\Mobile\LeadController::class, 'owner'])->name('leads.owner')->whereNumber('id');
        Route::delete('/leads/{id}', [App\Http\Controllers\Admin\Mobile\LeadController::class, 'destroy'])->name('leads.destroy')->whereNumber('id');

        // Clientes ("/clientes/novo" tem de ficar antes de "/clientes/{id}").
        Route::get('/clientes', [App\Http\Controllers\Admin\Mobile\ClientController::class, 'index'])->name('clients.index');
        Route::get('/clientes/novo', [App\Http\Controllers\Admin\Mobile\ClientController::class, 'create'])->name('clients.create');
        Route::post('/clientes', [App\Http\Controllers\Admin\Mobile\ClientController::class, 'store'])->name('clients.store');
        Route::get('/clientes/{id}', [App\Http\Controllers\Admin\Mobile\ClientController::class, 'show'])->name('clients.show')->whereNumber('id');
        Route::get('/clientes/{id}/editar', [App\Http\Controllers\Admin\Mobile\ClientController::class, 'edit'])->name('clients.edit')->whereNumber('id');
        Route::put('/clientes/{id}', [App\Http\Controllers\Admin\Mobile\ClientController::class, 'update'])->name('clients.update')->whereNumber('id');
        Route::post('/clientes/{id}/followup', [App\Http\Controllers\Admin\Mobile\ClientController::class, 'followup'])->name('clients.followup')->whereNumber('id');
        Route::post('/clientes/{id}/atividade', [App\Http\Controllers\Admin\Mobile\ClientController::class, 'activity'])->name('clients.activity')->whereNumber('id');

        // Pedidos de formulário ("/formularios/novo" tem de ficar antes de "/formularios/{id}").
        Route::get('/formularios', [App\Http\Controllers\Admin\Mobile\FormProposalController::class, 'index'])->name('form-proposals.index');
        Route::get('/formularios/novo', [App\Http\Controllers\Admin\Mobile\FormProposalController::class, 'create'])->name('form-proposals.create');
        Route::post('/formularios', [App\Http\Controllers\Admin\Mobile\FormProposalController::class, 'store'])->name('form-proposals.store');
        Route::get('/formularios/{id}', [App\Http\Controllers\Admin\Mobile\FormProposalController::class, 'show'])->name('form-proposals.show')->whereNumber('id');
        Route::patch('/formularios/{id}/estado', [App\Http\Controllers\Admin\Mobile\FormProposalController::class, 'status'])->name('form-proposals.status')->whereNumber('id');

        // Cotações ("/cotacoes/nova" tem de ficar antes de "/cotacoes/{id}").
        Route::get('/cotacoes', [App\Http\Controllers\Admin\Mobile\ProposalController::class, 'index'])->name('proposals.index');
        Route::get('/cotacoes/nova', [App\Http\Controllers\Admin\Mobile\ProposalController::class, 'create'])->name('proposals.create');
        Route::post('/cotacoes', [App\Http\Controllers\Admin\Mobile\ProposalController::class, 'store'])->name('proposals.store');
        Route::get('/cotacoes/{id}', [App\Http\Controllers\Admin\Mobile\ProposalController::class, 'show'])->name('proposals.show')->whereNumber('id');
        Route::get('/cotacoes/{id}/editar', [App\Http\Controllers\Admin\Mobile\ProposalController::class, 'edit'])->name('proposals.edit')->whereNumber('id');
        Route::put('/cotacoes/{id}', [App\Http\Controllers\Admin\Mobile\ProposalController::class, 'update'])->name('proposals.update')->whereNumber('id');
        Route::patch('/cotacoes/{id}/estado', [App\Http\Controllers\Admin\Mobile\ProposalController::class, 'status'])->name('proposals.status')->whereNumber('id');
        Route::post('/cotacoes/{id}/aceitar', [App\Http\Controllers\Admin\Mobile\ProposalController::class, 'accept'])->name('proposals.accept')->whereNumber('id');

        // Cotações convertidas: consulta e estado do pipeline (pagamentos, documentos e contrato no desktop).
        Route::get('/convertidas', [App\Http\Controllers\Admin\Mobile\ConvertedProposalController::class, 'index'])->name('converted-proposals.index');
        Route::get('/convertidas/{id}', [App\Http\Controllers\Admin\Mobile\ConvertedProposalController::class, 'show'])->name('converted-proposals.show')->whereNumber('id');
        Route::patch('/convertidas/{id}/estado', [App\Http\Controllers\Admin\Mobile\ConvertedProposalController::class, 'status'])->name('converted-proposals.status')->whereNumber('id');

        // Simulador de custos: consulta das simulações feitas no site (eliminar no desktop).
        Route::get('/simulador-custos', [App\Http\Controllers\Admin\Mobile\CostSimulatorController::class, 'index'])->name('cost-simulators.index');
        Route::get('/simulador-custos/{id}', [App\Http\Controllers\Admin\Mobile\CostSimulatorController::class, 'show'])->name('cost-simulators.show')->whereNumber('id');

        // Movimentos financeiros ("/movimentos/novo" tem de ficar antes de "/movimentos/{id}").
        // Os automáticos (venda, viatura, convertida) são só de consulta; exportações e comprovativos no desktop.
        Route::get('/movimentos', [App\Http\Controllers\Admin\Mobile\MovementController::class, 'index'])->name('movements.index');
        Route::get('/movimentos/novo', [App\Http\Controllers\Admin\Mobile\MovementController::class, 'create'])->name('movements.create');
        Route::post('/movimentos', [App\Http\Controllers\Admin\Mobile\MovementController::class, 'store'])->name('movements.store');
        Route::get('/movimentos/{id}', [App\Http\Controllers\Admin\Mobile\MovementController::class, 'show'])->name('movements.show')->whereNumber('id');
        Route::get('/movimentos/{id}/editar', [App\Http\Controllers\Admin\Mobile\MovementController::class, 'edit'])->name('movements.edit')->whereNumber('id');
        Route::put('/movimentos/{id}', [App\Http\Controllers\Admin\Mobile\MovementController::class, 'update'])->name('movements.update')->whereNumber('id');
        Route::delete('/movimentos/{id}', [App\Http\Controllers\Admin\Mobile\MovementController::class, 'destroy'])->name('movements.destroy')->whereNumber('id');

        // Oportunidades de um pedido (scopeBindings: a oportunidade tem de ser do pedido).
        Route::prefix('/formularios/{formProposal}/oportunidades')->name('form-proposals.opportunities.')->scopeBindings()->group(function () {
            Route::post('/', [App\Http\Controllers\Admin\Mobile\ImportOpportunityController::class, 'store'])->name('store');
            Route::get('/{opportunity}', [App\Http\Controllers\Admin\Mobile\ImportOpportunityController::class, 'show'])->name('show');
            Route::patch('/{opportunity}/estado', [App\Http\Controllers\Admin\Mobile\ImportOpportunityController::class, 'status'])->name('status');
            Route::patch('/{opportunity}/checklist', [App\Http\Controllers\Admin\Mobile\ImportOpportunityController::class, 'checklist'])->name('checklist');
            Route::patch('/{opportunity}/notas', [App\Http\Controllers\Admin\Mobile\ImportOpportunityController::class, 'notes'])->name('notes');
            Route::post('/{opportunity}/contactos', [App\Http\Controllers\Admin\Mobile\ImportOpportunityController::class, 'storeContact'])->name('contacts.store');
            Route::delete('/{opportunity}/contactos/{contact}', [App\Http\Controllers\Admin\Mobile\ImportOpportunityController::class, 'destroyContact'])->name('contacts.destroy');
        });
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
    // MARCAS (logos)
    // ============================================================
    Route::prefix('v2/brands')->name('admin.v2.brands.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\BrandLogoController::class, 'index'])->name('index');
        Route::post('/{brand}/logo', [App\Http\Controllers\Admin\BrandLogoController::class, 'update'])->name('logo.update');
        Route::delete('/{brand}/logo', [App\Http\Controllers\Admin\BrandLogoController::class, 'destroy'])->name('logo.destroy');
    });

    // ============================================================
    // FICHEIROS PRIVADOS (retomas, consignação, comprovativos, anexos)
    // ============================================================
    Route::get('/ficheiros/{tipo}/{caminho}', [App\Http\Controllers\Admin\PrivateFileController::class, 'show'])
        ->whereIn('tipo', App\Services\PrivateFiles::tipos())
        ->where('caminho', '.+')
        ->name('admin.v2.files.show');

    // ============================================================
    // RELATÓRIOS V2
    // ============================================================
    Route::prefix('v2/reports')->name('admin.v2.reports.')->group(function () {
        Route::get('/',                  [App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('index');
        Route::get('/{filename}/download', [App\Http\Controllers\Admin\ReportsController::class, 'download'])->name('download')->where('filename', '[^/]+\.pdf');
        Route::post('/gerar',            [App\Http\Controllers\Admin\ReportsController::class, 'generate'])->name('generate');
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
    // PORTAL DO ANGARIADOR
    // ============================================================
    Route::prefix('angariador')->name('admin.angariador.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AngariadorController::class, 'dashboard'])->name('dashboard');
        Route::get('/leads', [App\Http\Controllers\Admin\AngariadorController::class, 'leads'])->name('leads');
        Route::post('/leads', [App\Http\Controllers\Admin\AngariadorController::class, 'storeLead'])->name('leads.store');
        Route::get('/leads/kanban', [App\Http\Controllers\Admin\AngariadorController::class, 'kanban'])->name('leads.kanban');
        Route::get('/leads/{client}', [App\Http\Controllers\Admin\AngariadorController::class, 'leadShow'])->name('leads.show');
        Route::post('/leads/{client}/activity', [App\Http\Controllers\Admin\AngariadorController::class, 'storeActivity'])->name('leads.activity');
        Route::post('/leads/{client}/followup', [App\Http\Controllers\Admin\AngariadorController::class, 'saveFollowup'])->name('leads.followup');
        Route::post('/leads/{client}/status', [App\Http\Controllers\Admin\AngariadorController::class, 'updateStatus'])->name('leads.status');
        Route::get('/cotacoes', [App\Http\Controllers\Admin\AngariadorController::class, 'cotacoes'])->name('cotacoes');
        Route::redirect('/propostas', '/gestao/angariador/cotacoes')->name('propostas');
        Route::get('/cotacoes-convertidas', [App\Http\Controllers\Admin\AngariadorController::class, 'convertidas'])->name('convertidas');
        Route::get('/cotacoes-convertidas/{convertedProposal}', [App\Http\Controllers\Admin\AngariadorController::class, 'convertidaShow'])->name('convertidas.show');
        Route::get('/comissoes', [App\Http\Controllers\Admin\AngariadorController::class, 'comissoes'])->name('comissoes');
        Route::get('/comissoes/{convertedProposal}/comprovativo', [App\Http\Controllers\Admin\AngariadorController::class, 'comprovativo'])->name('comissoes.comprovativo');
        // Os pedidos de importação passaram para dentro de cada lead.
        Route::redirect('/formularios', '/gestao/angariador/leads')->name('formularios');
        Route::get('/manual', [App\Http\Controllers\Admin\ManualController::class, 'angariador'])->name('manual');
        Route::get('/faq', [App\Http\Controllers\Admin\ManualController::class, 'angariadorFaq'])->name('faq');
        Route::get('/contrato', [App\Http\Controllers\Admin\AngariadorController::class, 'contrato'])->name('contrato');
        Route::post('/stop-impersonating', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'stopImpersonating'])->name('stop-impersonating');
    });

    // ============================================================
    // ADMINISTRAÇÃO DE ANGARIADORES (visível apenas para admin/gestor,
    // já bloqueada para angariadores puros pelo middleware restrictAngariador)
    // ============================================================
    Route::prefix('v2/angariadores')->name('admin.v2.angariadores.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'index'])->name('index');
        Route::get('/comissoes', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'comissoes'])->name('comissoes');
        Route::post('/comissoes/{proposal}/pagar', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'togglePaid'])->name('toggle-paid');
        Route::post('/comissoes/{proposal}/comprovativo', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'uploadReceipt'])->name('upload-receipt');
        Route::post('/{user}/aprovar', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'approve'])->name('approve');
        Route::post('/{user}/rejeitar', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'reject'])->name('reject');
        Route::delete('/{user}', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'destroy'])->name('destroy');
        Route::get('/{user}', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'show'])->name('show');
        Route::get('/{user}/documento/{lado}', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'identityDocument'])->whereIn('lado', ['frente', 'verso'])->name('identity-document');
        Route::get('/{user}/contrato', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'contract'])->name('contract');
        Route::get('/{user}/contrato-assinado', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'signedContract'])->name('contract.signed');
        Route::post('/{user}/contrato-assinado', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'uploadSignedContract'])->name('contract.signed.store');
        Route::delete('/{user}/contrato-assinado', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'destroySignedContract'])->name('contract.signed.destroy');
        Route::post('/{user}/impersonate', [App\Http\Controllers\Admin\AngariadorAdminController::class, 'impersonate'])->name('impersonate');
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

    // Mudança de estado de uma cotação convertida (usada pelo pipeline do V2).
    Route::patch('/v2/converted-proposals/{id}/update-status', [ConvertedProposalController::class, 'updateStatus'])->name('admin.v2.converted-proposals.update-status');






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
    // Route::get('news', [PageController::class, 'getNews'])->name('pages.news'); // substituído por admin.news.*
    Route::get('categories', [PageController::class, 'getCategories'])->name('pages.categories');
    Route::get('legalizations', [PageController::class, 'getLegalizations'])->name('pages.legalizations');
    Route::get('imports', [PageController::class, 'getImports'])->name('pages.imports');
    Route::get('selling', [PageController::class, 'getSelling'])->name('pages.selling');
});
