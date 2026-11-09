<?php
// App/Http/Controllers/ProposalController.php
namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\ConvertedProposalDocument;
use App\Models\VehicleAttribute;
use Illuminate\Support\Facades\Mail;
use App\Services\ProposalAcceptanceException;
use App\Services\ProposalAcceptanceService;
use App\Mail\ProposalAcceptedMail;
use App\Services\ContractService;
use App\Models\AttributeGroup;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Páginas públicas da cotação: ver (`detail`), pedir cotação de uma opção
 * alternativa e aceitar. A gestão das cotações vive em Admin/ProposalV2Controller.
 */
class ProposalController extends Controller
{
    /**
     * Foto da cotação em JPEG 1200x630 para a pré-visualização do link
     * (WhatsApp, Facebook…): a imagem guardada é AVIF/WebP, que estas
     * aplicações não mostram de forma fiável. Pública como a própria cotação
     * (quem tem o código já vê a foto na página).
     */
    public function previewImage(string $proposal_code)
    {
        $proposal = Proposal::where('proposal_code', $proposal_code)->firstOrFail();

        $canonical = is_array($proposal->images) ? ($proposal->images[0] ?? null) : $proposal->images;
        // O caminho guardado é o AVIF "large"; a variante WebP está ao lado.
        $origem = $canonical ? preg_replace('/\.avif$/', '.webp', $canonical) : null;
        $disco = Storage::disk('public');
        abort_unless($origem && $disco->exists($origem), 404);

        try {
            $jpeg = (new ImageManager(new Driver()))->read($disco->get($origem))->cover(1200, 630)->toJpeg(80)->toString();
        } catch (\Throwable $e) {
            report($e);
            abort(404);
        }

        return response($jpeg, 200, ['Content-Type' => 'image/jpeg', 'Cache-Control' => 'public, max-age=86400']);
    }

    public function detail($proposal_code)
    {
        $proposal = Proposal::where('proposal_code', $proposal_code)
            ->firstOrFail();

        // Cotações com mais de Proposal::VALIDITY_DAYS dias expiram.
        if ($proposal->isExpired()) {
            return view('proposals.expired', compact('proposal'));
        }

        $attributes = [];

        $potencia = "";
        $caixa = "";
        $cilindrada = "";
        foreach ($proposal->attributeValues as $attributeValue) {


            $attribute = VehicleAttribute::find($attributeValue->attribute_id);
            if (!$attribute) continue;

            $group = $attribute->attribute_group ?? 'Outros'; // fallback caso esteja vazio
            $name = $attribute->name;
            if ($attribute->key == 'potencia') {
                $potencia = $attributeValue->value;
            }

            if ($attribute->key  == 'tipo_caixa') {
                $caixa = $attributeValue->value;
            }
            if ($attribute->key == 'cilindrada') {
                $cilindrada = $attributeValue->value;
            }
            // Inicializa o grupo se não existir
            if (!isset($attributes[$group])) {
                $attributes[$group] = [];
            }
            if ($attribute->type == 'select') {
                $attributes[$group][$name] = $attributeValue->value;
                continue;
            } elseif ($attribute->type == 'boolean') {
                $attributes[$group][$name] = $name;
                continue;
            } else if ($attribute->type == 'text' || $attribute->type == 'number') {
                $attributes[$group][$name] = $attributeValue->value;
                continue;
            }
            // Adiciona o nome do atributo e o valor no grupo correto
            $attributes[$group][$name] = $attributeValue->value;
        }

        $order = AttributeGroup::orderBy('order')->get()->pluck('name')->toArray();


        // Função para ordenar o array associativo $attributes
        uksort($attributes, function ($a, $b) use ($order) {
            $posA = array_search($a, $order);
            $posB = array_search($b, $order);

            // Se algum grupo não estiver na lista, joga ele para o fim
            if ($posA === false) $posA = count($order);
            if ($posB === false) $posB = count($order);

            return $posA <=> $posB;
        });

        $client = Client::find($proposal->client_id);
        $otherOpportunities = $this->otherOpportunities($proposal);

        return view('proposals.view-proposal', compact('proposal', 'attributes', 'potencia', 'caixa', 'cilindrada', 'client', 'otherOpportunities'));
    }

    /**
     * O cliente carrega em "Pedir cotação" numa das outras opções da sua
     * cotação: a equipa recebe um email (o cliente não envia nada). Um
     * segundo clique na mesma opção não volta a enviar.
     */
    public function requestAlternativeQuote(string $proposalCode, int $opportunity)
    {
        $proposal = Proposal::where('proposal_code', $proposalCode)->firstOrFail();
        $alternative = $this->otherOpportunities($proposal)->firstWhere('id', $opportunity);
        abort_unless($alternative, 404);

        if ($alternative->client_quote_requested_at) {
            return response()->json(['success' => true, 'already' => true]);
        }

        $client = Client::find($proposal->client_id);
        $car = trim(implode(' ', array_filter([$alternative->brand, $alternative->model, $alternative->version])));

        try {
            Mail::to(config('mail.admin_address', 'geral@izzycar.pt'))
                ->send(new \App\Mail\AlternativeQuoteRequestedMail($proposal, $alternative, $client));
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Não foi possível enviar o pedido. Tente novamente ou contacte-nos.'], 500);
        }

        $alternative->update(['client_quote_requested_at' => now()]);

        if ($client) {
            \App\Models\LeadActivity::log(
                $client->id,
                'Pediu cotação de outra opção',
                "Na cotação {$proposal->proposal_code} pediu cotação para {$car}.",
                'bi-hand-index-thumb',
                'primary'
            );
        }

        return response()->json(['success' => true]);
    }

