<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RadarEquipment;
use App\Models\RadarListing;
use App\Models\RadarSearch;
use App\Services\AutoscoutScraperRunner;
use App\Services\AutoscoutTaxonomyService;
use App\Services\CarmineTaxonomyService;
use App\Services\RadarValueScoreService;
use App\Services\StandvirtualTaxonomyService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\Yaml\Yaml;

class RadarController extends Controller
{
    /** Colunas que a view de detalhe pode usar para ordenar - lista fechada para não abrir um "orderBy" a qualquer coluna via query string. */
    private const SORTABLE_COLUMNS = ['price_eur', 'mileage_km', 'first_registration_year'];

    /** "rank" não é uma coluna da BD - é a posição no ranking calculado por RadarValueScoreService, ordenada em PHP (ver paginateByRank()). */
    private const SORT_OPTIONS = [...self::SORTABLE_COLUMNS, 'rank'];

    /** Fontes portuguesas, mostradas juntas na mesma tabela "Portugal" (ver dedup em Database::mark_pt_duplicates no lado Python). */
    private const PT_SOURCES = ['standvirtual', 'carmine'];

    /**
     * O YAML do Standvirtual usa os nomes reais dos parâmetros do site (ver
     * scarperAutoscout/scraper/standvirtual_filters.py), mas o formulário usa
     * nomes amigáveis tipo os da AutoScout24 - este mapa traduz entre os dois
     * nos dois sentidos (guardar / pré-preencher o formulário de edição).
     */
    private const PT_FILTER_MAP = [
        'pt_fregfrom' => 'filter_float_first_registration_year:from',
        'pt_fregto' => 'filter_float_first_registration_year:to',
        'pt_kmfrom' => 'filter_float_mileage:from',
        'pt_kmto' => 'filter_float_mileage:to',
        'pt_powerfrom' => 'filter_float_engine_power:from',
        'pt_powerto' => 'filter_float_engine_power:to',
        'pt_pricefrom' => 'filter_float_price:from',
        'pt_priceto' => 'filter_float_price:to',
        'pt_fuel' => 'filter_enum_fuel_type',
        'pt_gear' => 'filter_enum_gearbox',
    ];

    /** Só "_ate" (máximo) está confirmado no Carmine.pt - ver scraper/carmine_filters.py. */
    private const CARMINE_FILTER_MAP = [
        'carmine_fregto' => 'ano_ate',
        'carmine_kmto' => 'km_ate',
        'carmine_priceto' => 'preco_ate',
    ];

    public function index()
    {
        $searches = RadarSearch::with('latestRun')
            ->withCount([
                'listings as listings_count' => fn ($q) => $q->where('source', 'autoscout24')->whereNull('removed_at'),
                'listings as pt_listings_count' => fn ($q) => $q->whereIn('source', self::PT_SOURCES)
                    ->whereNull('removed_at')->whereNull('duplicate_of_listing_id'),
            ])
            ->orderBy('name')
            ->get();

        return view('admin.v2.radar.index', compact('searches'));
    }

