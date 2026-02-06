<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Multitenantable;

class Medico extends Model
{
    use HasFactory, LogsActivity, Multitenantable;

    protected $table = 'medicos';

    protected $fillable = [
        'user_id',
        'nombres',
        'apellidos',
        'documento_identidad',
        'telefono',
        'direccion',
        'licencia_medica',
        'anios_experiencia',
        'nivel_experiencia',
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

    public function especialidades()
    {
        return $this->belongsToMany(Especialidad::class, 'medico_especialidad', 'medico_id', 'especialidad_id')
            ->withPivot('tarifa_consulta', 'horario_atencion', 'status')
            ->withTimestamps();
    }

    public function subespecialidades()
    {
        return $this->belongsToMany(Subespecialidad::class, 'medico_subespecialidad', 'medico_id', 'subespecialidad_id')
            ->withPivot('tarifa_consulta', 'experiencia_anios', 'nivel_experiencia', 'horario_atencion', 'status')
            ->withTimestamps();
    }

    public function horarios()
    {
        return $this->hasMany(MedicoHorario::class);
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

    public function getTieneEspecialidadesAttribute()
    {
        return $this->especialidades()->where('status', true)->exists();
    }

    public function getTieneSubespecialidadesAttribute()
    {
        return $this->subespecialidades()->where('status', true)->exists();
    }

    public function getEspecialidadPrincipalAttribute()
    {
        return $this->especialidades()->where('status', true)->first();
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

    public function asignarEspecialidad($especialidadId, $tarifaConsulta = null)
    {
        $this->especialidades()->attach($especialidadId, [
            'tarifa_consulta' => $tarifaConsulta,
            'status' => true
        ]);
    }

    public function asignarSubespecialidad($subespecialidadId, $tarifaConsulta = null, $experienciaAnios = 0, $nivelExperiencia = 'Básico', $horarioAtencion = null)
    {
        $this->subespecialidades()->attach($subespecialidadId, [
            'tarifa_consulta' => $tarifaConsulta,
            'experiencia_anios' => $experienciaAnios,
            'nivel_experiencia' => $nivelExperiencia,
            'horario_atencion' => $horarioAtencion,
            'status' => true
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nombres', 
                'apellidos', 
                'documento_identidad', 
                'licencia_medica', 
                'anios_experiencia', 
                'nivel_experiencia', 
                'status'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}