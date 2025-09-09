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
        ]);

        // Aqui podes gravar na BD
        $proposal = FormProposal::create($request->all());

        $clientExist = Client::where('email', $proposal->email)->where('phone', $proposal->phone)->first();

        if (!$clientExist) {

            $client = Client::create([
                'name' => $proposal->name,
                'phone' => $proposal->phone,
                'email' => $proposal->email,
                'origin' => $proposal->source,
            ]);
        }

        //TO DO:enviar email para cliente

        // Montar corpo do email em texto
        $body = "
        Novo Pedido de Importação \n
        Nome: {$proposal->name} \n
        Telemóvel: {$proposal->phone} \n
        Email: {$proposal->email} \n
        Como conheceu: {$proposal->source} \n
        Mensagem: {$proposal->message} \n

        Opção Anúncio: {$proposal->ad_option} \n
        Links: {$proposal->ad_links} \n
        ";

        // Se for 'nao_sei' adiciona as preferências
        if ($proposal->ad_option === 'nao_sei') {
            $body .= "
            --- Preferências --- \n
            Marca: {$proposal->brand} \n
            Modelo: {$proposal->model} \n
            Sub-modelo: {$proposal->submodel} \n
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
