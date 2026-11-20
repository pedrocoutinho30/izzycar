<?php

namespace App\Services;

use App\Marketing\PostContent;
use App\Models\MarketingPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** Guarda as fotos carregadas de um post (recomendação, importação) em marketing-posts/{id}/. */
class MarketingPhotoStore
{
    public function __construct(private ImageOptimizerService $optimizer)
    {
    }

    /**
     * @param  array<UploadedFile|null>  $files
     * @return list<string> caminhos no disco "public", pela ordem recebida
     */
    public function store(MarketingPost $post, array $files): array
    {
        $paths = [];
        foreach (array_slice(array_values(array_filter($files)), 0, PostContent::MAX_PHOTOS) as $file) {
            if ($file->isValid()) {
                $paths[] = $this->optimizer->storeOptimized($file, "marketing-posts/{$post->id}");
            }
        }

        return $paths;
    }

    /** Remove os ficheiros do post (usado quando a criação falha a meio). */
    public function discard(MarketingPost $post): void
    {
        Storage::disk('public')->deleteDirectory("marketing-posts/{$post->id}");
    }
}
