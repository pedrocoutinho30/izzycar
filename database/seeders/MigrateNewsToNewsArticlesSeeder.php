<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewsArticle;
use App\Models\Page;
use App\Models\PageType;

class MigrateNewsToNewsArticlesSeeder extends Seeder
{
    public function run(): void
    {
        $pageType = PageType::where('name', 'Notícias')->first();

        if (!$pageType) {
            $this->command->warn('PageType "Notícias" não encontrado.');
            return;
        }

        $pages = Page::where('page_type_id', $pageType->id)
            ->whereHas('contents', fn($q) =>
                $q->where('field_name', 'status')->where('field_value', 'Publicado')
            )
            ->with(['contents', 'seo'])
            ->get();

        $migrated = 0;
        $skipped  = 0;

        foreach ($pages as $page) {
            // Skip if already migrated (same slug)
            if (NewsArticle::where('slug', $page->slug)->exists()) {
                $this->command->info("Já existe: {$page->slug}");
                $skipped++;
                continue;
            }

            $c = $page->contents->pluck('field_value', 'field_name');

            // Parse published_at from the date field (e.g. "2025-09-01T00:00")
            $publishedAt = null;
            if (!empty($c['date'])) {
                try {
                    $publishedAt = \Carbon\Carbon::parse($c['date']);
                } catch (\Exception $e) {
                    $publishedAt = $page->created_at;
                }
            } else {
                $publishedAt = $page->created_at;
            }

            // Gallery: stored as JSON string in old system
            $gallery = null;
            if (!empty($c['gallery'])) {
                $decoded = json_decode($c['gallery'], true);
                $gallery = is_array($decoded) ? $decoded : null;
            }

            NewsArticle::create([
                'title'           => $c['title']    ?? $page->slug,
                'slug'            => $page->slug,
                'subtitle'        => $c['subtitle']  ?? null,
                'content'         => $c['content']   ?? '',
                'summary'         => $c['summary']   ?? null,
                'cover_image'     => $c['image']     ?? null,
                'gallery'         => $gallery,
                'status'          => 'Publicado',
                'published_at'    => $publishedAt,
                'seo_title'       => $page->seo?->title,
                'seo_description' => $page->seo?->meta_description,
                'seo_keywords'    => $page->seo?->meta_keywords,
            ]);

            $this->command->info("Migrado: {$page->slug}");
            $migrated++;
        }

        $this->command->newLine();
        $this->command->info("Concluído — {$migrated} migrados, {$skipped} ignorados (já existiam).");
    }
}
