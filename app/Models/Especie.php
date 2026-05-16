<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;

class Especie extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Multitenantable, HasSpanishActivityLog;

    protected $table = 'especies';

    protected $fillable = [
        'nombre',
        'nombre_cientifico',
        'descripcion',
        'icono',
        'color',
        'activo',
        'orden',
        'empresa_id',
        'sucursal_id',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    protected $attributes = [
        'activo' => true,
        'orden' => 0,
        'color' => '#3B82F6',
    ];

    // Relaciones
    public function razas(): HasMany
    {
        return $this->hasMany(Raza::class);
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

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }

    // Accessores
    public function getNombreCompletoAttribute()
    {
        if ($this->nombre_cientifico) {
            return "{$this->nombre} ({$this->nombre_cientifico})";
        }
        return $this->nombre;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'nombre_cientifico', 'activo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}
