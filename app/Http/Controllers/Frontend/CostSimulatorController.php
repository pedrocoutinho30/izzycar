<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Frontend\ImportSimulatorController as ImportSimulator;
use App\Models\Client;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class CostSimulatorController extends Controller
{
    public function index()
    {
        return view('frontend.cost-simulator.simulator');
    }

    public function calculate(Request $request)
    {

        //guardar dados do cliente
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $client = Client::firstOrCreate(
            ['phone' => $request->input('phone')], // condição de procura
            [
                'name' => $request->input('name'),  // campos para criar se não existir
                'email' => $request->input('email'),
                'phone' => $request->input('phone')
            ]
        );
        // $request->validate([
        //     'valor_carro' => 'required|numeric|min:0',
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|email|max:255',
        //     'phone' => 'nullable|string|max:20',
        // ]);
        // Cálculo do ISV 
        $importSimulator = new ImportSimulator();
        $dataIsv = $importSimulator->calcular($request)->getData();

        $tableIsv = $dataIsv->html;
        $isv = $dataIsv->isv;
        $valorCarro = $request->input('valor_carro');

        $commission_cost = Setting::where('label', 'commission_cost')->first()->value;
        $inspection_commission_cost = Setting::where('label', 'inspection_commission_cost')->first()->value;
        $transporte = Setting::where('label', 'custo_transporte')->first()->value;
        $custo_imt = Setting::where('label', 'custo_imt')->first()->value;
        $custo_ipo = Setting::where('label', 'custo_ipo')->first()->value;
        $custo_registo = Setting::where('label', 'custo_registo')->first()->value;
        $custo_matriculas = Setting::where('label', 'custo_matriculas')->first()->value;
        $servicos = $commission_cost + $inspection_commission_cost + $transporte + $custo_imt + $custo_ipo + $custo_registo + $custo_matriculas + 300;
        // Cálculo do custo total
        $custoTotal = $valorCarro + $isv + $servicos;


        // enviar email com resultados
        //\Mail::to($request->input('email'))->send(new \App\Mail\CostSimulatorResultMail($valorCarro, $isv, $servicos, $custoTotal,));
        return view('frontend.cost-simulator.result', [
            'valorCarro' => $valorCarro,
            'tableIsv' => $tableIsv,
            'isv' => $isv,
            'servicos' => $servicos,
            'custoTotal' => $custoTotal,
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
        ]);
    }
}
