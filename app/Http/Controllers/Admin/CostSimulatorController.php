<?php

/**
 * ==================================================================
 * CLIENTS CONTROLLER V2
 * ==================================================================
 * 
 * Controller moderno para gestão de clientes
 * 
 * @author Izzycar Team
 * @version 2.0
 * ==================================================================
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CostSimulator;
use COM;
use Illuminate\Http\Request;

class CostSimulatorController extends Controller
{
    /**
     * INDEX - Listagem de simulações
     */
    public function index(Request $request)
    {
        // Marcar todas as simulações não lidas como lidas
        CostSimulator::where('read', 0)->update(['read' => 1]);

        $query = CostSimulator::orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%");
            });
        }

        $costSimulators = $query->paginate(10)->withQueryString();

        // Stats
        $stats = [
            ['title' => 'Total Simulações', 'value' => CostSimulator::count(), 'icon' => 'people', 'color' => 'primary'],
            ['title' => 'Novos Este Mês', 'value' => CostSimulator::whereMonth('created_at', now()->month)->count(), 'icon' => 'person-plus', 'color' => 'success'],
            ['title' => 'Não Lidos', 'value' => CostSimulator::where('read', 0)->count(), 'icon' => 'envelope', 'color' => 'warning'],
        ];

        return view('admin.v2.cost-simulators.index', compact('costSimulators', 'stats'));
    }

    
    /**
     * DESTROY - Eliminar cliente
     */
    public function destroy($id)
    {
        $costSimulator = CostSimulator::findOrFail($id);
        $costSimulator->delete();

        return redirect()->route('admin.v2.cost-simulators.index')
            ->with('success', 'Simulação eliminada com sucesso!');
    }
}
