<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use  \App\Models\Page;
use App\Models\Brand;
use App\Models\FormProposal;
use App\Models\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Frontend\PageController;

class ImportController extends Controller
{
    public function submitFormImport(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email',
            'message' => 'nullable|string',
            'payment_type' => 'required|in:pronto_pagamento,financiamento',
            'estimated_purchase_date' => 'required|in:imediato,1_3_meses,3_6_meses,pesquisar',
            'data_processing_consent' => 'accepted',
            'newsletter_consent' => 'nullable|boolean',
        ]);

        // Aqui podes gravar na BD

        $formPropposalData = $request->all();
        $dataProcessingConsent = $request->boolean('data_processing_consent');
        $newsletterConsent = $request->boolean('newsletter_consent');
        $clientExist = Client::where('email', $formPropposalData['email'])->where('phone', $formPropposalData['phone'])->first();

        if (!$clientExist) {

            $clientExist = Client::create([
                'name' => $formPropposalData['name'],
                'phone' => $formPropposalData['phone'],
                'email' => $formPropposalData['email'],
                'origin' => $formPropposalData['source'],
                'data_processing_consent' => $dataProcessingConsent,
                'newsletter_consent' => $newsletterConsent,
            ]);
        } else {
            $clientExist->update([
                'data_processing_consent' => $dataProcessingConsent,
                'newsletter_consent' => $newsletterConsent,
            ]);
        }
        $formPropposalData['client_id'] = $clientExist->id;
        $formPropposalData['status'] = 'novo';
        $formPropposalData['version'] = $formPropposalData['submodel'];
        unset($formPropposalData['data_processing_consent'], $formPropposalData['newsletter_consent']);
        //Guardar o formulário de proposta
        $proposal = FormProposal::create($formPropposalData);

        //TO DO:enviar email para cliente

        // Montar corpo do email em texto
        $body = "
        Novo Pedido de Importação \n
        Nome: {$proposal->name} \n
        Telemóvel: {$proposal->phone} \n
        Email: {$proposal->email} \n
        Como conheceu: {$proposal->source} \n
        Mensagem: {$proposal->message} \n

        Tipo de Pagamento: {$proposal->payment_type} \n
        Data Estimada da Compra: {$proposal->estimated_purchase_date} \n
        ";

        $body .= "Consentimento Tratamento de Dados: " . ($dataProcessingConsent ? 'Sim' : 'Não') . " \n";
        $body .= "Consentimento Newsletter: " . ($newsletterConsent ? 'Sim' : 'Não') . " \n";
        $body .= "Opção Anúncio: {$proposal->ad_option} \n";
        $body .= "Links: {$proposal->ad_links} \n";

        // Se for 'nao_sei' adiciona as preferências
        if ($proposal->ad_option === 'nao_sei') {
            $body .= "
            --- Preferências --- \n
            Marca: {$proposal->brand} \n
            Modelo: {$proposal->model} \n
            Sub-modelo: {$proposal->version} \n
            Combustível: {$proposal->fuel} \n
            Ano mínimo: {$proposal->year_min} \n
            KM máximo: {$proposal->km_max} \n
            Cor: {$proposal->color} \n
            Budget: {$proposal->budget} \n
            Caixa: {$proposal->gearbox} \n
            Extras: {$proposal->extras} \n
            ";
        }



        // Enviar email
        Mail::raw($body, function ($message) {
            $message->to('geral@izzycar.pt')
                ->subject('Novo Pedido de Importação');
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Pedido enviado com sucesso!'
        ]);
    }

    public function getImportPage()
    {
        // Obtém a página de importação
        // Verifica se a página existe e carrega os conteúdos
        // Se não existir, retorna um erro 404

        $brands = Brand::with(['models' => function ($query) {
            $query->orderBy('name');
        }])->get();
        $data = Page::where('slug', 'importacoes')
            ->with('contents')
            ->firstOrFail();



        $data->process_import = $data->contents->mapWithKeys(function ($content) {
            //verifica se o campo é enum  e se for obtem o page com os valores do campo que será um array
            if ($content->field_name == 'process_import') {
                $content->field_name = 'process_import';

                $pageController = new PageController();
                $contentEnum = $pageController->getEnumValues($content->field_value);

                return [$content->field_name => $contentEnum];
            }
            return [$content->field_name => $content->field_value];
        });



        $why_import = $data->contents->mapWithKeys(function ($content) {
            //verifica se o campo é enum  e se for obtem o page com os valores do campo que será um array
            if ($content->field_name == 'why_import') {
                $content->field_name = 'enum_why_import';

                $pageController = new PageController();
                $contentEnum = $pageController->getEnumValues($content->field_value);

                return [$content->field_name => $contentEnum];
            }
            return [];
        });

        $faq = $data->contents->mapWithKeys(function ($content) {
            //verifica se o campo é enum  e se for obtem o page com os valores do campo que será um array
            if ($content->field_name == 'enum_faq') {
                $content->field_name = 'enum';

                $pageController = new PageController();
                $contentEnum = $pageController->getEnumValues($content->field_value);

                return [$content->field_name => $contentEnum];
            }
            return [];
        });

        $items = $faq['enum'] ?? [];
        usort($items, function ($a, $b) {
            return (int)$a['order'] <=> (int)$b['order'];
        });

        $faq['enum'] = $items;



        // $data_custos = Page::where('slug', 'custos-do-processo-de-importacao')
        //     ->with('contents')
        //     ->firstOrFail();

        // $data_custos->contents = $data_custos->contents->mapWithKeys(function ($content) {
        //     //verifica se o campo é enum  e se for obtem o page com os valores do campo que será um array
        //     if ($content->field_name == 'enum_custos_importação') {
        //         $content->field_name = 'enum';

        //         $pageController = new PageController();
        //         $contentEnum = $pageController->getEnumValues($content->field_value);

        //         return [$content->field_name => $contentEnum];
        //     }
        //     return [$content->field_name => $content->field_value];
        // });



        $data_custos = $data->contents->mapWithKeys(function ($content) {
            //verifica se o campo é enum  e se for obtem o page com os valores do campo que será um array
            if ($content->field_name == 'import_cost') {
                $content->field_name = 'enum';

                $pageController = new PageController();
                $contentEnum = $pageController->getEnumValues($content->field_value);

                return [$content->field_name => $contentEnum];
            }
            return [];
        });


        return view('frontend.import', compact('data', 'data_custos', 'faq', 'why_import', 'brands'));
    }
}
