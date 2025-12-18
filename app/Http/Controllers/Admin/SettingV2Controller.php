<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingV2Controller extends Controller
{
    public function index(Request $request)
    {
        $query = Setting::query();

        // Filtro de pesquisa
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('label', 'like', '%' . $request->search . '%');
            });
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $settings = $query->orderBy('title')->paginate(15)->withQueryString();

        // Estatísticas
        $stats = [
            [
                'title' => 'Total de Configurações',
                'value' => Setting::count(),
                'color' => 'primary',
                'icon' => 'bi-gear'
            ],
            [
                'title' => 'Tipo Texto',
                'value' => Setting::where('type', 'text')->count(),
                'color' => 'info',
                'icon' => 'bi-input-cursor-text'
            ],
            [
                'title' => 'Tipo Imagem',
                'value' => Setting::where('type', 'image')->count(),
                'color' => 'success',
                'icon' => 'bi-image'
            ],
            [
                'title' => 'Tipo Boolean',
                'value' => Setting::where('type', 'boolean')->count(),
                'color' => 'warning',
                'icon' => 'bi-toggle-on'
            ]
        ];

        return view('admin.v2.settings.index', compact('settings', 'stats'));
    }

    public function create()
    {
        return view('admin.v2.settings.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'label' => 'required|string|max:255|unique:settings,label',
            'tipo' => 'required|string|in:text,number,boolean,image',
            'valor' => 'nullable',
        ]);

        $data = [
            'title' => $validated['titulo'],
            'label' => $validated['label'],
            'type' => $validated['tipo'],
            'value' => $request->valor
        ];

        // Upload de imagem se for o caso
        if ($request->tipo === 'image' && $request->hasFile('valor')) {
            $file = $request->file('valor');
            $filename = $request->label . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('settings', $filename, 'public');
            $data['value'] = $path;
        }

        Setting::create($data);

        return redirect()->route('admin.v2.settings.index')
            ->with('success', 'Configuração criada com sucesso!');
    }

    public function edit($id)
    {
        $setting = Setting::findOrFail($id);
        return view('admin.v2.settings.form', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'label' => 'required|string|max:255|unique:settings,label,' . $setting->id,
            'tipo' => 'required|string|in:text,number,boolean,image',
            'valor' => 'nullable',
        ]);

        $data = [
            'title' => $validated['titulo'],
            'label' => $validated['label'],
            'type' => $validated['tipo'],
            'value' => $request->valor
        ];

        // Upload de nova imagem
        if ($request->tipo === 'image' && $request->hasFile('valor')) {
            // Apagar imagem antiga
            if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                Storage::disk('public')->delete($setting->value);
            }

            $file = $request->file('valor');
            $filename = $setting->label . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('settings', $filename, 'public');
            $data['value'] = $path;
        }

        $setting->update($data);

        return redirect()->route('admin.v2.settings.index')
            ->with('success', 'Configuração atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $setting = Setting::findOrFail($id);

        // Eliminar imagem se existir
        if ($setting->type === 'image' && $setting->value) {
            if (Storage::disk('public')->exists($setting->value)) {
                Storage::disk('public')->delete($setting->value);
            }
        }

        $setting->delete();

        return redirect()->route('admin.v2.settings.index')
            ->with('success', 'Configuração eliminada com sucesso!');
    }
}
