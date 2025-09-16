<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Models\Menu;
use Illuminate\Support\Facades\View;
use App\Observers\SeoObserver;
use App\Models\Page;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        
        Page::observe(SeoObserver::class);
        View::composer('*', function ($view) {
            $menus = Menu::orderBy('order')
                ->where('show_online', 1)
                ->with('children_active')
                ->main()
                ->get();

            $logo = \App\Models\Setting::where('label', 'logo')->first()->value;
            $view->with('menus', $menus);
            $view->with('logotipo', "storage/" . $logo);
        });

        Paginator::useBootstrap();
    }
}
