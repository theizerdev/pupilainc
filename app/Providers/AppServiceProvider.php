<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Cargar helpers manualmente si composer dump-autoload no funciona
        if (file_exists(app_path('Helpers/helpers.php'))) {
            require_once app_path('Helpers/helpers.php');
        }
        if (file_exists(app_path('Helpers/SectorPermissionsHelper.php'))) {
            require_once app_path('Helpers/SectorPermissionsHelper.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar HTTPS y URL correcta en producción - NECESARIO para que Livewire funcione con HTTPS
        if (str_contains(config('app.url', ''), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        }

        // Configurar vista de paginación personalizada para Livewire
        Paginator::defaultView('livewire.pagination');
        Paginator::defaultSimpleView('livewire.pagination');
    }
}