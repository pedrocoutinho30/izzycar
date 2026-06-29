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
use App\Observers\ClientObserver;
use App\Observers\ProposalObserver;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Page;
use App\Models\Proposal;
use App\Models\Sale;
use App\Models\Vehicle;
use App\Models\Expense;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;

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
        Proposal::observe([SeoObserver::class, ProposalObserver::class]);
        Sale::observe(SaleObserver::class);
        Vehicle::observe(VehicleObserver::class);
        Expense::observe(ExpenseObserver::class);
        Client::observe(ClientObserver::class);

        // Log de login e logout
        Event::listen(Login::class, function (Login $event) {
            AuditLog::record('login', "Login: {$event->user->name} ({$event->user->email})");
        });
        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                AuditLog::record('logout', "Logout: {$event->user->name} ({$event->user->email})");
            }
        });
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
