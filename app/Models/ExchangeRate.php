<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;
use App\Traits\FiscalAuditable;

class ExchangeRate extends Model
{
    use LogsActivity, HasSpanishActivityLog, FiscalAuditable;

    protected $fillable = [
        'pais_id',
        'date',
        'usd_rate',
        'eur_rate',
        'source',
        'fetch_time',
        'raw_data'
    ];

    protected $casts = [
        'date' => 'date',
        'fetch_time' => 'datetime:H:i:s',
        'raw_data' => 'array',
        'usd_rate' => 'decimal:4',
        'eur_rate' => 'decimal:4'
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }

    public function config()
    {
        return $this->belongsTo(ExchangeRateConfig::class, 'pais_id');
    }

    /**
     * Obtener la tasa más reciente para una moneda y país específicos
     */
    public static function getLatestRate($currency = 'USD', $paisId = null)
    {
        // Si no se proporciona país, usar el del usuario actual
        if (!$paisId) {
            $config = ExchangeRateConfig::getCurrentConfig();
            $paisId = $config?->pais_id;
        }

        $column = strtolower($currency) . '_rate';
        
        $query = self::whereDate('date', today());
        
        if ($paisId) {
            $query->where('pais_id', $paisId);
        }
        
        return $query->whereNotNull($column)
            ->latest('fetch_time')
            ->value($column);
    }

    /**
     * Obtener la tasa de hoy para un país específico
     */
    public static function getTodayRate($paisId = null)
    {
        if (!$paisId) {
            $config = ExchangeRateConfig::getCurrentConfig();
            $paisId = $config?->pais_id;
        }

        $query = self::whereDate('date', today());
        
        if ($paisId) {
            $query->where('pais_id', $paisId);
        }
        
        return $query->first();
    }

    /**
     * Scope para filtrar por país
     */
    public function scopeForPais($query, $paisId = null)
    {
        if (!$paisId) {
            $config = ExchangeRateConfig::getCurrentConfig();
            $paisId = $config?->pais_id;
        }
        
        return $query->where('pais_id', $paisId);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['date', 'usd_rate', 'eur_rate', 'source'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}
