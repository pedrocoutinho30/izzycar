<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Marketing\ImageResizer;
use App\Models\MarketingPost;
use App\Models\V3VehiclePhoto;
use Illuminate\Http\Request;

/** Fotos para o editor do Marketing Studio, no mesmo domínio (evita canvas contaminado). */
class MarketingPhotoController extends Controller
{
    public function show(Request $request, V3VehiclePhoto $photo)
    {
        return ImageResizer::response($photo->path, $request->integer('w') ?: null);
    }

    /** Foto guardada num post (recomendações): só ficheiros da pasta do Studio, por índice. */
    public function post(Request $request, MarketingPost $post, int $index)
    {
        $path = $post->data['photos'][$index]['path'] ?? null;
        abort_unless(is_string($path) && str_starts_with($path, 'marketing-posts/') && ! str_contains($path, '..'), 404);

        return ImageResizer::response($path, $request->integer('w') ?: null);
    }
}
