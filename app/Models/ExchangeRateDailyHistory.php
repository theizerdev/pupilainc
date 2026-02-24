<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;
use App\Traits\FiscalAuditable;

class ExchangeRateDailyHistory extends Model
{
    use LogsActivity, HasSpanishActivityLog, FiscalAuditable;

    protected $fillable = [
        'monthly_history_id',
        'date',
        'usd_rate',
        'eur_rate',
        'source',
        'fetch_time',
        'recorded_at',
        'recorded_by',
    ];

    protected $casts = [
        'usd_rate' => 'decimal:4',
        'eur_rate' => 'decimal:4',
        'recorded_at' => 'datetime',
    ];

    public function monthlyHistory()
    {
        return $this->belongsTo(ExchangeRateMonthlyHistory::class, 'monthly_history_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['date', 'usd_rate', 'eur_rate', 'source', 'recorded_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}
