<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap for the website';

    public function handle()
    {
        $now = Carbon::now();

        $sitemap = Sitemap::create()
            // Homepage
            ->add(
                Url::create('/')
                    ->setPriority(1.0)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setLastModificationDate($now)
            )
            // Listagem de viaturas
            ->add(
                Url::create('/viaturas')
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setLastModificationDate($now)
            )
            // Páginas de serviço
            ->add(
                Url::create('/importacao')
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setLastModificationDate($now)
            )
            ->add(
                Url::create('/legalizacao')
                    ->setPriority(0.9)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setLastModificationDate($now)
            )
            ->add(
                Url::create('/consignacao')
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setLastModificationDate($now)
            )
           
            // Simulador
            ->add(
                Url::create('/simulador-custos')
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setLastModificationDate($now)
            )
            // Formulário de importação
            ->add(
                Url::create('/formulario-importacao')
                    ->setPriority(0.7)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setLastModificationDate($now)
            )
            // Notícias
            ->add(
                Url::create('/noticias')
                    ->setPriority(0.8)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setLastModificationDate($now)
            )
            // Páginas legais
            ->add(
                Url::create('/politica-privacidade')
                    ->setPriority(0.3)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setLastModificationDate($now)
            )
            ->add(
                Url::create('/termos-condicoes')
                    ->setPriority(0.3)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setLastModificationDate($now)
            );

        // Viaturas V2 (model: Vehicle)
        // foreach (\App\Models\VehicleV3::where('show_online', true)->get() as $vehicle) {
        //     $slug = $this->toSlug($vehicle->brand) . '/' . $this->toSlug($vehicle->model) . '/' . $vehicle->reference;
        //     $sitemap->add(
        //         Url::create("/viaturas/{$slug}")
        //             ->setPriority(0.7)
        //             ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
        //             ->setLastModificationDate($vehicle->updated_at ?? $now)
        //     );
        // }

        // Viaturas V3 (model: V3Vehicle) — se existir e tiver campo show_online
        if (class_exists(\App\Models\V3Vehicle::class)) {
            $v3Query = \App\Models\V3Vehicle::query();
            if (in_array('show_online', (new \App\Models\V3Vehicle())->getFillable())) {
                $v3Query->where('show_online', true);
            }
            foreach ($v3Query->get() as $vehicle) {
                if (!$vehicle->brand || !$vehicle->model || !$vehicle->reference) continue;
                $slug = $this->toSlug($vehicle->brand) . '/' . $this->toSlug($vehicle->model) . '/' . $vehicle->reference;
                $sitemap->add(
                    Url::create("/viaturas/{$slug}")
                        ->setPriority(0.7)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setLastModificationDate($vehicle->updated_at ?? $now)
                );
            }
        }

        // Notícias individuais
        // $pageType = \App\Models\PageType::where('name', \App\Models\PageType::NEWS)->first();
        // if ($pageType) {
        //     foreach (\App\Models\Page::where('page_type_id', $pageType->id)->latest()->get() as $noticia) {
        //         $sitemap->add(
        //             Url::create("/noticias/{$noticia->slug}")
        //                 ->setPriority(0.7)
        //                 ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
        //                 ->setLastModificationDate($noticia->updated_at ?? $now)
        //         );
        //     }
        // }

            // Notícias individuais (model: NewsArticle)
            foreach (\App\Models\NewsArticle::published()->latest()->get() as $noticia) {
                $sitemap->add(
                    Url::create("/noticias/{$noticia->slug}")
                        ->setPriority(0.7)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setLastModificationDate($noticia->updated_at ?? $now)
                );
            }

        $sitemap->writeToFile(public_path('sitemap.xml'));
        $this->info('Sitemap gerado com sucesso!');
    }

    private function toSlug(string $string): string
    {
        $string = strtolower($string);
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/\s+/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        return trim($string, '-');
    }
}
