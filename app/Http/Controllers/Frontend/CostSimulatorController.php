<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Frontend\ImportSimulatorController as ImportSimulator;
use App\Models\Client;
use App\Models\CostSimulator;
use App\Models\Setting;
use App\Models\Brand;
use Illuminate\Support\Facades\Mail;

class CostSimulatorController extends Controller
{
    public function index()
    {
        $brands = Brand::with('models')->get();
        return view('frontend.cost-simulator.simulator', compact('brands'));
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
                'phone' => $request->input('phone'),
                'origin' => 'Simulador de Custos'
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


        $costSimulator = CostSimulator::create([
            'client_id' => $client->id,
            'brand' => $request->input('brand'),
            'model' => $request->input('model'),
            'car_value' => $valorCarro,
            'commission_cost' => $commission_cost,
            'inspection_commission_cost' => $inspection_commission_cost,
            'transport' => $transporte,
            'ipo_cost' => $custo_ipo,
            'imt_cost' => $custo_imt,
            'registration_cost' => $custo_registo,
            'plates_cost' => $custo_matriculas,
            'total_cost' => $custoTotal,
            'isv_cost' => $isv,
            'fuel' => $request->input('combustivel'),
            'year' => $request->input('data_matricula'),
            'cc' => $request->input('cilindrada'),
            'co2' => $request->input('co2'),
            'emissao_particulas' => $request->input('emissao_particulas'),
            'tipo_veiculo' => $request->input('tipo_veiculo'),
            'autonomia' => $request->input('autonomia'),
            'pais_matricula' => $request->input('pais_matricula'),
        ]);


 




        // enviar email com resultados
         Mail::raw("Novo Simulador de Custos submetido por {$client->name}, Email: {$client->email}, Telefone: {$client->phone}. Valor do Carro: {$valorCarro}€, ISV: {$isv}€, Custo Total: {$custoTotal}€.", function ($message) {
             $message->to('geral@izzycar.pt')
                     ->subject('Novo Simulador de Custos');
         });
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
