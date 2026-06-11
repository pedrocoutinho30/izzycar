<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Models\Menu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use App\Observers\SeoObserver;
use App\Observers\SaleObserver;
use App\Observers\VehicleObserver;
use App\Observers\ExpenseObserver;
use App\Models\Page;
use App\Models\Proposal;
use App\Models\Sale;
use App\Models\Vehicle;
use App\Models\Expense;

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
        Proposal::observe(SeoObserver::class);
        Sale::observe(SaleObserver::class);
        Vehicle::observe(VehicleObserver::class);
        Expense::observe(ExpenseObserver::class);
        View::composer('*', function ($view) {
            $menus = Cache::remember('frontend_menus', 300, fn () =>
                Menu::orderBy('order')
                    ->where('show_online', 1)
                    ->with('children_active')
                    ->main()
                    ->get()
            );

            $logo = Cache::remember('site_logo', 3600, fn () =>
                optional(\App\Models\Setting::where('label', 'logo')->first())->value
            );

            $view->with('menus', $menus);
            $view->with('logotipo', "storage/" . $logo);
        });

        Paginator::useBootstrap();
    }
}
