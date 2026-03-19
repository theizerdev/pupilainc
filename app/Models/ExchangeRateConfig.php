<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;

class ExchangeRateConfig extends Model
{
    use LogsActivity, HasSpanishActivityLog;

    protected $fillable = [
        'pais_id',
        'moneda_base',
        'moneda_local',
        'requiere_tasa_cambio',
        'usar_api_bcv',
        'api_url',
        'api_key',
        'tasa_fija',
        'frecuencia_actualizacion_minutos',
        'activo'
    ];

    protected $casts = [
        'requiere_tasa_cambio' => 'boolean',
        'usar_api_bcv' => 'boolean',
        'activo' => 'boolean',
        'tasa_fija' => 'decimal:4',
        'frecuencia_actualizacion_minutos' => 'integer'
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }

    public function exchangeRates()
    {
        return $this->hasMany(ExchangeRate::class, 'pais_id');
    }

    /**
     * Obtener configuración para el país actual del usuario
     */
    public static function getCurrentConfig(): ?self
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }

        // Intentar obtener de la empresa del usuario
        $empresa = $user->empresa;
        if ($empresa && $empresa->pais_id) {
            return self::where('pais_id', $empresa->pais_id)
                ->where('activo', true)
                ->first();
        }

        // Fallback a Venezuela por defecto
        $venezuela = Pais::where('codigo_iso2', 'VE')->first();
        if ($venezuela) {
            return self::where('pais_id', $venezuela->id)
                ->where('activo', true)
                ->first();
        }

        return null;
    }

    /**
     * Verificar si el país usa tasa de cambio dinámica
     */
    public function usesDynamicRate(): bool
    {
        return $this->requiere_tasa_cambio && $this->activo;
    }

    /**
     * Verificar si usa API del BCV (Venezuela)
     */
    public function usesBCVApi(): bool
    {
        return $this->usar_api_bcv && !empty($this->api_url);
    }

    /**
     * Obtener tasa fija configurada
     */
    public function getFixedRate(): ?float
    {
        return $this->tasa_fija;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'moneda_base',
                'moneda_local',
                'requiere_tasa_cambio',
                'usar_api_bcv',
                'api_url',
                'tasa_fija',
                'frecuencia_actualizacion_minutos',
                'activo'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}