    public function show(Request $request, RadarSearch $radarSearch, RadarValueScoreService $valueScores)
    {
        $sort = in_array($request->query('sort'), self::SORT_OPTIONS, true) ? $request->query('sort') : 'price_eur';
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';
        $hasPt = $radarSearch->standvirtual_base_url || $radarSearch->carmine_base_url;

        // Ranking "melhor combinação ano/kms/preço" dentro de cada origem, e quais
        // anúncios alemães batem objetivamente o melhor anúncio português nessa
        // combinação (ver RadarValueScoreService). Calculado sempre (não só quando
        // sort=rank) porque também alimenta a coluna Nº/estrela nas duas tabelas.
        $scores = $valueScores->analyze($radarSearch);

        if ($sort === 'rank') {
            // "rank" não é uma coluna da BD - pagina-se em PHP sobre a posição já
            // calculada por RadarValueScoreService (ver paginateByRank()).
            $listings = $this->paginateByRank($this->listingsQuery($radarSearch, 'autoscout24', $request, 'price_eur', 'asc'), $scores['de_ranks'], $dir, $request, 'de_page');
            $ptListings = $hasPt
                ? $this->paginateByRank($this->listingsQuery($radarSearch, self::PT_SOURCES, $request, 'price_eur', 'asc'), $scores['pt_ranks'], $dir, $request, 'pt_page')
                : null;
        } else {
            $listings = $this->listingsQuery($radarSearch, 'autoscout24', $request, $sort, $dir)->paginate(25, ['*'], 'de_page')->withQueryString();
            $ptListings = $hasPt
                ? $this->listingsQuery($radarSearch, self::PT_SOURCES, $request, $sort, $dir)->paginate(25, ['*'], 'pt_page')->withQueryString()
                : null;
        }

        // Preço médio/mediana/mínimo/máximo - só sobre os anúncios ativos, não
        // duplicados (Standvirtual x Carmine.pt) E marcados para entrar na média (o
        // utilizador pode desmarcar versões que distorcem o preço, ex.:
        // bateria/autonomia diferente num elétrico). Sempre sobre TODOS os anúncios
        // da origem, sem os filtros de intervalo do formulário - é uma referência
        // geral do mercado, não do que se está a ver na tabela nesse momento.
        $deStats = $this->priceStats($radarSearch, 'autoscout24', priceOffset: (float) ($radarSearch->import_cost_eur ?? 0));
        $ptStats = $hasPt
            ? $this->priceStats($radarSearch, self::PT_SOURCES, onlyIncludedInAverage: true)
            : null;

        $runs = $radarSearch->runs()->orderByDesc('started_at')->limit(10)->get();

        return view('admin.v2.radar.show', [
            'radarSearch' => $radarSearch,
            'listings' => $listings,
            'ptListings' => $ptListings,
            'deStats' => $deStats,
            'ptStats' => $ptStats,
            'deRanks' => $scores['de_ranks'],
            'ptRanks' => $scores['pt_ranks'],
            'deStars' => $scores['de_stars'],
            'runs' => $runs,
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    /**
     * Média, mediana, mínimo e máximo do preço de uma origem, mais o id do anúncio
     * mais barato/mais caro (para o contorno verde/vermelho na tabela) - tudo sobre
     * os anúncios ativos dessa origem, sem paginação.
     *
     * $priceOffset soma-se a TODOS os preços antes de calcular (usado para incluir
     * o custo de importação nos anúncios da AutoScout24 - ver import_cost_eur em
     * radar_searches). Como é a mesma constante para todos os anúncios da mesma
     * origem, não afeta qual fica "mais barato"/"mais caro" (cheapest_id/
     * most_expensive_id), só desloca os valores mostrados.
     */
    /** @param string|array<int, string> $source */
    private function priceStats(RadarSearch $radarSearch, $source, bool $onlyIncludedInAverage = false, float $priceOffset = 0): array
    {
        $query = $radarSearch->listings()
            ->where(fn ($q) => is_array($source) ? $q->whereIn('source', $source) : $q->where('source', $source))
            ->whereNull('removed_at')
            ->whereNull('duplicate_of_listing_id')
            ->whereNotNull('price_eur');
        if ($onlyIncludedInAverage) {
            $query->where('include_in_average', true);
        }
        $this->applyEquipmentFilter($query, $radarSearch);

        $prices = $query->orderBy('price_eur')->pluck('price_eur', 'id');

        if ($prices->isEmpty()) {
            return ['count' => 0, 'avg' => null, 'median' => null, 'min' => null, 'max' => null, 'cheapest_id' => null, 'most_expensive_id' => null];
        }

        if ($priceOffset != 0) {
            $prices = $prices->map(fn ($price) => $price + $priceOffset);
        }

        $count = $prices->count();
        $values = $prices->values();
        $median = $count % 2 === 1
            ? $values[intdiv($count, 2)]
            : ($values[$count / 2 - 1] + $values[$count / 2]) / 2;

        return [
            'count' => $count,
            'avg' => round($prices->avg()),
            'median' => round($median),
            'min' => $values->first(),
            'max' => $values->last(),
            'cheapest_id' => $prices->keys()->first(),
            'most_expensive_id' => $prices->keys()->last(),
        ];
    }

    public function toggleAverage(RadarListing $radarListing, Request $request)
    {
        $radarListing->update(['include_in_average' => $request->boolean('include')]);

        // A checkbox só existe na tabela "Portugal" (junta Standvirtual + Carmine.pt),
        // por isso recalcula sempre as duas juntas, não só a origem deste anúncio.
        $stats = $this->priceStats($radarListing->search, self::PT_SOURCES, onlyIncludedInAverage: true);

        return response()->json($stats);
    }

    /**
     * Custo de importação (transporte, ISV, matrícula, etc.) somado ao preço de
     * TODOS os anúncios da AutoScout24 desta pesquisa - para que o ranking/estrela
     * e as estatísticas de preço reflitam o custo real de trazer o carro para
     * Portugal, não só o preço de tabela no anúncio alemão. É um valor único por
     * pesquisa (não editável por anúncio) porque é o utilizador quem o estima -
     * não recalcula nada no scraper Python, é puramente um ajuste de apresentação.
     *
     * Redireciona (em vez de responder em JSON) de propósito: mudar isto afeta o
     * ranking/estrela nas duas tabelas, não só as estatísticas de preço, por isso
     * é mais seguro recarregar a página inteira (recalcula tudo via show()) do que
     * tentar manter em sincronia um ranking já renderizado só com JS.
     */
    public function updateImportCost(RadarSearch $radarSearch, Request $request)
    {
        $validated = $request->validate([
            'import_cost_eur' => ['nullable', 'numeric', 'min:0'],
        ]);

        $radarSearch->update(['import_cost_eur' => $validated['import_cost_eur'] ?? null]);

        return redirect()->route('admin.v2.radar.show', $radarSearch)
            ->with('success', 'Custo de importação atualizado.');
    }

    /** @param string|array<int, string> $source */
    private function listingsQuery(RadarSearch $radarSearch, $source, Request $request, string $sort, string $dir)
    {
        $query = $radarSearch->listings()
            ->where(fn ($q) => is_array($source) ? $q->whereIn('source', $source) : $q->where('source', $source))
            ->whereNull('removed_at')
            ->whereNull('duplicate_of_listing_id');

        $this->applyEquipmentFilter($query, $radarSearch);

        if ($request->filled('km_min')) {
            $query->where('mileage_km', '>=', (int) $request->query('km_min'));
        }
        if ($request->filled('km_max')) {
            $query->where('mileage_km', '<=', (int) $request->query('km_max'));
        }
        if ($request->filled('price_min')) {
            $query->where('price_eur', '>=', (int) $request->query('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price_eur', '<=', (int) $request->query('price_max'));
        }

        return $query->orderBy($sort, $dir);
    }

    /**
     * Exige que o anúncio tenha TODO o equipamento selecionado na pesquisa (filtro
     * "E", não "OU") - um whereHas por item, porque um único whereHas com whereIn
     * só exigiria "pelo menos um destes", não "todos estes". Anúncios sem
     * equipamento capturado (mais antigos que esta funcionalidade, ou de fontes sem
     * suporte) nunca batem certo com nenhum filtro de equipamento - é uma
     * consequência aceite de não termos feito backfill retroativo.
     */
    private function applyEquipmentFilter($query, RadarSearch $radarSearch): void
    {
        foreach ($radarSearch->equipment()->pluck('radar_equipment.id') as $equipmentId) {
            $query->whereHas('equipment', fn ($q) => $q->where('radar_equipment.id', $equipmentId));
        }
    }

    /**
     * Pagina em PHP (não em SQL) por posição no ranking "melhor combinação
     * ano/kms/preço" - não dá para fazer isto com ORDER BY porque o ranking não é
     * uma coluna, é calculado sobre todos os anúncios ativos da pesquisa (ver
     * RadarValueScoreService). Anúncios sem ranking (faltam dados para o calcular)
     * vão sempre para o fim, nas duas direções - não são "os piores", são
     * simplesmente impossíveis de ordenar por esta métrica.
     *
     * @param  array<int, int>  $ranks  id do anúncio => posição (1 = melhor)
     */
    private function paginateByRank($query, array $ranks, string $dir, Request $request, string $pageName): LengthAwarePaginator
    {
        [$ranked, $unranked] = $query->get()->partition(fn ($row) => isset($ranks[$row->id]));

        $sorted = $ranked
            ->sortBy(fn ($row) => $ranks[$row->id], SORT_REGULAR, $dir === 'desc')
            ->values()
            ->concat($unranked->values());

        $perPage = 25;
        $page = max((int) $request->query($pageName, 1), 1);

        return new LengthAwarePaginator(
            $sorted->forPage($page, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query(), 'pageName' => $pageName]
        );
    }

    public function create(AutoscoutTaxonomyService $taxonomy, StandvirtualTaxonomyService $ptTaxonomy, CarmineTaxonomyService $carmineTaxonomy)
    {
        return view('admin.v2.radar.create', [
            'search' => null,
            'spec' => [],
            'makes' => $taxonomy->getMakes(),
            'fuelOptions' => $taxonomy->getFuelTypes(),
            'gearOptions' => $taxonomy->getGearOptions(),
            'powertypeOptions' => $taxonomy->getPowerTypes(),
            'ptMakes' => $ptTaxonomy->getMakes(),
            'ptFuelOptions' => StandvirtualTaxonomyService::FUEL_OPTIONS,
            'ptGearOptions' => StandvirtualTaxonomyService::GEAR_OPTIONS,
            'carmineMakes' => $carmineTaxonomy->getMakes(),
            'equipmentOptions' => RadarEquipment::where('show_in_filters', true)->orderBy('label')->get(),
            'selectedEquipmentIds' => [],
        ]);
    }

    public function edit(RadarSearch $radarSearch, AutoscoutTaxonomyService $taxonomy, StandvirtualTaxonomyService $ptTaxonomy, CarmineTaxonomyService $carmineTaxonomy)
    {
        // A BD é a fonte principal para make/model/filters - fica sempre correta,
        // mesmo para pesquisas antigas cujo ficheiro YAML tem um nome de ficheiro
        // diferente do campo "name:" interno (ex.: searches/example.yaml contém
        // "name: audi-a4-diesel-auto"), onde procurar por searches/{nome}.yaml
        // falharia. motor_type/model_variant/trim só existem no YAML (não são
        // colunas na BD), por isso vêm de lá quando o ficheiro existe e bate certo.
        $spec = array_merge(['make' => $radarSearch->make, 'model' => $radarSearch->model], $radarSearch->filters ?? []);

        $yamlPath = $this->findYamlPath($radarSearch->name);
        if ($yamlPath) {
            $yamlSpec = Yaml::parseFile($yamlPath) ?: [];
            foreach (['motor_type', 'model_variant', 'trim'] as $key) {
                if (!empty($yamlSpec[$key])) {
                    $spec[$key] = $yamlSpec[$key];
                }
            }

            if ($ptSpec = $yamlSpec['standvirtual'] ?? null) {
                $spec['pt_enabled'] = true;
                $spec['pt_make'] = $ptSpec['make'] ?? null;
                $spec['pt_model'] = $ptSpec['model'] ?? null;
                foreach (self::PT_FILTER_MAP as $formKey => $realKey) {
                    if (isset($ptSpec['filters'][$realKey])) {
                        $spec[$formKey] = $ptSpec['filters'][$realKey];
                    }
                }
            }

            if ($carmineSpec = $yamlSpec['carmine'] ?? null) {
                $spec['carmine_enabled'] = true;
                $spec['carmine_make'] = $carmineSpec['make'] ?? null;
                $spec['carmine_model'] = $carmineSpec['model'] ?? null;
                foreach (self::CARMINE_FILTER_MAP as $formKey => $realKey) {
                    if (isset($carmineSpec['filters'][$realKey])) {
                        $spec[$formKey] = $carmineSpec['filters'][$realKey];
                    }
                }
            }
        }

        if (isset($spec['eq']) && (int) $spec['eq'] === 49) {
            $spec['service_history'] = true;
        }

        return view('admin.v2.radar.create', [
            'search' => $radarSearch,
            'spec' => $spec,
            'makes' => $taxonomy->getMakes(),
            'fuelOptions' => $taxonomy->getFuelTypes(),
            'gearOptions' => $taxonomy->getGearOptions(),
            'powertypeOptions' => $taxonomy->getPowerTypes(),
            'ptMakes' => $ptTaxonomy->getMakes(),
            'ptFuelOptions' => StandvirtualTaxonomyService::FUEL_OPTIONS,
            'ptGearOptions' => StandvirtualTaxonomyService::GEAR_OPTIONS,
            'carmineMakes' => $carmineTaxonomy->getMakes(),
            'equipmentOptions' => RadarEquipment::where('show_in_filters', true)->orderBy('label')->get(),
            'selectedEquipmentIds' => $radarSearch->equipment()->pluck('radar_equipment.id')->all(),
        ]);
    }

    public function models(Request $request, AutoscoutTaxonomyService $taxonomy)
    {
        $validated = $request->validate([
            'make' => 'required|string|max:255',
        ]);

        return response()->json($taxonomy->getModels($validated['make']));
    }

    public function ptModels(Request $request, StandvirtualTaxonomyService $ptTaxonomy)
    {
        $validated = $request->validate([
            'make' => 'required|string|max:255',
        ]);

        return response()->json($ptTaxonomy->getModels($validated['make']));
    }

    public function carmineModels(Request $request, CarmineTaxonomyService $carmineTaxonomy)
    {
        $validated = $request->validate([
            'make' => 'required|string|max:255',
        ]);

        return response()->json($carmineTaxonomy->getModels($validated['make']));
    }

    public function submodels(Request $request, AutoscoutTaxonomyService $taxonomy)
    {
        $validated = $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
        ]);

        return response()->json($taxonomy->getSubmodelOptions($validated['make'], $validated['model']));
    }

    public function store(Request $request, AutoscoutScraperRunner $runner, AutoscoutTaxonomyService $taxonomy)
    {
        $name = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', 'unique:radar_searches,name'],
        ], [
            'name.regex' => 'O nome só pode ter letras minúsculas, números e hífens (ex.: mercedes-e-klasse-diesel-auto).',
        ])['name'];

        $spec = $this->validateAndBuildSpec($request, $taxonomy,$name);

        file_put_contents($this->yamlPath($name), Yaml::dump($spec, 4, 2, Yaml::DUMP_OBJECT_AS_MAP));

        // Cria já a linha na BD (o scraper Python só a criava minutos depois, em
        // segundo plano, via sync-searches) - só para termos um id estável já aqui
        // e podermos gravar o equipamento escolhido no formulário. "name" é único,
        // por isso quando o sync-searches correr a seguir só faz UPDATE a esta
        // mesma linha (por nome), nunca cria uma duplicada.
        $radarSearch = RadarSearch::create([
            'name' => $name,
            'make' => $spec['make'],
            'model' => $spec['model'] ?? null,
            // Placeholder - só o Python (scraper/filters.py) sabe construir o URL
            // real da AutoScout24 a partir da spec; o sync-searches substitui isto
            // pelo valor real segundos depois, em segundo plano.
            'base_url' => '',
        ]);
        $equipmentIds = RadarEquipment::whereIn('id', $request->input('equipment_ids', []))
            ->where('show_in_filters', true)
            ->pluck('id');
        $radarSearch->equipment()->sync($equipmentIds);

        $runner->syncAndRun($name);

        return redirect()
            ->route('admin.v2.radar.index')
            ->with('success', 'Pesquisa "'.$name.'" criada. A recolher os primeiros anúncios em segundo plano — atualiza a página daqui a um minuto.');
    }

