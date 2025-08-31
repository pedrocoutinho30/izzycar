<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Models\Menu;
use Illuminate\Support\Facades\View;

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

        View::composer('*', function ($view) {
            $menus = Menu::orderBy('order')
                ->where('show_online', 1)
                ->with('children_active')
                ->main()
                ->get();
            $view->with('menus', $menus);
        });

        Paginator::useBootstrap();
    }
}
