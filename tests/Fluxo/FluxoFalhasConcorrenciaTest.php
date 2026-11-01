<?php

namespace Tests\Fluxo;

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
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use App\Models\V3Vehicle;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\Fluxo\Concerns\PreparaFunil;
use Tests\Fluxo\Concerns\UsaMysqlIsolado;
use Tests\Fluxo\Support\ConcurrentRunner;
use Tests\Fluxo\Support\TransportFalha;
use Tests\TestCase;

/**
 * FALHAS E CONCORRÊNCIA REAL sobre o percurso lead -> entrega, em MySQL local (izzycar_fluxo_teste).
 *
 * - Documenta o comportamento ATUAL. Defeito conhecido => assert do atual + comentário "BUG:".
 *   (Nada da implementação foi corrigido.) "AVISO" = risco/fricção sem ser defeito claro.
 * - Os testes de concorrência lançam processos PHP independentes (tests/Fluxo/bin/worker.php, via
 *   ConcurrentRunner) que fazem o MESMO pedido ao kernel real, contra a MESMA base, com commits reais
 *   (não há transações a envolver estes dados: a infra usa TRUNCATE). Cada cenário repete-se
 *   REPETICOES vezes. IMPORTANTE: "não encontrado" NUNCA prova segurança — só diz que nestas
 *   N execuções a corrida não apareceu; uma corrida rara pode escapar.
 * - Segurança: cada worker recebe o ambiente EXPLÍCITO do phpunit.fluxo-mysql.xml, confirma-o, nunca lê
 *   o .env e passa a MESMA guarda (UsaMysqlIsolado::garantirBaseIsolada) antes de qualquer lógica.
 */
class FluxoFalhasConcorrenciaTest extends TestCase
{
    use UsaMysqlIsolado {
        setUp as protected setUpMysqlIsolado;
    }
    use PreparaFunil;

    /** Repetições de cada cenário concorrente (reduz falsos negativos; não os elimina). */
    private const REPETICOES = 5;

    private static array $evidencia = [];

    /** @var array<string, list<array>> resultados por cenário concorrente */
    private static array $conc = [];

    private static float $inicio = 0.0;

