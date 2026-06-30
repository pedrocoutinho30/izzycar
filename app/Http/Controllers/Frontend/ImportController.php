<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\CmsPage;
use App\Models\Brand;
use App\Models\FormProposal;
use App\Models\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ImportFormConfirmationMail;
use App\Models\LeadActivity;

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
                'is_lead' => true,
                'lead_source' => 'importacao',
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

        // Enviar email de confirmação ao cliente
        if ($clientExist->email) {
            Mail::to($clientExist->email)->send(new ImportFormConfirmationMail($proposal, $clientExist));
        }

        // Registar na timeline
        LeadActivity::log(
            $clientExist->id,
            'Pedido de importação submetido',
            'Formulário preenchido em izzycar.pt.' . ($proposal->brand ? " Veículo: {$proposal->brand} {$proposal->model}." : '') . ($proposal->budget ? " Orçamento: {$proposal->budget}€." : ''),
            'bi-envelope-fill',
            'primary'
        );

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
        $brands = Brand::with(['models' => fn($q) => $q->orderBy('name')])->get();

        $cmsPage = CmsPage::where('slug', 'importacao')->with('activeBlocks')->first();
        $cms     = $cmsPage ? $cmsPage->activeBlocks->keyBy('name') : collect();

        return view('frontend.import', compact('cms', 'brands'));
    }
}
