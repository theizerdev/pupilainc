<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pais;
use App\Models\ExchangeRateConfig;

class ExchangeRateConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Venezuela - Con tasa de cambio dinámica (BCV)
        $venezuela = Pais::where('codigo_iso2', 'VE')->first();
        
        if ($venezuela) {
            ExchangeRateConfig::updateOrCreate(
                ['pais_id' => $venezuela->id],
                [
                    'moneda_base' => 'USD',
                    'moneda_local' => 'VES',
                    'requiere_tasa_cambio' => true,
                    'usar_api_bcv' => true,
                    'api_url' => config('services.dolarvzla.base_url', 'https://api.dolarvzla.com/public'),
                    'api_key' => config('services.dolarvzla.key'),
                    'tasa_fija' => null,
                    'frecuencia_actualizacion_minutos' => 60,
                    'activo' => true
                ]
            );
        }

        // Colombia - Sin tasa de cambio (usa USD directamente)
        $colombia = Pais::where('codigo_iso2', 'CO')->first();
        
        if ($colombia) {
            ExchangeRateConfig::updateOrCreate(
                ['pais_id' => $colombia->id],
                [
                    'moneda_base' => 'USD',
                    'moneda_local' => 'COP',
                    'requiere_tasa_cambio' => false,
                    'usar_api_bcv' => false,
                    'api_url' => null,
                    'api_key' => null,
                    'tasa_fija' => null,
                    'frecuencia_actualizacion_minutos' => 60,
                    'activo' => true
                ]
            );
        }

        // Argentina - Podría usar tasa fija o dinámica
        $argentina = Pais::where('codigo_iso2', 'AR')->first();
        
        if ($argentina) {
            ExchangeRateConfig::updateOrCreate(
                ['pais_id' => $argentina->id],
                [
                    'moneda_base' => 'USD',
                    'moneda_local' => 'ARS',
                    'requiere_tasa_cambio' => false,
                    'usar_api_bcv' => false,
                    'api_url' => null,
                    'api_key' => null,
                    'tasa_fija' => null,
                    'frecuencia_actualizacion_minutos' => 60,
                    'activo' => true
                ]
            );
        }

        // México
        $mexico = Pais::where('codigo_iso2', 'MX')->first();
        
        if ($mexico) {
            ExchangeRateConfig::updateOrCreate(
                ['pais_id' => $mexico->id],
                [
                    'moneda_base' => 'USD',
                    'moneda_local' => 'MXN',
                    'requiere_tasa_cambio' => false,
                    'usar_api_bcv' => false,
                    'api_url' => null,
                    'api_key' => null,
                    'tasa_fija' => null,
                    'frecuencia_actualizacion_minutos' => 60,
                    'activo' => true
                ]
            );
        }

        // España - Euro (no requiere conversión)
        $espana = Pais::where('codigo_iso2', 'ES')->first();
        
        if ($espana) {
            ExchangeRateConfig::updateOrCreate(
                ['pais_id' => $espana->id],
                [
                    'moneda_base' => 'EUR',
                    'moneda_local' => 'EUR',
                    'requiere_tasa_cambio' => false,
                    'usar_api_bcv' => false,
                    'api_url' => null,
                    'api_key' => null,
                    'tasa_fija' => null,
                    'frecuencia_actualizacion_minutos' => 60,
                    'activo' => true
                ]
            );
        }
    }
}
