<?php

namespace App\Traits;

use App\Services\RegionalConfigurationService;

trait HasRegionalFormatting
{
    protected $regionalService;
    protected $regionalConfig = null;

    public function bootHasRegionalFormatting()
    {
        $this->regionalService = app(RegionalConfigurationService::class);
        $this->regionalConfig = $this->regionalService->getCurrentConfiguration();
    }

    public function formatMoney($amount, $includeSymbol = true)
    {
        if (!$this->regionalConfig) {
            $this->regionalConfig = $this->regionalService->getCurrentConfiguration();
        }

        $currencyFormat = $this->regionalConfig['currency_format'] ?? '#,##0.00';
        $currencySymbol = $this->regionalConfig['currency_symbol'] ?? '$';

        // Convertir el formato a número
        $formatted = format_money($amount, 2, '.', ',');

        if ($includeSymbol) {
            return $currencySymbol . $formatted;
        }

        return $formatted;
    }

    public function formatDate($date, $format = null)
    {
        if (!$this->regionalConfig) {
            $this->regionalConfig = $this->regionalService->getCurrentConfiguration();
        }

        $dateFormat = $format ?? $this->regionalConfig['date_format'] ?? 'd/m/Y';

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format($dateFormat);
    }

    public function formatDateTime($date, $includeTime = true)
    {
        if (!$this->regionalConfig) {
            $this->regionalConfig = $this->regionalService->getCurrentConfiguration();
        }

        $dateFormat = $this->regionalConfig['date_format'] ?? 'd/m/Y';

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        if ($includeTime) {
            return $date->format($dateFormat . ' H:i');
        }

        return $date->format($dateFormat);
    }

    public function getRegionalConfig()
    {
        if (!$this->regionalConfig) {
            $this->regionalConfig = $this->regionalService->getCurrentConfiguration();
        }

        return $this->regionalConfig;
    }
}
