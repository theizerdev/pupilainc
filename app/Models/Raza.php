<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;

class Raza extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Multitenantable, HasSpanishActivityLog;

    protected $table = 'razas';

    protected $fillable = [
        'nombre',
        'especie_id',
        'descripcion',
        'tamano_promedio_cm',
        'peso_promedio_kg',
        'esperanza_vida_anios',
        'color',
        'activo',
        'orden',
        'empresa_id',
        'sucursal_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
        'tamano_promedio_cm' => 'integer',
        'peso_promedio_kg' => 'integer',
        'esperanza_vida_anios' => 'integer',
    ];

    protected $attributes = [
        'activo' => true,
        'orden' => 0,
        'color' => '#3B82F6',
    ];

    // Relaciones
    public function especie(): BelongsTo
    {
        return $this->belongsTo(Especie::class);
    }

    public function mascotas(): HasMany
    {
        return $this->hasMany(Mascota::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeForUser($query)
    {
        if (auth()->check() && !auth()->user()->hasRole('Super Administrador')) {
            if (auth()->user()->empresa_id) {
                $query->where('empresa_id', auth()->user()->empresa_id);
            }
            if (auth()->user()->sucursal_id) {
                $query->where('sucursal_id', auth()->user()->sucursal_id);
            }
        }
        return $query;
    }

    public function scopePorEspecie($query, $especieId)
    {
        return $query->where('especie_id', $especieId);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }

    // Accessores
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombre} ({$this->especie->nombre})";
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'especie_id', 'activo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}
