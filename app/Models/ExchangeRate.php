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

    public static function getLatestRate($currency = 'USD')
    {
        $column = strtolower($currency) . '_rate';
        return self::whereDate('date', today())
            ->whereNotNull($column)
            ->latest('fetch_time')
            ->value($column);
    }

    public static function getTodayRate()
    {
        return self::whereDate('date', today())->first();
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
