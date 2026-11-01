<?php

namespace Tests\Fluxo\Concerns;

use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\Expense;
use App\Models\FinancialMovement;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\LeadActivity;
use App\Models\Legalization;
use App\Models\Proposal;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Models\V3Vehicle;
use App\Permissions\PermissionSynchronizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

/**
 * Andaime partilhado pelos testes de falhas/concorrência: base de teste alinhada, utilizadores
 * por perfil real, e construtores do funil por pedidos HTTP (mesmas rotas do BO e da página pública).
 *
 * Acrescenta helpers sem tocar na infraestrutura existente (UsaMysqlIsolado, FluxoLeadAteEntregaTest).
 */
trait PreparaFunil
{
    private static bool $migracoesCompletadasF = false;

    private static int $seqFunil = 0;

    protected User $operador;       // perfil real Importador
    protected User $admin;
    protected User $angariador;
    protected User $semFunil;       // perfil real cms
    protected User $semPerfil;      // sem role
    protected User $roleVazio;      // role sem nenhuma permissão

    /** Mesmo alinhamento de esquema da base de teste que o cenário principal (só em izzycar_fluxo_teste, após a guarda). */
    protected function completarMigracoesEmFaltaF(): void
    {
        if (self::$migracoesCompletadasF) {
            return;
        }
        self::garantirBaseIsolada(DB::connection()->getConfig(), DB::connection()->selectOne('SELECT DATABASE() AS db')->db ?? null);
        foreach (['2026_08_05_110100_add_angariador_code_to_clients_and_form_proposals.php', '2026_08_05_150000_add_comprovativo_pagamento_to_converted_proposals_table.php'] as $ficheiro) {
            Artisan::call('migrate', ['--path' => 'database/migrations/'.$ficheiro, '--force' => true]);
        }
        DB::statement('ALTER TABLE `proposals` MODIFY `status` VARCHAR(255) NULL');
        DB::statement("ALTER TABLE `converted_proposals` MODIFY `status` VARCHAR(255) NOT NULL DEFAULT 'Iniciada'");
        DB::statement('ALTER TABLE `clients` MODIFY `origin` VARCHAR(255) NULL');
        self::$migracoesCompletadasF = true;
    }

