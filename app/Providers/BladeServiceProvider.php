<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Directiva @money - Para Venezuela siempre USD, otros países su moneda regional
        Blade::directive('money', function ($expression) {
            // Manejar expresiones complejas y parámetros
            if (strpos($expression, ',') !== false) {
                // Tiene múltiples parámetros
                return "<?php \n                    \$args = [$expression];\n                    \$amount = \$args[0];\n                    \$decimals = isset(\$args[1]) ? (int)\$args[1] : 2;\n                    echo is_venezuela_company() ? '$' . format_money((float)\$amount, \$decimals, '.', ',') : format_money(\$amount, true);\n                ?>";
            } else {
                // Un solo parámetro
                return "<?php echo is_venezuela_company() ? '$' . format_money((float)($expression), 2, '.', ',') : format_money($expression, true); ?>";
            }
        });

        // Directiva @currency para formatear moneda sin símbolo
        Blade::directive('currency', function ($expression) {
            return "<?php echo format_money($expression, false); ?>";
        });

        // Directiva @date para formatear fechas
        Blade::directive('date', function ($expression) {
            return "<?php echo format_date($expression); ?>";
        });

        // Directiva @datetime para formatear fecha y hora
        Blade::directive('datetime', function ($expression) {
            $parts = explode(',', str_replace(['(', ')', ' '], '', $expression));
            $date = trim($parts[0]);
            $includeTime = isset($parts[1]) ? trim($parts[1]) : 'true';
            
            if ($includeTime === 'false') {
                return "<?php echo format_date($date); ?>";
            } else {
                return "<?php echo format_date($date, 'd/m/Y H:i'); ?>";
            }
        });

        // Directiva @money_dual para mostrar doble moneda en Venezuela
        Blade::directive('money_dual', function ($expression) {
            return "<?php echo format_dual_currency($expression, true); ?>";
        });

        // Directiva @money_ve para mostrar moneda según el país (dual si es Venezuela)
        Blade::directive('money_ve', function ($expression) {
            return "<?php echo format_dual_currency($expression, true); ?>";
        });

        // Directiva @usd para mostrar siempre en USD
        Blade::directive('usd', function ($expression) {
            return "<?php echo '$' . format_money((float)$expression, 2, '.', ','); ?>";
        });

        // Directiva @money_regional para mostrar con formato regional
        Blade::directive('money_regional', function ($expression) {
            return "<?php echo format_money($expression, true); ?>";
        });
    }
}