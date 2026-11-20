<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Marketing\CaptionLimitExceeded;
use App\Models\MarketingPost;
use App\Services\MarketingCaptionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Gera uma sugestão de legenda. Nunca grava: o utilizador revê e guarda no editor. */
class MarketingCaptionController extends Controller
{
    public function generate(Request $request, MarketingPost $post, MarketingCaptionService $service)
    {
        $v = $request->validate(['tone' => ['required', Rule::in(array_keys(MarketingCaptionService::TONES))]]);

        if (! empty($post->data['pending_selection'])) {
            return response()->json(['message' => 'Escolhe primeiro o conteúdo real: um marcador "por selecionar" não tem dados para a legenda.'], 422);
        }

        try {
            return response()->json($service->generate($post, $v['tone'], (int) auth()->id()));
        } catch (CaptionLimitExceeded $e) {
            return response()->json(['message' => $e->getMessage()], 429);
        }
    }
}
