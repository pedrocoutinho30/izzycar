<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\PageType;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\VehicleController;
use App\Models\Vehicle;

class NewsController extends Controller
{
    public function getNewsPage()
    {

        $pageType = PageType::where('name', 'Notícias')->first();

        $news = Page::where('page_type_id', $pageType->id)
            ->whereHas('contents', function ($query) {
                $query->where('field_name', 'status')
                    ->where('field_value', 'Publicado');
            })
            ->orderBy('created_at', 'desc')
            ->get();


        foreach ($news as $key => $new) {
            $news[$key]->contents = $new->contents->mapWithKeys(function ($content) {


                //verifica se o campo é enum  e se for obtem o page com os valores do campo que será um array
                if ($content->field_name == 'category') {
                    $content->field_name = 'categorias';

                    $pageController = new PageController();
                    $contentEnum = $pageController->getEnumValues($content->field_value);

                    return [$content->field_name => $contentEnum];
                }


                return [$content->field_name => $content->field_value];
            });
        }

        $last_vehicles = Vehicle::where('show_online', true)->get()->take(5)->sortByDesc('created_at');

        $seo = [
            'title' => 'Notícias Automóveis em Portugal | Izzycar',
            'meta_description' => 'Fique atualizado com as últimas notícias do mercado automóvel em Portugal. Tendências, novidades, dicas e tudo sobre carros importados e usados.',
            'meta_keywords' => 'notícias automóveis, mercado automóvel Portugal, carros importados e usados',
            'meta_secundary_keywords' => 'dicas carros, novidades automóveis, tendências carros Portugal',
            'meta_image' => "https://izzycar.pt/storage/photos/1/seo/noticias.png",
            'canonical_url' => url()->current(),
            'og_title' => 'Notícias Automóveis em Portugal | Izzycar',
            'og_description' => 'Fique atualizado com as últimas notícias do mercado automóvel em Portugal. Tendências, novidades, dicas e tudo sobre carros importados e usados.',
            'og_image' => "https://izzycar.pt/storage/photos/1/seo/noticias.png",
            'og_url' => url()->current(),
            'og_type' => 'website',
        ];
        $seo = (object) $seo;

        return view('frontend.news', compact('news', 'last_vehicles', 'seo'));
    }

    public function getNewsPageBySlug($slug)
    {

        $vehicles = Vehicle::where('show_online', true)->get()->take(5);
        $recentNews = Page::where('slug', '!=', $slug)
            ->whereHas('contents', function ($query) {
                $query->where('field_name', 'status')
                    ->where('field_value', 'Publicado');
            })
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        foreach ($recentNews as $key => $recent) {
            $recentNews[$key]->contents = $recent->contents->mapWithKeys(function ($content) {


                //verifica se o campo é enum  e se for obtem o page com os valores do campo que será um array
                if ($content->field_name == 'category') {
                    $content->field_name = 'categorias';

                    $pageController = new PageController();
                    $contentEnum = $pageController->getEnumValues($content->field_value);

                    return [$content->field_name => $contentEnum];
                }


                return [$content->field_name => $content->field_value];
            });
        }



        $news = Page::where('slug', $slug)->firstOrFail();

        $news->contents = $news->contents->mapWithKeys(function ($content) {


            //verifica se o campo é enum  e se for obtem o page com os valores do campo que será um array
            if ($content->field_name == 'category') {
                $content->field_name = 'categorias';

                $pageController = new PageController();
                $contentEnum = $pageController->getEnumValues($content->field_value);

                return [$content->field_name => $contentEnum];
            }


            return [$content->field_name => $content->field_value];
        });


        return view('frontend.news-details', compact('news', 'recentNews', 'vehicles'));
    }
}
