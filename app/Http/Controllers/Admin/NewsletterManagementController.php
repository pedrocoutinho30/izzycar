<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Models\NewsletterOffer;
use App\Models\NewsletterSendLog;
use App\Mail\NewsletterPreview as NewsletterPreviewMail;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

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
    public function sendPage($id)
    {
        $newsletter = Newsletter::with('offers')->findOrFail($id);

        $sentClientIds = NewsletterSendLog::where('newsletter_id', $id)
            ->whereNotNull('sent_at')
            ->pluck('client_id')
            ->toArray();

        $clients = Client::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('newsletter_consent', true)
            ->orderBy('name')
            ->get();

        return view('admin.v2.newsletter-management.send', compact('newsletter', 'clients', 'sentClientIds'));
    }

    public function doSend(Request $request, $id)
    {
        $request->validate([
            'client_ids'   => ['required', 'array', 'min:1', 'max:20'],
            'client_ids.*' => ['integer', 'exists:clients,id'],
        ]);

        $newsletter = Newsletter::with(['offers' => function ($q) {
            $q->where('is_active', true);
        }])->findOrFail($id);

        $clientIds = $request->input('client_ids');
        $clients   = Client::whereIn('id', $clientIds)->get()->keyBy('id');

        $sent    = 0;
        $skipped = 0;
        $errors  = 0;

        foreach ($clientIds as $clientId) {
            $client = $clients->get($clientId);
            if (!$client) {
                continue;
            }

            // Skip if already sent
            $alreadySent = NewsletterSendLog::where('newsletter_id', $id)
                ->where('client_id', $clientId)
                ->whereNotNull('sent_at')
                ->exists();

            if ($alreadySent) {
                $skipped++;
                continue;
            }

            $recipient = [
                'email' => $client->email,
                'name'  => $client->name,
            ];

            try {
                Mail::to($client->email)->send(new NewsletterPreviewMail($newsletter, $recipient));

                NewsletterSendLog::updateOrCreate(
                    ['newsletter_id' => $id, 'client_id' => $clientId],
                    ['sent_at' => Carbon::now()]
                );

                $sent++;
                Log::info("Newsletter #{$id} enviada para {$client->email}");
            } catch (\Exception $e) {
                $errors++;
                Log::error("Erro ao enviar newsletter #{$id} para {$client->email}: " . $e->getMessage());
            }
        }

        $message = "Newsletter enviada com sucesso para {$sent} cliente(s).";
        if ($skipped > 0) {
            $message .= " {$skipped} cliente(s) ignorado(s) (já tinham recebido).";
        }
        if ($errors > 0) {
            $message .= " {$errors} erro(s) ao enviar.";
        }

        return redirect()
            ->route('admin.v2.newsletter-management.send', $id)
            ->with($errors > 0 && $sent === 0 ? 'error' : 'success', $message);
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
