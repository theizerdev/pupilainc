<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\HasSpanishActivityLog;

class Sucursal extends Model
{
    use HasFactory, LogsActivity, HasSpanishActivityLog;

    protected $table = 'sucursales';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'telefono',
        'direccion',
        'latitud',
        'longitud',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function scopeForUser($query)
    {
        if (auth()->check() && !auth()->user()->hasRole('Super Administrador')) {
            if (auth()->user()->empresa_id) {
                $query->where('empresa_id', auth()->user()->empresa_id);
            }
            if (auth()->user()->sucursal_id) {
                $query->where('id', auth()->user()->sucursal_id);
            }
        }
        return $query;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'telefono', 'direccion', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}
