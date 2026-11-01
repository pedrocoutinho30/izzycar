<?php

namespace Tests\Fluxo\Concerns;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Infraestrutura dos testes de fluxo em MySQL real.
 *
 * - Guarda: antes de qualquer migração confirma que a ligação é ao MySQL local
 *   e à base descartável `izzycar_fluxo_teste`; caso contrário aborta.
 * - migrate:fresh uma vez por processo (TODAS as migrations, incluindo as "só MySQL").
 *   Migrations que falham ficam em self::$migrationsFalhadas (achado, não corrigido).
 * - Limpeza entre testes por TRUNCATE (sem transação: os testes de concorrência
 *   precisam de commits reais).
 */
trait UsaMysqlIsolado
{
    public const BASE_PERMITIDA = 'izzycar_fluxo_teste';

    public const HOSTS_PERMITIDOS = ['127.0.0.1', 'localhost'];

    private static bool $migrado = false;

    /** @var array<string, string> migration => erro */
    public static array $migrationsFalhadas = [];

    public static int $migrationsAplicadas = 0;

    /** Função pura da guarda: lança se a configuração não for a permitida. */
    public static function garantirBaseIsolada(array $config, ?string $baseEmUso): void
    {
        $driver = $config['driver'] ?? null;
        $host = $config['host'] ?? null;
        $base = $config['database'] ?? null;

        if ($driver !== 'mysql'
            || ! in_array($host, self::HOSTS_PERMITIDOS, true)
            || $base !== self::BASE_PERMITIDA
            || $baseEmUso !== self::BASE_PERMITIDA) {
            throw new RuntimeException(sprintf(
                'ABORTADO: testes de fluxo só correm em mysql@(127.0.0.1|localhost)/%s. Recebido driver=%s host=%s database=%s SELECT DATABASE()=%s.',
                self::BASE_PERMITIDA,
                var_export($driver, true),
                var_export($host, true),
                var_export($base, true),
                var_export($baseEmUso, true)
            ));
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $config = DB::connection()->getConfig();
        // Se a base não existir, a ligação falha aqui, antes de qualquer escrita.
        $emUso = DB::connection()->selectOne('SELECT DATABASE() AS db')->db ?? null;
        self::garantirBaseIsolada($config, $emUso);

        if (! self::$migrado) {
            $this->migrarTudo();
            self::$migrado = true;
        }

        $this->limparTabelas();
    }

    /** migrate:fresh; se falhar, recorre a correr migration a migration e anota as que falham. */
    private function migrarTudo(): void
    {
        try {
            $exit = Artisan::call('migrate:fresh', ['--force' => true]);
        } catch (\Throwable $e) {
            $exit = 1;
            fwrite(STDERR, "\n[fluxo] migrate:fresh falhou: ".substr($e->getMessage(), 0, 600)."\n");
        }
        if ($exit === 0) {
            self::$migrationsAplicadas = (int) DB::table('migrations')->count();

            return;
        }

        // Fallback: base limpa, depois ficheiro a ficheiro; falhas anotadas.
        self::garantirBaseIsolada(DB::connection()->getConfig(), DB::connection()->selectOne('SELECT DATABASE() AS db')->db ?? null);
        Artisan::call('db:wipe', ['--force' => true]);
        Artisan::call('migrate:install');

        $batch = 1;
        foreach (glob(database_path('migrations/*.php')) as $ficheiro) {
            $nome = basename($ficheiro);
            try {
                $codigo = Artisan::call('migrate', [
                    '--path' => 'database/migrations/'.$nome,
                    '--force' => true,
                ]);
                if ($codigo !== 0) {
                    self::$migrationsFalhadas[$nome] = trim(Artisan::output());
                }
            } catch (\Throwable $e) {
                self::$migrationsFalhadas[$nome] = $e->getMessage();
            }
        }
        self::$migrationsAplicadas = (int) DB::table('migrations')->count();
        fwrite(STDERR, "\n[fluxo] migrations falhadas (".count(self::$migrationsFalhadas)."):\n".json_encode(self::$migrationsFalhadas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");
    }

    /** TRUNCATE de todas as tabelas excepto `migrations`. */
    protected function limparTabelas(): void
    {
        $tabelas = DB::select('SELECT TABLE_NAME AS t FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?', [self::BASE_PERMITIDA, 'BASE TABLE']);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($tabelas as $linha) {
                if ($linha->t === 'migrations') {
                    continue;
                }
                DB::statement('TRUNCATE TABLE `'.str_replace('`', '', $linha->t).'`');
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
