<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Testimonial;
use App\Models\V3Vehicle;
use Illuminate\Support\Facades\Cache;

/**
 * Páginas de comparação do novo design do site público — vivem em `/novo/...`,
 * fora do menu, não substituem nem alteram as rotas/views atuais. Servem para
 * ir comparando lado a lado enquanto o novo layout é desenhado por etapas.
 */
class NovoFrontController extends Controller
{
    public function home()
    {
        $reviews = Testimonial::where('published', true)->orderBy('review_date', 'desc')->get();

        $media = $reviews->count()
            ? round($reviews->avg('rating'), 1)
            : 0;

        $vehicles = Cache::remember('v3vehicles', 600, function () {
            return V3Vehicle::with(['photos' => fn ($q) => $q->where('is_cover', true)->limit(1)])
                ->where('show_online', true)
                ->orderByRaw("CASE status WHEN 'em_stock' THEN 1 WHEN 'reservado' THEN 2 WHEN 'vendido' THEN 3 ELSE 4 END")
                ->orderByRaw('purchase_date IS NULL')
                ->orderBy('purchase_date', 'desc')
                ->get();
        });

        $vehicles_count = Cache::remember('v3vehicles_count', 600, function () use ($vehicles) {
            return $vehicles->count();
        });

        $last_vehicles = Cache::remember('v3last_vehicles', 600, function () use ($vehicles) {
            return $vehicles->sortByDesc('created_at')->take(5);
        });

        $page = Page::where('slug', 'homepage')
            ->with('contents')
            ->firstOrFail();

        $partners = Partner::where('show_on_site', true)->whereNotNull('image')->get();

        return view('novo-front.index', compact('vehicles_count', 'last_vehicles', 'vehicles', 'page', 'reviews', 'media', 'partners'));
    }
}
