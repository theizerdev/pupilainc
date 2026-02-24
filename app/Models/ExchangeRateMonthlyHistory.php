<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;
use App\Traits\FiscalAuditable;

class ExchangeRateMonthlyHistory extends Model
{
    use LogsActivity, HasSpanishActivityLog, FiscalAuditable;

    protected $fillable = [
        'year',
        'month',
        'usd_avg',
        'usd_min',
        'usd_max',
        'eur_avg',
        'eur_min',
        'eur_max',
        'records_count',
        'sources',
        'daily_records',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'usd_avg' => 'decimal:4',
        'usd_min' => 'decimal:4',
        'usd_max' => 'decimal:4',
        'eur_avg' => 'decimal:4',
        'eur_min' => 'decimal:4',
        'eur_max' => 'decimal:4',
        'sources' => 'array',
        'daily_records' => 'array',
        'generated_at' => 'datetime',
    ];

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['year', 'month', 'usd_avg', 'usd_min', 'usd_max', 'eur_avg', 'records_count', 'generated_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}
