<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\Multitenantable;

class Cita extends Model
{
    use HasFactory, LogsActivity, Multitenantable;

    protected $table = 'citas';

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_CONFIRMADA = 'confirmada';
    const ESTADO_EN_CURSO = 'en_curso';
    const ESTADO_COMPLETADA = 'completada';
    const ESTADO_CANCELADA = 'cancelada';
    const ESTADO_NO_ASISTIO = 'no_asistio';

    const ESTADOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_CONFIRMADA,
        self::ESTADO_EN_CURSO,
        self::ESTADO_COMPLETADA,
        self::ESTADO_CANCELADA,
        self::ESTADO_NO_ASISTIO,
    ];

    const ESTADO_COLORES = [
        'pendiente' => 'warning',
        'confirmada' => 'primary',
        'en_curso' => 'info',
        'completada' => 'success',
        'cancelada' => 'danger',
        'no_asistio' => 'secondary',
    ];

    const ESTADO_LABELS = [
        'pendiente' => 'Pendiente',
        'confirmada' => 'Confirmada',
        'en_curso' => 'En Curso',
        'completada' => 'Completada',
        'cancelada' => 'Cancelada',
        'no_asistio' => 'No Asistió',
    ];

    protected $fillable = [
        'paciente_id',
        'medico_id',
        'especialidad_id',
        'subespecialidad_id',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'estado',
        'notas',
        'color',
        'empresa_id',
        'sucursal_id',
        'created_by',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'estado' => 'string',
    ];

    protected $attributes = [
        'estado' => 'pendiente',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function subespecialidad(): BelongsTo
    {
        return $this->belongsTo(Subespecialidad::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActivas($query)
    {
        return $query->whereNotIn('estado', [self::ESTADO_CANCELADA, self::ESTADO_NO_ASISTIO]);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorMedico($query, $medicoId)
    {
        return $query->where('medico_id', $medicoId);
    }

    public function scopePorPaciente($query, $pacienteId)
    {
        return $query->where('paciente_id', $pacienteId);
    }

    public function scopeEnRango($query, $inicio, $fin)
    {
        return $query->where('fecha_inicio', '<', $fin)
                     ->where('fecha_fin', '>', $inicio);
    }

    public function scopeSinConflicto($query, $medicoId, $inicio, $fin, $excludeId = null)
    {
        return $query->where('medico_id', $medicoId)
                     ->where('fecha_inicio', '<', $fin)
                     ->where('fecha_fin', '>', $inicio)
                     ->whereNotIn('estado', [self::ESTADO_CANCELADA, self::ESTADO_NO_ASISTIO])
                     ->when($excludeId, function ($q) use ($excludeId) {
                         $q->where('id', '!=', $excludeId);
                     });
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

    public function getNombreEstadoAttribute()
    {
        return self::ESTADO_LABELS[$this->estado] ?? $this->estado;
    }

    public function getColorEstadoAttribute()
    {
        return self::ESTADO_COLORES[$this->estado] ?? 'secondary';
    }

    public function getDuracionMinutosAttribute()
    {
        return $this->fecha_inicio->diffInMinutes($this->fecha_fin);
    }

    public function getTituloCalendarioAttribute()
    {
        return "{$this->paciente->nombre_completo} - {$this->medico->nombre_completo}";
    }

    public function cambiarEstado($nuevoEstado)
    {
        $this->update(['estado' => $nuevoEstado]);
        return $this;
    }

    public function tieneConflicto()
    {
        return static::sinConflicto($this->medico_id, $this->fecha_inicio, $this->fecha_fin, $this->id)->exists();
    }

    public function toFullCalendarEvent()
    {
        return [
            'id' => $this->id,
            'title' => $this->paciente->nombre_completo,
            'start' => $this->fecha_inicio->toIso8601String(),
            'end' => $this->fecha_fin->toIso8601String(),
            'allDay' => false,
            'extendedProps' => [
                'calendar' => $this->estado,
                'medico' => $this->medico->nombre_completo,
                'paciente' => $this->paciente->nombre_completo,
                'paciente_id' => $this->paciente_id,
                'medico_id' => $this->medico_id,
                'especialidad_id' => $this->especialidad_id,
                'subespecialidad_id' => $this->subespecialidad_id,
                'es_menor' => $this->paciente->es_menor,
                'motivo' => $this->motivo,
                'estado' => $this->estado,
                'estado_label' => $this->nombre_estado,
                'notas' => $this->notas,
                'descripcion' => $this->motivo,
            ],
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['paciente_id', 'medico_id', 'especialidad_id', 'subespecialidad_id', 'fecha_inicio', 'fecha_fin', 'motivo', 'estado', 'notas'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
