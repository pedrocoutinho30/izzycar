<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;

class NewsController extends Controller
{
    public function getNewsPage()
    {
        $news = NewsArticle::published()
            ->orderByDesc('published_at')
            ->get();

        $seo = (object)[
            'title'                  => 'Notícias sobre Importação Automóvel em Portugal | Izzycar',
            'meta_description'       => 'Guias práticos, análises de ISV e as últimas novidades do mercado automóvel europeu. Tudo sobre importação de carros para Portugal.',
            'meta_keywords'          => 'notícias automóveis, importação automóvel, ISV Portugal, mercado automóvel',
            'meta_secundary_keywords'=> 'carros importados, guia importação, ISV 2025',
            'meta_image'             => 'https://izzycar.pt/storage/photos/1/seo/noticias.png',
            'canonical_url'          => url()->current(),
            'og_title'               => 'Notícias sobre Importação Automóvel | Izzycar',
            'og_description'         => 'Guias práticos e análises sobre importação de carros e ISV em Portugal.',
            'og_image'               => 'https://izzycar.pt/storage/photos/1/seo/noticias.png',
            'og_url'                 => url()->current(),
            'og_type'                => 'website',
        ];

        return view('frontend.news', compact('news', 'seo'));
    }

    public function getNewsPageBySlug(string $slug)
    {
        $news = NewsArticle::where('slug', $slug)->firstOrFail();

        $recentNews = NewsArticle::published()
            ->where('slug', '!=', $slug)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('frontend.news-details', compact('news', 'recentNews'));
    }
}
