<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;

class EnfermeroEspecialidad extends Model
{
    use HasFactory, LogsActivity, Multitenantable, HasSpanishActivityLog;

    protected $table = 'enfermero_especialidades';

    protected $fillable = [
        'enfermero_id',
        'especialidad',
        'empresa_id',
        'sucursal_id'
    ];

    // Relaciones
    public function enfermero(): BelongsTo
    {
        return $this->belongsTo(Enfermero::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['enfermero_id', 'especialidad'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}