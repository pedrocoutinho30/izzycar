<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\Brand;
use App\Models\Client;
use Illuminate\Support\Facades\Mail;

class TradeInController extends Controller
{
    public function getTradeInPage()
    {
        // Obtém a página de retomas
        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();

        $page = Page::with('contents', 'seo')
            ->where('slug', 'vendemos-o-seu-carro')
            ->first();

        if (!$page) {
            // Se não existir a página, retorna uma estrutura básica
            $data = (object) [
                'contents' => [
                    'title' => 'Vendemos o seu carro',
                    'subtitle' => 'Confie em nós para obter o melhor preço na retoma do seu veículo.',
                    'content' => '<p>Retome o seu veículo usado e adquira um novo com condições vantajosas. Preencha o formulário e receba uma proposta.</p>',
                ],
                'seo' => null
            ];
        } else {
            $contents = $page->contents->mapWithKeys(function ($content) {
                $value = $content->field_value;
                
                if ($content->field_type === 'repeater' && is_string($value)) {
                    $value = json_decode($value, true) ?? [];
                }
                
                return [$content->field_name => $value];
            });

            $data = (object) [
                'contents' => $contents->toArray(),
                'seo' => $page->seo
            ];
        }

        return view('frontend.trade-in', compact('brands', 'data'));
    }

    public function submitTradeInForm(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'submodel' => 'nullable|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'fuel' => 'required|string|max:100',
            'body_type' => 'required|string|max:100',
            'km' => 'required|integer|min:0',
            'extras' => 'nullable|array',
            'sale_type' => 'required|string|in:Particular,Profissional',
            'message' => 'nullable|string',
        ]);

        // Verifica se o cliente já existe
        $clientExist = Client::where('email', $validated['email'])
            ->where('phone', $validated['phone'])
            ->first();

        if (!$clientExist) {
            $clientExist = Client::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'origin' => 'Retomas',
            ]);
        }

        // Montar corpo do email
        $body = "Novo Pedido de Retoma\n\n";
        $body .= "=== DADOS PESSOAIS ===\n";
        $body .= "Nome: {$validated['name']}\n";
        $body .= "Telemóvel: {$validated['phone']}\n";
        $body .= "Email: {$validated['email']}\n\n";
        
        $body .= "=== DADOS DO VEÍCULO ===\n";
        $body .= "Marca: {$validated['brand']}\n";
        $body .= "Modelo: {$validated['model']}\n";
        if (!empty($validated['submodel'])) {
            $body .= "Sub-modelo: {$validated['submodel']}\n";
        }
        $body .= "Ano: {$validated['year']}\n";
        $body .= "Combustível: {$validated['fuel']}\n";
        $body .= "Tipo de Carroçaria: {$validated['body_type']}\n";
        $body .= "Quilómetros: " . number_format($validated['km'], 0, ',', '.') . " km\n\n";
        
        if (!empty($validated['extras'])) {
            $body .= "=== EXTRAS ===\n";
            foreach ($validated['extras'] as $extra) {
                $body .= "- {$extra}\n";
            }
            $body .= "\n";
        }
        
        $body .= "=== TIPO DE VENDA ===\n";
        $body .= "Deseja vender a: {$validated['sale_type']}\n\n";
        
        if (!empty($validated['message'])) {
            $body .= "=== MENSAGEM ===\n";
            $body .= $validated['message'] . "\n";
        }

        // Enviar email
        Mail::raw($body, function ($message) {
            $message->to('geral@izzycar.pt')
                ->subject('Novo Pedido de Retoma');
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Pedido enviado com sucesso! Entraremos em contacto brevemente.'
        ]);
    }
}
