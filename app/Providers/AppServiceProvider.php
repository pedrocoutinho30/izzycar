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
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Mapa nome da marca → logo, partilhado durante o pedido (sem N+1).
        $this->app->scoped(\App\Support\BrandLogos::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Política central de palavras-passe: usada em todos os sítios onde se define uma password nova.
        // O teste de passwords comprometidas (HIBP) faz chamadas de rede, por isso fica de fora nos testes.
        Password::defaults(function () {
            $regra = Password::min(10)->mixedCase()->numbers();

            return app()->environment('testing') ? $regra : $regra->uncompromised();
        });

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
        // Só as views do site público (layout, parciais e páginas que o estendem) usam $menus/$logotipo.
        // Antes corria em '*' (backoffice, PDFs, e-mails…) e sobrepunha o $menus do MenuController.
        View::composer(['frontend.*'], function ($view) {
            $menus = Cache::remember('frontend_menus', 300, fn () =>
                Menu::orderBy('order')
                    ->where('show_online', 1)
                    ->with('children_active')
                    ->main()
                    ->get()
            );

            $logo = Cache::remember('site_logo', 3600, fn () =>
                \App\Models\Setting::cachedValue('logo')
            );

            // Miniatura do logo (só se existir no disco); as views caem para o original sem ela.
            $logoSmall = Cache::remember('site_logo_small', 3600, function () use ($logo) {
                $webp = $logo ? preg_replace('/\.(png|jpe?g)$/i', '.webp', $logo) : null;
                $variant = \App\Services\ThumbnailService::existing($webp, \App\Services\ThumbnailService::LOGO_WIDTH);
                return $variant ? 'storage/' . $variant : '';
            });

            
            $view->with('menus', $menus);
            $view->with('logotipo_small', $logoSmall ?: null);
            $view->with('logotipo', "storage/" . $logo);
        });

        Paginator::useBootstrap();
    }
}
