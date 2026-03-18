<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Models\NewsletterOffer;
use App\Mail\NewsletterPreview as NewsletterPreviewMail;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NewsletterManagementController extends Controller
{
    public function index()
    {
        $newsletters = Newsletter::query()
            ->withCount('offers')
            ->latest()
            ->paginate(20);

        return view('admin.v2.newsletter-management.index', compact('newsletters'));
    }

    public function create()
    {
        return view('admin.v2.newsletter-management.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'text' => ['nullable', 'string'],
        ]);

        $newsletter = Newsletter::create($data);

        return redirect()
            ->route('admin.v2.newsletter-management.show', $newsletter->id)
            ->with('success', 'Newsletter criada com sucesso. Agora adicione ofertas.');
    }

    public function show($id)
    {
        $newsletter = Newsletter::with('offers')->findOrFail($id);

        return view('admin.v2.newsletter-management.show', compact('newsletter'));
    }

    public function edit($id)
    {
        $newsletter = Newsletter::findOrFail($id);

        return view('admin.v2.newsletter-management.form', compact('newsletter'));
    }

    public function update(Request $request, $id)
    {
        $newsletter = Newsletter::findOrFail($id);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'text' => ['nullable', 'string'],
        ]);

        $newsletter->update($data);

        return redirect()
            ->route('admin.v2.newsletter-management.show', $newsletter->id)
            ->with('success', 'Newsletter atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $newsletter = Newsletter::findOrFail($id);
        $newsletter->delete();

        return redirect()
            ->route('admin.v2.newsletter-management.index')
            ->with('success', 'Newsletter eliminada com sucesso.');
    }

    public function preview($id)
    {
        $newsletter = Newsletter::with(['offers' => function ($query) {
            $query->where('is_active', true);
        }])->findOrFail($id);
        $previewClient = [
            'email' => 'izzycarpt@gmail.com"',
            'name' => 'Preview Client',
        ];
        return view('emails.newsletter-preview', compact('newsletter', 'previewClient'));
    }
    public function sendNewsletter($id)
    {
        $newsletter = Newsletter::with(['offers' => function ($query) {
            $query->where('is_active', true);
        }])->findOrFail($id);

        // Dados de exemplo para o preview (usar o admin como exemplo)
        // $clients = Client::whereNotNull('email')->get();

        $clients = [
            [
                'email' => 'izzycarpt@gmail.com',
                'name' => 'Admin',
            ],
            [
                'email' => 'pedroc_30@hotmail.com',
                'name' => 'Pedro Coutinho',
            ],
            // [
            //     'email' => 'dvpc1993@gmail.com',
            //     'name' => 'DVPC',
            // ],
            // [
            //     'email' => 'diana_vilar_8488@hotmail.com',  
            //     'name' => 'Diana Vilar',
            // ],
        ];

        foreach ($clients as $client) {
            Log::info('Client email: ' . $client['email']);
            dump("Enviando newsletter para: " . $client['email']);
            $previewClient = [
                'email' => !empty($client['email']) ? $client['email'] : null,
                'name' => !empty($client['name']) ? $client['name'] : 'Subscriber',
            ];
            try {

                Mail::to($previewClient['email'])->send(new NewsletterPreviewMail($newsletter, $previewClient));
                dump("newsletter enviada para: " . $previewClient['email']);
            } catch (\Exception $e) {
                dump("Erro ao enviar newsletter para: " . $previewClient['email'] . " - " . $e->getMessage());
                // Se falhar o envio, continua mostrando a preview
                Log::error('Erro ao enviar newsletter para ' . $previewClient['email'] . ': ' . $e->getMessage());
            }
            // sleep(60); // Espera 1 minuto entre cada email
            usleep(10000000); // Espera 60 segundos (1 minuto) entre cada email
        }


        dd("enviado para todos os clientes");
    }

    // Offer management within newsletter
    public function createOffer($newsletterId)
    {
        $newsletter = Newsletter::findOrFail($newsletterId);

        return view('admin.v2.newsletter-management.offer-form', compact('newsletter'));
    }

    public function storeOffer(Request $request, $newsletterId)
    {
        $newsletter = Newsletter::findOrFail($newsletterId);

        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff', 'max:5120'],
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'year' => ['required', 'string', 'max:255'],
            'kms' => ['required', 'string', 'max:255'],
            'price' => ['required', 'string', 'max:255'],
            'savings' => ['required', 'string', 'max:255'],
            'equipamentos' => ['nullable', 'string'],
            'combustivel' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        // Upload da imagem
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('newsletter-offers', 'public');
        }

        $data['newsletter_id'] = $newsletter->id;
        NewsletterOffer::create($data);

        return redirect()
            ->route('admin.v2.newsletter-management.show', $newsletter->id)
            ->with('success', 'Oferta adicionada com sucesso.');
    }

    public function editOffer($newsletterId, $offerId)
    {
        $newsletter = Newsletter::findOrFail($newsletterId);
        $offer = NewsletterOffer::where('newsletter_id', $newsletterId)->findOrFail($offerId);

        return view('admin.v2.newsletter-management.offer-form', compact('newsletter', 'offer'));
    }

    public function updateOffer(Request $request, $newsletterId, $offerId)
    {
        $newsletter = Newsletter::findOrFail($newsletterId);
        $offer = NewsletterOffer::where('newsletter_id', $newsletterId)->findOrFail($offerId);

        $data = $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp,bmp,tiff', 'max:5120'],
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'year' => ['required', 'string', 'max:255'],
            'kms' => ['required', 'string', 'max:255'],
            'price' => ['required', 'string', 'max:255'],
            'savings' => ['required', 'string', 'max:255'],
            'equipamentos' => ['nullable', 'string'],
            'combustivel' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        // Upload da nova imagem (opcional)
        if ($request->hasFile('image')) {
            // Apagar imagem antiga se existir
            if ($offer->image && Storage::disk('public')->exists($offer->image)) {
                Storage::disk('public')->delete($offer->image);
            }
            $data['image'] = $request->file('image')->store('newsletter-offers', 'public');
        }

        $offer->update($data);

        return redirect()
            ->route('admin.v2.newsletter-management.show', $newsletter->id)
            ->with('success', 'Oferta atualizada com sucesso.');
    }

    public function destroyOffer($newsletterId, $offerId)
    {
        $newsletter = Newsletter::findOrFail($newsletterId);
        $offer = NewsletterOffer::where('newsletter_id', $newsletterId)->findOrFail($offerId);

        // Apagar imagem se existir
        if ($offer->image && Storage::disk('public')->exists($offer->image)) {
            Storage::disk('public')->delete($offer->image);
        }

        $offer->delete();

        return redirect()
            ->route('admin.v2.newsletter-management.show', $newsletter->id)
            ->with('success', 'Oferta eliminada com sucesso.');
    }
}
