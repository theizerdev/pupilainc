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
        
        // Configurar vista de paginación personalizada para Livewire
        Paginator::defaultView('livewire.pagination');
        Paginator::defaultSimpleView('livewire.pagination');
    }
}