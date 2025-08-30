<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AnuncioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('anuncios.index');
    }

    public function importar(Request $request)
    {
        $request->validate([
            'ficheiro' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('ficheiro')->getRealPath();
        $csv = array_map('str_getcsv', file($path));
        $headers = array_map('trim', array_shift($csv));
        $registos = collect($csv)->map(fn($linha) => array_combine($headers, $linha));

        $dados = $this->processar($registos);

        return view('anuncios.resultado', $dados);
    }

    private function processar(Collection $registos)
    {
        $registos = $registos->map(function ($item) {
            $item['Preço'] = $this->parsePreco($item['Preço'] ?? null);
            $item['Quilometragem'] = $this->parseKm($item['Quilometragem'] ?? null);
            $item['Ano'] = intval($item['Ano'] ?? 0);
            // $item['TempoMin'] = $this->parseTempo($item['Tempo Publicação'] ?? '');
            // $item['Região'] = $this->classificarRegiao($item['Localidade'] ?? '');
            return $item;
        });

        $precos = $registos->pluck('Preço')->filter();
        $km = $registos->pluck('Quilometragem')->filter();
        $anos = $registos->pluck('Ano')->filter();
        $tempos = $registos->pluck('TempoMin')->filter();

        $maisCaro = $registos->where('Preço', $precos->max())->first();
        $maisBarato = $registos->where('Preço', $precos->min())->first();
        $mediaPreco = $precos->avg();
        $mediaDias = $tempos->avg() / 1440;

        // Normalização
        $registos = $registos->map(function ($item) use ($precos, $km, $anos) {
            $item['PrecoNorm'] = ($item['Preço'] - $precos->min()) / max(($precos->max() - $precos->min()), 1);
            $item['KmNorm'] = ($item['Quilometragem'] - $km->min()) / max(($km->max() - $km->min()), 1);
            $item['AnoNorm'] = ($item['Ano'] - $anos->min()) / max(($anos->max() - $anos->min()), 1);
            $item['Score'] = 0.5 * $item['PrecoNorm'] + 0.3 * $item['KmNorm'] - 0.2 * $item['AnoNorm'];
            return $item;
        });

        $melhorCompra = $registos->sortBy('Score')->first();
        // $regioes = $registos->groupBy('Região')->map->count();

        return compact('maisCaro', 'maisBarato', 'mediaPreco', 'mediaDias', 'melhorCompra');
    }

    private function parsePreco($str)
    {
        return floatval(str_replace(['€', '.', ',', ' '], ['', '', '.', ''], $str));
    }

    private function parseKm($str)
    {
        return intval(str_replace(['km', '.', ' '], '', strtolower($str)));
    }

    
}
