<?php

/**
 * Worker de concorrência dos testes de fluxo (processo PHP independente).
 *
 *   php tests/Fluxo/bin/worker.php <ficheiro-do-job.json>
 *
 * Faz pedidos HTTP ao kernel REAL da aplicação (Request::create + $kernel->handle) contra a
 * MESMA base MySQL de teste, depois de uma barreira (ficheiro "go" com um instante comum).
 *
 * SEGURANÇA (o .env do projeto aponta para PRODUÇÃO):
 *  1. O runner entrega o ambiente EXPLÍCITO (proc_open com env completo, todas as variáveis
 *     forçadas do phpunit.fluxo-mysql.xml). Nada é herdado da shell.
 *  2. ANTES de arrancar a aplicação, o worker confirma que cada variável forçada do XML tem
 *     exatamente o valor do XML. Qualquer diferença => saída com código 3, sem tocar em nada.
 *  3. A aplicação é arrancada com um ficheiro de ambiente INEXISTENTE (o .env nunca é lido).
 *  4. Depois do arranque (ainda antes de qualquer pedido/lógica): GuardaWorker::garantirBaseIsolada
 *     com a configuração real da ligação + SELECT DATABASE(). Falha => código 4.
 *  5. Mailer array, fila sync, HTTP externo falso, notificações falsas, discos num diretório temporário.
 *
 * Saída: uma linha "@@FLUXO-WORKER@@<json>" no stdout.
 */

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

const MARCA = '@@FLUXO-WORKER@@';

function sair(array $dados, int $codigo): never
{
    fwrite(STDOUT, MARCA.json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)."\n");
    exit($codigo);
}

$raiz = dirname(__DIR__, 3);
$jobFile = $argv[1] ?? null;
if (! $jobFile || ! is_file($jobFile)) {
    sair(['guard_ok' => false, 'erro' => 'job em falta'], 2);
}
$job = json_decode((string) file_get_contents($jobFile), true);
$resultado = ['index' => $job['index'], 'pid' => getmypid(), 'guard_ok' => false, 'requests' => []];