    public function update(Request $request, RadarSearch $radarSearch, AutoscoutScraperRunner $runner, AutoscoutTaxonomyService $taxonomy)
    {
        // O nome não é editável (é a chave que o Python usa para fazer upsert em
        // radar_searches e o nome do ficheiro YAML) - evita ficar com um ficheiro
        // órfão se alguém mudasse o nome sem saber que isso cria uma pesquisa nova.
        $spec = $this->validateAndBuildSpec($request, $taxonomy,$radarSearch->name);

        // Se esta pesquisa vinha de um YAML com nome de ficheiro diferente do "name:"
        // interno (pesquisas antigas, anteriores a esta ferramenta), apaga esse
        // ficheiro antigo depois de escrever o novo - senão o "sync-searches"
        // processava os dois e o antigo (com os filtros desatualizados) podia
        // sobrescrever esta alteração na próxima sincronização.
        $canonicalPath = $this->yamlPath($radarSearch->name);
        $existingPath = $this->findYamlPath($radarSearch->name);

        file_put_contents($canonicalPath, Yaml::dump($spec, 4, 2, Yaml::DUMP_OBJECT_AS_MAP));

        if ($existingPath && $existingPath !== $canonicalPath) {
            unlink($existingPath);
        }

        $runner->syncAndRun($radarSearch->name);

        // Equipamento exigido é só Laravel/BD (não entra no YAML/scraper Python) -
        // só faz sentido editar aqui porque a pesquisa já existe (tem id estável),
        // ao contrário de create() onde a BD só é criada depois pelo sync em segundo
        // plano. Só aceita ids com show_in_filters=true - impede escolher (via
        // pedido manipulado) equipamento ainda escondido dos filtros.
        $equipmentIds = RadarEquipment::whereIn('id', $request->input('equipment_ids', []))
            ->where('show_in_filters', true)
            ->pluck('id');
        $radarSearch->equipment()->sync($equipmentIds);

        return redirect()
            ->route('admin.v2.radar.show', $radarSearch)
            ->with('success', 'Pesquisa "'.$radarSearch->name.'" atualizada. A recolher os novos dados em segundo plano — atualiza a página daqui a um minuto.');
    }

