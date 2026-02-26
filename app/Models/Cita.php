<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Str;
use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;

class Cita extends Model
{
    use HasFactory, LogsActivity, Multitenantable, HasSpanishActivityLog;

    protected $table = 'citas';

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_CONFIRMADA = 'confirmada';
    const ESTADO_EN_CURSO = 'en_curso';
    const ESTADO_SALA_ESPERA = 'sala_espera';
    const ESTADO_COMPLETADA = 'completada';
    const ESTADO_CANCELADA = 'cancelada';
    const ESTADO_NO_ASISTIO = 'no_asistio';

    const ESTADOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_CONFIRMADA,
        self::ESTADO_EN_CURSO,
        self::ESTADO_SALA_ESPERA,
        self::ESTADO_COMPLETADA,
        self::ESTADO_CANCELADA,
        self::ESTADO_NO_ASISTIO,
    ];

    const ESTADO_COLORES = [
        'pendiente' => 'warning',
        'confirmada' => 'primary',
        'en_curso' => 'info',
        'sala_espera' => 'warning',
        'completada' => 'success',
        'cancelada' => 'danger',
        'no_asistio' => 'secondary',
    ];

    const ESTADO_LABELS = [
        'pendiente' => 'Pendiente',
        'confirmada' => 'Confirmada',
        'en_curso' => 'En Curso',
        'sala_espera' => 'En Sala de Espera',
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
        'tipo_consulta_id',
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

    public function respuestasPreconsulta(): HasMany
    {
        return $this->hasMany(RespuestaPreconsulta::class);
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

    public function recordatorios(): HasMany
    {
        return $this->hasMany(CitaRecordatorio::class);
    }

    public function tipoConsulta(): BelongsTo
    {
        return $this->belongsTo(TipoConsulta::class);
    }

    public function confirmaciones()
    {
        return $this->hasMany(CitaConfirmacion::class);
    }

    public function ultimaConfirmacion()
    {
        return $this->hasOne(CitaConfirmacion::class)->latestOfMany();
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
        $estadoAnterior = $this->estado;
        $this->update(['estado' => $nuevoEstado]);
        try {
            activity()
                ->performedOn($this)
                ->causedBy(auth()->user() ?? null)
                ->withProperties([
                    'previous' => $estadoAnterior,
                    'new' => $nuevoEstado,
                ])
                ->log('cita_estado_cambiado');
        } catch (\Throwable $e) {
        }
        return $this;
    }

    public function programarRecordatorios(): void
    {
        // Limpiar recordatorios existentes
        $this->recordatorios()->delete();

        // Solo programar para citas activas futuras
        if (!$this->activas()->exists() || $this->fecha_inicio <= now()) {
            return;
        }

        // Recordatorio 24 horas antes
        if ($this->fecha_inicio->subDay() > now()) {
            $this->recordatorios()->create([
                'tipo' => '24h',
                'fecha_envio_programado' => $this->fecha_inicio->copy()->subDay(),
                'canal' => 'whatsapp',
                'mensaje' => $this->generarMensajeRecordatorio('24h')
            ]);
        }

        // Recordatorio 2 horas antes
        if ($this->fecha_inicio->subHours(2) > now()) {
            $this->recordatorios()->create([
                'tipo' => '2h',
                'fecha_envio_programado' => $this->fecha_inicio->copy()->subHours(2),
                'canal' => 'whatsapp',
                'mensaje' => $this->generarMensajeRecordatorio('2h')
            ]);
        }
    }

    public function generarMensajeRecordatorio(string $tipo): string
    {
        $fecha = $this->fecha_inicio->format('d/m/Y H:i');
        $medico = $this->medico->nombre_completo;
        $especialidad = $this->especialidad->nombre;
        $sucursal = $this->sucursal->nombre;

        switch ($tipo) {
            case '24h':
                return "🩺 *Recordatorio de Cita Médica*\n\n" .
                       "Hola {$this->paciente->nombre_completo},\n\n" .
                       "Le recordamos que tiene una cita médica programada para mañana:\n\n" .
                       "📅 *Fecha:* {$fecha}\n" .
                       "👨‍⚕️ *Médico:* {$medico}\n" .
                       "🏥 *Especialidad:* {$especialidad}\n" .
                       "🏢 *Sucursal:* {$sucursal}\n\n" .
                       "Por favor confirme su asistencia respondiendo *SI* o *NO*";

            case '2h':
                return "⏰ *Próxima Cita Médica*\n\n" .
                       "Hola {$this->paciente->nombre_completo},\n\n" .
                       "Su cita médica es en 2 horas:\n\n" .
                       "📅 *Fecha:* {$fecha}\n" .
                       "👨‍⚕️ *Médico:* {$medico}\n" .
                       "🏥 *Especialidad:* {$especialidad}\n" .
                       "🏢 *Sucursal:* {$sucursal}\n\n" .
                       "¡Lo esperamos! 🏥";

            default:
                return "Recordatorio de cita médica el {$fecha} con {$medico}";
        }
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
                'medico_full' => $this->medico->nombre_completo,
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
                'tipo_consulta_id' => $this->tipo_consulta_id,
                'tipo_consulta_nombre' => $this->tipoConsulta?->nombre,
                'tipo_consulta_color' => $this->tipoConsulta?->color,
                'sucursal_id' => $this->sucursal_id,
            ],
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['paciente_id', 'medico_id', 'especialidad_id', 'subespecialidad_id', 'fecha_inicio', 'fecha_fin', 'motivo', 'estado', 'notas'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => static::getSpanishDescription($eventName));
    }

    // Método para iniciar confirmación automática
    public function solicitarConfirmacion()
    {
        if ($this->estado !== self::ESTADO_PENDIENTE) {
            return null;
        }

        $service = new \App\Services\CitaConfirmationService();
        return $service->iniciarConfirmacion($this);
    }

    // Método para verificar si necesita confirmación
    public function necesitaConfirmacion(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE && 
               $this->fecha_inicio > now()->addHours(24);
    }

    // Sobreescribir método de creación para auto-confirmación
    protected static function booted()
    {
        static::created(function ($cita) {
            // No despachar el job de confirmación separado.
            // La confirmación ahora se incluye dentro de la notificación
            // de nueva cita en CitaNotificationService::notificarNuevaCita()
            \Log::info('Cita ' . $cita->id . ' creada - confirmación se enviará integrada en la notificación');
        });
    }
}
