<?php

namespace Tests\Fluxo;

use App\Mail\OverdueCommissionMail;
use App\Models\Client;
use App\Models\ConvertedProposal;
use App\Models\ConvertedProposalDocument;
use App\Models\Expense;
use App\Models\FinancialMovement;
use App\Models\FormProposal;
use App\Models\ImportOpportunity;
use App\Models\LeadActivity;
use App\Models\Legalization;
use App\Models\Proposal;
use App\Models\Seller;
use App\Models\StatusProposalHistory;
use App\Models\Task;
use App\Models\User;
use App\Models\V3Vehicle;
use App\Permissions\PermissionSynchronizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Fluxo\Concerns\UsaMysqlIsolado;
use Tests\TestCase;

/**
 * CENÁRIO PRINCIPAL: importação POR CONTA DO CLIENTE, da lead à entrega.
 *
 * Tudo corre por pedidos HTTP às MESMAS rotas/controllers do backoffice e da
 * página pública (actingAs de um utilizador do perfil real "Importador", com as
 * permissões geradas a partir de config/permissions.php). Só corre em MySQL
 * local (guarda do trait UsaMysqlIsolado) com mailer "array" e Http::fake.
 *
 * Regras de leitura deste ficheiro:
 *  - Cada etapa é um método etapaNN_*; o teste "percurso completo" chama-os por ordem
 *    e os testes finos partilham o mesmo andaime (ate(N) corre as etapas 1..N).
 *  - Cada etapa afirma resposta HTTP, BD, relações, valores, emails/tarefas e regista
 *    em self::$evidencia (esperado vs observado + estado).
 *  - Comentários "BUG:" marcam comportamento ATUAL que é defeito; o teste fixa o que
 *    acontece hoje (não o que devia acontecer). Nada da implementação foi corrigido.
 *  - Métodos prepararXSemAcaoNoBO fazem preparação isolada onde NÃO existe ação no BO
 *    ou a ação falha: NÃO contam como conclusão pela interface.
 *
 * Estados de evidência: OK, BUG, FRICCAO (usabilidade), PREPARACAO (sem ação no BO), INFO.
 */
class FluxoLeadAteEntregaTest extends TestCase
{
    use UsaMysqlIsolado {
        setUp as protected setUpMysqlIsolado;
    }

    /** Evidência acumulada de TODOS os testes da classe (impressa no fim). */
    private static array $evidencia = [];

    private static float $inicio = 0.0;

    private static bool $migracoesCompletadas = false;

    /** Tipo das colunas ENUM tal como as migrations do repositório as criam (antes do alinhamento). */
    private static array $esquemaOriginal = [];

    /** Instante "zero" do cenário (segunda-feira de manhã). */
    private Carbon $t0;

    private User $operador;      // perfil real Importador (opera todo o funil)
    private User $admin;         // perfil admin (Gate::before)
    private User $angariador;    // perfil real angariador (dono da lead)
    private User $semFunil;      // perfil real cms (sem permissões do funil)
    private User $semPerfil;     // sem nenhum perfil

    /** Estado partilhado entre etapas (ids, códigos, etc.). */
    private array $c = [];

