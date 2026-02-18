<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Multitenantable;

class Enfermero extends Model
{
    use HasFactory, LogsActivity, Multitenantable;

    protected $table = 'enfermeros';

    protected $fillable = [
        'user_id',
        'nombres',
        'apellidos',
        'genero',
        'documento_identidad',
        'telefono',
        'direccion',
        'licencia_enfermeria',
        'anios_experiencia',
        'nivel_experiencia',
        'tipo_enfermero',
        'especialidad_enfermeria',
        'status',
        'empresa_id',
        'sucursal_id'
    ];

    protected $casts = [
        'status' => 'boolean',
        'anios_experiencia' => 'integer',
    ];

    protected $attributes = [
        'status' => true,
        'anios_experiencia' => 0,
        'nivel_experiencia' => 'Básico',
        'tipo_enfermero' => 'General',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function horarios()
    {
        return $this->hasMany(EnfermeroHorario::class);
    }

    public function asignacionesConsultorios()
    {
        return $this->hasMany(ConsultorioAsignacion::class);
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class);
    }

    public function especialidades(): HasMany
    {
        return $this->hasMany(EnfermeroEspecialidad::class);
    }

    public function signosVitales(): HasMany
    {
        return $this->hasMany(SignosVitales::class);
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

    public function getInicialesAttribute()
    {
        // Obtener primer nombre
        $nombres = explode(' ', trim($this->nombres));
        $primerNombre = !empty($nombres[0]) ? strtoupper(substr($nombres[0], 0, 1)) : '';
        
        // Obtener primer apellido
        $apellidos = explode(' ', trim($this->apellidos));
        $primerApellido = !empty($apellidos[0]) ? strtoupper(substr($apellidos[0], 0, 1)) : '';
        
        return $primerNombre . $primerApellido;
    }

    public function getColorClaseAttribute()
    {
        // Array de clases de color disponibles
        $colores = [
            'bg-label-primary',
            'bg-label-secondary',
            'bg-label-success',
            'bg-label-danger',
            'bg-label-warning',
            'bg-label-info',
            'bg-label-dark',
            'bg-label-light',
        ];
        
        // Usar el ID del enfermero para determinar qué color usar
        // Esto asegura que el mismo enfermero siempre tenga el mismo color
        $indiceColor = ($this->id - 1) % count($colores);
        
        return $colores[$indiceColor];
    }

    // Métodos
    public function activar()
    {
        $this->update(['status' => true]);
        $this->user()->update(['status' => true]);
    }

    public function desactivar()
    {
        $this->update(['status' => false]);
        $this->user()->update(['status' => false]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nombres', 
                'apellidos', 
                'documento_identidad', 
                'licencia_enfermeria', 
                'anios_experiencia', 
                'nivel_experiencia', 
                'tipo_enfermero',
                'especialidad_enfermeria',
                'status'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}