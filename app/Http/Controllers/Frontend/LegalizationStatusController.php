<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Legalization;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class LegalizationStatusController extends Controller
{
    // ---------------------------------------------------------------
    // Página pública de acompanhamento do processo de legalização
    // ---------------------------------------------------------------
    public function show(string $token)
    {
        $legalization = Legalization::where('token', $token)
            ->with(['documents', 'client'])
            ->firstOrFail();

        App::setLocale($legalization->client?->language ?: 'pt');

        $passos     = $legalization->passosTranslated();
        $documentos = $legalization->allDocumentosTranslated();

        return view('frontend.legalizations.status', compact('legalization', 'passos', 'documentos'));
    }

    // ---------------------------------------------------------------
    // Download da fatura de serviço (quando disponível)
    // ---------------------------------------------------------------
    public function downloadInvoice(string $token)
    {
        $legalization = Legalization::where('token', $token)->firstOrFail();

        abort_unless(
            $legalization->invoice_path && Storage::disk('local')->exists($legalization->invoice_path),
            404
        );

        return Storage::disk('local')->download(
            $legalization->invoice_path,
            'fatura_' . $legalization->id . '.' . pathinfo($legalization->invoice_path, PATHINFO_EXTENSION)
        );
    }
}