    /**
     * Cotação criada a partir de uma Oportunidade: as restantes oportunidades
     * do mesmo pedido (exceto as rejeitadas) aparecem ao cliente como
     * alternativas, para poder pedir cotação de outra.
     */
    private function otherOpportunities(Proposal $proposal): \Illuminate\Support\Collection
    {
        $source = \App\Models\ImportOpportunity::where('proposal_id', $proposal->id)->first();

        if (!$source) {
            return collect();
        }

        return \App\Models\ImportOpportunity::where('form_proposal_id', $source->form_proposal_id)
            ->whereKeyNot($source->id)
            ->where('status', '!=', \App\Enums\OpportunityStatus::Rejected->value)
            ->orderByRaw('price is null')
            ->orderBy('price')
            ->get();
    }




    /**
     * O cliente aceita a cotação na página pública. A cotação é encontrada
     * pelo código (o id sequencial permitia aceitar cotações de outros), e
     * tudo é validado antes de gravar — ver ProposalAcceptanceService.
     */
    public function accept(string $proposalCode, Request $request, ProposalAcceptanceService $acceptance)
    {
        $proposal = Proposal::where('proposal_code', $proposalCode)->firstOrFail();

        $request->validate([
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'vat_number' => 'nullable|string|max:20',
            'identification_number' => 'nullable|string|max:50',
            'validate_identification_number' => 'nullable|date',
        ], [
            'email.email' => 'O email não é válido.',
            'validate_identification_number.date' => 'A validade do documento não é uma data válida.',
        ]);

        try {
            $convertedProposal = $acceptance->accept($proposal, $request->only(ProposalAcceptanceService::CLIENT_FIELDS), byClient: true);
        } catch (ProposalAcceptanceException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Emails depois de gravar: uma falha no envio não desfaz a aceitação
        // nem mostra um erro ao cliente.
        try {
            $this->sendAcceptanceEmail($proposal->fresh(), $convertedProposal);
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            Mail::raw(
                "A cotação {$proposal->proposal_code} (#{$proposal->id}) foi aceite pelo cliente. " . route('admin.v2.converted-proposals.edit', $convertedProposal->id),
                fn ($mail) => $mail->to('geral@izzycar.pt')->subject('Proposta aceite - Izzycar')
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'A proposta foi aceite. O cliente receberá um email com o contrato.');
    }

    public function sendAcceptanceEmail($proposal, $convertedProposal)
    {
        $client = Client::find($proposal->client_id);

        // Liga de acompanhamento do processo (código aleatório, nunca o id).
        $trackingUrl = $convertedProposal->trackingUrl();

        $data = [
            'client_name' => $client->name,
            'brand' => $convertedProposal->brand,
            'model' => $convertedProposal->modelCar,
            'version' => $convertedProposal->version,
            'car_image' => is_array(json_decode($proposal->images, true))
                ? json_decode($proposal->images, true)[0] ?? null
                : null,
            'tracking_url' => $trackingUrl,
        ];

        // Gerar PDF em memória
        $pdfContent = ContractService::generateContractPdf($client, $convertedProposal);

        // Guardar o contrato gerado para ficar visível na página da cotação
        // convertida ("Documentos Gerados/Enviados"), já que é enviado ao
        // cliente por email mas nunca era persistido em lado nenhum.
        $nomeFicheiro = 'contrato_' . $convertedProposal->id . '_' . now()->format('YmdHis') . '.pdf';
        $caminho = "converted-proposals/{$convertedProposal->id}/{$nomeFicheiro}";
        Storage::disk('local')->put($caminho, $pdfContent);
        ConvertedProposalDocument::create([
            'converted_proposal_id' => $convertedProposal->id,
            'tipo' => 'gerado',
            'nome_original' => 'Contrato de Prestação de Serviços',
            'caminho' => $caminho,
            'enviado_em' => now(),
        ]);

        // Enviar para o cliente e em cc para admin
        Mail::to($client->email)
            ->cc('geral@izzycar.pt')
            ->bcc('izzycarpt@gmail.com')
            ->send(new ProposalAcceptedMail($convertedProposal, $pdfContent, $data));

        // Enviar também ao angariador da lead (se existir), para que possa
        // acompanhar o processo — sem o contrato/PDF, que é só entre a
        // Izzycar e o cliente.
        $owner = $convertedProposal->owner;
        if ($owner && $owner->email) {
            Mail::to($owner->email)
                ->bcc('izzycarpt@gmail.com')
                ->send(new ProposalAcceptedMail($convertedProposal, $pdfContent, $data, forAngariador: true));
        }
    }
}