    protected function prepararBase(): void
    {
        $this->completarMigracoesEmFaltaF();

        $this->assertSame('array', config('mail.default'));
        Http::fake();
        Notification::fake();
        Storage::fake('local');
        Storage::fake('public');
        Cache::flush();
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        config(['permissions.enforce' => false, 'logging.default' => 'null']);

        $sync = app(PermissionSynchronizer::class);
        $sync->syncCatalog();
        $sync->applyProfiles();
        Role::findOrCreate('admin', 'web');

        foreach (['name' => 'TESTE FLUXO Izzycar', 'vat_number' => '999999990', 'address' => 'Rua de Teste, Lisboa', 'iban' => 'PT50000000000000000000000',
            'phone' => '910000000', 'email' => 'geral.fluxo@teste.invalid', 'facebook' => 'https://facebook.invalid/x', 'insta' => 'https://instagram.invalid/x',
            'init_payment' => '50', 'final_payment' => '50'] as $label => $value) {
            Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => $value]);
        }

        $this->vehicleCatalog(['BMW' => ['i4', 'X1'], 'Tesla' => ['Model 3']]);

        $this->operador = $this->backofficeUser('Importador', ['name' => 'TESTE FLUXO Operador', 'email' => 'operador.fluxo@teste.invalid']);
        $this->admin = $this->backofficeUser('admin', ['name' => 'TESTE FLUXO Admin', 'email' => 'admin.fluxo@teste.invalid']);
        $this->angariador = $this->backofficeUser('angariador', ['name' => 'TESTE FLUXO Angariador', 'email' => 'angariador.fluxo@teste.invalid', 'commission_fixed_value' => 100]);
        $this->semFunil = $this->backofficeUser('cms', ['name' => 'TESTE FLUXO CMS', 'email' => 'cms.fluxo@teste.invalid']);
        $this->semPerfil = User::factory()->create(['name' => 'TESTE FLUXO SemPerfil', 'last_name' => 'Teste', 'email' => 'semperfil.fluxo@teste.invalid', 'password' => 'secret']);
        $this->roleVazio = $this->backofficeUser('TESTE sem objetos', ['name' => 'TESTE FLUXO RoleVazio', 'email' => 'vazio.fluxo@teste.invalid']);
    }

    // ------------------------------------------------------------------
    // Helpers de pedidos
    // ------------------------------------------------------------------

    protected function comoVisitante(): static
    {
        $this->app['auth']->forgetGuards();

        return $this;
    }

    protected function bo(?User $user = null): static
    {
        $this->actingAs($user ?? $this->operador);

        return $this;
    }

    protected function emails(): Collection
    {
        return Mail::mailer()->getSymfonyTransport()->messages()->map(fn ($m) => $m->getOriginalMessage());
    }

    protected function dadosAceitacao(array $extra = []): array
    {
        return $extra + [
            'address' => 'Rua de Teste 1', 'postal_code' => '0000-000', 'city' => 'Cidade Teste', 'vat_number' => '999999990',
            'identification_number' => 'TESTE-CC-0001', 'validate_identification_number' => '2031-03-15',
        ];
    }

    // ------------------------------------------------------------------
    // Construtores do funil (rotas reais)
    // ------------------------------------------------------------------

    /**
     * Lead + pedido + oportunidades A/B/C + cotações A e B (Enviado, sem emails).
     *
     * @return array<string, mixed>
     */
    protected function novoFunil(array $opt = []): array
    {
        $n = ++self::$seqFunil;
        $suf = sprintf('%04d', $n);
        $comEmail = $opt['email'] ?? true;
        $dono = array_key_exists('owner', $opt) ? $opt['owner'] : $this->angariador->id;
        $c = ['sufixo' => $suf];

        $lead = ['name' => "TESTE FLUXO Cliente {$suf}", 'phone' => '91000'.$suf, 'origin' => 'Outro', 'lead_source' => 'manual'];
        if ($comEmail) {
            $lead['email'] = "cliente.{$suf}@teste.invalid";
        }
        if ($dono) {
            $lead['owner_id'] = $dono;
        }
        $this->bo()->post(route('admin.v2.leads.store'), $lead)->assertSessionHasNoErrors();
        $cliente = Client::where('phone_key', '91000'.$suf)->firstOrFail();
        $c['client_id'] = $cliente->id;
        $c['email'] = $cliente->email;

        $this->bo()->post(route('admin.v2.form-proposals.store'), [
            'client_id' => $cliente->id, 'title' => "TESTE FLUXO Pedido {$suf}", 'brand' => 'BMW', 'model' => 'i4', 'fuel' => 'eletrico',
            'year_min' => 2021, 'km_max' => 80000, 'budget' => 35000, 'message' => 'Pedido de teste (fictício).',
        ])->assertSessionHasNoErrors();
        $fp = FormProposal::where('client_id', $cliente->id)->firstOrFail();
        $c['form_proposal_id'] = $fp->id;

        $payloads = [
            'A' => ['brand' => 'BMW', 'model' => 'i4', 'version' => 'eDrive40', 'year' => 2022, 'mileage' => 68400, 'price' => 29500, 'fuel' => 'eletrico'],
            'B' => ['brand' => 'Tesla', 'model' => 'Model 3', 'version' => 'Long Range', 'year' => 2021, 'mileage' => 52000, 'price' => 24900, 'fuel' => 'eletrico'],
            'C' => ['brand' => 'BMW', 'model' => 'X1', 'version' => 'xDrive20d', 'year' => 2020, 'mileage' => 71000, 'price' => 27000, 'fuel' => 'diesel'],
        ];
        foreach ($payloads as $k => $dados) {
            $dados['listing_url'] = "https://exemplo.invalid/{$suf}-{$k}";
            $this->bo()->post(route('admin.v2.form-proposals.opportunities.store', $fp->id), $dados)->assertSessionHasNoErrors();
            $c['opp'.$k] = ImportOpportunity::where('listing_url', $dados['listing_url'])->firstOrFail()->id;
        }

        if (! ($opt['cotacoes'] ?? true)) {
            return $c;
        }
        foreach (['A', 'B'] as $k) {
            $this->bo()->post(route('admin.v2.proposals.store'), $this->payloadCotacao($c, $k))->assertSessionHasNoErrors();
            $opp = ImportOpportunity::findOrFail($c['opp'.$k]);
            $c['prop'.$k] = $opp->proposal_id;
            $c['code'.$k] = Proposal::findOrFail($opp->proposal_id)->proposal_code;
        }

        if ($opt['enviar'] ?? true) {
            foreach (['A', 'B'] as $k) {
                $this->bo()->patchJson(route('admin.v2.proposals.updateStatus', $c['prop'.$k]), ['status' => 'Enviado', 'notify' => 0])->assertOk();
            }
        }

        return $c;
    }

    protected function payloadCotacao(array $c, string $k, array $extra = []): array
    {
        $o = ImportOpportunity::findOrFail($c['opp'.$k]);

        return $extra + [
            'form_proposal_id' => $c['form_proposal_id'], 'import_opportunity_id' => $o->id, 'client_id' => $c['client_id'],
            'brand' => $o->brand, 'model' => $o->model, 'version' => $o->version, 'fuel' => 'Elétrico',
            'proposed_car_value' => $o->price, 'proposed_car_mileage' => $o->mileage, 'proposed_car_year_month' => '03/'.$o->year, 'url' => $o->listing_url,
            'transport_cost' => 1350, 'ipo_cost' => 100, 'imt_cost' => 45, 'registration_cost' => 65, 'isv_cost' => 1500,
            'license_plate_cost' => 20, 'inspection_commission_cost' => 350, 'commission_cost' => 615,
        ];
    }

    /** Funil + aceitação pública da cotação B (cria a convertida). */
    protected function funilComConvertida(array $opt = []): array
    {
        $c = $this->novoFunil($opt);
        $this->comoVisitante()->post(route('proposals.accept', $c['codeB']), $this->dadosAceitacao())->assertSessionHas('success');
        $c['conv_id'] = ConvertedProposal::where('proposal_id', $c['propB'])->firstOrFail()->id;

        return $c;
    }

    protected function funilComViatura(array $opt = []): array
    {
        $c = $this->funilComConvertida($opt);
        $this->bo()->put(route('admin.v2.converted-proposals.update', $c['conv_id']), [
            'client_id' => $c['client_id'], 'vin' => 'LRWTESTFLUX000002', 'matricula_origem' => 'TESTE-ORIG-1', 'matricula_destino' => 'TT-00-TT',
        ])->assertSessionHasNoErrors();
        $this->bo()->post(route('admin.v2.converted-proposals.create-vehicle', $c['conv_id']))->assertSessionHas('success');
        $conv = ConvertedProposal::findOrFail($c['conv_id']);
        $c['vehicle_id'] = $conv->v3_vehicle_id;
        $c['legalization_id'] = Legalization::where('v3_vehicle_id', $conv->v3_vehicle_id)->firstOrFail()->id;

        return $c;
    }

    // ------------------------------------------------------------------
    // Contagens (antes/depois)
    // ------------------------------------------------------------------

    /** @return array<string,int> */
    protected function contagens(): array
    {
        return [
            'convertidas' => ConvertedProposal::count(),
            'viaturas' => V3Vehicle::count(),
            'legalizacoes' => Legalization::count(),
            'expenses' => Expense::count(),
            'financial_movements' => FinancialMovement::count(),
            'tarefas' => Task::count(),
            'clientes' => Client::count(),
            'pedidos' => FormProposal::count(),
            'oportunidades' => ImportOpportunity::count(),
            'cotacoes' => Proposal::count(),
            'atividades' => LeadActivity::count(),
        ];
    }

    /** @return array<string,int> diferenças (só as que mudam) */
    protected function diff(array $antes, array $depois): array
    {
        $d = [];
        foreach ($depois as $k => $v) {
            if (($antes[$k] ?? 0) !== $v) {
                $d[$k] = $v - ($antes[$k] ?? 0);
            }
        }

        return $d;
    }

    /** Duplicados de movimentos da convertida por (source_type, source_id). */
    protected function movimentosDuplicados(int $convId): array
    {
        return Expense::fromConvertedProposal($convId)->selectRaw('source_type, count(*) as n')->groupBy('source_type')->havingRaw('count(*) > 1')->pluck('n', 'source_type')->all();
    }

    protected function movimentosDe(int $convId): int
    {
        return Expense::fromConvertedProposal($convId)->count();
    }
}