    /** @var list<array{nivel: string, texto: string}> */
    private array $logs = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$inicio = microtime(true);
    }

    public static function tearDownAfterClass(): void
    {
        $tempo = round(microtime(true) - self::$inicio, 1);
        $out = ["\n[falhas] EVIDÊNCIA ({$tempo}s)"];
        foreach (self::$evidencia as $e) {
            $out[] = sprintf("  [%s] %-5s %s | esperado: %s | observado: %s (linha %s)", $e['teste'], $e['estado'], $e['caso'], $e['esperado'], $e['observado'], $e['linha']);
        }
        foreach (self::$conc as $cenario => $linhas) {
            $out[] = "  [concorrência] {$cenario}";
            foreach ($linhas as $i => $l) {
                $out[] = '     rep '.($i + 1).': '.json_encode($l, JSON_UNESCAPED_UNICODE);
            }
        }
        fwrite(STDERR, implode("\n", $out)."\n");
        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        $this->setUpMysqlIsolado();   // guarda + migrações + TRUNCATE
        $this->prepararBase();
        TransportFalha::$tentativas = 0;
        $this->logs = [];
        Event::listen(MessageLogged::class, function (MessageLogged $e) {
            $texto = $e->message.' '.json_encode(array_filter($e->context, 'is_scalar'), JSON_UNESCAPED_UNICODE);
            if (($e->context['exception'] ?? null) instanceof \Throwable) {
                $texto .= ' EXC='.get_class($e->context['exception']).': '.$e->context['exception']->getMessage();
            }
            $this->logs[] = ['nivel' => $e->level, 'texto' => $texto];
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function ev(string $caso, string $esperado, string $observado, string $estado = 'OK'): void
    {
        self::$evidencia[] = [
            'teste' => $this->name(), 'caso' => $caso, 'esperado' => $esperado, 'observado' => $observado, 'estado' => $estado,
            'linha' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['line'] ?? '?',
        ];
    }

    // ==================================================================
    // Runner de concorrência (assertions comuns)
    // ==================================================================

    /**
     * @param  \Closure(int): array  $criar     prepara os dados de uma repetição (commits reais) e devolve o contexto
     * @param  \Closure(array): list<array>  $jobs  um job por processo
     * @param  \Closure(array, array, array): array  $medir  (ctx, run, agregado) => medições desta repetição
     * @return list<array>
     */
    private function concorrente(string $cenario, \Closure $criar, \Closure $jobs, \Closure $medir, int $reps = self::REPETICOES): array
    {
        $linhas = [];
        for ($r = 0; $r < $reps; $r++) {
            $ctx = $criar($r);
            $lista = $jobs($ctx);
            $run = ConcurrentRunner::executar($lista);
            $agg = ConcurrentRunner::agregar($run);

            // Todos os workers arrancaram, passaram a guarda e dispararam juntos.
            $this->assertSame([], $run['falhas_arranque'], "{$cenario}: falhas de arranque dos workers");
            $this->assertSame(count($lista), $run['guardas_ok'], "{$cenario}: todos os workers têm de passar a guarda");
            foreach ($run['workers'] as $w) {
                $this->assertSame('izzycar_fluxo_teste', $w['db']);
            }
            $this->assertTrue($run['barreira_ok'], "{$cenario}: barreira falhou");
            $this->assertLessThan(250, $run['dispersao_ms'], "{$cenario}: disparos pouco simultâneos");

            $linha = $medir($ctx, $run, $agg) + [
                'workers' => count($lista), 'dispersao_ms' => $run['dispersao_ms'], 'guardas_ok' => $run['guardas_ok'], 'duracao_s' => $run['duracao_s'],
                'status' => $agg['status'], 'http500' => $agg['http500'], 'deadlocks' => $agg['deadlocks'], 'lock_timeouts' => $agg['lock_timeouts'],
                'unicidade' => $agg['unicidade'], 'sucessos' => $agg['sucessos'], 'erros' => $agg['mensagens_erro'],
            ];
            $linhas[] = $linha;
        }
        self::$conc[$cenario] = $linhas;

        return $linhas;
    }

    /** Pedido de worker para uma rota (path relativo, sem domínio). */
    private function pedido(string $metodo, string $rota, array $params = [], array $rotaParams = [], bool $json = false, string $label = ''): array
    {
        return ['label' => $label ?: $rota, 'method' => $metodo, 'uri' => route($rota, $rotaParams, false), 'params' => $params, 'json' => $json];
    }

    private function jobs(int $n, array $pedidos, ?int $userId = null): array
    {
        return array_fill(0, $n, ['user_id' => $userId, 'requests' => $pedidos]);
    }

    // ==================================================================
    // (1) Duplo clique / repetição do mesmo POST de aceitação pública (sequencial)
    // ==================================================================
    public function test_c01_repeticao_sequencial_da_aceitacao_publica(): void
    {
        $c = $this->novoFunil();
        $codigo = $c['codeB'];

        $r1 = $this->comoVisitante()->post(route('proposals.accept', $codigo), $this->dadosAceitacao());
        $r1->assertSessionHas('success')->assertSessionMissing('error');
        $emails1 = $this->emails()->count();
        $antes = $this->contagens();

        // 2.º e 3.º pedidos idênticos (duplo clique / F5 sobre o POST).
        foreach ([2, 3] as $n) {
            $this->comoVisitante()->post(route('proposals.accept', $codigo), $this->dadosAceitacao())
                ->assertSessionHas('error', 'Esta cotação já foi aceite.')->assertSessionMissing('success');
        }

        $this->assertSame([], $this->diff($antes, $this->contagens()), 'Repetir não grava nada.');
        $this->assertSame($emails1, $this->emails()->count(), 'Repetir não reenvia emails.');
        $this->assertSame(1, ConvertedProposal::where('proposal_id', $c['propB'])->count());
        $this->assertSame(1, ConvertedProposalDocument::where('tipo', 'gerado')->count(), 'Um só contrato gerado.');
        $this->assertSame(1, LeadActivity::where('client_id', $c['client_id'])->where('title', 'Cotação aceite')->count());
        $this->assertSame(1, LeadActivity::where('client_id', $c['client_id'])->where('title', 'Lead convertido em cliente')->count());
        $this->assertSame('convertido', FormProposal::find($c['form_proposal_id'])->status);

        // Limite de pedidos da rota (throttle:5,1): o 6.º do mesmo IP no minuto é recusado com 429.
        $this->comoVisitante()->post(route('proposals.accept', $codigo), $this->dadosAceitacao());   // 4.º
        $this->comoVisitante()->post(route('proposals.accept', $codigo), $this->dadosAceitacao());   // 5.º
        $this->comoVisitante()->post(route('proposals.accept', $codigo), $this->dadosAceitacao())->assertStatus(429);
        $this->assertSame(1, ConvertedProposal::count());

        $this->ev('1 repetição sequencial da aceitação pública', '2.º pedido recusado, 1 convertida, sem emails/contratos repetidos',
            "2.º/3.º: erro 'Esta cotação já foi aceite.'; 0 linhas novas; emails inalterados ({$emails1}); 6.º pedido 429 (throttle)");
    }

    // ==================================================================
    // (2) Aceitação SIMULTÂNEA da mesma cotação (N processos reais)
    // ==================================================================
    public function test_c02_aceitacao_simultanea_da_mesma_cotacao(): void
    {
        $linhas = $this->concorrente(
            'C2 aceitar a MESMA cotação (8 processos)',
            fn () => $this->novoFunil(),
            fn ($c) => $this->jobs(8, [$this->pedido('POST', 'proposals.accept', $this->dadosAceitacao(), ['proposal_code' => $c['codeB']])]),
            function ($c, $run, $agg) {
                return [
                    'convertidas_da_cotacao' => ConvertedProposal::where('proposal_id', $c['propB'])->count(),
                    'contratos_gerados' => ConvertedProposalDocument::where('tipo', 'gerado')->whereIn('converted_proposal_id', ConvertedProposal::where('client_id', $c['client_id'])->pluck('id'))->count(),
                    'act_cotacao_aceite' => LeadActivity::where('client_id', $c['client_id'])->where('title', 'Cotação aceite')->count(),
                    'act_lead_convertido' => LeadActivity::where('client_id', $c['client_id'])->where('title', 'Lead convertido em cliente')->count(),
                    'historico_iniciada' => \App\Models\StatusProposalHistory::whereIn('converted_proposal_id', ConvertedProposal::where('proposal_id', $c['propB'])->pluck('id'))->count(),
                    'pedido' => FormProposal::find($c['form_proposal_id'])->status,
                    'proposal_status' => Proposal::find($c['propB'])->status,
                ];
            }
        );

        foreach ($linhas as $l) {
            // A garantia que existe de facto: lock da cotação + índice único em converted_proposals.proposal_id.
            $this->assertSame(1, $l['convertidas_da_cotacao'], 'Nunca 2 convertidas da mesma cotação.');
            $this->assertSame(1, $l['sucessos'], 'Exatamente 1 processo teve sucesso.');
            $this->assertSame(0, $l['http500']);
            $this->assertSame(0, $l['deadlocks']);
            $this->assertSame(1, $l['contratos_gerados']);
            $this->assertSame(1, $l['act_cotacao_aceite']);
            $this->assertSame(1, $l['act_lead_convertido']);
            $this->assertSame(1, $l['historico_iniciada']);
            $this->assertSame('Aprovada', $l['proposal_status']);
            $this->assertSame('convertido', $l['pedido']);
        }
        $this->ev('2 aceitação simultânea da mesma cotação', '1 convertida; 7 recusados com mensagem; 0 erros 500/deadlocks',
            self::resumo($linhas, ['convertidas_da_cotacao', 'sucessos', 'http500', 'deadlocks', 'unicidade', 'contratos_gerados']).' (não encontrado != provado seguro)');
    }

    // ==================================================================
    // (3) Aceitação simultânea das DUAS cotações do mesmo pedido
    // ==================================================================
    public function test_c03_aceitacao_simultanea_das_duas_cotacoes_do_mesmo_pedido(): void
    {
        $linhas = $this->concorrente(
            'C3 aceitar A e B do mesmo pedido (4+4 processos)',
            fn () => $this->novoFunil(),
            function ($c) {
                $a = $this->jobs(4, [$this->pedido('POST', 'proposals.accept', $this->dadosAceitacao(), ['proposal_code' => $c['codeA']])]);
                $b = $this->jobs(4, [$this->pedido('POST', 'proposals.accept', $this->dadosAceitacao(), ['proposal_code' => $c['codeB']])]);

                return array_merge($a, $b);
            },
            function ($c, $run, $agg) {
                return [
                    'convertidas_cliente' => ConvertedProposal::where('client_id', $c['client_id'])->count(),
                    'convertidas_A' => ConvertedProposal::where('proposal_id', $c['propA'])->count(),
                    'convertidas_B' => ConvertedProposal::where('proposal_id', $c['propB'])->count(),
                    'pedido' => FormProposal::find($c['form_proposal_id'])->status,
                    'act_lead_convertido' => LeadActivity::where('client_id', $c['client_id'])->where('title', 'Lead convertido em cliente')->count(),
                    'oppC' => ImportOpportunity::find($c['oppC'])->status->value,
                ];
            }
        );

        foreach ($linhas as $l) {
            // REGRA ATUAL (mapa 3): nada impede 2 convertidas do mesmo pedido; cada cotação só aceita 1x.
            // BUG: carro duplicado possível; o servidor serializa pelo lock do cliente mas não verifica o pedido.
            $this->assertSame(1, $l['convertidas_A']);
            $this->assertSame(1, $l['convertidas_B']);
            $this->assertSame(2, $l['convertidas_cliente'], 'BUG: as duas cotações do mesmo pedido ficam aceites.');
            $this->assertSame(2, $l['sucessos']);
            $this->assertSame(0, $l['http500']);
            $this->assertSame(0, $l['deadlocks']);
            // BUG (baixa): a lead é convertida uma vez, mas a entrada "Lead convertido em cliente" pode sair DUAS vezes:
            // ProposalObserver::updated lê $proposal->client com o snapshot REPEATABLE READ da transação (is_lead ainda
            // true) mesmo depois de o outro processo ter convertido; observado 2 em todas as repetições.
            $this->assertGreaterThanOrEqual(1, $l['act_lead_convertido']);
            $this->assertLessThanOrEqual(2, $l['act_lead_convertido']);
            $this->assertSame('convertido', $l['pedido']);
            $this->assertSame('por_contactar', $l['oppC']);
        }
        $this->ev('3 aceitação simultânea de A e B do mesmo pedido', 'idealmente só 1 convertida por pedido',
            'BUG (regra atual): '.self::resumo($linhas, ['convertidas_cliente', 'convertidas_A', 'convertidas_B', 'sucessos', 'deadlocks', 'http500', 'act_lead_convertido']).'; pedido convertido; oportunidade C continua por_contactar; act_lead_convertido=2 => entrada duplicada na timeline', 'BUG');
    }

    // ==================================================================
    // (4) Cotação expirada (relógio): público e BO
    // ==================================================================
    public function test_c04_cotacao_expirada_publico_e_backoffice(): void
    {
        $c = $this->novoFunil();
        $criada = Proposal::findOrFail($c['propA'])->created_at->copy();

        // ---- fronteira do prazo (Proposal::isExpired = created_at->diffInDays(now()) > 15) ----
        $fronteira = [];
        foreach (['14d23h' => $criada->copy()->addDays(14)->addHours(23), '15d0h' => $criada->copy()->addDays(15), '15d1h' => $criada->copy()->addDays(15)->addHour(), '16d' => $criada->copy()->addDays(16)] as $rotulo => $instante) {
            Carbon::setTestNow($instante);
            $fronteira[$rotulo] = Proposal::find($c['propA'])->isExpired();
        }
        Carbon::setTestNow();
        $this->assertFalse($fronteira['14d23h']);
        $this->assertFalse($fronteira['15d0h'], 'No instante exato dos 15 dias ainda não expirou.');
        $this->assertTrue($fronteira['16d']);
        $this->ev('4 fronteira do prazo (relógio)', 'expira depois de 15 dias', 'expirada? '.json_encode($fronteira).' (15d1h => '.($fronteira['15d1h'] ? 'já expirada (fração conta)' : 'ainda válida').')', 'INFO');

        // ---- público: depois de 16 dias ----
        Carbon::setTestNow($criada->copy()->addDays(16));
        $antes = $this->contagens();
        $this->comoVisitante()->get(route('proposals.detail', $c['codeA']))->assertOk()->assertSee('Cotação Expirada');
        $this->comoVisitante()->post(route('proposals.accept', $c['codeA']), $this->dadosAceitacao())
            ->assertSessionHas('error', 'Esta cotação já expirou. Contacte-nos para receber uma cotação atualizada.');
        $this->assertSame([], $this->diff($antes, $this->contagens()));
        $this->assertSame('Enviado', Proposal::find($c['propA'])->status, 'Expirar não grava estado.');
        $this->ev('4 aceitar expirada pelo fluxo público', 'recusado, nada gravado', "erro 'já expirou', 0 linhas novas, estado continua Enviado");

        // ---- BO: aceita a expirada (decisão de design; comentário no código) ----
        $emails = $this->emails()->count();
        $r = $this->bo()->postJson(route('admin.v2.proposals.accept', $c['propA']))->assertOk()->assertJson(['success' => true, 'status' => 'Aprovada']);
        $convA = ConvertedProposal::where('proposal_id', $c['propA'])->sole();
        $this->assertSame($emails, $this->emails()->count(), 'Aceitar no BO não envia email ao cliente.');
        $this->assertSame(0, ConvertedProposalDocument::where('converted_proposal_id', $convA->id)->count(), 'Nem gera/guarda o contrato.');
        $this->assertSame('Iniciada', $convA->status);
        $this->ev('4 aceitar expirada pelo BO', 'aceitar uma expirada pode ser intencional (cliente aceitou por telefone)',
            'aceita (convertida #'.$convA->id.'), sem email e sem contrato: a expiração não se aplica ao BO', 'AVISO');

        // ---- BO: aceitar uma Reprovada (a oportunidade já estava rejeitada) ----
        Carbon::setTestNow();
        $this->bo()->patchJson(route('admin.v2.proposals.updateStatus', $c['propB']), ['status' => 'Reprovada'])->assertOk();
        $this->assertSame('rejeitado', ImportOpportunity::find($c['oppB'])->status->value);
        $this->bo()->postJson(route('admin.v2.proposals.accept', $c['propB']))->assertOk()->assertJson(['success' => true]);
        $this->assertSame('Aprovada', Proposal::find($c['propB'])->status);
        $this->assertSame('rejeitado', ImportOpportunity::find($c['oppB'])->status->value, 'BUG: cotação Aprovada com a oportunidade ainda rejeitada.');
        $this->assertSame(2, ConvertedProposal::where('client_id', $c['client_id'])->count());
        $this->ev('4 aceitar uma Reprovada pelo BO', 'recusar ou repor a oportunidade', 'BUG: aceita; cotação Aprovada mas a oportunidade fica "rejeitado"; 2 convertidas do mesmo pedido', 'BUG');
    }

    // ==================================================================
    // (5) Criação repetida da viatura a partir da mesma convertida
    // ==================================================================
    public function test_c05_viatura_repetida_sequencial(): void
    {
        $c = $this->funilComConvertida();
        $this->bo()->post(route('admin.v2.converted-proposals.create-vehicle', $c['conv_id']))->assertSessionHas('success');
        $antes = $this->contagens();
        foreach ([1, 2] as $_) {
            $this->bo()->post(route('admin.v2.converted-proposals.create-vehicle', $c['conv_id']))
                ->assertSessionHas('success', 'Esta cotação convertida já tinha viatura.');
        }
        $this->assertSame([], $this->diff($antes, $this->contagens()));
        $this->assertSame(1, V3Vehicle::count());
        $this->assertSame(1, Legalization::count());
        $this->assertSame(1, LeadActivity::where('title', 'Viatura e legalização criadas')->count());
        $this->ev('5 viatura repetida (sequencial)', 'idempotente', '2 repetições => "já tinha viatura", 0 linhas novas');
    }

    public function test_c05_viatura_simultanea(): void
    {
        $linhas = $this->concorrente(
            'C5 criar viatura da MESMA convertida (8 processos)',
            fn () => $this->funilComConvertida(),
            fn ($c) => $this->jobs(8, [$this->pedido('POST', 'admin.v2.converted-proposals.create-vehicle', [], ['id' => $c['conv_id']])], $this->operador->id),
            function ($c, $run, $agg) {
                $conv = ConvertedProposal::find($c['conv_id']);

                return [
                    'viaturas_do_cliente' => V3Vehicle::where('client_id', $c['client_id'])->count(),
                    'legalizacoes' => $conv->v3_vehicle_id ? Legalization::where('v3_vehicle_id', $conv->v3_vehicle_id)->count() : 0,
                    'legalizacoes_cliente' => Legalization::where('client_id', $c['client_id'])->count(),
                    'viaturas_total' => V3Vehicle::count(),
                    'act_criadas' => LeadActivity::where('client_id', $c['client_id'])->where('title', 'Viatura e legalização criadas')->count(),
                    'v3_vehicle_id_ligado' => (bool) $conv->v3_vehicle_id,
                    'novas' => collect($run['workers'])->flatMap(fn ($w) => $w['requests'])->filter(fn ($r) => str_contains((string) ($r['flash']['success'] ?? ''), 'criadas a partir'))->count(),
                ];
            }
        );
        foreach ($linhas as $l) {
            $this->assertSame(1, $l['viaturas_do_cliente'], 'Lock da convertida: 1 só viatura.');
            $this->assertSame(1, $l['legalizacoes']);
            $this->assertSame(1, $l['act_criadas']);
            $this->assertSame(1, $l['novas'], 'Só um processo cria; os outros reutilizam.');
            $this->assertSame(0, $l['http500']);
            $this->assertSame(0, $l['deadlocks']);
        }
        $this->ev('5 viatura simultânea da mesma convertida', '1 viatura + 1 legalização', self::resumo($linhas, ['viaturas_do_cliente', 'legalizacoes', 'novas', 'deadlocks', 'http500']).' (não encontrado != provado seguro; sem índice único em legalizations.v3_vehicle_id)');
    }

    // ==================================================================
    // (6) Falha de email DEPOIS de gravar
    // ==================================================================
    private function mailFalha(): void
    {
        Mail::extend('falha', fn () => new TransportFalha());
        config(['mail.mailers.falha' => ['transport' => 'falha'], 'mail.default' => 'falha']);
        app('mail.manager')->purge('falha');
        app('mail.manager')->purge('array');
    }

    private function mailNormal(): void
    {
        config(['mail.default' => 'array']);
        app('mail.manager')->purge('falha');
        app('mail.manager')->purge('array');
    }

    private function textoLogs(?string $nivel = null): string
    {
        return collect($this->logs)->when($nivel, fn ($c) => $c->where('nivel', $nivel))->pluck('texto')->implode("\n");
    }

    public function test_c06a_falha_de_email_no_envio_da_cotacao_observer(): void
    {
        $c = $this->novoFunil(['enviar' => false]);
        $this->mailFalha();

        $r = $this->bo()->patchJson(route('admin.v2.proposals.updateStatus', $c['propA']), ['status' => 'Enviado', 'notify' => 1])->assertOk()->assertJson(['success' => true, 'status' => 'Enviado']);

        // Estado gravado, timeline e follow-up gravados ANTES dos emails; o pedido não rebenta.
        $this->assertSame('Enviado', Proposal::find($c['propA'])->status);
        $this->assertSame(2, TransportFalha::$tentativas, 'cliente + angariador tentados');
        $this->assertNotNull(Client::find($c['client_id'])->next_followup_at, 'Follow-up agendado apesar da falha.');
        $falha = LeadActivity::where('client_id', $c['client_id'])->where('title', "Falha ao enviar o email da cotação #{$c['propA']}")->sole();
        $this->assertStringContainsString('cliente, angariador', $falha->body);
        // BUG: a API devolve notified=true e a timeline diz "Email enviado com o link de acesso." mesmo com os dois envios falhados.
        $this->assertTrue($r->json('notified'));
        $enviada = LeadActivity::where('client_id', $c['client_id'])->where('title', "Cotação #{$c['propA']} enviada ao cliente")->sole();
        $this->assertStringContainsString('Email enviado com o link de acesso.', $enviada->body);

        // Logs: 2 erros sem dados pessoais (só ids, papel e classe da exceção).
        $erros = collect($this->logs)->where('nivel', 'error')->filter(fn ($l) => str_contains($l['texto'], 'Falha ao enviar o email de cotação enviada.'));
        $this->assertCount(2, $erros);
        $this->assertStringNotContainsString('@teste.invalid', $this->textoLogs());
        $this->assertStringNotContainsString('TESTE FLUXO Cliente', $this->textoLogs());

        $this->ev('6a falha de email no envio da cotação (observer)', 'estado/timeline/follow-up gravados; falha registada; log sem dados pessoais',
            'conforme (2 tentativas, atividade de falha, follow-up ok, log sem email). BUG menor: resposta notified=true e timeline "Email enviado com o link de acesso." apesar de ambos falharem', 'AVISO');
    }

    public function test_c06b_falha_de_email_na_aceitacao_publica(): void
    {
        $c = $this->novoFunil();
        $this->mailFalha();

        $resp = $this->comoVisitante()->post(route('proposals.accept', $c['codeB']), $this->dadosAceitacao());
        // Aceitação gravada; o cliente não vê erro nenhum.
        $resp->assertSessionHas('success', 'A proposta foi aceite. O cliente receberá um email com o contrato.')->assertSessionMissing('error');
        $conv = ConvertedProposal::where('proposal_id', $c['propB'])->sole();
        $this->assertSame('Aprovada', Proposal::find($c['propB'])->status);
        $this->assertSame('convertido', FormProposal::find($c['form_proposal_id'])->status);

        // BUG: o contrato fica registado como "enviado" (enviado_em) ANTES do envio, que falhou.
        $doc = ConvertedProposalDocument::where('converted_proposal_id', $conv->id)->sole();
        $this->assertSame('gerado', $doc->tipo);
        $this->assertNotNull($doc->enviado_em);
        // A tentativa ao cliente falha e o angariador nem é tentado; a 2.ª tentativa é o aviso interno a geral@.
        $this->assertSame(2, TransportFalha::$tentativas);
        // Nada na timeline avisa a equipa (só o log).
        $this->assertSame(0, LeadActivity::where('client_id', $c['client_id'])->where('title', 'like', '%alha%')->count());
        // Logs: report() regista a exceção com a mensagem do SMTP, que traz endereços (cliente, cc e bcc fixos).
        $logs = $this->textoLogs('error');
        $this->assertStringContainsString('SMTP indisponível', $logs);
        $this->assertStringContainsString('@teste.invalid', $logs);

        $this->ev('6b falha de email na aceitação pública', 'aceitação mantém-se; equipa avisada; log sem dados pessoais',
            'aceitação e contrato gerados; mensagem ao cliente diz "O cliente receberá um email" (falso); enviado_em preenchido sem envio; angariador não é tentado; nada na timeline; log do report() inclui endereços de email (mensagem SMTP)', 'BUG');
    }

    public function test_c06c_aceitacao_publica_de_cliente_sem_email(): void
    {
        $c = $this->novoFunil(['email' => false]);
        $this->comoVisitante()->post(route('proposals.accept', $c['codeB']), $this->dadosAceitacao())->assertSessionHas('success');
        $conv = ConvertedProposal::where('proposal_id', $c['propB'])->sole();
        $doc = ConvertedProposalDocument::where('converted_proposal_id', $conv->id)->sole();
        $this->assertNotNull($doc->enviado_em);
        $destinos = $this->emails()->map(fn ($m) => collect($m->getTo())->map->getAddress()->implode(','))->all();
        // O código NÃO protege o cliente sem email (Mail::to(null)): o transporte array aceita uma mensagem sem destinatário
        // ('' na lista) e o angariador recebe; com SMTP real o envio ao cliente lançaria exceção e o angariador ficaria sem email (NÃO verificado aqui).
        $this->assertSame(['', 'angariador.fluxo@teste.invalid', 'geral@izzycar.pt'], $destinos);
        $this->ev('6c aceitação pública de cliente sem email', 'não tentar enviar sem email e avisar a equipa',
            'aceita; contrato marcado enviado_em sem destinatário; tenta enviar ao cliente com destinatário vazio (array aceita; SMTP real não verificado); nada avisa a equipa de que o cliente não recebeu o contrato', 'BUG');
    }

    public function test_c06d_falha_de_email_no_envio_do_contrato_e_alertas(): void
    {
        config(['mail.admin_address' => 'alertas.fluxo@teste.invalid']);

        // --- estado da convertida com notificação ---
        $c = $this->funilComViatura();
        $this->mailFalha();
        $r = $this->bo()->patchJson(route('admin.v2.converted-proposals.update-status', $c['conv_id']), ['status' => 'Negociação Carro', 'notify' => 1])->assertOk();
        $this->assertSame('Negociação Carro', ConvertedProposal::find($c['conv_id'])->status);
        $this->assertFalse($r->json('notified'), 'Aqui a resposta é honesta: notified=false.');
        $this->assertStringContainsString('SMTP indisponível', $this->textoLogs('error'));

        // --- alerta de comissão em atraso (comando agendado) ---
        $this->bo()->patchJson(route('admin.v2.converted-proposals.update-status', $c['conv_id']), ['status' => 'Entrega', 'notify' => 0])->assertOk();
        $entrega = ConvertedProposal::find($c['conv_id'])->deliveredAt();
        Carbon::setTestNow($entrega->copy()->addHours(49));
        $excecao = null;
        try {
            Artisan::call('commissions:overdue-alert');
        } catch (\Throwable $e) {
            $excecao = $e;
        }
        $this->assertNotNull($excecao, 'O comando propaga a falha (o cron regista o erro).');
        $chave = 'commission_overdue_alert:'.$c['conv_id'];
        $this->assertFalse(Cache::has($chave), 'Falhou => não marca como avisada: volta a tentar no dia seguinte.');
        $this->mailNormal();
        Artisan::call('commissions:overdue-alert');
        $this->assertTrue(Cache::has($chave));
        $this->assertCount(1, $this->emails()->filter(fn ($m) => collect($m->getTo())->map->getAddress()->contains('alertas.fluxo@teste.invalid')));
        Carbon::setTestNow();

        // --- email de acompanhamento da legalização (comando agendado) ---
        $leg = Legalization::findOrFail($c['legalization_id']);
        $this->assertFalse((bool) $leg->email_enviado);
        $this->mailFalha();
        Artisan::call('legalizations:send-tracking-emails');
        $this->assertStringContainsString('Erro ao enviar email da legalização', Artisan::output());
        $this->assertFalse((bool) Legalization::find($leg->id)->email_enviado, 'Falhou => continua por enviar.');
        $this->mailNormal();
        Artisan::call('legalizations:send-tracking-emails');
        $this->assertTrue((bool) Legalization::find($leg->id)->email_enviado);

        $this->ev('6d falha de email: estado da convertida, alerta de comissão, acompanhamento da legalização', 'estado gravado; sem marcar como enviado; repete depois',
            'conforme: update-status notified=false; alerta propaga a exceção e não marca (repete); acompanhamento: email_enviado fica false e envia na corrida seguinte. Só a consola mostra o erro do acompanhamento (sem log nem alerta); o log do report() do estado inclui endereços', 'AVISO');
    }

    // ==================================================================
    // (7) Dados obrigatórios em falta, formulário a formulário
    // ==================================================================
    public function test_c07_dados_em_falta_em_cada_formulario(): void
    {
        $c = $this->funilComViatura();
        $semGravar = function (string $nome, \Closure $acao, array $campos) {
            $antes = $this->contagens();
            $r = $acao();
            if ($campos) {
                $r->assertSessionHasErrors($campos);
            }
            $this->assertSame([], $this->diff($antes, $this->contagens()), "{$nome}: não podia gravar nada.");
            $this->ev("7 {$nome}", '422/redirect com erros; nada gravado', 'erros em '.json_encode($campos).'; 0 linhas novas');

            return $r;
        };

        $semGravar('lead sem nome', fn () => $this->bo()->post(route('admin.v2.leads.store'), ['email' => 'sem.nome@teste.invalid']), ['name']);
        $semGravar('pedido manual sem cliente', fn () => $this->bo()->post(route('admin.v2.form-proposals.store'), ['brand' => 'BMW']), ['client_id']);
        $semGravar('oportunidade sem marca/modelo', fn () => $this->bo()->post(route('admin.v2.form-proposals.opportunities.store', $c['form_proposal_id']), ['price' => 1000]), ['brand', 'model']);
        $semGravar('oportunidade com VIN curto', fn () => $this->bo()->post(route('admin.v2.form-proposals.opportunities.store', $c['form_proposal_id']), ['brand' => 'BMW', 'model' => 'i4', 'vin' => 'CURTO']), ['vin']);
        $semGravar('cotação sem cliente/marca/modelo/transporte', fn () => $this->bo()->post(route('admin.v2.proposals.store'), ['status' => 'Pendente']), ['client_id', 'brand', 'model', 'transport_cost']);
        $semGravar('aceitação pública com email inválido', fn () => $this->comoVisitante()->post(route('proposals.accept', $c['codeA']), ['email' => 'nao-e-email']), ['email']);
        $semGravar('aceitação pública com validade do documento inválida', fn () => $this->comoVisitante()->post(route('proposals.accept', $c['codeA']), ['validate_identification_number' => 'ontem']), ['validate_identification_number']);
        $semGravar('convertida sem cliente', fn () => $this->bo()->put(route('admin.v2.converted-proposals.update', $c['conv_id']), ['observacoes' => 'x']), ['client_id']);
        $semGravar('convertida com valor não numérico', fn () => $this->bo()->put(route('admin.v2.converted-proposals.update', $c['conv_id']), ['client_id' => $c['client_id'], 'custo_ipo' => 'abc']), ['custo_ipo']);
        $semGravar('legalização sem marca/modelo/combustível', fn () => $this->bo()->post(route('admin.legalizations.store'), ['matricula' => 'AA-00-AA']), ['marca', 'modelo', 'combustivel']);
        $semGravar('legalização com viatura inexistente', fn () => $this->bo()->post(route('admin.legalizations.store'), ['v3_vehicle_id' => 999999]), ['v3_vehicle_id']);
        $semGravar('viatura com ano impossível', fn () => $this->bo()->post(route('admin.v3.vehicles.store'), ['year' => 1800]), ['year']);
        $semGravar('viatura de convertida inexistente', fn () => tap($this->bo()->post(route('admin.v2.converted-proposals.create-vehicle', 999999)), fn ($r) => $r->assertNotFound()), []);
        foreach ([0, 8, 'x', ''] as $passo) {
            $antes = $this->contagens();
            $this->bo()->postJson(route('admin.legalizations.toggle-step', $c['legalization_id']), ['step' => $passo])->assertStatus(422)->assertJsonValidationErrors('step');
            $this->bo()->postJson(route('admin.v3.vehicles.legalization.toggle-step', $c['vehicle_id']), ['step' => $passo])->assertStatus(422)->assertJsonValidationErrors('step');
            $this->assertSame([], $this->diff($antes, $this->contagens()));
        }
        $this->assertSame([], Legalization::find($c['legalization_id'])->steps_completed);
        $this->ev('7 passo da legalização inválido (0, 8, "x", vazio)', '422 nas duas portas, nada gravado', '422 com erro em step nas duas portas');

        // Casos em que o servidor ACEITA dados em falta (comportamento atual):
        // BUG: viatura sem nenhum dado (marca/modelo/ano) é criada vazia.
        $antes = $this->contagens();
        $this->bo()->post(route('admin.v3.vehicles.store'), [])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(['viaturas' => 1], $this->diff($antes, $this->contagens()));
        $this->ev('7 viatura (admin.v3.vehicles.store) sem dados', 'exigir pelo menos marca/modelo', 'BUG: cria viatura vazia (todos os campos nullable) com referência gerada', 'BUG');

        // BUG (B7): aceitação pública sem NIF/CC/validade/morada é aceite.
        $this->comoVisitante()->post(route('proposals.accept', $c['codeA']), [])->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame(2, ConvertedProposal::where('client_id', $c['client_id'])->count());
        $this->ev('7 aceitação pública sem identificação', 'servidor exige NIF/CC', 'BUG (B7): aceita corpo vazio (campos só obrigatórios no browser)', 'BUG');
    }

    // ==================================================================
    // (8) Matriz de permissões do percurso
    // ==================================================================
    public function test_c08_matriz_de_permissoes_do_percurso(): void
    {
        $c = $this->funilComViatura();
        $rotas = [
            'GET leads.index' => fn () => $this->get(route('admin.v2.leads.index')),
            'GET leads.create' => fn () => $this->get(route('admin.v2.leads.create')),
            'POST leads.store' => fn () => $this->post(route('admin.v2.leads.store'), ['name' => 'TESTE FLUXO matriz '.uniqid(), 'origin' => 'Outro']),
            'POST form-proposals.store' => fn () => $this->post(route('admin.v2.form-proposals.store'), ['client_id' => $c['client_id'], 'brand' => 'BMW']),
            'POST opportunities.store' => fn () => $this->post(route('admin.v2.form-proposals.opportunities.store', $c['form_proposal_id']), ['brand' => 'BMW', 'model' => 'i4']),
            'GET proposals.index' => fn () => $this->get(route('admin.v2.proposals.index')),
            'POST proposals.store' => fn () => $this->post(route('admin.v2.proposals.store'), $this->payloadCotacao($c, 'C')),
            'PATCH proposals.updateStatus' => fn () => $this->patchJson(route('admin.v2.proposals.updateStatus', $c['propA']), ['status' => 'Sem resposta', 'notify' => 0]),
            'POST proposals.accept (BO)' => fn () => $this->postJson(route('admin.v2.proposals.accept', $c['propA'])),
            'GET converted.index' => fn () => $this->get(route('admin.v2.converted-proposals.index')),
            'PUT converted.update' => fn () => $this->put(route('admin.v2.converted-proposals.update', $c['conv_id']), ['client_id' => $c['client_id'], 'observacoes' => 'matriz']),
            'POST converted.create-vehicle' => fn () => $this->post(route('admin.v2.converted-proposals.create-vehicle', $c['conv_id'])),
            'POST angariadores.toggle-paid' => fn () => $this->post(route('admin.v2.angariadores.toggle-paid', $c['conv_id'])),
            'POST legalizations.toggle-step' => fn () => $this->postJson(route('admin.legalizations.toggle-step', $c['legalization_id']), ['step' => 1]),
            'POST vehicles.legalization.toggle-step' => fn () => $this->postJson(route('admin.v3.vehicles.legalization.toggle-step', $c['vehicle_id']), ['step' => 1]),
            'GET tasks.index' => fn () => $this->get(route('admin.tasks.index')),
            'GET movements.index' => fn () => $this->get(route('admin.v2.movements.index')),
            'GET angariadores.comissoes' => fn () => $this->get(route('admin.v2.angariadores.comissoes')),
        ];
        $utilizadores = [
            'sem perfil' => $this->semPerfil, 'role vazio' => $this->roleVazio, 'cms (sem funil)' => $this->semFunil,
            'angariador' => $this->angariador, 'Importador' => $this->operador, 'admin' => $this->admin,
        ];

        $matriz = [];
        foreach ([false, true] as $enforce) {
            config(['permissions.enforce' => $enforce]);
            foreach ($utilizadores as $nome => $user) {
                foreach ($rotas as $rota => $chamar) {
                    $this->actingAs($user);
                    $matriz[$enforce ? 'enforce ON' : 'enforce OFF'][$nome][$rota] = $chamar()->getStatusCode();
                }
            }
        }
        config(['permissions.enforce' => false]);

        $bloqueadas = fn (string $modo, string $quem) => array_keys(array_filter($matriz[$modo][$quem], fn ($s) => $s === 403));
        $todas = array_keys($rotas);

        // Sem perfil e angariador (fora do portal): 403 em tudo, com enforce ligado ou não.
        foreach (['enforce OFF', 'enforce ON'] as $modo) {
            $this->assertEqualsCanonicalizing($todas, $bloqueadas($modo, 'sem perfil'), "sem perfil / {$modo}");
            $this->assertEqualsCanonicalizing($todas, $bloqueadas($modo, 'angariador'), "angariador / {$modo}");
        }
        // Enforce LIGADO: perfis sem os objetos do funil ficam bloqueados em tudo.
        foreach (['role vazio', 'cms (sem funil)'] as $quem) {
            $this->assertEqualsCanonicalizing($todas, $bloqueadas('enforce ON', $quem), "{$quem} / enforce ON");
        }
        // Enforce DESLIGADO: perfis sem permissões passam (as regras só registam). Exceção: o "can()" direto do create-vehicle.
        foreach (['role vazio', 'cms (sem funil)'] as $quem) {
            // BUG/AVISO: sem permissões do funil conseguem criar/editar/aceitar/pagar comissões (só log "would_block").
            $this->assertSame(['POST converted.create-vehicle'], $bloqueadas('enforce OFF', $quem), "{$quem} / enforce OFF");
        }
        // Enforce LIGADO, Importador: só as comissões (B3 do cenário principal).
        $this->assertEqualsCanonicalizing(['POST angariadores.toggle-paid', 'GET angariadores.comissoes'], $bloqueadas('enforce ON', 'Importador'));
        $this->assertSame([], $bloqueadas('enforce OFF', 'Importador'));
        $this->assertSame([], $bloqueadas('enforce OFF', 'admin'));
        $this->assertSame([], $bloqueadas('enforce ON', 'admin'));

        foreach ($matriz as $modo => $porUser) {
            foreach ($porUser as $nome => $linhas) {
                $this->ev("8 matriz {$modo} / {$nome}", 'ver asserts', 'bloqueadas(403)='.json_encode($bloqueadas($modo, $nome), JSON_UNESCAPED_UNICODE).' | resto='.json_encode(array_count_values(array_map('strval', array_diff_key($linhas, array_flip($bloqueadas($modo, $nome)))))), 'INFO');
            }
        }
        $this->ev('8 perfil sem permissões com enforce desligado', 'bloquear ou pelo menos recusar escritas', 'BUG (design): role vazio e cms criam leads/pedidos/oportunidades/cotações, aceitam cotações, editam convertidas e pagam comissões (PERMISSIONS_ENFORCE=false só regista); só o create-vehicle recusa (can() direto)', 'BUG');
    }

    // ==================================================================
    // (9) Repetição de ações geradoras de movimentos/tarefas/comissões
    // ==================================================================
    public function test_c09_indices_unicos_em_falta(): void
    {
        $existe = fn (string $tabela, array $colunas) => collect(DB::select(
            'SELECT INDEX_NAME AS i, NON_UNIQUE AS nu, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS cols FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? GROUP BY INDEX_NAME, NON_UNIQUE',
            [self::BASE_PERMITIDA, $tabela]
        ))->contains(fn ($ix) => $ix->cols === implode(',', $colunas) && (int) $ix->nu === 0);

        $this->assertFalse($existe('expenses', ['source_type', 'source_id']), 'BUG: expenses(source_type, source_id) NÃO tem índice único.');
        $this->assertFalse($existe('legalizations', ['v3_vehicle_id']), 'Sem índice único em legalizations.v3_vehicle_id.');
        $this->assertTrue($existe('converted_proposals', ['proposal_id']), 'converted_proposals.proposal_id é único (a única rede real da aceitação).');
        $this->assertFalse($existe('tasks', ['v3_vehicle_id']));
        $this->ev('9 índices únicos', 'expenses(source_type,source_id) único', 'BUG: não existe (nem em legalizations.v3_vehicle_id); existe em converted_proposals.proposal_id (information_schema na base de teste, migrate:fresh do repositório)', 'BUG');
    }

    public function test_c09_repeticoes_sequenciais_de_acoes_geradoras(): void
    {
        $c = $this->funilComViatura();
        $id = $c['conv_id'];

        // togglePaid da comissão: 0 -> 1 -> 0 -> 1, sempre <= 1 movimento e igual à flag.
        $estados = [];
        for ($i = 0; $i < 4; $i++) {
            $this->bo()->post(route('admin.v2.angariadores.toggle-paid', $id))->assertRedirect();
            $estados[] = [(bool) ConvertedProposal::find($id)->comissao_paga, Expense::where('source_type', Expense::importSourceType('comissao'))->where('source_id', $id)->count()];
        }
        $this->assertSame([[true, 1], [false, 0], [true, 1], [false, 0]], $estados);
        $this->assertSame([], $this->movimentosDuplicados($id));

        // Tranche paga / não paga / paga.
        $t = fn () => Expense::where('source_type', Expense::importSourceType('primeira_tranche'))->where('source_id', $id)->count();
        $put = fn (array $x) => $this->bo()->put(route('admin.v2.converted-proposals.update', $id), ['client_id' => $c['client_id']] + $x)->assertSessionHasNoErrors();
        $put(['primeira_tranche_pago' => 1]);
        $this->assertSame(1, $t());
        $put(['primeira_tranche_pago' => 1]);
        $this->assertSame(1, $t());
        $put(['primeira_tranche_pago' => 0]);
        $this->assertSame(0, $t());
        $this->assertNull(ConvertedProposal::find($id)->primeira_tranche_paga_em);
        $put(['primeira_tranche_pago' => 1]);
        $this->assertSame(1, $t());

        // Registar custos 2x.
        $put(['transporte_pago' => 1, 'ipo_pago' => 1]);
        $put(['transporte_pago' => 1, 'ipo_pago' => 1]);
        $this->assertSame(3, $this->movimentosDe($id));
        $this->assertSame([], $this->movimentosDuplicados($id));
        $this->assertSame($this->movimentosDe($id), FinancialMovement::where('movable_type', Expense::class)->whereIn('movable_id', Expense::fromConvertedProposal($id)->pluck('id'))->count());

        // Aceitar 2x (BO) e criar convertida outra vez.
        $antes = $this->contagens();
        $this->bo()->postJson(route('admin.v2.proposals.accept', $c['propB']))->assertStatus(422)->assertJson(['success' => false, 'message' => 'Esta cotação já foi aceite.']);
        $this->assertSame([], $this->diff($antes, $this->contagens()));

        $this->ev('9 repetição sequencial (comissão 4x, tranche on/off/on, custos 2x, aceitar 2x)', 'sem duplicados, flag e movimento coerentes', 'conforme: movimentos 1/0/1/0 e 1/1/0/1; custos 2x => 3 movimentos sem duplicados; FinancialMovement 1:1; aceitar 2x pelo BO => 422');
    }

    public function test_c09_recalculo_de_tranches(): void
    {
        $c = $this->funilComConvertida();
        $id = $c['conv_id'];
        $conv = ConvertedProposal::find($id);
        $this->assertEquals(1272.50, $conv->valor_primeira_tranche);

        Setting::where('label', 'init_payment')->update(['value' => '60']);
        Artisan::call('converted-proposals:recalculate-tranches', ['id' => $id]);   // só mostra
        $this->assertEquals(1272.50, ConvertedProposal::find($id)->valor_primeira_tranche, 'Sem --apply nada grava.');
        $antes = $this->contagens();
        Artisan::call('converted-proposals:recalculate-tranches', ['id' => $id, '--apply' => true]);
        $conv->refresh();
        $this->assertEquals(1527.00, $conv->valor_primeira_tranche);
        $this->assertEquals(1018.00, $conv->valor_segunda_tranche);
        Artisan::call('converted-proposals:recalculate-tranches', ['id' => $id, '--apply' => true]);   // 2.ª vez
        $this->assertStringContainsString('Nada a alterar', Artisan::output());
        $this->assertSame([], $this->diff($antes, $this->contagens()), 'Recalcular não cria movimentos nem tarefas.');

        // Com a 1.ª tranche paga o comando ignora e o movimento mantém o valor antigo (coerente, mas só por ignorar).
        $this->bo()->put(route('admin.v2.converted-proposals.update', $id), ['client_id' => $c['client_id'], 'primeira_tranche_pago' => 1])->assertSessionHasNoErrors();
        $valorMov = (float) Expense::where('source_type', Expense::importSourceType('primeira_tranche'))->where('source_id', $id)->value('amount');
        Setting::where('label', 'init_payment')->update(['value' => '30']);
        Artisan::call('converted-proposals:recalculate-tranches', ['id' => $id, '--apply' => true]);
        $this->assertStringContainsString('ignorada', Artisan::output());
        $this->assertEquals(1527.00, ConvertedProposal::find($id)->valor_primeira_tranche);
        $this->assertEquals($valorMov, (float) Expense::where('source_type', Expense::importSourceType('primeira_tranche'))->where('source_id', $id)->value('amount'));
        $this->assertSame([], $this->movimentosDuplicados($id));

        $this->ev('9 recálculo de tranches', 'idempotente, sem tocar em movimentos; ignora convertidas com tranche paga', 'conforme: 60/40 => 1527,00/1018,00; 2.ª vez "Nada a alterar"; paga => ignorada; 0 linhas novas');
    }

    public function test_c09_passo_5_repetido_sequencial_e_tarefa_modelo9(): void
    {
        $c = $this->funilComViatura();
        $mod = fn (int $n) => $this->bo()->postJson(route('admin.legalizations.toggle-step', $c['legalization_id']), ['step' => $n])->assertOk();
        $tab = fn (int $n) => $this->bo()->postJson(route('admin.v3.vehicles.legalization.toggle-step', $c['vehicle_id']), ['step' => $n])->assertOk();

        foreach ([1, 2, 3, 4] as $n) {
            $mod($n);
        }
        $tab(5);
        $this->assertSame(1, Task::count());
        // desmarcar e remarcar o 5, alternando as duas portas: tarefa aberta => não duplica
        $mod(5); $tab(5); $mod(5); $tab(5);
        $this->assertSame(1, Task::count());
        // tarefa concluída => o passo 5 repetido cria outra (B8); cancelada também
        $t = Task::sole();
        $this->bo()->patchJson(route('admin.tasks.update-status', $t->id), ['status' => 'cancelada'])->assertOk();
        $mod(5); $mod(5);
        $this->assertSame(2, Task::count(), 'BUG (B8): com a tarefa fechada, repetir o passo 5 cria outra.');
        $this->assertSame(1, Task::whereIn('status', ['pendente', 'em_progresso'])->count());
        $this->ev('9 passo 5 repetido (sequencial, duas portas)', 'tarefa Modelo 9 única por processo', 'aberta => não duplica; fechada/cancelada => 2.ª tarefa (B8 confirmado também para "cancelada")', 'BUG');
    }

    // --- concorrência das ações geradoras ---

    public function test_c09_comissao_toggle_em_paralelo(): void
    {
        $linhas = $this->concorrente(
            'C9a togglePaid da comissão (6 processos)',
            fn () => $this->funilComConvertida(),
            fn ($c) => $this->jobs(6, [$this->pedido('POST', 'admin.v2.angariadores.toggle-paid', [], ['proposal' => $c['conv_id']])], $this->operador->id),
            function ($c, $run, $agg) {
                $conv = ConvertedProposal::find($c['conv_id']);
                $n = Expense::where('source_type', Expense::importSourceType('comissao'))->where('source_id', $c['conv_id'])->count();

                return ['comissao_paga' => (bool) $conv->comissao_paga, 'movimentos_comissao' => $n, 'paridade_esperada_paga' => false,
                    'coerente' => ((bool) $conv->comissao_paga) === ($n === 1), 'duplicados' => $this->movimentosDuplicados($c['conv_id'])];
            }
        );
        foreach ($linhas as $l) {
            // BUG: sem lock nem índice único, o toggle em paralelo pode gravar VÁRIOS movimentos de comissão (observado até 6) e
            // perde alternâncias (a flag fica "paga" mesmo com nº par de cliques). O que se garante: nunca 500/deadlock e >=1 movimento.
            $this->assertGreaterThanOrEqual(1, $l['movimentos_comissao']);
            $this->assertSame(0, $l['http500']);
            $this->assertSame(0, $l['deadlocks']);
        }
        $perdas = count(array_filter($linhas, fn ($l) => $l['comissao_paga'] !== $l['paridade_esperada_paga']));
        $incoerentes = count(array_filter($linhas, fn ($l) => ! $l['coerente']));
        $this->ev('9 togglePaid em paralelo (6x)', '6 alternâncias => não paga (par), 0 movimentos, sem duplicados',
            self::resumo($linhas, ['comissao_paga', 'movimentos_comissao', 'coerente', 'duplicados', 'deadlocks']).". Estado final diferente da paridade em {$perdas}/".count($linhas)." repetições (lost update: sem lock); flag/movimento incoerentes em {$incoerentes}", $perdas || $incoerentes ? 'BUG' : 'OK');
    }

    public function test_c09_tranche_paga_em_paralelo_duplica_movimentos(): void
    {
        $linhas = $this->concorrente(
            'C9b marcar tranche+custos pagos (8 processos)',
            fn () => $this->funilComConvertida(),
            fn ($c) => $this->jobs(8, [$this->pedido('PUT', 'admin.v2.converted-proposals.update', ['client_id' => $c['client_id'], 'primeira_tranche_pago' => 1, 'transporte_pago' => 1, 'ipo_pago' => 1], ['id' => $c['conv_id']])], $this->operador->id),
            function ($c, $run, $agg) {
                $por = Expense::fromConvertedProposal($c['conv_id'])->selectRaw('source_type, count(*) as n')->groupBy('source_type')->pluck('n', 'source_type')->all();
                $ids = Expense::fromConvertedProposal($c['conv_id'])->pluck('id');

                return ['movimentos_total' => array_sum($por), 'esperado' => 3, 'duplicados' => $this->movimentosDuplicados($c['conv_id']),
                    'financial_movements' => FinancialMovement::where('movable_type', Expense::class)->whereIn('movable_id', $ids)->count()];
            }
        );
        foreach ($linhas as $l) {
            $this->assertSame(0, $l['http500']);
            $this->assertSame(0, $l['deadlocks']);
            $this->assertGreaterThanOrEqual(3, $l['movimentos_total']);
        }
        $comDup = count(array_filter($linhas, fn ($l) => $l['duplicados'] !== []));
        $this->ev('9 marcar tranche/custos pagos em paralelo (8x)', '3 movimentos (1 por entrada), sem duplicados',
            self::resumo($linhas, ['movimentos_total', 'duplicados', 'financial_movements', 'deadlocks', 'unicidade']).". Repetições com duplicados: {$comDup}/".count($linhas)." (sem índice único em expenses(source_type,source_id); firstOrNew não é atómico)", $comDup ? 'BUG' : 'OK');
        // O resultado é registado, não imposto: "não encontrado" não prova segurança (ver test_c09_indices_unicos_em_falta).
    }

    public function test_c09_passo_5_em_paralelo_cria_varias_tarefas(): void
    {
        $linhas = $this->concorrente(
            'C9c passo 5 em paralelo (6 processos, duas portas)',
            function () {
                $c = $this->funilComViatura();
                foreach ([1, 2, 3, 4] as $n) {
                    $this->bo()->postJson(route('admin.legalizations.toggle-step', $c['legalization_id']), ['step' => $n])->assertOk();
                }

                return $c;
            },
            function ($c) {
                $mod = $this->pedido('POST', 'admin.legalizations.toggle-step', ['step' => 5], ['legalization' => $c['legalization_id']], true);
                $tab = $this->pedido('POST', 'admin.v3.vehicles.legalization.toggle-step', ['step' => 5], ['id' => $c['vehicle_id']], true);

                return array_merge($this->jobs(3, [$mod], $this->operador->id), $this->jobs(3, [$tab], $this->operador->id));
            },
            function ($c, $run, $agg) {
                return ['tarefas_modelo9' => Task::where('v3_vehicle_id', $c['vehicle_id'])->where('title', 'like', 'Entregar Modelo 9 IMT%')->count(),
                    'passos' => Legalization::find($c['legalization_id'])->steps_completed];
            }
        );
        foreach ($linhas as $l) {
            $this->assertSame(0, $l['http500']);
            $this->assertGreaterThanOrEqual(1, $l['tarefas_modelo9']);
        }
        $comVarias = count(array_filter($linhas, fn ($l) => $l['tarefas_modelo9'] > 1));
        $this->ev('9 passo 5 em paralelo (módulo + tab)', 'exatamente 1 tarefa Modelo 9', self::resumo($linhas, ['tarefas_modelo9', 'passos', 'deadlocks', 'http500']).". Repetições com >1 tarefa: {$comVarias}/".count($linhas).' (verificação "já existe aberta" sem lock nem índice)', $comVarias ? 'BUG' : 'OK');
    }

    public function test_c09_cotacao_duplicada_por_oportunidade_em_paralelo(): void
    {
        $linhas = $this->concorrente(
            'C9d criar cotação da MESMA oportunidade (6 processos)',
            fn () => $this->novoFunil(['cotacoes' => false]),
            fn ($c) => $this->jobs(6, [$this->pedido('POST', 'admin.v2.proposals.store', $this->payloadCotacao($c, 'A'))], $this->operador->id),
            function ($c, $run, $agg) {
                return ['cotacoes_do_cliente' => Proposal::where('client_id', $c['client_id'])->count(),
                    'oportunidade_aponta_para' => ImportOpportunity::find($c['oppA'])->proposal_id,
                    'atividades_cotacao_criada' => LeadActivity::where('client_id', $c['client_id'])->where('title', 'like', 'Cotação #% criada%')->count()];
            }
        );
        foreach ($linhas as $l) {
            $this->assertSame(0, $l['http500']);
            $this->assertGreaterThanOrEqual(1, $l['cotacoes_do_cliente']);
        }
        $com = count(array_filter($linhas, fn ($l) => $l['cotacoes_do_cliente'] > 1));
        $this->ev('9 criar cotação da mesma oportunidade em paralelo', '1 cotação por oportunidade', self::resumo($linhas, ['cotacoes_do_cliente', 'atividades_cotacao_criada', 'deadlocks', 'http500']).". Repetições com >1 cotação: {$com}/".count($linhas).' (mapa 1.3: verificação sem lock)', $com ? 'BUG' : 'OK');
    }

    // ==================================================================
    // (10) Reversão de etapas da legalização, pelas duas portas
    // ==================================================================
    public function test_c10_reversao_de_etapas_da_legalizacao_nas_duas_portas(): void
    {
        $resultados = [];
        foreach (['modulo', 'tab'] as $porta) {
            $c = $this->funilComViatura();
            $leg = $c['legalization_id'];
            $vid = $c['vehicle_id'];
            $passo = $porta === 'modulo'
                ? fn (int $n) => $this->bo()->postJson(route('admin.legalizations.toggle-step', $leg), ['step' => $n])
                : fn (int $n) => $this->bo()->postJson(route('admin.v3.vehicles.legalization.toggle-step', $vid), ['step' => $n]);
            $L = fn () => Legalization::find($leg);

            foreach ([1, 2, 3, 4, 5, 6, 7] as $n) {
                $passo($n)->assertOk();
            }
            $this->assertSame([1, 2, 3, 4, 5, 6, 7], $L()->steps_completed);
            $this->assertNull($L()->currentStep());
            $tarefa = Task::where('v3_vehicle_id', $vid)->sole();
            $L()->forceFill(['email_enviado' => true, 'modelo9_dados' => ['x' => 'TESTE']])->save();
            $statusConv = ConvertedProposal::find($c['conv_id'])->status;
            $statusViatura = V3Vehicle::find($vid)->status;

            // desmarcar o passo 3 com 4-7 feitos: cascata
            $passo(3)->assertOk()->assertJson(['completed' => [1, 2]]);
            $this->assertSame([1, 2], $L()->steps_completed);
            $this->assertSame(3, $L()->currentStep());
            $this->assertSame('pendente', $tarefa->fresh()->status, 'AVISO: a tarefa do Modelo 9 não é cancelada quando o passo 5 é desfeito.');
            $this->assertTrue((bool) $L()->email_enviado, 'O email de acompanhamento já enviado não é reposto.');
            $this->assertSame(['x' => 'TESTE'], $L()->modelo9_dados);
            $this->assertSame($statusConv, ConvertedProposal::find($c['conv_id'])->status);
            $this->assertSame($statusViatura, V3Vehicle::find($vid)->status);
            $this->assertSame(0, Legalization::filtrar(['estado' => 'concluida'])->count());
            $this->assertSame(1, Legalization::filtrar(['estado' => 'em_curso'])->where('id', $leg)->count());

            // remarcar o 3: os 4-7 NÃO voltam (perde-se o progresso)
            $passo(3)->assertOk();
            $this->assertSame([1, 2, 3], $L()->steps_completed);
            $passo(5)->assertStatus(422);   // continua a exigir o 4
            $passo(4)->assertOk();

            // desmarcar o 5 e voltar a marcar com a tarefa aberta: sem 2.ª tarefa
            $passo(5)->assertOk();   // marca (a tarefa aberta é reutilizada)
            $passo(5)->assertOk();   // desmarca
            $this->assertSame([1, 2, 3, 4], $L()->steps_completed);
            $passo(5)->assertOk();   // marca outra vez
            $this->assertSame([1, 2, 3, 4, 5], $L()->steps_completed);
            $this->assertSame(1, Task::where('v3_vehicle_id', $vid)->count());

            // tarefa Modelo 9 concluída; desfazer e refazer o 5 cria nova (B8)
            $this->bo()->patchJson(route('admin.tasks.update-status', $tarefa->id), ['status' => 'concluida'])->assertOk();
            $passo(5)->assertOk();   // desmarca
            $passo(5)->assertOk();   // marca
            $this->assertSame(2, Task::where('v3_vehicle_id', $vid)->count());

            $resultados[$porta] = ['passos' => $L()->steps_completed, 'tarefas' => Task::where('v3_vehicle_id', $vid)->count(), 'estado_conv' => ConvertedProposal::find($c['conv_id'])->status, 'estado_viatura' => V3Vehicle::find($vid)->status];
        }
        $this->assertSame($resultados['modulo'], $resultados['tab'], 'As duas portas têm de se comportar igual.');

        // Kanban (módulo): arrastar para trás desmarca vários; arrastar para a frente MARCA vários de uma vez.
        $c = $this->funilComViatura();
        $leg = $c['legalization_id'];
        $move = fn ($col) => $this->bo()->postJson(route('admin.legalizations.move', $leg), ['column' => $col])->assertOk();
        $move('done');
        $this->assertSame([1, 2, 3, 4, 5, 6, 7], Legalization::find($leg)->steps_completed);
        $this->assertSame(1, Task::where('v3_vehicle_id', $c['vehicle_id'])->count(), 'Arrastar para "done" marca o passo 5 e cria a tarefa.');
        $move(3);
        $this->assertSame([1, 2], Legalization::find($leg)->steps_completed);
        $move(6);
        $this->assertSame([1, 2, 3, 4, 5], Legalization::find($leg)->steps_completed, 'Avançar para a coluna 6 marca 3, 4 e 5 de uma vez, sem validar nada.');
        $this->assertSame(1, Task::where('v3_vehicle_id', $c['vehicle_id'])->count(), 'A tarefa aberta é reutilizada.');

        $this->ev('10 reversão de etapas (módulo vs tab)', 'desmarcar 3 desfaz 4-7; remarcar não repõe; tarefa Modelo 9 coerente; mesmas regras nas duas portas',
            'iguais nas duas portas: '.json_encode($resultados).'. Desmarcar o 5 deixa a tarefa "Entregar Modelo 9" pendente (AVISO); progresso perdido ao remarcar; email_enviado/Modelo 9/convertida/viatura inalterados; kanban marca vários passos de uma vez; tarefa fechada => nova (B8)', 'AVISO');
    }

    // ==================================================================
    // utilitário
    // ==================================================================
    private static function resumo(array $linhas, array $chaves): string
    {
        $partes = [];
        foreach ($chaves as $k) {
            $vals = array_map(fn ($l) => $l[$k] ?? null, $linhas);
            $partes[] = $k.'='.(count(array_unique(array_map('json_encode', $vals))) === 1 ? json_encode($vals[0]) : json_encode($vals));
        }

        return count($linhas).' repetições: '.implode(', ', $partes);
    }
}
