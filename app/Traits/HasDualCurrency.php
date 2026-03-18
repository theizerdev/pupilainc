<?php

namespace App\Traits;

use App\Models\ExchangeRate;

trait HasDualCurrency
{
    /**
     * Verificar si la empresa actual es de Venezuela
     */
    public function isVenezuelaCompany(): bool
    {
        return is_venezuela_company();
    }

    /**
     * Obtener la tasa de cambio actual
     */
    public function getCurrentExchangeRate(): ?float
    {
        return ExchangeRate::getLatestRate('USD');
    }

    /**
     * Convertir USD a Bolívares usando tasa actual
     */
    public function convertUsdToBs(float $usdAmount): ?float
    {
        $rate = $this->getCurrentExchangeRate();
        return $rate ? $usdAmount * $rate : null;
    }

    /**
     * Formatear monto USD con doble moneda si es Venezuela
     * @param float $usdAmount Monto en USD (moneda base del sistema)
     */
    public function formatDualCurrency(float $usdAmount, bool $showBoth = true): string
    {
        return format_dual_currency($usdAmount, $showBoth);
    }

    /**
     * Formatear monto solo en USD
     */
    public function formatUsd(float $usdAmount): string
    {
        return '$' . format_money($usdAmount, 2, '.', ',');
    }

    /**
     * Obtener información de monedas para Venezuela
     */
    public function getCurrencyInfo(): array
    {
        if (!$this->isVenezuelaCompany()) {
            return [
                'primary' => get_current_currency_symbol(),
                'secondary' => null,
                'rate' => null
            ];
        }

        return [
            'primary' => '$',
            'secondary' => 'Bs.',
            'rate' => $this->getCurrentExchangeRate()
        ];
    }
}