// ---- 2. Ambiente explícito igual ao do XML (antes de arrancar a aplicação) ----
$xml = simplexml_load_file($raiz.'/phpunit.fluxo-mysql.xml');
$esperado = [];
foreach ($xml->php->server ?? [] as $s) {
    $esperado[(string) $s['name']] = (string) $s['value'];
}
foreach (['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'DB_URL', 'DB_SOCKET', 'APP_ENV', 'MAIL_MAILER', 'QUEUE_CONNECTION', 'CACHE_STORE', 'APP_KEY'] as $obrigatoria) {
    if (! array_key_exists($obrigatoria, $esperado)) {
        $resultado['erro'] = "XML sem a variável forçada {$obrigatoria}";
        sair($resultado, 3);
    }
}
foreach ($esperado as $nome => $valor) {
    $atual = getenv($nome);
    // proc_open não transmite variáveis vazias: "ausente" equivale a "" (e como o .env não é lido, nada o preenche).
    if ($valor === '' ? ($atual !== false && $atual !== '') : ($atual === false || $atual !== $valor)) {
        $resultado['erro'] = "Ambiente do worker difere do XML em {$nome} (não herdado do runner).";
        sair($resultado, 3);
    }
}
if (getenv('DB_DATABASE') !== 'izzycar_fluxo_teste' || getenv('DB_HOST') !== '127.0.0.1') {
    $resultado['erro'] = 'DB_DATABASE/DB_HOST inesperados antes do arranque.';
    sair($resultado, 3);
}

require $raiz.'/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require $raiz.'/bootstrap/app.php';
// 3. Nunca ler o .env: aponta para um ficheiro que não existe (safeLoad ignora-o).
$app->loadEnvironmentFrom('.env.fluxo-worker-inexistente');

$kernel = $app->make(HttpKernel::class);
// O UrlGenerator precisa de um 'request' ligado ao arrancar; cada pedido real substitui-o no handle().
$app->instance('request', Request::create('http://localhost/'));
$kernel->bootstrap();

// ---- 4. Guarda com a configuração REAL da ligação (antes de qualquer lógica) ----
try {
    $config = DB::connection()->getConfig();
    $emUso = DB::connection()->selectOne('SELECT DATABASE() AS db')->db ?? null;
    \Tests\Fluxo\Support\GuardaWorker::garantirBaseIsolada($config, $emUso);
} catch (\Throwable $e) {
    $resultado['erro'] = 'GUARDA: '.$e->getMessage();
    sair($resultado, 4);
}
if (config('mail.default') !== 'array' || config('queue.default') !== 'sync' || config('app.env') !== 'testing') {
    $resultado['erro'] = 'Configuração de mail/queue/env inesperada.';
    sair($resultado, 4);
}
$resultado['guard_ok'] = true;
$resultado['db'] = $emUso;

// ---- 5. Isolamento do resto ----
config([
    'logging.default' => 'null',
    'filesystems.disks.local.root' => $job['storage_dir'],
    'filesystems.disks.public.root' => $job['storage_dir'].'/public',
]);
Http::fake();
Notification::fake();

$excecoes = [];
$app->make(ExceptionHandler::class)->reportable(function (\Throwable $e) use (&$excecoes) {
    $info = ['classe' => get_class($e), 'mensagem' => mb_substr($e->getMessage(), 0, 300)];
    if ($e instanceof \Illuminate\Database\QueryException) {
        $info['sqlstate'] = $e->errorInfo[0] ?? null;
        $info['codigo_driver'] = $e->errorInfo[1] ?? null;
    }
    $excecoes[] = $info;
});

if (! empty($job['user_id'])) {
    $user = \App\Models\User::findOrFail($job['user_id']);
    Auth::guard('web')->setUser($user);
    Auth::shouldUse('web');
}
if (! empty($job['config'])) {
    config($job['config']);
}

// Preparar os pedidos ANTES da barreira (o que custa tempo fica fora da janela).
$pedidos = [];
foreach ($job['requests'] as $r) {
    $server = ['HTTP_HOST' => 'localhost'];
    if (! empty($r['json'])) {
        $server['HTTP_ACCEPT'] = 'application/json';
        $server['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    }
    $pedidos[] = [$r, ['method' => $r['method'], 'uri' => 'http://localhost'.$r['uri'], 'params' => $r['params'] ?? [], 'server' => $server]];
}
DB::connection()->getPdo();   // ligação aberta antes da barreira

// ---- Barreira: sinal de pronto + espera pelo instante comum ----
$pronto = microtime(true);
file_put_contents($job['dir'].'/ready.'.$job['index'], (string) $pronto);
$limite = microtime(true) + 60;
while (! is_file($job['dir'].'/go')) {
    if (microtime(true) > $limite) {
        $resultado['erro'] = 'barreira: timeout à espera do go';
        sair($resultado, 5);
    }
    usleep(300);
}
$instante = (float) file_get_contents($job['dir'].'/go');
while (($falta = $instante - microtime(true)) > 0.003) {
    usleep((int) (($falta - 0.002) * 1e6));
}
while (microtime(true) < $instante) {
    // espera ativa nos últimos milissegundos
}

$resultado['pronto_em'] = $pronto;
$resultado['go_instante'] = $instante;

foreach ($pedidos as [$r, $p]) {
    $inicio = microtime(true);
    $excecoes = [];
    $request = Request::create($p['uri'], $p['method'], $p['params'], [], [], $p['server']);
    $item = ['label' => $r['label'] ?? '', 'inicio' => $inicio];
    try {
        $response = $kernel->handle($request);
        $item['status'] = $response->getStatusCode();
        $item['location'] = $response->headers->get('Location');
        $conteudo = $response->getContent();
        $json = json_decode((string) $conteudo, true);
        if (is_array($json)) {
            $item['json'] = $json;
        }
        if ($request->hasSession()) {
            $s = $request->session();
            $item['flash'] = array_filter([
                'success' => $s->get('success'),
                'error' => $s->get('error'),
                'errors' => ($bag = $s->get('errors')) ? $bag->getBags()['default']->getMessages() ?? null : null,
            ]);
        }
    } catch (\Throwable $e) {
        $item['status'] = 'exception';
        $excecoes[] = ['classe' => get_class($e), 'mensagem' => mb_substr($e->getMessage(), 0, 300)];
    }
    $item['fim'] = microtime(true);
    $item['duracao_ms'] = round(($item['fim'] - $inicio) * 1000, 1);
    $item['excecoes'] = $excecoes;
    $resultado['requests'][] = $item;
}

// Emails "enviados" por este processo (transporte array; nada sai de verdade).
try {
    $resultado['emails'] = \Illuminate\Support\Facades\Mail::mailer()->getSymfonyTransport()->messages()->count();
} catch (\Throwable) {
    $resultado['emails'] = null;
}

sair($resultado, 0);
