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

        return view('frontend.news', compact('news', 'last_vehicles'));
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