    public function run(RadarSearch $radarSearch, AutoscoutScraperRunner $runner)
    {
        $runner->syncAndRun($radarSearch->name);

        return back()->with('success', 'A atualizar os dados de "'.$radarSearch->name.'" em segundo plano — atualiza a página daqui a um minuto.');
    }

    public function destroy(RadarSearch $radarSearch)
    {
        $name = $radarSearch->name;

        // Sem isto, a próxima "sync-searches" (que corre sempre antes de qualquer
        // "run", incluindo o de outra pesquisa) recriava esta pesquisa a partir do
        // YAML que sobrava - apagar só na BD não seria suficiente.
        $yamlPath = $this->findYamlPath($name);
        if ($yamlPath) {
            unlink($yamlPath);
        }

        // Delete em lote, não um a um: radar_price_history tem cascadeOnDelete a
        // partir de radar_listings, por isso apagar os listings já limpa o
        // histórico de preços sozinho. Importante para pesquisas com muitos
        // anúncios - apagar um a um chegava a demorar minutos.
        $radarSearch->listings()->delete();
        $radarSearch->runs()->delete();
        $radarSearch->delete();

        return redirect()
            ->route('admin.v2.radar.index')
            ->with('success', 'Pesquisa "'.$name.'" apagada.');
    }

