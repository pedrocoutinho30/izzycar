<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsignmentEvaluation;
use Illuminate\Http\Request;

class ConsignmentEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsignmentEvaluation::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('brand', 'like', "%{$s}%")
                  ->orWhere('model', 'like', "%{$s}%")
                  ->orWhere('plate', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $evaluations = $query->paginate(15)->withQueryString();

        $stats = [
            ['title' => 'Total',           'value' => ConsignmentEvaluation::count(),                              'icon' => 'bi-inbox',       'color' => 'primary'],
            ['title' => 'Novos',           'value' => ConsignmentEvaluation::where('status', 'novo')->count(),     'icon' => 'bi-envelope',    'color' => 'warning'],
            ['title' => 'Em Consignação',  'value' => ConsignmentEvaluation::where('status', 'em_consignacao')->count(), 'icon' => 'bi-car-front', 'color' => 'success'],
            ['title' => 'Este Mês',        'value' => ConsignmentEvaluation::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(), 'icon' => 'bi-calendar3', 'color' => 'info'],
        ];

        return view('admin.v2.consignment-evaluations.index', compact('evaluations', 'stats'));
    }

    public function show($id)
    {
        $evaluation = ConsignmentEvaluation::findOrFail($id);
        return view('admin.v2.consignment-evaluations.show', compact('evaluation'));
    }

    public function updateStatus(Request $request, $id)
    {
        $evaluation = ConsignmentEvaluation::findOrFail($id);
        $request->validate(['status' => 'required|in:novo,contactado,avaliado,em_consignacao,vendido,cancelado']);
        $evaluation->update(['status' => $request->status]);
        return back()->with('success', 'Estado actualizado.');
    }

    public function updateNotes(Request $request, $id)
    {
        $evaluation = ConsignmentEvaluation::findOrFail($id);
        $request->validate(['notes' => 'nullable|string|max:5000']);
        $evaluation->update(['notes' => $request->notes]);
        return back()->with('success', 'Notas guardadas.');
    }

    public function destroy($id)
    {
        ConsignmentEvaluation::findOrFail($id)->delete();
        return redirect()->route('admin.v2.consignment-evaluations.index')
                         ->with('success', 'Registo eliminado.');
    }
}
