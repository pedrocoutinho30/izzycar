<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all();
        return view('settings.index', compact('settings'));
    }

    public function create()
    {
        return view('settings.form');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        // Upload de imagem se for o caso
        if ($request->tipo === 'image' && $request->hasFile('valor')) {
            $data['value'] = $request->file('valor')->store('settings', 'public');
        }

        Setting::create($data);

        return redirect()->route('settings.index')->with('success', 'Configuração criada com sucesso!');
    }

    public function edit(Setting $setting)
    {
        return view('settings.form', compact('setting'));
    }

    public function update(Request $request, Setting $setting)
    {
        $data = $this->validateData($request);

        if ($request->tipo === 'image' && $request->hasFile('valor')) {
            // apagar imagem antiga
            if ($setting->value) {
                Storage::disk('public')->delete($setting->value);
            }
            $data['value'] = $request->file('valor')->store('settings', 'public');
        }

        $setting->update($data);

        return redirect()->route('settings.index')->with('success', 'Configuração atualizada com sucesso!');
    }

    public function destroy(Setting $setting)
    {
        if ($setting->type === 'image' && $setting->value) {
            Storage::disk('public')->delete($setting->value);
        }

        $setting->delete();

        return redirect()->route('settings.index')->with('success', 'Configuração eliminada com sucesso!');
    }

    private function validateData(Request $request)
    {
        return $request->validate([
            'titulo' => 'required|string|max:255',
            'label' => 'required|string|max:255|unique:settings,label,' . ($request->setting->id ?? 'NULL'),
            'tipo' => 'required|string|in:text,number,boolean,image',
            'valor' => 'nullable',
        ], [], [
            'titulo' => 'Título',
            'label' => 'Label',
            'tipo' => 'Tipo',
            'valor' => 'Valor',
        ]) + [
            // mapear para os nomes reais da DB
            'title' => $request->titulo,
            'type' => $request->tipo,
            'value' => $request->tipo !== 'image' ? $request->valor : null,
        ];
    }
}
