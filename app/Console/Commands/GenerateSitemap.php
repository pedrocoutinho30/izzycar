<?php
// app/Console/Commands/GenerateSitemap.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap for the website';

    public function handle()
    {
        $sitemap = Sitemap::create()
            ->add(Url::create('/')->setPriority(1.0))
            ->add(Url::create('/importacao')->setPriority(0.9))
            ->add(Url::create('/legalizacao')->setPriority(0.9))
            ->add(Url::create('/viaturas')->setPriority(0.9))
            ->add(Url::create('/noticias')->setPriority(0.8));

        // Adicionar páginas de detalhe dinamicamente
        foreach (\App\Models\Vehicle::where('show_online', true)->get() as $carro) {
            $slug = $this->toSlug($carro->brand) . '/' . $this->toSlug($carro->model) . '/' . $carro->reference;
            $sitemap->add(Url::create("/viaturas/{$slug}")->setPriority(0.7));
        }
        $pageType = \App\Models\PageType::where('name', \App\Models\PageType::NEWS)->first();
        foreach (\App\Models\Page::where('page_type_id', $pageType->id)->latest()->get() as $noticia) {
            $sitemap->add(Url::create("/noticias/{$noticia->slug}")->setPriority(0.7));
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));
        $this->info('Sitemap gerado com sucesso!');
    }

    private function toSlug($string)
    {
        // Remove acentos, espaços e caracteres inválidos, similar à função JS fornecida
        $string = strtolower($string);
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string); // remove acentos
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string); // remove caracteres inválidos
        $string = preg_replace('/\s+/', '-', $string); // espaços por hífen
        $string = preg_replace('/-+/', '-', $string); // múltiplos hífens para 1
        $string = trim($string, '-'); // remove hífens no início/fim
        return $string;
    }
}