    private function yamlPath(string $name): string
    {
        return base_path('scarperAutoscout/searches/'.$name.'.yaml');
    }

    /**
     * Localiza o YAML de uma pesquisa mesmo que o nome do ficheiro não bata certo
     * com o campo "name:" interno - situação legada de pesquisas criadas à mão
     * antes desta ferramenta existir (ex.: searches/example.yaml tem
     * "name: audi-a4-diesel-auto"). Pesquisas criadas/editadas por aqui nunca têm
     * este problema, já que o caminho canónico é sempre usado ao escrever.
     */
    private function findYamlPath(string $name): ?string
    {
        $canonical = $this->yamlPath($name);
        if (file_exists($canonical)) {
            return $canonical;
        }

        foreach (glob(base_path('scarperAutoscout/searches/*.yaml')) ?: [] as $path) {
            $spec = Yaml::parseFile($path) ?: [];
            if (($spec['name'] ?? null) === $name) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Valida os campos de marca/modelo/submodelo/filtros (tudo exceto o nome, que
     * cada chamador resolve à sua maneira - criar exige um nome novo e único,
     * editar usa sempre o nome já existente) e devolve a spec pronta a gravar
     * como YAML.
     */
    private function validateAndBuildSpec(Request $request, AutoscoutTaxonomyService $taxonomy, string $name): array
    {
        $fuelCodes = array_column($taxonomy->getFuelTypes(), 'value');
        $gearCodes = array_column($taxonomy->getGearOptions(), 'value');
        $powertypeCodes = array_column($taxonomy->getPowerTypes(), 'value');

        // Motorização/variante/equipamento só existem depois de escolher marca+modelo,
        // por isso só valida contra a lista real da AutoScout24 quando há modelo.
        $submodels = $request->filled('model') && $request->filled('make')
            ? $taxonomy->getSubmodelOptions((string) $request->input('make'), (string) $request->input('model'))
            : ['motorTypes' => [], 'modelVariants' => [], 'trims' => []];

        $validated = $request->validate([
            'make' => ['required', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'motor_type' => ['nullable', 'in:'.implode(',', array_column($submodels['motorTypes'], 'value'))],
            'model_variant' => ['nullable', 'in:'.implode(',', array_column($submodels['modelVariants'], 'value'))],
            'trim' => ['nullable', 'in:'.implode(',', array_column($submodels['trims'], 'value'))],
            'fregfrom' => ['nullable', 'integer', 'min:1990', 'max:'.(date('Y') + 1)],
            'fregto' => ['nullable', 'integer', 'min:1990', 'max:'.(date('Y') + 1)],
            'kmfrom' => ['nullable', 'integer', 'min:0'],
            'kmto' => ['nullable', 'integer', 'min:0'],
            'powerfrom' => ['nullable', 'integer', 'min:0'],
            'powerto' => ['nullable', 'integer', 'min:0'],
            'powertype' => ['nullable', 'in:'.implode(',', $powertypeCodes)],
            'fuel' => ['nullable', 'in:'.implode(',', $fuelCodes)],
            'gear' => ['nullable', 'in:'.implode(',', $gearCodes)],
            'pricefrom' => ['nullable', 'integer', 'min:0'],
            'priceto' => ['nullable', 'integer', 'min:0'],
            'custtype' => ['nullable', 'in:D,P'],
            'service_history' => ['nullable', 'boolean'],

            'pt_enabled' => ['nullable', 'boolean'],
            'pt_make' => ['nullable', 'string', 'max:255', 'required_if:pt_enabled,1'],
            'pt_model' => ['nullable', 'string', 'max:255'],
            'pt_fregfrom' => ['nullable', 'integer', 'min:1990', 'max:'.(date('Y') + 1)],
            'pt_fregto' => ['nullable', 'integer', 'min:1990', 'max:'.(date('Y') + 1)],
            'pt_kmfrom' => ['nullable', 'integer', 'min:0'],
            'pt_kmto' => ['nullable', 'integer', 'min:0'],
            'pt_powerfrom' => ['nullable', 'integer', 'min:0'],
            'pt_powerto' => ['nullable', 'integer', 'min:0'],
            'pt_fuel' => ['nullable', 'in:'.implode(',', array_keys(StandvirtualTaxonomyService::FUEL_OPTIONS))],
            'pt_gear' => ['nullable', 'in:'.implode(',', array_keys(StandvirtualTaxonomyService::GEAR_OPTIONS))],
            'pt_pricefrom' => ['nullable', 'integer', 'min:0'],
            'pt_priceto' => ['nullable', 'integer', 'min:0'],

            'carmine_enabled' => ['nullable', 'boolean'],
            'carmine_make' => ['nullable', 'string', 'max:255', 'required_if:carmine_enabled,1'],
            'carmine_model' => ['nullable', 'string', 'max:255'],
            'carmine_fregto' => ['nullable', 'integer', 'min:1990', 'max:'.(date('Y') + 1)],
            'carmine_kmto' => ['nullable', 'integer', 'min:0'],
            'carmine_priceto' => ['nullable', 'integer', 'min:0'],
        ]);

        $integerFilters = ['fregfrom', 'fregto', 'kmfrom', 'kmto', 'powerfrom', 'powerto', 'pricefrom', 'priceto'];

        $filters = collect($validated)
            ->only(array_merge($integerFilters, ['powertype', 'fuel', 'gear', 'custtype']))
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value, $key) => in_array($key, $integerFilters, true) ? (int) $value : $value)
            ->all();

        // eq=49 = "Scheckheftgepflegt" (histórico de revisões completo), confirmado
        // empiricamente (2026-09-03) - ver scarperAutoscout/scraper/filters.py.
        if ($request->boolean('service_history')) {
            $filters['eq'] = 49;
        }

        $spec = [
            'name' => $name,
            'make' => $validated['make'],
        ];
        if (!empty($validated['model'])) {
            $spec['model'] = $validated['model'];
        }
        foreach (['motor_type', 'model_variant', 'trim'] as $key) {
            if (!empty($validated[$key])) {
                $spec[$key] = $validated[$key];
            }
        }
        $spec['filters'] = (object) $filters; // (object) força "{}" em vez de "[]" quando vazio

        if ($request->boolean('pt_enabled') && $request->filled('pt_make')) {
            $ptFilters = collect(self::PT_FILTER_MAP)
                ->mapWithKeys(fn ($realKey, $formKey) => [$realKey => $validated[$formKey] ?? null])
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all();

            $ptSpec = ['make' => $validated['pt_make']];
            if (!empty($validated['pt_model'])) {
                $ptSpec['model'] = $validated['pt_model'];
            }
            $ptSpec['filters'] = (object) $ptFilters;

            $spec['standvirtual'] = (object) $ptSpec;
        }

        if ($request->boolean('carmine_enabled') && $request->filled('carmine_make')) {
            $carmineFilters = collect(self::CARMINE_FILTER_MAP)
                ->mapWithKeys(fn ($realKey, $formKey) => [$realKey => $validated[$formKey] ?? null])
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all();

            $carmineSpec = ['make' => $validated['carmine_make']];
            if (!empty($validated['carmine_model'])) {
                $carmineSpec['model'] = $validated['carmine_model'];
            }
            $carmineSpec['filters'] = (object) $carmineFilters;

            $spec['carmine'] = (object) $carmineSpec;
        }

        return $spec;
    }
}
