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
use App\Mail\ImportFormConfirmationMail;
use App\Models\LeadActivity;
use App\Models\User;
use App\Http\Controllers\Frontend\PageController;

class ImportController extends Controller
{
    public function submitFormImport(Request $request)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'phone'                   => ['required', 'string', 'max:20', 'regex:/^(\+351|00351|351)?[\s\-]?[29]\d{8}$/'],
            'email'                   => 'required|email:rfc',
            'source'                  => 'nullable|string|max:100',
            'message'                 => 'nullable|string|max:2000',
            'payment_type'            => 'required|in:pronto_pagamento,financiamento',
            'estimated_purchase_date' => 'required|in:imediato,1_3_meses,3_6_meses,pesquisar',
            'data_processing_consent' => 'accepted',
            'newsletter_consent'      => 'nullable|boolean',
            'angariador'              => 'nullable|string|max:100',
        ]);

        // Aqui podes gravar na BD

        $formPropposalData = $request->all();
        $dataProcessingConsent = $request->boolean('data_processing_consent');
        $newsletterConsent = $request->boolean('newsletter_consent');
        $angariadorCode = $request->filled('angariador') ? $request->input('angariador') : null;
        $angariadorOwner = $angariadorCode ? User::where('referral_code', $angariadorCode)->first() : null;

        $clientExist = Client::where('email', $formPropposalData['email'])->where('phone', $formPropposalData['phone'])->first();
        $isDuplicateClient = (bool) $clientExist;

        if (!$clientExist) {

            $clientExist = Client::create([
                'name' => $formPropposalData['name'],
                'phone' => $formPropposalData['phone'],
                'email' => $formPropposalData['email'],
                'origin' => $formPropposalData['source'] ?? null,
                'data_processing_consent' => $dataProcessingConsent,
                'newsletter_consent' => $newsletterConsent,
                'is_lead' => true,
                'lead_source' => 'importacao',
                'angariador_code' => $angariadorCode,
                'owner_id' => $angariadorOwner?->id,
            ]);
        } else {
            $updateData = [
                'data_processing_consent' => $dataProcessingConsent,
                'newsletter_consent' => $newsletterConsent,
            ];

            // O primeiro angariador atribuído a esta lead tem sempre prioridade — só
            // gravamos se ainda não houver nenhum código/proprietário associado.
            if ($angariadorCode && !$clientExist->angariador_code) {
                $updateData['angariador_code'] = $angariadorCode;
            }
            if ($angariadorOwner && !$clientExist->owner_id) {
                $updateData['owner_id'] = $angariadorOwner->id;
            }

            $clientExist->update($updateData);
        }
        $formPropposalData['client_id'] = $clientExist->id;
        $formPropposalData['angariador_code'] = $angariadorCode;
        $formPropposalData['status'] = 'novo';
        $formPropposalData['version'] = $formPropposalData['submodel'];
        unset($formPropposalData['data_processing_consent'], $formPropposalData['newsletter_consent'], $formPropposalData['angariador']);
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

        // Se o email+telefone já correspondiam a um cliente existente, avisar a
        // administração para gerir manualmente este caso (pode já ter uma lead/
        // processo em curso, um angariador diferente, etc.).
        if ($isDuplicateClient) {
            LeadActivity::log(
                $clientExist->id,
                'Pedido de importação duplicado — cliente já existente',
                'Este formulário foi submetido com um email e telefone que já correspondiam a um registo existente. Reveja manualmente para decidir como associar este novo pedido.',
                'bi-exclamation-triangle-fill',
                'warning'
            );

            $adminEmail = config('mail.admin_address', env('MAIL_FROM_ADDRESS', 'geral@izzycar.pt'));

            Mail::raw(
                "Foi submetido um novo pedido de importação através do formulário, mas o email e telefone já correspondem a um cliente existente.\n\n"
                    . "Cliente: {$clientExist->name} (#{$clientExist->id})\n"
                    . "Email: {$clientExist->email}\n"
                    . "Telefone: {$clientExist->phone}\n\n"
                    . "Reveja manualmente para decidir como associar este novo pedido: " . route('admin.v2.leads.show', $clientExist->id),
                function ($message) use ($adminEmail) {
                    $message->to($adminEmail)
                        ->subject('Pedido de Importação Duplicado — Cliente Já Existente');
                }
            );
        }

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
