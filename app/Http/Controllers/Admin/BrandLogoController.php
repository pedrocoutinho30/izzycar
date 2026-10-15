<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandLogoController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));
        $onlyMissing = $request->boolean('sem_logo');

        $brands = Brand::withCount('models')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($onlyMissing, fn ($q) => $q->whereNull('logo_path'))
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => Brand::count(),
            'com_logo' => Brand::whereNotNull('logo_path')->count(),
        ];
        $stats['sem_logo'] = $stats['total'] - $stats['com_logo'];

        return view('admin.v2.brands.index', compact('brands', 'stats', 'search', 'onlyMissing'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'logo.required' => 'Escolha uma imagem.',
            'logo.image' => 'O ficheiro tem de ser uma imagem.',
            'logo.mimes' => 'O logo tem de ser PNG, JPG ou WEBP.',
            'logo.max' => 'O logo não pode ter mais de 2 MB.',
        ]);

        $old = $brand->logo_path;
        $brand->update(['logo_path' => $request->file('logo')->store('brands', 'public')]);

        $this->deleteIfUnshared($old, $brand);

        return back()->with('success', "Logo de {$brand->name} atualizado.");
    }

    public function destroy(Brand $brand)
    {
        if ($brand->logo_path) {
            $old = $brand->logo_path;
            $brand->update(['logo_path' => null]);
            $this->deleteIfUnshared($old, $brand);
        }

        return back()->with('success', "Logo de {$brand->name} removido.");
    }

    /** O comando de importação partilha ficheiros entre marcas (VW/Volkswagen): só apaga se mais nenhuma o usar. */
    private function deleteIfUnshared(?string $path, Brand $brand): void
    {
        if ($path && ! Brand::where('logo_path', $path)->where('id', '!=', $brand->id)->exists()) {
            Storage::disk('public')->delete($path);
        }
    }
}
