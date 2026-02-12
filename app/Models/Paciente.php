<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Multitenantable;
use Carbon\Carbon;

class Paciente extends Model
{
    use HasFactory, LogsActivity, Multitenantable;

    protected $table = 'pacientes';

    protected $fillable = [
        'nombres',
        'apellidos',
        'documento_identidad',
        'telefono',
        'email',
        'direccion',
        'nickname',
        'fecha_nacimiento',
        'genero',
        'estado_civil',
        'ocupacion',
        'nacionalidad',
        'foto',
        'status',
        'empresa_id',
        'sucursal_id'
    ];

    protected $casts = [
        'status' => 'boolean',
        'fecha_nacimiento' => 'date',
    ];

    protected $attributes = [
        'status' => true,
    ];

    // Relaciones
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function tutor()
    {
        return $this->hasOne(Tutor::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('status', true);
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
            return Carbon::now()->diffInYears($this->fecha_nacimiento);
        }
        return null;
    }

    public function getEsMenorAttribute()
    {
        return $this->edad !== null && $this->edad < 18;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nombres',
                'apellidos',
                'documento_identidad',
                'telefono',
                'email',
                'status'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}