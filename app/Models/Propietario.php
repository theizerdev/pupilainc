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
use Carbon\Carbon;

class Propietario extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, Multitenantable, HasSpanishActivityLog;

    protected $table = 'propietarios';

    protected $fillable = [
        'nombres',
        'apellidos',
        'documento_identidad',
        'fecha_nacimiento',
        'genero',
        'telefono',
        'telefono_alternativo',
        'email',
        'direccion',
        'ocupacion',
        'notas',
        'foto',
        'preferencia_contacto',
        'acepta_recordatorios',
        'acepta_promociones',
        'activo',
        'empresa_id',
        'sucursal_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'acepta_recordatorios' => 'boolean',
        'acepta_promociones' => 'boolean',
        'activo' => 'boolean',
    ];

    protected $attributes = [
        'activo' => true,
        'acepta_recordatorios' => true,
        'acepta_promociones' => false,
        'preferencia_contacto' => 'whatsapp',
    ];

    // Relaciones
    public function mascotas(): HasMany
    {
        return $this->hasMany(Mascota::class, 'propietario_id');
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
    public function scopeActivos($query)
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

    // Accessores
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    public function getEdadAttribute()
    {
        if ($this->fecha_nacimiento) {
            return (int) $this->fecha_nacimiento->diffInYears(Carbon::now());
        }
        return null;
    }

    public function getTelefonoPrincipalAttribute()
    {
        return $this->telefono ?? $this->telefono_alternativo;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombres', 'apellidos', 'documento_identidad', 'telefono', 'email', 'activo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }
}