    // ------------------------------------------------------------------
    // Andaime
    // ------------------------------------------------------------------

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$inicio = microtime(true);
    }

    public static function tearDownAfterClass(): void
    {
        $tempo = round(microtime(true) - self::$inicio, 1);
        $linhas = ["\n[fluxo] EVIDÊNCIA ({$tempo}s)"];
        foreach (self::$evidencia as $e) {
            $linhas[] = sprintf("  [%s] %-9s E%s %s | rota: %s\n      esperado: %s\n      observado: %s  (linha %s)",
                $e['teste'], $e['estado'], $e['etapa'], $e['acao'], $e['rota'], $e['esperado'], $e['observado'], $e['linha']);
        }
        fwrite(STDERR, implode("\n", $linhas)."\n");
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        $this->setUpMysqlIsolado(); // guarda + migrações + TRUNCATE
        $this->completarMigracoesEmFalta();

        // Nada real: mailer array, filas síncronas, sem HTTP externo, discos falsos.
        $this->assertSame('array', config('mail.default'));
        Http::fake();
        Notification::fake();
        Storage::fake('local');
        Storage::fake('public');
        Cache::flush();
        // O View Composer global lê menus/logo da cache: pré-preencher evita depender do CMS.
        Cache::forever('frontend_menus', collect());
        Cache::forever('site_logo', '');
        config(['permissions.enforce' => false]);

        $this->t0 = Carbon::parse('2026-11-02 10:00:00');
        $this->travelTo($this->t0);

        // Permissões REAIS por perfil: catálogo + perfis de config/permissions.php.
        $sync = app(PermissionSynchronizer::class);
        $sync->syncCatalog();
        $sync->applyProfiles();
        Role::findOrCreate('admin', 'web');

        // Definições da empresa (em produção existem; a página pública, o contrato e os emails leem-nas).
        foreach (['name' => 'TESTE FLUXO Izzycar', 'vat_number' => '999999990', 'address' => 'Rua de Teste, Lisboa', 'iban' => 'PT50000000000000000000000',
            'phone' => '910000000', 'email' => 'geral.fluxo@teste.invalid', 'facebook' => 'https://facebook.invalid/x', 'insta' => 'https://instagram.invalid/x',
            'init_payment' => '50', 'final_payment' => '50'] as $label => $value) {
            \App\Models\Setting::create(['title' => $label, 'label' => $label, 'type' => 'text', 'value' => $value]);
        }

        $this->vehicleCatalog(['BMW' => ['i4', 'X1'], 'Tesla' => ['Model 3']]);

        $this->operador = $this->backofficeUser('Importador', ['name' => 'TESTE FLUXO Operador', 'email' => 'operador.fluxo@teste.invalid']);
        $this->admin = $this->backofficeUser('admin', ['name' => 'TESTE FLUXO Admin', 'email' => 'admin.fluxo@teste.invalid']);
        $this->angariador = $this->backofficeUser('angariador', [
            'name' => 'TESTE FLUXO Angariador', 'email' => 'angariador.fluxo@teste.invalid', 'commission_fixed_value' => 100,
        ]);
        $this->semFunil = $this->backofficeUser('cms', ['name' => 'TESTE FLUXO CMS', 'email' => 'cms.fluxo@teste.invalid']);
        $this->semPerfil = User::factory()->create(['name' => 'TESTE FLUXO SemPerfil', 'last_name' => 'Teste', 'email' => 'semperfil.fluxo@teste.invalid', 'password' => 'secret']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * ACHADO DE INFRA (não é do cenário): num migrate:fresh em MySQL 8 duas migrations
     * falham por ordem (ver tests/Fluxo/README.md) e deixam por criar clients.angariador_code,
     * form_proposals.angariador_code e converted_proposals.comprovativo_pagamento, que existem
     * em produção (migrations aplicadas pela ordem histórica). Aqui corremos só essas duas
     * migrations, já com as dependências criadas, para a base de teste ficar equivalente à de
     * produção. Não altera migrations nem a guarda; só atua em izzycar_fluxo_teste.
     */
    private function completarMigracoesEmFalta(): void
    {
        if (self::$migracoesCompletadas) {
            return;
        }
        self::garantirBaseIsolada(DB::connection()->getConfig(), DB::connection()->selectOne('SELECT DATABASE() AS db')->db ?? null);
        foreach (['2026_08_05_110100_add_angariador_code_to_clients_and_form_proposals.php', '2026_08_05_150000_add_comprovativo_pagamento_to_converted_proposals_table.php'] as $ficheiro) {
            Artisan::call('migrate', ['--path' => 'database/migrations/'.$ficheiro, '--force' => true]);
        }

        // ACHADO DE ESQUEMA (DRIFT migrations <-> código): estas colunas são ENUM nas migrations e o
        // código grava valores fora do ENUM ('Enviado', 'Cancelada', 'Pagamento do Carro', origens do
        // select do BO). Em MySQL estrito isso dá 500 (Data truncated). Nenhuma migration do repositório
        // as alarga, logo produção só funciona se alguém as alterou à mão (CONFIRMAR em produção com
        // SHOW COLUMNS). Registamos o tipo original (test_drift_*) e alargamos SÓ na base de teste, para
        // o cenário poder correr com o esquema que o código pressupõe.
        foreach ([['proposals', 'status'], ['converted_proposals', 'status'], ['clients', 'origin']] as [$tabela, $coluna]) {
            self::$esquemaOriginal["{$tabela}.{$coluna}"] = DB::selectOne(
                'SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [self::BASE_PERMITIDA, $tabela, $coluna]
            )->t;
        }
        DB::statement('ALTER TABLE `proposals` MODIFY `status` VARCHAR(255) NULL');
        DB::statement("ALTER TABLE `converted_proposals` MODIFY `status` VARCHAR(255) NOT NULL DEFAULT 'Iniciada'");
        DB::statement('ALTER TABLE `clients` MODIFY `origin` VARCHAR(255) NULL');

        self::$migracoesCompletadas = true;
    }

    /** Regista evidência (esperado vs observado). */
    private function ev(string $etapa, string $acao, string $rota, string $esperado, string $observado, string $estado = 'OK'): void
    {
        self::$evidencia[] = [
            'teste' => $this->name(),
            'etapa' => $etapa,
            'acao' => $acao,
            'rota' => $rota,
            'esperado' => $esperado,
            'observado' => $observado,
            'estado' => $estado,
            'linha' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['line'] ?? '?',
        ];
    }

    /** Pedidos como visitante (página pública). */
    private function comoVisitante(): static
    {
        $this->app['auth']->forgetGuards();

        return $this;
    }

    private function bo(?User $user = null): static
    {
        $this->actingAs($user ?? $this->operador);

        return $this;
    }

    /** Mensagens guardadas pelo transporte "array" (nada sai de verdade). */
    private function emails(): Collection
    {
        return Mail::mailer()->getSymfonyTransport()->messages()->map(fn ($m) => $m->getOriginalMessage());
    }

    /** @return list<string> destinatários "para" de todos os emails */
    private function destinatarios(): array
    {
        return $this->emails()->map(fn ($m) => collect($m->getTo())->map->getAddress()->implode(','))->values()->all();
    }

    private function emailsPara(string $endereco): Collection
    {
        return $this->emails()->filter(fn ($m) => collect($m->getTo())->map->getAddress()->contains($endereco))->values();
    }

    /** Corre as etapas 1..$n (andaime dos testes finos). */
    private function ate(int $n): void
    {
        $etapas = ['etapa01_criarLead', 'etapa02_pedidoDeImportacao', 'etapa03_tresOportunidades', 'etapa04_duasCotacoes', 'etapa05_enviarCotacoes',
            'etapa06_expiracao', 'etapa07_aceitarPelaPaginaPublica', 'etapa08_convertida', 'etapa09_leadParaCliente', 'etapa10_viatura',
            'etapa11_etapasSeguintes', 'etapa12_coerenciaEPermissoes'];
        foreach (array_slice($etapas, 0, $n) as $metodo) {
            $this->{$metodo}();
        }
    }

    // ==================================================================
    // E01 — criar lead (rota BO)
    // ==================================================================
    private function etapa01_criarLead(): void
    {
        $resp = $this->bo()->get(route('admin.v2.leads.create'));
        $resp->assertOk();

        $resp = $this->bo()->post(route('admin.v2.leads.store'), [
            'name' => 'TESTE FLUXO Cliente Importação',
            'email' => 'cliente.fluxo@teste.invalid',
            'phone' => '910000001',
            // "Outro": a única origem do select do BO que a coluna ENUM de MySQL aceita por omissão
            // (ver test_drift_esquema_enums_migrations_vs_codigo).
            'origin' => 'Outro',
            'lead_source' => 'manual',
            'observation' => 'Lead de teste do fluxo completo (dados fictícios).',
            'owner_id' => $this->angariador->id,
        ]);

        $lead = Client::where('email', 'cliente.fluxo@teste.invalid')->firstOrFail();
        $resp->assertRedirect(route('admin.v2.leads.show', $lead->id));
        $this->c['client_id'] = $lead->id;

        $this->assertTrue($lead->is_lead);
        $this->assertSame('nova', $lead->lead_status);
        $this->assertSame('manual', $lead->lead_source);
        $this->assertSame($this->angariador->id, (int) $lead->owner_id);
        $this->assertNull($lead->converted_at);
        $this->assertSame('910000001', $lead->phone_key);
        $this->assertSame('cliente.fluxo@teste.invalid', $lead->email_normalized);
        // A lead criada no BO com dono NÃO grava angariador_code (só o formulário público com ?angariador=).
        $this->assertNull($lead->angariador_code);
        $this->assertSame(1, LeadActivity::where('client_id', $lead->id)->where('title', 'Lead criado manualmente')->count());

        // Contactos da lead: nota/chamada pela rota real.
        $this->bo()->post(route('admin.v2.leads.activity', $lead->id), [
            'type' => 'call', 'title' => 'Chamada de qualificação (teste)', 'body' => 'Cliente quer importar um BMW i4 ou Tesla Model 3.',
        ])->assertRedirect();
        $act = LeadActivity::where('client_id', $lead->id)->where('type', 'call')->sole();
        $this->assertSame($this->operador->id, (int) $act->user_id);

        // Duplicado: mesmo email volta a ser bloqueado (nunca forçável).
        $this->bo()->post(route('admin.v2.leads.store'), ['name' => 'TESTE FLUXO Duplicado', 'email' => 'cliente.fluxo@teste.invalid'])
            ->assertSessionHasErrors('duplicate');
        $this->assertSame(1, Client::count());

        $this->bo()->get(route('admin.v2.leads.show', $lead->id))->assertOk()->assertSee('TESTE FLUXO Cliente Importação');

        $this->ev('01', 'Criar lead com contactos e dono (angariador)', 'POST admin.v2.leads.store + activity',
            'redirect para a ficha; Client is_lead=true, nova, owner_id=angariador; timeline com "Lead criado manualmente"; email repetido bloqueado',
            "lead #{$lead->id} criada, owner={$lead->owner_id}, angariador_code=null; atividade call gravada por user {$act->user_id}");
        $this->ev('01', 'angariador_code na lead criada no BO', 'POST admin.v2.leads.store',
            'dono atribuído => angariador_code do dono', 'owner_id preenchido mas angariador_code=null (só o formulário público com ?angariador= o grava)', 'INFO');
    }

    // ==================================================================
    // E02 — pedido de importação (rota BO; o do site tem teste próprio)
    // ==================================================================
    private function etapa02_pedidoDeImportacao(): void
    {
        $resp = $this->bo()->post(route('admin.v2.form-proposals.store'), [
            'client_id' => $this->c['client_id'],
            'title' => 'TESTE FLUXO Pedido BMW/Tesla',
            'brand' => 'BMW',
            'model' => 'i4',
            'fuel' => 'eletrico',
            'year_min' => 2021,
            'km_max' => 80000,
            'budget' => 35000,
            'message' => 'Pedido de teste (fictício).',
        ]);

        $fp = FormProposal::where('client_id', $this->c['client_id'])->sole();
        $resp->assertRedirect(route('admin.v2.form-proposals.show', $fp->id).'#oportunidades');
        $this->c['form_proposal_id'] = $fp->id;

        $this->assertSame('em_analise', $fp->status);          // pedido manual nasce em análise (do site: "novo")
        $this->assertSame(FormProposal::ORIGIN_MANUAL, $fp->origin);
        $this->assertSame($this->operador->id, (int) $fp->created_by);
        $this->assertSame('TESTE FLUXO Cliente Importação', $fp->name);
        $this->assertSame('cliente.fluxo@teste.invalid', $fp->email);
        $this->assertNull($fp->proposal_id);
        $this->assertNull($fp->angariador_code);
        $this->assertSame(1, LeadActivity::where('client_id', $this->c['client_id'])->where('title', 'Pedido de importação criado')->count());

        $this->bo()->get(route('admin.v2.form-proposals.show', $fp->id))->assertOk()->assertSee('TESTE FLUXO Pedido BMW/Tesla');

        // Marca fora do catálogo é recusada (o select do catálogo manda).
        $this->bo()->post(route('admin.v2.form-proposals.store'), ['client_id' => $this->c['client_id'], 'brand' => 'MarcaInexistente'])
            ->assertSessionHasErrors('brand');
        $this->assertSame(1, FormProposal::count());

        $this->ev('02', 'Registar pedido de importação (manual no BO)', 'POST admin.v2.form-proposals.store',
            'FormProposal ligado ao cliente, origin=manual, status=em_analise, timeline "Pedido de importação criado"',
            "pedido #{$fp->id} status={$fp->status} origin={$fp->origin} created_by={$fp->created_by}");
    }

    // ==================================================================
    // E03 — três oportunidades no mesmo pedido
    // ==================================================================
    private function etapa03_tresOportunidades(): void
    {
        $seller = Seller::create(['name' => 'TESTE FLUXO Stand Alemão', 'country' => 'DE']);
        $contact = $seller->contacts()->create(['name' => 'TESTE FLUXO Contacto', 'email' => 'stand.fluxo@teste.invalid', 'is_primary' => true]);
        $this->c['seller_id'] = $seller->id;
        $this->c['seller_contact_id'] = $contact->id;

        $payloads = [
            'A' => ['brand' => 'BMW', 'model' => 'i4', 'version' => 'eDrive40', 'year' => 2022, 'mileage' => 68400, 'price' => 29500, 'fuel' => 'eletrico',
                'vin' => 'WBYTESTFLUX000001', 'country' => 'DE', 'listing_url' => 'https://exemplo.invalid/anuncio-a',
                'seller_id' => $seller->id, 'seller_contact_id' => $contact->id],
            'B' => ['brand' => 'Tesla', 'model' => 'Model 3', 'version' => 'Long Range', 'year' => 2021, 'mileage' => 52000, 'price' => 24900, 'fuel' => 'eletrico',
                'vin' => 'LRWTESTFLUX000002', 'country' => 'DE', 'listing_url' => 'https://exemplo.invalid/anuncio-b'],
            'C' => ['brand' => 'BMW', 'model' => 'X1', 'version' => 'xDrive20d', 'year' => 2020, 'mileage' => 71000, 'price' => 27000, 'fuel' => 'diesel',
                'listing_url' => 'https://exemplo.invalid/anuncio-c'],
        ];
        foreach ($payloads as $chave => $dados) {
            $this->bo()->post(route('admin.v2.form-proposals.opportunities.store', $this->c['form_proposal_id']), $dados)
                ->assertRedirect(route('admin.v2.form-proposals.show', $this->c['form_proposal_id']).'#oportunidades')
                ->assertSessionHasNoErrors();
            $this->c['opp'.$chave] = ImportOpportunity::where('listing_url', $dados['listing_url'])->sole()->id;
        }

        $this->assertSame(3, ImportOpportunity::where('form_proposal_id', $this->c['form_proposal_id'])->count());
        foreach (['A', 'B', 'C'] as $k) {
            $o = ImportOpportunity::findOrFail($this->c['opp'.$k]);
            $this->assertSame('por_contactar', $o->status->value);
            $this->assertSame('nao_contactado', $o->contact_status->value);
            $this->assertNull($o->proposal_id);
        }
        $this->assertSame('WBYTESTFLUX000001', ImportOpportunity::find($this->c['oppA'])->vin);
        // Criar oportunidades não mexe na lead nem no pedido (sem timeline).
        $this->assertSame('em_analise', FormProposal::find($this->c['form_proposal_id'])->status);
        $this->assertSame(0, LeadActivity::where('client_id', $this->c['client_id'])->where('title', 'like', '%portunidade%')->count());

        // Contacto com o vendedor da oportunidade A: avança por_contactar -> contactado.
        $this->bo()->post(route('admin.v2.form-proposals.opportunities.contacts.store', [$this->c['form_proposal_id'], $this->c['oppA']]), [
            'contacted_at' => now()->format('Y-m-d H:i:s'), 'method' => 'email', 'type' => 'enviado', 'message' => 'Pedido de disponibilidade (teste).',
        ])->assertRedirect();
        $a = ImportOpportunity::find($this->c['oppA']);
        $this->assertSame('contactado', $a->status->value);

        $this->bo()->get(route('admin.v2.form-proposals.show', $this->c['form_proposal_id']))
            ->assertOk()->assertSee('eDrive40')->assertSee('Long Range')->assertSee('xDrive20d');

        $this->ev('03', '3 oportunidades no mesmo pedido', 'POST admin.v2.form-proposals.opportunities.store (x3) + contacts.store',
            '3 ImportOpportunity por_contactar sem cotação; sem timeline; contacto avança A para contactado',
            'oportunidades A/B/C=#'.implode('/#', [$this->c['oppA'], $this->c['oppB'], $this->c['oppC']]).'; A passou a '.$a->status->value);
    }

    // ==================================================================
    // E04 — 2 oportunidades viram cotações; a 3.ª fica sem cotação
    // ==================================================================
    private function payloadCotacao(string $opp, array $extra = []): array
    {
        $o = ImportOpportunity::findOrFail($this->c['opp'.$opp]);

        return $extra + [
            'form_proposal_id' => $this->c['form_proposal_id'],
            'import_opportunity_id' => $o->id,
            'client_id' => $this->c['client_id'],
            'brand' => $o->brand,
            'model' => $o->model,
            'version' => $o->version,
            'fuel' => 'Elétrico',
            'proposed_car_value' => $o->price,
            'proposed_car_mileage' => $o->mileage,
            'proposed_car_year_month' => '03/'.$o->year,
            'url' => $o->listing_url,
            'transport_cost' => 1350,
            'ipo_cost' => 100,
            'imt_cost' => 45,
            'registration_cost' => 65,
            'isv_cost' => 1500,
            'license_plate_cost' => 20,
            'inspection_commission_cost' => 350,
            'commission_cost' => 615,
        ];
    }

    private function etapa04_duasCotacoes(): void
    {
        foreach (['A', 'B'] as $k) {
            $oppId = $this->c['opp'.$k];
            $this->bo()->get(route('admin.v2.proposals.createFromOpportunity', $oppId))
                ->assertOk()->assertSee('name="import_opportunity_id" value="'.$oppId.'"', false);

            $this->bo()->post(route('admin.v2.proposals.store'), $this->payloadCotacao($k))
                ->assertRedirect(route('admin.v2.proposals.index'))->assertSessionHasNoErrors();

            $opp = ImportOpportunity::findOrFail($oppId);
            $this->assertNotNull($opp->proposal_id);
            $this->c['prop'.$k] = $opp->proposal_id;
        }
        $this->assertNotSame($this->c['propA'], $this->c['propB']);

        foreach (['A', 'B'] as $k) {
            $p = Proposal::findOrFail($this->c['prop'.$k]);
            $this->assertSame('Pendente', $p->status);               // nasce sempre Pendente
            $this->assertSame($this->c['client_id'], (int) $p->client_id);
            $this->assertMatchesRegularExpression('/^[A-Z0-9]{9,12}$/', $p->proposal_code);
            $this->assertEquals(ImportOpportunity::find($this->c['opp'.$k])->price, $p->proposed_car_value);
            $this->assertSame('selecionado', ImportOpportunity::find($this->c['opp'.$k])->status->value);
            $this->c['code'.$k] = $p->proposal_code;
        }
        $this->assertNotSame($this->c['codeA'], $this->c['codeB']);

        // A 3.ª oportunidade não é tocada.
        $c = ImportOpportunity::findOrFail($this->c['oppC']);
        $this->assertNull($c->proposal_id);
        $this->assertSame('por_contactar', $c->status->value);

        // O pedido guarda só a 1.ª cotação em proposal_id (a 2.ª liga-se pela oportunidade).
        $fp = FormProposal::find($this->c['form_proposal_id']);
        $this->assertSame($this->c['propA'], (int) $fp->proposal_id);
        $this->assertSame('em_analise', $fp->status);

        // Uma cotação por oportunidade: repetir "criar cotação" abre a existente.
        $this->bo()->get(route('admin.v2.proposals.createFromOpportunity', $this->c['oppA']))
            ->assertRedirect(route('admin.v2.proposals.edit', $this->c['propA']));
        $this->bo()->post(route('admin.v2.proposals.store'), $this->payloadCotacao('A'))
            ->assertRedirect(route('admin.v2.proposals.edit', $this->c['propA']));
        $this->assertSame(2, Proposal::count());

        $this->assertSame(2, LeadActivity::where('client_id', $this->c['client_id'])->where('title', 'like', 'Cotação #% criada%')->count());
        $this->assertSame(0, $this->emails()->count(), 'Cotação Pendente não envia emails.');

        $this->ev('04', '2 oportunidades viram cotações; a 3.ª fica sem cotação', 'GET createFromOpportunity + POST admin.v2.proposals.store (x2)',
            '2 cotações Pendente com proposal_code único; A/B selecionado; C sem cotação; pedido.proposal_id = 1.ª; sem emails',
            "cotações #{$this->c['propA']} ({$this->c['codeA']}) e #{$this->c['propB']} ({$this->c['codeB']}); C intacta; repetir devolve a existente");
    }

    // ==================================================================
    // E05 — enviar as 2 cotações (estado Enviado dispara o email do observer)
    // ==================================================================
    private function etapa05_enviarCotacoes(): void
    {
        foreach (['A', 'B'] as $k) {
            $this->bo()->patchJson(route('admin.v2.proposals.updateStatus', $this->c['prop'.$k]), ['status' => 'Enviado', 'notify' => 1])
                ->assertOk()->assertJson(['success' => true, 'status' => 'Enviado']);
            $this->assertSame('Enviado', Proposal::find($this->c['prop'.$k])->status);
        }

        // Emails na fila array: 1 ao cliente + 1 ao angariador por cotação.
        $this->assertCount(2, $this->emailsPara('cliente.fluxo@teste.invalid'));
        $this->assertCount(2, $this->emailsPara('angariador.fluxo@teste.invalid'));
        $this->assertCount(4, $this->emails());
        $linkA = route('proposals.detail', $this->c['codeA']);
        $this->assertTrue($this->emailsPara('cliente.fluxo@teste.invalid')->contains(fn ($m) => str_contains((string) $m->getHtmlBody().(string) $m->getTextBody(), $linkA)),
            'O email ao cliente tem o link público da cotação A.');

        $lead = Client::find($this->c['client_id']);
        $this->assertNotNull($lead->next_followup_at);                       // follow-up automático
        $this->assertEquals(now()->addDays(2)->toDateTimeString(), $lead->next_followup_at->toDateTimeString());
        $this->assertSame(2, LeadActivity::where('client_id', $lead->id)->where('title', 'like', 'Cotação #% enviada ao cliente')->count());
        // O follow-up só é agendado uma vez (a 2.ª cotação não o substitui).
        $this->assertSame(1, LeadActivity::where('client_id', $lead->id)->where('title', 'like', 'Follow-up agendado automaticamente%')->count());
        $this->assertSame('nova', $lead->lead_status, 'Enviar cotação não muda lead_status.');

        // Sem alteração de estado não há email repetido.
        $this->bo()->patchJson(route('admin.v2.proposals.updateStatus', $this->c['propA']), ['status' => 'Enviado', 'notify' => 1])->assertOk();
        $this->assertCount(4, $this->emails());

        $this->c['emails_pos_envio'] = $this->emails()->count();
        $this->ev('05', 'Enviar as 2 cotações (Pendente -> Enviado)', 'PATCH admin.v2.proposals.updateStatus (x2)',
            '4 emails (cliente+angariador x2) com o link /proposta/{code}; follow-up +2 dias uma só vez; timeline "enviada ao cliente"',
            'emails='.implode(' | ', $this->destinatarios()).'; follow-up='.$lead->next_followup_at->toDateTimeString().'; lead_status='.$lead->lead_status);
        $this->ev('05', 'WhatsApp', 'n/a', 'envio de WhatsApp pelo BO', 'não existe: só o link wa.me do formulário (envio manual fora do sistema)', 'PREPARACAO');
    }


    // ==================================================================
    // E06 — "expira por falta de resposta" (cotação A): (a) calculada, (b) manual
    // ==================================================================
    private function dadosAceitacaoPublica(array $extra = []): array
    {
        return $extra + [
            'address' => 'Rua de Teste 1',
            'postal_code' => '0000-000',
            'city' => 'Cidade Teste',
            'vat_number' => '999999990',
            'identification_number' => 'TESTE-CC-0001',
            'validate_identification_number' => '2031-03-15',
        ];
    }

    private function etapa06_expiracao(): void
    {
        $a = $this->c['propA'];

        // ---- (a) EXPIRAÇÃO CALCULADA: 16 dias depois da criação (prazo = 15) ----
        $this->travelTo($this->t0->copy()->addDays(16));
        $pa = Proposal::findOrFail($a);
        $this->assertTrue($pa->isExpired());

        $this->comoVisitante()->get(route('proposals.detail', $this->c['codeA']))
            ->assertOk()->assertSee('Cotação Expirada');
        $this->comoVisitante()->post(route('proposals.accept', $this->c['codeA']), $this->dadosAceitacaoPublica())
            ->assertSessionHas('error', 'Esta cotação já expirou. Contacte-nos para receber uma cotação atualizada.');

        // Não existe expiração PERSISTIDA: o estado gravado continua "Enviado", nada foi escrito.
        $pa->refresh();
        $this->assertSame('Enviado', $pa->status);
        $this->assertSame(0, ConvertedProposal::count());
        $this->assertSame('selecionado', ImportOpportunity::find($this->c['oppA'])->status->value);
        $this->assertSame(0, Proposal::where('status', 'like', 'Expirad%')->count());
        $this->assertSame('Enviado', Proposal::find($this->c['propB'])->status, 'B também "expirou" por cálculo (criada ao mesmo tempo), mas continua Enviado.');

        // O BO continua a listar A como Enviado (filtro por estado não a apanha como expirada) e mostra só um crachá.
        $this->bo()->get(route('admin.v2.proposals.kanban'))->assertOk()->assertSee('Expirada');
        $filtrada = $this->bo()->get(route('admin.v2.proposals.index', ['status' => 'Enviado']))->assertOk();
        $this->assertContains($a, $filtrada->viewData('proposals')->pluck('id')->all(), 'Filtro Enviado continua a incluir a expirada.');

        $this->ev('06a', 'Expiração CALCULADA pelo prazo (+16 dias)', 'GET proposals.detail + POST proposals.accept (público)',
            'página pública recusa; estado gravado continua Enviado (não há expiração persistida)',
            'GET mostra "Cotação Expirada"; POST aceitar => erro "já expirou"; proposals.status=Enviado; 0 convertidas; oportunidade A ainda selecionado');

        // ---- (b) ESTADO MANUAL "Sem resposta" pela rota BO real (no mesmo instante, +16 dias) ----
        $antes = $this->emails()->count();
        $this->bo()->patchJson(route('admin.v2.proposals.updateStatus', $a), ['status' => 'Sem resposta'])
            ->assertOk()->assertJson(['success' => true, 'status' => 'Sem resposta']);
        $this->assertSame('Sem resposta', Proposal::find($a)->status);
        $this->assertSame($antes, $this->emails()->count(), '"Sem resposta" não envia emails.');
        // Não toca nas oportunidades (só "Reprovada" rejeita).
        $this->assertSame('selecionado', ImportOpportunity::find($this->c['oppA'])->status->value);
        // Inconsistência: a mudança rápida (kanban/lista) NÃO escreve na timeline da lead ("estado alterado"
        // só existe ao gravar pelo formulário de edição, ProposalService::update).
        $this->assertSame(0, LeadActivity::where('client_id', $this->c['client_id'])->where('title', "Cotação #{$a}: estado alterado para Sem resposta")->count());
        // Continua a mostrar a página de expirada por causa do cálculo (os dois critérios são independentes).
        $this->comoVisitante()->get(route('proposals.detail', $this->c['codeA']))->assertOk()->assertSee('Cotação Expirada');

        $this->ev('06b', 'Estado manual "Sem resposta" (cotação A)', 'PATCH admin.v2.proposals.updateStatus',
            'estado gravado "Sem resposta", sem emails, oportunidade intacta',
            'proposals.status="Sem resposta"; emails inalterados; oportunidade A selecionado; sem entrada na timeline da lead (só o formulário de edição a escreve); critério independente da expiração calculada');
        $this->ev('06', 'Diferenças expiração calculada vs "Sem resposta"', 'n/a',
            'um só conceito de "sem resposta/expirada"',
            'TRÊS critérios sem ligação: (1) isExpired() calculado a 15 dias desde created_at (bloqueia aceitação pública, não grava nada); (2) estado manual "Sem resposta" (não bloqueia aceitação, não toca oportunidades); (3) alerta/ação em massa de +30 dias. Nenhum comando agendado expira cotações.',
            'FRICCAO');

        // Volta ao dia 2 para a aceitação de B (A e B foram criadas ao mesmo tempo; ver README do doc).
        $this->travelTo($this->t0->copy()->addDays(2));
    }

    // ==================================================================
    // E07 — aceitar a cotação B pela página pública
    // ==================================================================
    private function etapa07_aceitarPelaPaginaPublica(): void
    {
        $b = $this->c['propB'];
        $this->comoVisitante()->get(route('proposals.detail', $this->c['codeB']))->assertOk()->assertSee('Model 3');

        $antes = $this->emails()->count();
        $resp = $this->comoVisitante()->post(route('proposals.accept', $this->c['codeB']), $this->dadosAceitacaoPublica());
        $resp->assertRedirect()->assertSessionHas('success')->assertSessionMissing('error');

        $pb = Proposal::findOrFail($b);
        $this->assertSame('Aprovada', $pb->status);                  // "Aprovada" = aceite pelo cliente
        $conv = ConvertedProposal::where('proposal_id', $b)->sole();
        $this->c['conv_id'] = $conv->id;

        // Emails (mailer array, nada real): cliente (+cc/bcc fixos), angariador e aviso interno.
        $novos = $this->emails()->slice($antes)->values();
        $this->assertCount(3, $novos, 'cliente, angariador e aviso à geral');
        $destinos = $novos->map(fn ($m) => collect($m->getTo())->map->getAddress()->implode(','))->all();
        $this->assertContains('cliente.fluxo@teste.invalid', $destinos);
        $this->assertContains('angariador.fluxo@teste.invalid', $destinos);
        $this->assertContains('geral@izzycar.pt', $destinos);       // endereço fixo no código (não sai: transporte array)
        $emailCliente = $novos->first(fn ($m) => collect($m->getTo())->map->getAddress()->contains('cliente.fluxo@teste.invalid'));
        $this->assertCount(1, $emailCliente->getAttachments(), 'Contrato em PDF anexado ao email do cliente.');
        $this->assertSame('geral@izzycar.pt', collect($emailCliente->getCc())->first()?->getAddress());

        // Contrato gerado e guardado (disco "local" falso) + documento "gerado".
        $doc = ConvertedProposalDocument::where('converted_proposal_id', $conv->id)->sole();
        $this->assertSame('gerado', $doc->tipo);
        Storage::disk('local')->assertExists($doc->caminho);
        $this->c['doc_contrato_id'] = $doc->id;

        // Dados de identificação gravados no cliente (só preenche vazios).
        $cl = Client::find($this->c['client_id']);
        $this->assertSame('999999990', $cl->vat_number);
        $this->assertSame('TESTE-CC-0001', $cl->identification_number);
        $this->assertSame('2031-03-15', $cl->validate_identification_number->format('Y-m-d'));
        $this->assertSame('Rua de Teste 1', $cl->address);

        // Segunda aceitação da mesma cotação: recusada, sem 2.ª convertida.
        $this->comoVisitante()->post(route('proposals.accept', $this->c['codeB']), $this->dadosAceitacaoPublica())
            ->assertSessionHas('error', 'Esta cotação já foi aceite.');
        $this->assertSame(1, ConvertedProposal::count());

        $this->ev('07', 'Aceitar a cotação B pela página pública', 'GET proposals.detail + POST proposals.accept',
            'B=Aprovada; convertida criada; contrato PDF guardado e enviado; emails cliente/angariador/geral; dados do cliente completados; reaceitar recusado',
            "B Aprovada; convertida #{$conv->id}; documento gerado #{$doc->id}; 3 emails ({$this->resumoDestinos($destinos)}); NIF/CC/morada gravados");
        $this->ev('07', 'Identificação do cliente na aceitação pública', 'POST proposals.accept',
            'apenas o proposal_code identifica o cliente (sem login/SMS); NIF/CC obrigatórios no browser',
            'só o proposal_code; no servidor TODOS os campos são nullable (ver test_aceitacao_publica_sem_identificacao_e_aceite)', 'FRICCAO');
    }

    private function resumoDestinos(array $d): string
    {
        return implode('; ', $d);
    }

    // ==================================================================
    // E08 — cotação convertida criada: campos
    // ==================================================================
    private function etapa08_convertida(): void
    {
        $conv = ConvertedProposal::findOrFail($this->c['conv_id']);
        $b = Proposal::findOrFail($this->c['propB']);

        $this->assertSame('Iniciada', $conv->status);
        $this->assertSame($this->c['client_id'], (int) $conv->client_id);
        $this->assertSame($b->id, (int) $conv->proposal_id);
        $this->assertSame($this->angariador->id, (int) $conv->owner_id);
        $this->assertEquals(100.00, $conv->angariador_commission);   // users.commission_fixed_value no momento
        $this->assertSame('Tesla', $conv->brand);
        $this->assertSame('Model 3', $conv->modelCar);
        $this->assertSame('Long Range', $conv->version);
        $this->assertSame('03/2021', $conv->year);                    // texto livre da cotação
        $this->assertSame(52000, (int) $conv->km);
        $this->assertEquals(350, $conv->custo_inspecao_origem);
        $this->assertEquals(1350, $conv->custo_transporte);
        $this->assertEquals(100, $conv->custo_ipo);
        $this->assertEquals(1500, $conv->isv);
        $this->assertEquals(45, $conv->custo_imt);
        $this->assertEquals(20, $conv->custo_matricula);
        $this->assertEquals(65, $conv->custo_registo_automovel);
        $this->assertEquals(24900, $conv->valor_carro);
        $this->assertEquals(615, $conv->valor_comissao);
        // Tranches: serviço = 1350+350+100+45+65+20+615 = 2545 (sem ISV nem valor do carro), 50/50.
        $this->assertEquals(2545, $conv->serviceTotal());
        $this->assertEquals(1272.50, $conv->valor_primeira_tranche);
        $this->assertEquals(1272.50, $conv->valor_segunda_tranche);
        $this->assertFalse((bool) $conv->primeira_tranche_pago);
        $this->assertFalse((bool) $conv->comissao_paga);
        $this->assertSame(24, strlen($conv->tracking_code));
        // O que NÃO vem: VIN, matrículas, viatura.
        $this->assertNull($conv->vin);
        $this->assertNull($conv->matricula_origem);
        $this->assertNull($conv->matricula_destino);
        $this->assertNull($conv->v3_vehicle_id);

        // Histórico de estados e pedido de formulário.
        $h = StatusProposalHistory::where('converted_proposal_id', $conv->id)->sole();
        $this->assertNull($h->old_status);
        $this->assertSame('Iniciada', $h->new_status);
        $this->assertSame('convertido', FormProposal::find($this->c['form_proposal_id'])->status);
        $this->assertSame(0, Expense::count(), 'A criação da convertida não gera movimentos (nada está pago).');

        // O que acontece às outras cotações/oportunidades quando uma é aceite.
        $this->assertSame('Sem resposta', Proposal::find($this->c['propA'])->status);
        $this->assertSame('selecionado', ImportOpportunity::find($this->c['oppB'])->status->value);
        $this->assertSame('selecionado', ImportOpportunity::find($this->c['oppA'])->status->value);
        $this->assertSame('por_contactar', ImportOpportunity::find($this->c['oppC'])->status->value);
        $this->assertSame(1, ConvertedProposal::count());

        // Ficha da convertida abre; o campo Ano é um <input type="number"> com valor "03/2021".
        $html = $this->bo()->get(route('admin.v2.converted-proposals.edit', $conv->id))->assertOk()->getContent();
        $anoNumerico = (bool) preg_match('/<input type="number" name="year"[^>]*value="03\/2021"/s', $html);

        $this->ev('08', 'Cotação convertida criada (campos copiados)', 'efeito de POST proposals.accept',
            'Iniciada; cliente/dono/comissão 100; dados e custos copiados; tranches 1272,50+1272,50; tracking_code; histórico null->Iniciada; pedido=convertido; sem movimentos',
            "convertida #{$conv->id} Iniciada, serviço=2545, tranches 1272,50/1272,50, comissão={$conv->angariador_commission}, VIN/matrículas/viatura=null; pedido convertido; oportunidades A,B selecionado, C por_contactar; A='Sem resposta' mantém-se");
        $this->ev('08', 'Outras cotações/oportunidades após aceitar B', 'n/a',
            'restantes cotações/oportunidades invalidadas',
            'NADA é invalidado: A continua aceitável pelo BO (e pelo público se <15d), oportunidade C continua a ser mostrada ao cliente como "outra opção"; possível 2.ª convertida do mesmo pedido', 'INFO');
        $this->ev('08', 'Campo Ano da convertida no formulário do BO', 'GET admin.v2.converted-proposals.edit',
            'campo ano preenchido e editável',
            $anoNumerico
                ? 'BUG: <input type="number" name="year" value="03/2021"> — o valor "03/2021" não é um número válido: o browser mostra vazio e guardar o formulário apaga/rejeita o ano (PUT valida year como integer)'
                : 'sem o padrão esperado',
            $anoNumerico ? 'BUG' : 'INFO');
    }

    // ==================================================================
    // E09 — lead -> cliente: o que muda e o que se preserva
    // ==================================================================
    private function etapa09_leadParaCliente(): void
    {
        $cl = Client::findOrFail($this->c['client_id']);

        $this->assertFalse($cl->is_lead);
        $this->assertNotNull($cl->converted_at);
        $this->assertEquals($this->t0->copy()->addDays(2)->toDateTimeString(), $cl->converted_at->toDateTimeString());
        // Preservado: dono, estado da lead (NÃO muda), origem, follow-up, atividades.
        $this->assertSame($this->angariador->id, (int) $cl->owner_id);
        $this->assertSame('nova', $cl->lead_status, 'lead_status nunca é atualizado pela conversão.');
        $this->assertSame('manual', $cl->lead_source);
        $this->assertNotNull($cl->next_followup_at, 'O follow-up agendado ao enviar continua ativo depois da conversão.');

        $titulos = LeadActivity::where('client_id', $cl->id)->pluck('title');
        foreach (['Lead criado manualmente', 'Chamada de qualificação (teste)', 'Pedido de importação criado', 'Lead convertido em cliente', 'Cotação aceite'] as $t) {
            $this->assertTrue($titulos->contains($t), "Falta a atividade \"{$t}\".");
        }
        $this->assertTrue($titulos->contains(fn ($t) => str_starts_with($t, 'Cotação #') && str_contains($t, 'enviada ao cliente')));
        $conversao = LeadActivity::where('client_id', $cl->id)->where('title', 'Lead convertido em cliente')->sole();
        $this->assertStringContainsString('automaticamente ao aceitar a cotação', $conversao->body);

        // Ligações Client <-> FormProposal <-> ImportOpportunity <-> Proposal <-> ConvertedProposal.
        $fp = FormProposal::findOrFail($this->c['form_proposal_id']);
        $this->assertSame($cl->id, (int) $fp->client_id);
        $this->assertEqualsCanonicalizing([$this->c['propA'], $this->c['propB']], $cl->proposals()->pluck('id')->all());
        $this->assertSame([$fp->id], $cl->formProposals()->pluck('id')->all());
        $this->assertSame(3, $fp->opportunities()->count());
        $oppB = ImportOpportunity::find($this->c['oppB']);
        $this->assertSame($this->c['propB'], (int) $oppB->proposal_id);
        $this->assertSame($fp->id, (int) $oppB->form_proposal_id);
        $conv = ConvertedProposal::find($this->c['conv_id']);
        $this->assertSame($this->c['propB'], (int) $conv->proposal_id);
        $this->assertSame([$conv->id], $cl->convertedProposals()->pluck('id')->all());

        // Navegação: a lead deixa a lista de leads e a ficha de lead; passa para clientes.
        $this->bo()->get(route('admin.v2.leads.index'))->assertOk()->assertDontSee('TESTE FLUXO Cliente Importação');
        $this->bo()->get(route('admin.v2.leads.show', $cl->id))->assertNotFound();
        $this->bo()->post(route('admin.v2.leads.convert', $cl->id))->assertNotFound();
        $this->bo()->get(route('admin.v2.clients.index'))->assertOk()->assertSee('TESTE FLUXO Cliente Importação');
        $this->bo()->get(route('admin.v2.clients.show', $cl->id))->assertOk()->assertSee('TESTE FLUXO Cliente Importação');

        $this->ev('09', 'Lead passa a cliente (automático na aceitação)', 'efeito de POST proposals.accept',
            'is_lead=false, converted_at; histórico, dono e ligações preservados; lead_status NÃO muda',
            "is_lead=false converted_at={$cl->converted_at}; lead_status='{$cl->lead_status}' (continua); owner={$cl->owner_id}; atividades preservadas; follow-up ainda agendado ({$cl->next_followup_at}); ficha de lead 404");
        $this->ev('09', 'Estado da lead depois de converter', 'n/a',
            'lead_status coerente (ex. convertida)',
            "lead_status='nova' num cliente já convertido e com follow-up ativo (o lembrete por email continua a sair): ninguém o limpa", 'FRICCAO');
    }

    // ==================================================================
    // E10 — viatura a partir da convertida (rota BO)
    // ==================================================================
    private function etapa10_viatura(): void
    {
        $conv = ConvertedProposal::findOrFail($this->c['conv_id']);

        // O BO exige preencher VIN e matrículas à mão (não vêm da oportunidade/cotação): é o que o utilizador faria.
        $this->bo()->put(route('admin.v2.converted-proposals.update', $conv->id), [
            'client_id' => $this->c['client_id'],
            'vin' => 'LRWTESTFLUX000002',
            'matricula_origem' => 'TESTE-ORIG-1',
            'matricula_destino' => 'TT-00-TT',
        ])->assertRedirect(route('admin.v2.converted-proposals.index'))->assertSessionHasNoErrors();
        $conv->refresh();
        $this->assertSame('LRWTESTFLUX000002', $conv->vin);
        $this->assertSame('Iniciada', $conv->status);
        $this->assertSame(1, StatusProposalHistory::where('converted_proposal_id', $conv->id)->count(), 'Guardar o formulário sem mudar o estado não gera histórico.');

        $resp = $this->bo()->post(route('admin.v2.converted-proposals.create-vehicle', $conv->id));
        $v = V3Vehicle::where('client_id', $this->c['client_id'])->sole();
        $resp->assertRedirect(route('admin.v3.vehicles.edit', $v->id))->assertSessionHas('success');
        $this->c['vehicle_id'] = $v->id;
        $conv->refresh();

        $this->assertSame($v->id, (int) $conv->v3_vehicle_id);
        $this->assertSame('Tesla', $v->brand);
        $this->assertSame('Model 3', $v->model);
        $this->assertSame('Long Range', $v->version);
        $this->assertSame(2021, (int) $v->year);                      // regex ao texto "03/2021"
        $this->assertSame(52000, (int) $v->kilometers);
        $this->assertSame('TT-00-TT', $v->registration);              // matrícula de destino (copiada na criação)
        $this->assertTrue((bool) $v->is_imported);
        $this->assertSame('reservado', $v->status);
        $this->assertFalse((bool) $v->show_online);
        $this->assertSame($this->c['client_id'], (int) $v->client_id);
        $this->assertNotEmpty($v->reference);
        $this->assertNull($v->seller_id, 'A oportunidade B não tem vendedor (a A, com vendedor, não foi a escolhida).');
        // NÃO copiados:
        $this->assertNull($v->vin, 'VIN da convertida/oportunidade não é copiado para a viatura.');
        $this->assertTrue($v->purchase_price === null || (float) $v->purchase_price == 0.0, 'valor_carro não vira preço de compra.');
        $this->assertTrue($v->asking_price === null || (float) $v->asking_price == 0.0);

        $leg = Legalization::where('v3_vehicle_id', $v->id)->sole();
        $this->c['legalization_id'] = $leg->id;
        $this->assertSame($this->c['client_id'], (int) $leg->client_id);
        $this->assertSame('Tesla', $leg->marca);
        $this->assertSame('Model 3', $leg->modelo);
        $this->assertSame('TT-00-TT', $leg->matricula);
        $this->assertSame([], $leg->steps_completed);
        $this->assertNotEmpty($leg->token);
        $this->assertSame(1, LeadActivity::where('client_id', $this->c['client_id'])->where('title', 'Viatura e legalização criadas')->count());

        // Idempotente: repetir não duplica.
        $this->bo()->post(route('admin.v2.converted-proposals.create-vehicle', $conv->id))
            ->assertRedirect(route('admin.v3.vehicles.edit', $v->id))
            ->assertSessionHas('success', 'Esta cotação convertida já tinha viatura.');
        $this->assertSame(1, V3Vehicle::count());
        $this->assertSame(1, Legalization::count());

        $this->bo()->get(route('admin.v3.vehicles.edit', $v->id))->assertOk()->assertSee($v->reference);

        $this->ev('10', 'Criar viatura (+legalização) a partir da convertida', 'POST admin.v2.converted-proposals.create-vehicle',
            'V3Vehicle importada/reservada/offline ligada ao cliente e à convertida; legalização criada; idempotente',
            "viatura #{$v->id} {$v->reference} reservado, is_imported, show_online=false, ano 2021, km 52000, matrícula TT-00-TT; legalização #{$leg->id}; repetir não duplica");
        $this->ev('10', 'O que NÃO é copiado para a viatura', 'n/a',
            'VIN, preço de compra e matrícula de origem disponíveis na viatura',
            'VIN=null (a convertida tinha-o), purchase_price/asking_price vazios (valor_carro=24900 não passa), matrícula de origem só na nota; a matrícula de destino só é copiada se já estiver preenchida na convertida ANTES de criar a viatura', 'FRICCAO');
    }


    // ==================================================================
    // E11 — etapas seguintes (pipeline, pagamentos, documentos, legalização, tarefas, comissão, entrega)
    // ==================================================================
    private function estadoConvertida(string $estado, bool $notificar): \Illuminate\Testing\TestResponse
    {
        return $this->bo()->patchJson(route('admin.v2.converted-proposals.update-status', $this->c['conv_id']), ['status' => $estado, 'notify' => $notificar ? 1 : 0]);
    }

    private function guardarConvertida(array $campos): void
    {
        $this->bo()->put(route('admin.v2.converted-proposals.update', $this->c['conv_id']), ['client_id' => $this->c['client_id']] + $campos)
            ->assertRedirect(route('admin.v2.converted-proposals.index'))->assertSessionHasNoErrors();
    }

    private function movimentosDaConvertida(): Collection
    {
        return Expense::fromConvertedProposal($this->c['conv_id'])->get();
    }

    private function assertSemDuplicados(): void
    {
        $dups = Expense::fromConvertedProposal($this->c['conv_id'])->selectRaw('source_type, count(*) as n')->groupBy('source_type')->havingRaw('count(*) > 1')->get();
        $this->assertCount(0, $dups, 'Movimentos duplicados por source_type.');
        $this->assertSame(
            Expense::fromConvertedProposal($this->c['conv_id'])->count(),
            FinancialMovement::where('movable_type', Expense::class)->whereIn('movable_id', Expense::fromConvertedProposal($this->c['conv_id'])->pluck('id'))->count(),
            'Cada movimento tem exatamente um FinancialMovement.'
        );
    }

    private function etapa11_etapasSeguintes(): void
    {
        $cid = $this->c['conv_id'];

        // ---- a) pipeline: Negociação Carro (com aviso) ----
        $antes = $this->emails()->count();
        $this->estadoConvertida('Negociação Carro', true)->assertOk()->assertJson(['success' => true, 'notified' => true, 'newStatus' => 'Negociação Carro']);
        $this->assertSame($antes + 1, $this->emails()->count());
        $this->assertContains('cliente.fluxo@teste.invalid', $this->destinatarios());
        $this->assertSame('Negociação Carro', ConvertedProposal::find($cid)->status);
        $this->estadoConvertida('Negociação Carro', true)->assertOk()->assertJson(['message' => 'O estado já era este.']);
        $this->assertSame($antes + 1, $this->emails()->count(), 'Mesmo estado: sem email repetido.');
        $this->ev('11a', 'Pipeline: Iniciada -> Negociação Carro (notificar)', 'PATCH admin.v2.converted-proposals.update-status',
            'estado gravado, histórico, 1 email ao cliente', 'estado=Negociação Carro, 1 email ao cliente, repetir não duplica');

        // ---- b) Pagamento do Carro + compra (valor_carro/carro_pago) ----
        $this->estadoConvertida('Pagamento do Carro', false)->assertOk()->assertJson(['notified' => false]);
        $this->assertFalse((bool) ConvertedProposal::find($cid)->carro_pago, 'Mudar a etapa não marca carro_pago.');
        $this->guardarConvertida(['valor_carro' => 24900, 'carro_pago' => 1]);
        $conv = ConvertedProposal::find($cid);
        $this->assertTrue((bool) $conv->carro_pago);
        $this->assertSame('Pagamento do Carro', $conv->status, 'Marcar carro_pago não muda a etapa.');
        $this->assertCount(0, $this->movimentosDaConvertida(), 'O pagamento do carro não gera movimento.');
        $v = V3Vehicle::find($this->c['vehicle_id']);
        $this->assertTrue($v->purchase_price === null || (float) $v->purchase_price == 0.0, 'A viatura não recebe preço de compra.');
        $this->ev('11b', 'Compra: valor_carro/carro_pago + etapa "Pagamento do Carro"', 'PATCH update-status + PUT admin.v2.converted-proposals.update',
            'carro pago registado; (sem movimento: o carro não passa pela Izzycar)', 'carro_pago=true; 0 movimentos; etapa e flag independentes; viatura sem preço de compra (3 sítios para registar compra)', 'FRICCAO');

        // ---- c) tranches: 1.ª paga ----
        $this->guardarConvertida(['primeira_tranche_pago' => 1]);
        $conv->refresh();
        $this->assertEquals(now()->toDateString(), $conv->primeira_tranche_paga_em->toDateString());
        $m = Expense::where('source_type', Expense::importSourceType('primeira_tranche'))->where('source_id', $cid)->sole();
        $this->assertSame('income', $m->movement_type);
        $this->assertSame('import_service', $m->category);
        $this->assertEquals(1272.50, $m->amount);
        $this->assertSame('paid', $m->status);
        $this->assertNull($m->v3_vehicle_id);
        $this->assertSame($this->c['client_id'], (int) $m->client_id);
        $this->guardarConvertida(['primeira_tranche_pago' => 1, 'valor_carro' => 24900]);   // gravar de novo
        $this->assertCount(1, $this->movimentosDaConvertida());
        $this->assertSemDuplicados();
        $this->ev('11c', '1.ª tranche paga', 'PUT admin.v2.converted-proposals.update (primeira_tranche_pago=1)',
            '1 movimento receita 1272,50; sem duplicação ao regravar', "movimento #{$m->id} income import_service 1272,50 paid; regravar mantém 1; FinancialMovement 1:1");

        // ---- d) custos: transporte e inspeção pagos; desmarcar/remarcar sem duplicar ----
        $this->estadoConvertida('Transporte', false)->assertOk();
        $this->guardarConvertida(['transporte_pago' => 1, 'inspecao_origem_pago' => 1]);
        $this->assertCount(3, $this->movimentosDaConvertida());
        $t = Expense::where('source_type', Expense::importSourceType('custo_transporte'))->where('source_id', $cid)->sole();
        $this->assertSame('expense', $t->movement_type);
        $this->assertEquals(1350, $t->amount);
        $this->guardarConvertida(['transporte_pago' => 0]);
        $this->assertCount(2, $this->movimentosDaConvertida(), 'Desmarcar apaga o movimento.');
        $this->guardarConvertida(['transporte_pago' => 1]);
        $this->assertCount(3, $this->movimentosDaConvertida());
        $this->assertSemDuplicados();
        $this->estadoConvertida('IPO', false)->assertOk();
        $this->guardarConvertida(['ipo_pago' => 1]);
        $this->estadoConvertida('DAV', false)->assertOk();
        $this->estadoConvertida('ISV', false)->assertOk();
        $this->guardarConvertida(['isv_pago' => 1]);
        $this->estadoConvertida('Matriculação', false)->assertOk();
        $this->guardarConvertida(['matricula_pago_impressa' => 1]);
        $this->estadoConvertida('IMT', false)->assertOk();
        $this->guardarConvertida(['imt_pago' => 1, 'registo_pago' => 1]);
        $conv->refresh();
        $this->assertCount(8, $this->movimentosDaConvertida(), '1.ª tranche + inspeção, transporte, IPO, ISV, matrícula, IMT, registo (sem comissão nem 2.ª tranche).');
        $this->assertSemDuplicados();
        $despesas = $this->movimentosDaConvertida()->where('movement_type', 'expense')->sum('amount');
        $this->assertEquals(350 + 1350 + 100 + 1500 + 45 + 20 + 65, $despesas);
        $this->assertSame(0, Expense::whereNotNull('v3_vehicle_id')->count());
        $this->ev('11d', 'Custos pagos (transporte, inspeção, IPO, ISV, matrícula, IMT, registo)', 'PUT converted-proposals.update (flags *_pago) + update-status por etapa',
            '1 movimento de despesa por custo pago; desmarcar apaga; sem duplicados', 'despesas='.number_format($despesas, 2).'; 8 movimentos no total; sem v3_vehicle_id (não é custo da viatura); datas = hoje (não há campo para a data real do pagamento)', 'INFO');

        // ---- e) documentos: convertida, viatura, legalização (Storage fake) ----
        $this->bo()->post(route('admin.v2.converted-proposals.documents.uploadSigned', $cid), ['ficheiro' => UploadedFile::fake()->create('TESTE_contrato_assinado.pdf', 50, 'application/pdf')])
            ->assertRedirect()->assertSessionHasNoErrors();
        $assinado = ConvertedProposalDocument::where('converted_proposal_id', $cid)->where('tipo', 'assinado')->sole();
        Storage::disk('local')->assertExists($assinado->caminho);
        $this->bo()->get(route('admin.v2.converted-proposals.documents.download', [$cid, $assinado->id]))->assertOk();
        $this->bo()->post(route('admin.v3.vehicles.documents.upload', $this->c['vehicle_id']), ['ficheiro' => UploadedFile::fake()->create('TESTE_docs_viatura.pdf', 30, 'application/pdf'), 'tipo' => 'CMATR', 'titulo' => 'TESTE livrete'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1, DB::table('v3_vehicle_documents')->where('v3_vehicle_id', $this->c['vehicle_id'])->count());
        $this->bo()->post(route('admin.legalizations.upload-document', $this->c['legalization_id']), ['tipo' => 'CMATR', 'ficheiro' => UploadedFile::fake()->create('TESTE_cmatr.pdf', 30, 'application/pdf')])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->bo()->post(route('admin.v3.vehicles.legalization.upload-document', $this->c['vehicle_id']), ['tipo' => 'CCIDA', 'ficheiro' => UploadedFile::fake()->create('TESTE_cc.pdf', 30, 'application/pdf')])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(2, DB::table('legalization_documents')->where('legalization_id', $this->c['legalization_id'])->count());
        $this->assertSame(2, ConvertedProposalDocument::where('converted_proposal_id', $cid)->count(), 'contrato gerado + assinado');
        $this->ev('11e', 'Documentos (convertida, viatura, legalização)', 'POST documents.uploadSigned / v3 documents.upload / legalizations.upload-document / v3 legalization.upload-document',
            'ficheiros guardados e ligados', 'convertida: gerado+assinado; viatura: 1; legalização: 2 (módulo e tab); três locais distintos para documentos do mesmo processo', 'FRICCAO');

        // ---- f) legalização: passos 1->7 com regra N-1, tarefa do Modelo 9, Modelo 9 ----
        $leg = $this->c['legalization_id'];
        $vid = $this->c['vehicle_id'];
        $passoMod = fn (int $n) => $this->bo()->postJson(route('admin.legalizations.toggle-step', $leg), ['step' => $n]);
        $passoTab = fn (int $n) => $this->bo()->postJson(route('admin.v3.vehicles.legalization.toggle-step', $vid), ['step' => $n]);

        $this->bo()->post(route('admin.v3.vehicles.legalization.create', $vid))->assertRedirect();
        $this->assertSame(1, Legalization::where('v3_vehicle_id', $vid)->count(), 'Criar processo outra vez não duplica (tab da viatura).');

        $passoMod(1)->assertOk()->assertJson(['completed' => [1]]);
        $passoMod(2)->assertOk();
        $passoMod(4)->assertStatus(422)->assertJson(['error' => 'Conclui o passo 3 antes de avançar.']);
        $passoTab(4)->assertStatus(422)->assertJson(['error' => 'Conclui o passo 3 primeiro.']);
        $passoMod(3)->assertOk();
        $passoMod(4)->assertOk();
        $this->assertSame(0, Task::count());
        $passoTab(5)->assertOk();
        $tarefa = Task::sole();
        $this->assertSame('Entregar Modelo 9 IMT — Tesla Model 3', $tarefa->title);
        $this->assertSame('pendente', $tarefa->status);
        $this->assertNull($tarefa->user_id);
        $this->assertSame($vid, (int) $tarefa->v3_vehicle_id);
        $this->assertEquals(now()->addDays(30)->toDateString(), $tarefa->due_date->toDateString());
        $this->assertEquals(now()->addDays(15)->toDateString(), $tarefa->reminder_date->toDateString());
        // Regra N-1 ao desmarcar: desmarcar o 3 desmarca 4 e 5; remarcar não duplica a tarefa (aberta).
        $passoMod(3)->assertOk()->assertJson(['completed' => [1, 2]]);
        $passoMod(3)->assertOk(); $passoMod(4)->assertOk(); $passoMod(5)->assertOk();
        $this->assertSame(1, Task::count());
        $passoMod(6)->assertOk();
        $passoMod(7)->assertOk()->assertJson(['completed' => [1, 2, 3, 4, 5, 6, 7], 'progress' => 100]);
        $this->assertSame([1, 2, 3, 4, 5, 6, 7], Legalization::find($leg)->steps_completed);
        $this->assertNull(Legalization::find($leg)->currentStep());
        // Nada nos passos mexe na convertida/viatura.
        $this->assertSame('IMT', ConvertedProposal::find($cid)->status);
        $this->assertSame('reservado', V3Vehicle::find($vid)->status);

        $pdf = $this->bo()->post(route('admin.legalizations.modelo9.save-and-generate', $leg), ['modelo9' => []]);
        $pdf->assertOk();
        $this->assertStringStartsWith('application/pdf', $pdf->headers->get('Content-Type'));
        $this->assertNotNull(Legalization::find($leg)->modelo9_dados);
        $this->bo()->get(route('admin.legalizations.show', $leg))->assertOk();
        $this->ev('11f', 'Legalização: passos 1->7, regra N-1, tarefa Modelo 9, Modelo 9 PDF', 'POST admin.legalizations.toggle-step / admin.v3.vehicles.legalization.toggle-step / modelo9.save-and-generate',
            '422 ao saltar passo; desmarcar cascata; tarefa criada ao concluir o passo 5 (1 só); PDF Modelo 9',
            '422 (2 mensagens diferentes: "antes de avançar" vs "primeiro"); cascata ok; 1 tarefa "Entregar Modelo 9 IMT — Tesla Model 3" sem user_id, +30d/lembrete +15d, criada no passo 5 (não no 6); PDF gerado', 'FRICCAO');

        // ---- g) tarefas: lista, tarefa manual atribuída, concluir ----
        $this->bo()->get(route('admin.tasks.show', $tarefa->id))->assertOk()->assertSee('Entregar Modelo 9 IMT');
        $this->bo()->post(route('admin.tasks.store'), [
            'title' => 'TESTE FLUXO Preparar entrega ao cliente', 'due_date' => now()->addDays(3)->toDateString(), 'reminder_date' => now()->addDays(2)->toDateString(),
            'user_id' => $this->admin->id, 'v3_vehicle_id' => $vid, 'status' => 'pendente',
        ])->assertRedirect(route('admin.tasks.index'))->assertSessionHasNoErrors();
        $manual = Task::where('title', 'like', 'TESTE FLUXO%')->sole();
        Notification::assertSentTo($this->admin, \App\Notifications\TaskAssignedNotification::class);
        $this->bo()->patchJson(route('admin.tasks.update-status', $manual->id), ['status' => 'concluida'])->assertOk();
        $this->assertSame('concluida', $manual->fresh()->status);
        $this->assertSame('pendente', $tarefa->fresh()->status);
        $this->bo()->get(route('admin.tasks.index'))->assertOk();
        $this->c['task_modelo9'] = $tarefa->id;
        $this->ev('11g', 'Tarefas', 'GET admin.tasks.show / POST admin.tasks.store / PATCH update-status',
            'tarefa do Modelo 9 visível; manual criada, notifica atribuído, conclui', 'ok; a tarefa do Modelo 9 fica sem responsável e sem ligação a cliente/convertida (só à viatura)', 'FRICCAO');

        // ---- h) Entrega, 2.ª tranche, alerta de comissão em atraso (48h) ----
        $this->estadoConvertida('Entrega', false)->assertOk();
        $entrega = ConvertedProposal::find($cid)->deliveredAt();
        $this->assertNotNull($entrega);
        $this->guardarConvertida(['segunda_tranche_pago' => 1]);
        $this->assertCount(9, $this->movimentosDaConvertida());
        $this->assertEquals(2545.0, (float) $this->movimentosDaConvertida()->where('movement_type', 'income')->sum('amount'));
        $this->assertSemDuplicados();

        config(['mail.admin_address' => 'alertas.fluxo@teste.invalid']);
        Mail::fake();
        $this->travelTo($entrega->copy()->addHours(47));
        $this->assertFalse(ConvertedProposal::find($cid)->isCommissionOverdue());
        Artisan::call('commissions:overdue-alert');
        Mail::assertNothingSent();
        $this->travelTo($entrega->copy()->addHours(49));
        $this->assertTrue(ConvertedProposal::find($cid)->isCommissionOverdue());
        Artisan::call('commissions:overdue-alert');
        Mail::assertSent(OverdueCommissionMail::class, fn ($m) => $m->hasTo('alertas.fluxo@teste.invalid') && $m->convertedProposals->count() === 1);
        Artisan::call('commissions:overdue-alert');
        Mail::assertSent(OverdueCommissionMail::class, 1);       // repetição suprimida por cache (7 dias)
        $this->ev('11h', 'Alerta de comissão em atraso (48h após Entrega)', 'artisan commissions:overdue-alert (comando agendado real, Mail fake)',
            'sem alerta às 47h; 1 email às 49h; sem repetição', 'ok; o destinatário vem de config mail.admin_address (inexistente em config/mail.php: cai em MAIL_FROM_ADDRESS)', 'INFO');

        // ---- i) comissão: pagar (toggle), movimento, sem alerta; desmarcar/remarcar ----
        $this->bo()->post(route('admin.v2.angariadores.toggle-paid', $cid))->assertRedirect();
        $conv = ConvertedProposal::find($cid);
        $this->assertTrue((bool) $conv->comissao_paga);
        $this->assertEquals(now()->toDateString(), $conv->comissao_paga_em->toDateString());
        $c = Expense::where('source_type', Expense::importSourceType('comissao'))->where('source_id', $cid)->sole();
        $this->assertSame('expense', $c->movement_type);
        $this->assertSame('commission', $c->category);
        $this->assertEquals(100, $c->amount);
        $this->assertCount(10, $this->movimentosDaConvertida());
        $this->assertSemDuplicados();
        Cache::flush();
        $this->assertFalse(ConvertedProposal::find($cid)->isCommissionOverdue());
        $this->bo()->post(route('admin.v2.angariadores.toggle-paid', $cid))->assertRedirect();
        $this->assertCount(9, $this->movimentosDaConvertida());
        $this->bo()->post(route('admin.v2.angariadores.toggle-paid', $cid))->assertRedirect();
        $this->assertCount(10, $this->movimentosDaConvertida());
        $this->assertSemDuplicados();
        $this->ev('11i', 'Comissão do angariador: marcar paga', 'POST admin.v2.angariadores.toggle-paid ({proposal} = id da convertida)',
            'comissao_paga=true, data, despesa 100 (commission), sem alerta; alternar não duplica', 'ok; movimento commission 100,00; 10 movimentos; sem duplicados; parâmetro {proposal} é uma ConvertedProposal (nome enganador)', 'FRICCAO');

        // ---- j) registo, conclusão ----
        $this->estadoConvertida('Registo automóvel', false)->assertOk();
        $this->estadoConvertida('Concluido', true)->assertOk()->assertJson(['notified' => true]);
        $conv = ConvertedProposal::find($cid);
        $this->assertSame('Concluido', $conv->status);
        $this->assertSame(['Iniciada', 'Negociação Carro', 'Pagamento do Carro', 'Transporte', 'IPO', 'DAV', 'ISV', 'Matriculação', 'IMT', 'Entrega', 'Registo automóvel', 'Concluido'],
            StatusProposalHistory::where('converted_proposal_id', $cid)->orderBy('id')->pluck('new_status')->all());
        // O que a conclusão NÃO propaga:
        $this->assertSame('reservado', V3Vehicle::find($vid)->status);
        $this->assertSame('convertido', FormProposal::find($this->c['form_proposal_id'])->status);
        $this->assertSame('pendente', Task::find($this->c['task_modelo9'])->status);
        $this->ev('11j', 'Entrega e conclusão', 'PATCH update-status (Entrega, Registo automóvel, Concluido)',
            'histórico completo; processo concluído', 'histórico de 12 estados; NÃO propaga: viatura continua "reservado", tarefa do Modelo 9 continua pendente, pedido continua convertido', 'FRICCAO');
    }

    // ==================================================================
    // E12 — coerência entre listagens, ficha, kanban, dashboard e permissões
    // ==================================================================
    private function etapa12_coerenciaEPermissoes(): void
    {
        $cid = $this->c['conv_id'];
        $nome = 'TESTE FLUXO Cliente Importação';

        $this->bo()->get(route('admin.v2.leads.index'))->assertOk()->assertDontSee($nome);
        $this->bo()->get(route('admin.v2.leads.kanban'))->assertOk()->assertDontSee($nome);
        $this->bo()->get(route('admin.v2.clients.index'))->assertOk()->assertSee($nome);
        $this->bo()->get(route('admin.v2.clients.show', $this->c['client_id']))->assertOk()->assertSee($nome)->assertSee('Tesla');
        $this->bo()->get(route('admin.v2.form-proposals.index'))->assertOk();
        $props = $this->bo()->get(route('admin.v2.proposals.index'))->assertOk()->viewData('proposals');
        $this->assertSame(2, $props->total());
        $kanban = $this->bo()->get(route('admin.v2.proposals.kanban'))->assertOk();
        $conv = $this->bo()->get(route('admin.v2.converted-proposals.index'))->assertOk();
        $this->assertSame(1, $conv->viewData('convertedProposals')->total());
        $stats = collect($conv->viewData('stats'))->pluck('value', 'title');
        $this->assertSame(1, $stats['Total Convertidas']);
        $this->assertSame(1, $stats['Concluídas']);
        $this->assertSame(0, $stats['Em Processo']);
        $ck = $this->bo()->get(route('admin.v2.converted-proposals.kanban'))->assertOk();
        $this->assertSame(1, $ck->viewData('doneCount'));
        $mov = $this->bo()->get(route('admin.v2.movements.index'))->assertOk();
        $this->assertGreaterThanOrEqual(10, $mov->viewData('movements')->total());
        $this->bo()->get(route('admin.legalizations.index'))->assertOk();
        $this->bo()->get(route('admin.legalizations.kanban'))->assertOk();
        $dash = $this->bo()->get(route('admin.v2.dashboard'))->assertOk();
        $dstats = collect($dash->viewData('stats'))->pluck('value', 'title');
        $this->assertSame(1, $dstats['Clientes']);
        $this->assertSame(0, $dstats['Formulários Novos']);
        $this->assertSame(0, $dstats['Propostas Pendentes']);
        $this->assertSame(2, $dash->viewData('recentProposals')->count());
        $this->assertSame(0, $dash->viewData('totalLeadsActivas'));

        // Angariador: portal próprio vê a sua convertida e comissão; o resto do BO está vedado.
        $this->bo($this->angariador)->get(route('admin.angariador.convertidas'))->assertOk()->assertSee('Tesla');
        $this->bo($this->angariador)->get(route('admin.angariador.comissoes'))->assertOk();
        $this->bo($this->angariador)->get(route('admin.v2.leads.index'))->assertForbidden();
        $this->bo($this->angariador)->get(route('admin.v2.converted-proposals.index'))->assertForbidden();

        // Sem perfil: sempre 403. Sem permissões do funil (cms) com ENFORCE DESLIGADO: passa (só regista would_block).
        $this->bo($this->semPerfil)->get(route('admin.v2.leads.index'))->assertForbidden();
        $this->assertFalse(config('permissions.enforce'));
        $this->bo($this->semFunil)->get(route('admin.v2.leads.index'))->assertOk();
        $this->bo($this->semFunil)->get(route('admin.v2.converted-proposals.index'))->assertOk();

        // Com ENFORCE LIGADO (runtime)
        config(['permissions.enforce' => true]);
        $this->bo($this->semFunil)->get(route('admin.v2.leads.index'))->assertForbidden();
        $this->bo($this->semFunil)->get(route('admin.v2.proposals.index'))->assertForbidden();
        $this->bo($this->semFunil)->get(route('admin.v2.converted-proposals.index'))->assertForbidden();
        $this->bo($this->semFunil)->get(route('admin.v2.dashboard'))->assertOk();     // "shared"
        $this->bo($this->semPerfil)->get(route('admin.v2.dashboard'))->assertForbidden();
        $this->bo($this->angariador)->get(route('admin.angariador.comissoes'))->assertOk();
        $this->bo($this->admin)->get(route('admin.v2.converted-proposals.index'))->assertOk();
        $this->bo($this->admin)->post(route('admin.v2.angariadores.toggle-paid', $cid))->assertRedirect();   // admin: Gate::before
        $this->bo($this->admin)->post(route('admin.v2.angariadores.toggle-paid', $cid))->assertRedirect();
        $this->assertTrue((bool) ConvertedProposal::find($cid)->comissao_paga);

        $liberado = [];
        foreach ([
            'leads.index' => route('admin.v2.leads.index'), 'clients.show' => route('admin.v2.clients.show', $this->c['client_id']),
            'form-proposals.show' => route('admin.v2.form-proposals.show', $this->c['form_proposal_id']),
            'opportunities.show' => route('admin.v2.form-proposals.opportunities.show', [$this->c['form_proposal_id'], $this->c['oppA']]),
            'proposals.index' => route('admin.v2.proposals.index'), 'converted.index' => route('admin.v2.converted-proposals.index'),
            'converted.edit' => route('admin.v2.converted-proposals.edit', $cid), 'legalizations.show' => route('admin.legalizations.show', $this->c['legalization_id']),
            'vehicles.edit' => route('admin.v3.vehicles.edit', $this->c['vehicle_id']), 'tasks.index' => route('admin.tasks.index'),
            'movements.index' => route('admin.v2.movements.index'), 'comissoes.admin' => route('admin.v2.angariadores.comissoes'),
        ] as $nomeRota => $url) {
            $liberado[$nomeRota] = $this->bo($this->operador)->get($url)->getStatusCode();
        }
        $bloqueadas = array_keys(array_filter($liberado, fn ($c) => $c === 403));
        // BUG: o perfil real "Importador" não tem o objeto "commissions": com enforce ligado não vê nem paga comissões.
        $this->assertSame(['comissoes.admin'], $bloqueadas);
        $this->bo($this->operador)->post(route('admin.v2.angariadores.toggle-paid', $cid))->assertForbidden();
        $this->assertTrue((bool) ConvertedProposal::find($cid)->comissao_paga);
        $this->bo($this->operador)->post(route('admin.v2.converted-proposals.create-vehicle', $cid))->assertRedirect();   // vehicles.create ok

        $this->ev('12', 'Coerência listagens/ficha/kanban/dashboard', 'GET leads, clients, proposals, converted (lista+kanban), movements, legalizations, dashboard',
            'cliente só em clientes; 2 cotações; 1 convertida Concluída; >=10 movimentos; dashboard coerente',
            'tudo coerente: lead fora de leads/kanban; cliente com ficha; convertidas 1/1 concluída/0 em processo; dashboard Clientes=1, Formulários Novos=0, Pendentes=0');
        $this->ev('12', 'Permissões com enforce desligado vs ligado', 'actingAs por perfil',
            'sem perfil 403; cms passa (log) com enforce off e 403 com enforce on; angariador só portal',
            'confirmado; admin passa tudo (Gate::before)');
        $this->ev('12', 'Perfil Importador com enforce ligado', 'GET/POST funil',
            'o perfil de operação consegue fazer o funil todo', 'BUG: não tem "commissions": 403 em admin.v2.angariadores.comissoes e toggle-paid (só admin paga comissões quando PERMISSIONS_ENFORCE ligar)', 'BUG');
    }

    // ==================================================================
    // TESTES
    // ==================================================================

    /** Percurso completo e sequencial, etapas 1 a 12. */
    public function test_percurso_completo_lead_ate_entrega(): void
    {
        $this->ate(12);
        $this->ev('--', 'Percurso completo', 'todas as rotas acima', 'lead até entrega só pela UI/rotas reais', 'concluído sem preparação isolada nas etapas 1-12 (únicas exceções: alinhamento de esquema/migrations da base de teste e vendedor criado como dado de referência)', 'INFO');
    }

    public function test_etapas_1_a_5_lead_pedido_oportunidades_cotacoes_enviadas(): void
    {
        $this->ate(5);
        $this->assertSame(4, $this->emails()->count());
    }

    /** DRIFT: o esquema das migrations não suporta os valores que o código grava (ver completarMigracoesEmFalta). */
    public function test_drift_esquema_enums_migrations_vs_codigo(): void
    {
        $o = self::$esquemaOriginal;
        // BUG: proposals.status é ENUM('', Pendente, Enviada, Aprovada, Reprovada, Sem resposta); o código grava "Enviado".
        $this->assertStringContainsString("'Enviada'", $o['proposals.status']);
        $this->assertStringNotContainsString("'Enviado'", $o['proposals.status']);
        // BUG: converted_proposals.status não tem "Pagamento do Carro" nem "Cancelada".
        $this->assertStringNotContainsString("'Pagamento do Carro'", $o['converted_proposals.status']);
        $this->assertStringNotContainsString("'Cancelada'", $o['converted_proposals.status']);
        // BUG: clients.origin só aceita Olx/StandVirtual/Facebook/Instagram/Amigo/Outro; o select do BO (e o valor por omissão "Manual BO") usa outros.
        $this->assertStringNotContainsString("'Manual BO'", $o['clients.origin']);
        $this->assertStringNotContainsString("'Telefone'", $o['clients.origin']);
        $this->ev('--', 'Esquema das migrations vs valores do código', 'information_schema (esquema recém-migrado)',
            'colunas que aceitam os valores gravados pelo código',
            'BUG: '.json_encode($o, JSON_UNESCAPED_UNICODE).' => em MySQL estrito (migrate:fresh do repositório) marcar Enviado, "Pagamento do Carro", Cancelada ou criar lead com origem por omissão dá 500 (Data truncated). Produção só funciona se estas colunas foram alteradas à mão: CONFIRMAR com SHOW COLUMNS.', 'BUG');
    }

    /** O pedido nasce do site público: liga-se à lead criada no BO (mesmo email) em vez de criar outra. */
    public function test_pedido_do_site_publico_liga_a_lead_existente(): void
    {
        $this->etapa01_criarLead();
        $antes = $this->emails()->count();
        $this->comoVisitante()->post(route('frontend.import-submit'), [
            'name' => 'TESTE FLUXO Cliente Importação', 'phone' => '910000001', 'email' => 'cliente.fluxo@teste.invalid',
            'buyer_type' => 'particular', 'payment_type' => 'pronto_pagamento', 'estimated_purchase_date' => 'imediato',
            'data_processing_consent' => '1', 'ad_option' => 'nao_sei', 'brand' => 'BMW', 'model' => 'i4', 'budget' => '35000',
            '_form_started' => time() - 10, // o anti-spam usa time() real (não o relógio de teste)
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Client::count(), 'Sem lead duplicada.');
        $fp = FormProposal::sole();
        $this->assertSame($this->c['client_id'], (int) $fp->client_id);
        $this->assertSame('novo', $fp->status);
        $this->assertSame(1, LeadActivity::where('client_id', $this->c['client_id'])->where('title', 'Pedido de importação submetido')->count());
        $this->assertGreaterThan($antes, $this->emails()->count());
        $this->assertContains('cliente.fluxo@teste.invalid', $this->destinatarios());
        $this->assertSame($this->angariador->id, (int) Client::first()->owner_id, 'O dono da lead preserva-se.');
        $this->ev('02', 'Pedido de importação pelo formulário PÚBLICO', 'POST frontend.import-submit',
            'liga-se à lead existente (email/telefone); pedido "novo"; email de confirmação', "pedido #{$fp->id} novo ligado à lead #{$this->c['client_id']}; 1 só Client; email de confirmação enviado");
    }

    public function test_aceitacao_publica_sem_identificacao_e_aceite(): void
    {
        $this->ate(5);
        $this->comoVisitante()->post(route('proposals.accept', $this->c['codeA']), [])->assertSessionHasNoErrors()->assertSessionHas('success');
        $cl = Client::find($this->c['client_id']);
        $this->assertNull($cl->vat_number);
        $this->assertNull($cl->identification_number);
        $this->assertNull($cl->validate_identification_number);
        $this->assertSame(1, ConvertedProposal::count());
        $this->ev('07', 'Aceitação pública sem NIF/CC/validade', 'POST proposals.accept (corpo vazio)',
            'servidor exige identificação', 'BUG: aceita; cliente fica sem NIF/CC/morada (validação só no browser) => contrato e Modelo 9 com dados em falta', 'BUG');
    }

    public function test_sem_resposta_nao_bloqueia_a_aceitacao_publica(): void
    {
        $this->ate(5);
        $this->travelTo($this->t0->copy()->addDay());
        $this->bo()->patchJson(route('admin.v2.proposals.updateStatus', $this->c['propA']), ['status' => 'Sem resposta'])->assertOk();
        $this->comoVisitante()->get(route('proposals.detail', $this->c['codeA']))->assertOk()->assertDontSee('Cotação Expirada');
        $this->comoVisitante()->post(route('proposals.accept', $this->c['codeA']), $this->dadosAceitacaoPublica())->assertSessionHas('success');
        $this->assertSame('Aprovada', Proposal::find($this->c['propA'])->status);
        $this->ev('06b', '"Sem resposta" dentro do prazo', 'PATCH updateStatus + POST proposals.accept',
            '"Sem resposta" bloquearia aceitar', 'não bloqueia: a cotação continua aceitável pelo cliente (só Reprovada ou +15d bloqueiam)', 'FRICCAO');
    }

    public function test_reprovada_bloqueia_a_aceitacao_e_rejeita_a_oportunidade(): void
    {
        $this->ate(5);
        $this->bo()->patchJson(route('admin.v2.proposals.updateStatus', $this->c['propA']), ['status' => 'Reprovada'])->assertOk();
        $this->assertSame('rejeitado', ImportOpportunity::find($this->c['oppA'])->status->value);
        $this->comoVisitante()->post(route('proposals.accept', $this->c['codeA']), $this->dadosAceitacaoPublica())
            ->assertSessionHas('error', 'Esta cotação já não está disponível. Contacte-nos para receber uma cotação atualizada.');
        $this->assertSame(0, ConvertedProposal::count());
        $this->ev('06', 'Reprovada (alternativa a Sem resposta)', 'PATCH updateStatus',
            'bloqueia aceitação e rejeita oportunidade', 'confirmado');
    }

    public function test_aceitar_uma_cotacao_nao_invalida_as_outras(): void
    {
        $this->ate(8);
        $this->bo()->postJson(route('admin.v2.proposals.accept', $this->c['propA']))->assertOk()->assertJson(['success' => true]);
        $this->assertSame(2, ConvertedProposal::count());
        $this->assertSame(2, ConvertedProposal::where('client_id', $this->c['client_id'])->distinct()->count('proposal_id'));
        // A 3.ª oportunidade continua como "outra opção" na página pública da cotação aceite.
        $r = $this->comoVisitante()->get(route('proposals.detail', $this->c['codeB']))->assertOk();
        $this->ev('08', 'Aceitar uma 2.ª cotação do mesmo pedido', 'POST admin.v2.proposals.accept',
            'recusar/avisar: o pedido já tem uma convertida', 'BUG: aceita; 2 convertidas para o mesmo pedido (carro duplicado possível); a oportunidade C continua a ser oferecida ao cliente', 'BUG');
    }

    public function test_criar_viatura_exige_vehicles_create_mesmo_com_enforce_desligado(): void
    {
        $this->ate(8);
        $role = Role::findOrCreate('TESTE fluxo so convertidas', 'web');
        $role->givePermissionTo(Permission::findByName('converted-proposals.update', 'web'));
        $u = $this->backofficeUser('TESTE fluxo so convertidas', ['email' => 'soconv.fluxo@teste.invalid']);
        $this->bo($u)->post(route('admin.v2.converted-proposals.create-vehicle', $this->c['conv_id']))->assertForbidden();
        $this->assertSame(0, V3Vehicle::count());
        $this->ev('10', 'Criar viatura sem vehicles.create', 'POST create-vehicle', 'botão funciona para quem edita convertidas', 'FRICCAO: 403 "Não tem permissão para criar viaturas" (can() direto, também com enforce desligado)', 'FRICCAO');
    }

    public function test_comissao_nao_alerta_se_a_convertida_salta_a_entrega(): void
    {
        $this->ate(8);
        $this->estadoConvertida('Concluido', false)->assertOk();
        Mail::fake();
        $this->travelTo(now()->addHours(100));
        Artisan::call('commissions:overdue-alert');
        Mail::assertNothingSent();
        $this->assertFalse(ConvertedProposal::find($this->c['conv_id'])->isCommissionOverdue());
        $this->ev('11h', 'Concluir sem passar por Entrega', 'update-status + commissions:overdue-alert', 'alerta de comissão', 'BUG: sem alerta (o relógio das 48h só nasce na etapa Entrega)', 'BUG');
    }

    public function test_formulario_da_convertida_apaga_o_ano_texto_da_cotacao(): void
    {
        $this->ate(8);
        $id = $this->c['conv_id'];
        $this->bo()->put(route('admin.v2.converted-proposals.update', $id), ['client_id' => $this->c['client_id'], 'year' => '03/2021'])->assertSessionHasErrors('year');
        $this->bo()->put(route('admin.v2.converted-proposals.update', $id), ['client_id' => $this->c['client_id'], 'year' => ''])->assertSessionHasNoErrors();
        $this->assertNull(ConvertedProposal::find($id)->year);
        $this->ev('08', 'Gravar o formulário da convertida com o Ano que veio da cotação', 'PUT converted-proposals.update',
            'o ano mantém-se', 'BUG: "03/2021" é rejeitado (integer) e o input number vazio apaga o ano (year=null)', 'BUG');
    }

    public function test_tarefa_modelo9_concluida_e_recriada_ao_repetir_o_passo_5(): void
    {
        $this->ate(10);
        $leg = $this->c['legalization_id'];
        foreach ([1, 2, 3, 4, 5] as $n) {
            $this->bo()->postJson(route('admin.legalizations.toggle-step', $leg), ['step' => $n])->assertOk();
        }
        $t = Task::sole();
        $this->bo()->patchJson(route('admin.tasks.update-status', $t->id), ['status' => 'concluida'])->assertOk();
        $this->bo()->postJson(route('admin.legalizations.toggle-step', $leg), ['step' => 5])->assertOk();
        $this->bo()->postJson(route('admin.legalizations.toggle-step', $leg), ['step' => 5])->assertOk();
        $this->assertSame(2, Task::count());
        $this->ev('11f', 'Repetir o passo 5 com a tarefa já concluída', 'toggle-step', 'sem duplicar', 'BUG: cria 2.ª tarefa "Entregar Modelo 9" (só impede com tarefa aberta)', 'BUG');
    }
}
