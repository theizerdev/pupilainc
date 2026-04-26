<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Str;
use App\Traits\Multitenantable;
use App\Traits\HasSpanishActivityLog;
use App\Models\Consulta;

class Cita extends Model
{
    use HasFactory, LogsActivity, Multitenantable, HasSpanishActivityLog;

    protected $table = 'citas';

    const ESTADO_PENDIENTE = 'programada';
    const ESTADO_CONFIRMADA = 'confirmada';
    const ESTADO_CANCELADA = 'cancelada';
    const ESTADO_NO_ASISTIO = 'no_asistio';

    // Estados de consulta (sincronizados con la consulta asociada)
    const ESTADO_SALA_ESPERA = 'sala_espera';
    const ESTADO_EN_ENFERMERIA = 'en_enfermeria';
    const ESTADO_EN_CONSULTORIO = 'en_consultorio';
    const ESTADO_EN_CONSULTORIO_OPTOMETRISTA = 'en_consultorio_optometrista';
    const ESTADO_EN_GOTAS = 'en_gotas';
    const ESTADO_DILATADO = 'dilatado';
    const ESTADO_EN_OPTICA = 'en_optica';
    const ESTADO_EN_ESTUDIO = 'en_estudio';
    const ESTADO_FINALIZADA = 'finalizada';
    const ESTADO_PAGADA = 'pagada';

    const ESTADOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_CONFIRMADA,
        self::ESTADO_CANCELADA,
        self::ESTADO_NO_ASISTIO,
        // Estados de consulta
        self::ESTADO_SALA_ESPERA,
        self::ESTADO_EN_ENFERMERIA,
        self::ESTADO_EN_CONSULTORIO,
        self::ESTADO_EN_CONSULTORIO_OPTOMETRISTA,
        self::ESTADO_EN_GOTAS,
        self::ESTADO_DILATADO,
        self::ESTADO_EN_OPTICA,
        self::ESTADO_EN_ESTUDIO,
        self::ESTADO_FINALIZADA,
        self::ESTADO_PAGADA,
    ];

    const ESTADO_COLORES = [
        'programada' => '#ffc107',
        'confirmada' => '#0d6efd',
        'cancelada' => '#dc3545',
        'no_asistio' => '#6c757d',
        'por_llegar' => '#9E9E9E',
        'sala_espera' => '#FFA726',
        'en_enfermeria' => '#EF5350',
        'en_consultorio' => '#42A5F5',
        'en_consultorio_optometrista' => '#7E57C2',
        'en_gotas' => '#26C6DA',
        'dilatado' => '#00BCD4',
        'en_optica' => '#AB47BC',
        'en_estudio' => '#EC407A',
        'finalizada' => '#66BB6A',
        'pagada' => '#4CAF50',
        'borrador' => '#BDBDBD',
    ];

    const ESTADO_LABELS = [
        'programada' => 'Programada',
        'confirmada' => 'Confirmada',
        'cancelada' => 'Cancelada',
        'no_asistio' => 'No Asistió',
        // Consulta states
        'sala_espera' => 'Sala de Espera',
        'en_enfermeria' => 'En Enfermería',
        'en_consultorio' => 'En Consultorio',
        'en_consultorio_optometrista' => 'En Consultorio Optometrista',
        'en_gotas' => 'En Gotas',
        'dilatado' => 'Dilatado',
        'en_optica' => 'En Óptica',
        'en_estudio' => 'En Estudio',
        'finalizada' => 'Finalizada',
        'pagada' => 'Pagada',
    ];

    // Prioridades de citas
    const PRIORIDAD_NORMAL = 'normal';
    const PRIORIDAD_ALTA = 'alta';
    const PRIORIDAD_EMERGENCIA = 'emergencia';

    const PRIORIDADES = [
        self::PRIORIDAD_NORMAL,
        self::PRIORIDAD_ALTA,
        self::PRIORIDAD_EMERGENCIA,
    ];

    const PRIORIDAD_LABELS = [
        'normal' => 'Normal',
        'alta' => 'Alta',
        'emergencia' => 'Emergencia',
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
        'prioridad', // Agregado: campo de prioridad
        'estado_preconsulta',
        'token_preconsulta',
        'fecha_envio_preconsulta',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'estado' => 'string',
    ];

    protected $attributes = [
        'estado' => 'programada',
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

    public function consulta(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Consulta::class);
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(CitaAuditoria::class)->orderBy('created_at', 'desc');
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
        return $query->where('fecha_inicio', '<=', $fin)
                     ->where('fecha_fin', '>=', $inicio);
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
        if (!in_array($nuevoEstado, self::ESTADOS)) {
            throw new \InvalidArgumentException("Estado inválido: {$nuevoEstado}");
        }

        $estadoAnterior = $this->estado;

        // No realizar acciones si el estado no cambia
        if ($estadoAnterior === $nuevoEstado) {
            \Log::info('cambiarEstado: mismo estado, no hace nada', ['cita_id' => $this->id, 'estado' => $nuevoEstado]);
            return;
        }

        \Log::info('cambiarEstado: cambio detectado', ['cita_id' => $this->id, 'estadoAnterior' => $estadoAnterior, 'nuevoEstado' => $nuevoEstado]);

        $consultaEstados = [
            self::ESTADO_SALA_ESPERA, self::ESTADO_EN_ENFERMERIA, self::ESTADO_EN_CONSULTORIO,
            self::ESTADO_EN_CONSULTORIO_OPTOMETRISTA, self::ESTADO_EN_GOTAS, self::ESTADO_DILATADO, self::ESTADO_EN_OPTICA,
            self::ESTADO_EN_ESTUDIO, self::ESTADO_FINALIZADA, self::ESTADO_PAGADA,
        ];

        $this->estado = $nuevoEstado;
        $this->save();

        if (in_array($nuevoEstado, $consultaEstados)) {
            $this->crearConsultaSiNoExiste(true);
            $consulta = Consulta::withoutGlobalScopes()->where('cita_id', $this->id)->first();
            if ($consulta) {
                $consulta->update([
                    'estado' => $nuevoEstado,
                    'estado_changed_at' => now(),
                ]);
            }
            
            // Verificar si es el primer cambio al estado de sala de espera y si la preconsulta aún está pendiente
            // y si no se han completado las respuestas de preconsulta
            if ($nuevoEstado === self::ESTADO_SALA_ESPERA && 
                $estadoAnterior !== self::ESTADO_SALA_ESPERA) {
                
                // Verificar si ya se completaron las respuestas de preconsulta
                $yaTieneRespuestas = $this->respuestasPreconsulta()
                    ->where('completado', true)
                    ->exists();
                
                // Solo enviar el formulario si no ha sido completado previamente
                if ($this->estado_preconsulta === 'pendiente') {
                    $this->crearPreconsultaYEnviarWhatsApp();
                }
            }
        } elseif ($nuevoEstado === self::ESTADO_CONFIRMADA) {
            // Solo enviar preconsulta si está pendiente y no hay respuestas completadas
            $yaTieneRespuestas = $this->respuestasPreconsulta()
                ->where('completado', true)
                ->exists();

            if ($this->estado_preconsulta === 'pendiente' && !$yaTieneRespuestas) {
                $this->crearPreconsultaYEnviarWhatsApp();
            }
            $this->crearConsultaSiNoExiste(true);
        } elseif ($nuevoEstado === self::ESTADO_PENDIENTE) {
        } else {
            Consulta::withoutGlobalScopes()->where('cita_id', $this->id)->delete();
        }
    }

    public function crearPreconsultaYEnviarWhatsApp(): array
    {
        $resultado = ['preconsulta_creada' => false, 'whatsapp_enviado' => false, 'token' => null];

        try {
            $token = \Illuminate\Support\Str::random(32);
            $resultado['token'] = $token;

            // Buscar cuestionario: primero por especialidad, luego genérico
            $cuestionario = null;
            if ($this->especialidad_id) {
                $cuestionario = \App\Models\Cuestionario::where('activo', true)
                    ->where('empresa_id', $this->empresa_id)
                    ->where('tipo', 'preconsulta')
                    ->where('especialidad_id', $this->especialidad_id)
                    ->first();
            }
            if (!$cuestionario) {
                $cuestionario = \App\Models\Cuestionario::where('activo', true)
                    ->where('empresa_id', $this->empresa_id)
                    ->where('tipo', 'preconsulta')
                    ->whereNull('especialidad_id')
                    ->first();
            }

            if ($cuestionario) {
                foreach ($cuestionario->preguntas as $pregunta) {
                    \App\Models\RespuestaPreconsulta::create([
                        'paciente_id' => $this->paciente_id,
                        'cita_id' => $this->id,
                        'consulta_id' => null,
                        'pregunta_id' => $pregunta->id,
                        'token_unico' => $token,
                        'empresa_id' => $this->empresa_id,
                        'sucursal_id' => $this->sucursal_id ?? 1,
                        'completado' => false,
                        'created_by' => $this->paciente_id,
                    ]);
                }
                $resultado['preconsulta_creada'] = true;
            }

            $whatsappEnviado = $this->enviarCuestionarioWhatsApp($token);
            $resultado['whatsapp_enviado'] = $whatsappEnviado;

            // Actualizar el estado de la preconsulta a 'enviado' después de enviar el cuestionario
            if ($whatsappEnviado) {
                $this->update([
                    'estado_preconsulta' => 'enviado',
                    'token_preconsulta' => $token,
                    'fecha_envio_preconsulta' => now(),
                ]);
            }

            \Log::info('Preconsulta creada para cita', ['cita_id' => $this->id, 'token' => $token, 'resultado' => $resultado]);
        } catch (\Exception $e) {
            \Log::error('Error creando preconsulta', ['cita_id' => $this->id, 'error' => $e->getMessage()]);
        }

        return $resultado;
    }

    protected function enviarCuestionarioWhatsApp(string $token): bool
    {
        try {
            $paciente = $this->paciente;
            if (!$paciente) return false;

            $telefono = $paciente->telefono;
            $edad = $paciente->fecha_nacimiento ? \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age : null;
            $esMenor = $edad !== null && $edad < 18;

            if ($esMenor && $paciente->tutor && !empty($paciente->tutor->telefono)) {
                $telefono = $paciente->tutor->telefono;
            } elseif (empty($telefono) && $paciente->tutor) {
                $telefono = $paciente->tutor->telefono;
            }

            if (empty($telefono) || trim($telefono) === '') {
                return false;
            }

            $telefonoFormateado = $this->formatearTelefono($telefono);
            $link = route('preconsulta.formulario', ['token' => $token]);

            $saludo = $esMenor
                ? "Estimado representante de *{$paciente->nombre_completo}*"
                : "Hola *{$paciente->nombres}*";

            $mensaje = "🏥 *Pre-consulta Médica*\n\n"
                . "{$saludo},\n\n"
                . "Para agilizar su atención médica, le solicitamos completar el siguiente cuestionario antes de su consulta:\n\n"
                . "📋 *Cuestionario Pre-consulta*\n"
                . "🔗 {$link}\n\n"
                . "El cuestionario es confidencial y nos ayudará a brindarle una mejor atención.\n\n"
                . "⏰ Le recomendamos completarlo mientras espera.\n\n"
                . "Gracias por su confianza. 🙏";

            $whatsappService = new \App\Services\WhatsAppService($this->empresa_id);
            $resultado = $whatsappService->sendMessage($telefonoFormateado, $mensaje);

            return $resultado !== null;
        } catch (\Exception $e) {
            \Log::warning('Error enviando WhatsApp de preconsulta', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function formatearTelefono(string $telefono): string
    {
        $telefonoLimpio = preg_replace('/[^0-9]/', '', $telefono);

        try {
            if ($this->empresa_id) {
                $empresa = \App\Models\Empresa::with('pais')->find($this->empresa_id);
                if ($empresa && $empresa->pais && $empresa->pais->codigo_telefonico) {
                    $codigoPais = preg_replace('/[^0-9]/', '', $empresa->pais->codigo_telefonico);
                    if (!str_starts_with($telefonoLimpio, $codigoPais)) {
                        if (str_starts_with($telefonoLimpio, '0')) {
                            $telefonoLimpio = substr($telefonoLimpio, 1);
                        }
                        $telefonoLimpio = $codigoPais . $telefonoLimpio;
                    }
                    return $telefonoLimpio;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error formateando teléfono', ['error' => $e->getMessage()]);
        }

        if (strlen($telefonoLimpio) === 10 && str_starts_with($telefonoLimpio, '0')) {
            return '52' . $telefonoLimpio;
        } elseif (strlen($telefonoLimpio) === 9 && !str_starts_with($telefonoLimpio, '5')) {
            return '52' . $telefonoLimpio;
        }

        return $telefonoLimpio;
    }



    protected function crearConsultaSiNoExiste(bool $force = false): void
    {
        if (!$force && $this->estado !== self::ESTADO_CONFIRMADA) {
            \Log::info('crearConsultaSiNoExiste llamada sin estado confirmada', ['cita_id' => $this->id, 'estado' => $this->estado]);
            return;
        }

        try {
            $existe = Consulta::withoutGlobalScopes()->where('cita_id', $this->id)->exists();
            if ($existe) {
                \Log::info('consulta ya existe para cita, actualizando tiempos', ['cita_id' => $this->id]);

                // Update the existing consultation to match the appointment timing exactly
                $consulta = Consulta::withoutGlobalScopes()->where('cita_id', $this->id)->first();

                $consulta->update([
                    'fecha_consulta' => $this->fecha_inicio,
                    'updated_at' => now()
                ]);

                \Log::info("Consulta #{$consulta->id} actualizada para coincidir con la cita #{$this->id}", [
                    'fecha_consulta' => $this->fecha_inicio,
                    'duracion_minutos' => $this->duracion_minutos
                ]);

                return;
            }

            // Solo crear consulta cuando exista confirmación activa confirmada, excepto en force
            if (!$force && !\App\Models\CitaConfirmacion::where('cita_id', $this->id)
                ->where('estado', \App\Models\CitaConfirmacion::ESTADO_CONFIRMADO)
                ->exists()) {
                \Log::warning('No hay confirmacion confirmada, no se crea consulta', ['cita_id' => $this->id]);
                return;
            }

            Consulta::withoutGlobalScopes()->create([
                'cita_id' => $this->id,
                'paciente_id' => $this->paciente_id,
                'medico_id' => $this->medico_id,
                'especialidad_id' => $this->especialidad_id,
                'fecha_consulta' => $this->fecha_inicio, // Align consultation start time with appointment
                'motivo_consulta' => $this->motivo,
                'preconsulta' => true,
                'estado' => Consulta::ESTADO_SALA_ESPERA,
                'estado_changed_at' => now(),
                'empresa_id' => $this->empresa_id,
                'sucursal_id' => $this->sucursal_id,
                'created_by' => auth()->id(),
            ]);

            \Log::info("Consulta sala_espera creada para cita #{$this->id} con la misma duración", [
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
                'duracion_minutos' => $this->duracion_minutos
            ]);
        } catch (\Throwable $e) {
            \Log::error("Error creando consulta para cita #{$this->id}: " . $e->getMessage(), [
                'exception' => get_class($e),
                'line' => $e->getLine()
            ]);
        }
    }

    public function programarRecordatorios(): void
    {
        // Limpiar recordatorios existentes
        $this->recordatorios()->delete();

        // Solo programar para citas activas futuras
        if (!$this->activas()->exists() || $this->fecha_inicio <= now()) {
            return;
        }

        // Recordatorio 48 horas antes
        if ($this->fecha_inicio->subHours(48) > now()) {
            $this->recordatorios()->create([
                'tipo' => '48h',
                'fecha_envio_programado' => $this->fecha_inicio->copy()->subHours(48),
                'canal' => 'whatsapp',
                'mensaje' => $this->generarMensajeRecordatorio('48h')
            ]);
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
            case '48h':
                $mensaje = "🩺 *Recordatorio de Cita Médica*\n\n" .
                           "Hola {$this->paciente->nombre_completo},\n\n" .
                           "Le recordamos que tiene una cita médica programada:\n\n" .
                           "📅 *Fecha:* {$fecha}\n" .
                           "👨‍⚕️ *Médico:* {$medico}\n" .
                           "🏥 *Especialidad:* {$especialidad}\n" .
                           "🏢 *Sucursal:* {$sucursal}\n\n";
                           
                // Incluir formulario de preconsulta si aún no ha sido completado
                if ($this->estado_preconsulta !== 'completado') {
                    // Generar token si no existe
                    if (!$this->token_preconsulta) {
                        $token = \Illuminate\Support\Str::random(32);
                        $this->update(['token_preconsulta' => $token]);
                    }
                    
                    $mensaje .= "📋 *FORMULARIO DE PRECONSULTA*\n\n" .
                               "Para agilizar su atención, le solicitamos completar el formulario de preconsulta:\n\n" .
                               "🔗 Complete su preconsulta aquí: " . route('preconsulta.formulario', ['token' => $this->token_preconsulta]) . "\n\n" .
                               "⏰ Recuerde completarlo antes de su cita.\n\n";
                }
                
                $mensaje .= "Por favor confirme su asistencia respondiendo *SI* o *NO*";
                return $mensaje;

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
        $nickname = $this->paciente->nickname ?? '';
        $nombreCompleto = $this->paciente->nombre_completo;
        $edad = $this->paciente->edad;
        $edadTexto = $edad !== null ? (int) $edad . ' años' : '';

        $nombrePaciente = $nombreCompleto;
        if (!empty($nickname)) {
            $nombrePaciente = "({$nickname}) {$nombreCompleto}";
        }

        $tieneConsulta = $this->consulta !== null;

        // Información de la consulta asociada si existe
        $consultaEstadoLabel = null;
        $tiempoGotasFormateado = null;
        $gotasCount = 0;
        $gotasOdTotal = 0;
        $gotasOiTotal = 0;
        $tiempoUltimaGota = null;
        if ($this->consulta) {
            $consultaEstadoLabel = Consulta::ESTADO_LABELS[$this->consulta->estado] ?? ucfirst($this->consulta->estado);
            // Agregar tiempo en gotas si aplica
            if (in_array($this->consulta->estado, ['en_gotas', 'dilatado'])) {
                $tiempoGotasFormateado = $this->consulta->tiempo_gotas_formateado;
            }
            // Datos de gotas aplicadas (como en lista-por-estado.blade.php)
            $gotasAplicadas = $this->consulta->gotasAplicadas;
            $gotasCount = $gotasAplicadas->count();
            if ($gotasCount > 0) {
                $gotasOdTotal = (int) $gotasAplicadas->sum('gotas_od');
                $gotasOiTotal = (int) $gotasAplicadas->sum('gotas_oi');
                $ultimaGota = $gotasAplicadas->last();
                if ($ultimaGota && $ultimaGota->created_at) {
                    $tiempoUltimaGota = $ultimaGota->created_at->diffForHumans(null, true, true);
                }
            }
        }

        // Convertir las fechas de UTC a la zona horaria del usuario para mostrarlas correctamente
        $timezone = session('timezone', config('app.timezone', 'America/Caracas'));
        $inicioLocal = $this->fecha_inicio->tz($timezone);
        $finLocal = $this->fecha_fin->tz($timezone);

        return [
            'id' => $this->id,
            'title' => $nombrePaciente,
            'start' => $inicioLocal->format('Y-m-d\TH:i:s'),
            'end' => $finLocal->format('Y-m-d\TH:i:s'),
            'allDay' => false,
            'extendedProps' => [
                'tipo_evento' => 'cita',
                'calendar' => $this->estado,
                'medico' => $this->medico->nombre_completo,
                'medico_full' => $this->medico->nombre_completo,
                'paciente' => $nombreCompleto,
                'nickname' => $nickname,
                'edad' => $edadTexto,
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
                'prioridad' => $this->prioridad ?? 'normal',
                'prioridad_label' => self::PRIORIDAD_LABELS[$this->prioridad ?? 'normal'] ?? 'Normal',
                'tiene_consulta' => $tieneConsulta,
                'consulta_id' => $this->consulta?->id,
                'consulta_estado_label' => $consultaEstadoLabel, // Etiqueta del estado de la consulta asociada
                'tiempo_gotas_formateado' => $tiempoGotasFormateado, // Tiempo en gotas si aplica
                'gotas_count' => $gotasCount,
                'gotas_od_total' => $gotasOdTotal,
                'gotas_oi_total' => $gotasOiTotal,
                'tiempo_ultima_gota' => $tiempoUltimaGota,
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
        static::creating(function ($cita) {
            // Forzar estado pendiente para todas las nuevas citas
            if ($cita->estado !== self::ESTADO_PENDIENTE) {
                \Log::warning("Cita creando con estado no-pendiente: {$cita->estado}. Forzando pendiente", ['cita' => $cita->toArray()]);
            }
            $cita->estado = self::ESTADO_PENDIENTE;
        });

        static::saving(function ($cita) {
            if (!$cita->exists && $cita->estado !== self::ESTADO_PENDIENTE) {
                \Log::warning("Cita guardando nueva con estado no-pendiente: {$cita->estado}. Forzando pendiente", ['cita' => $cita->toArray()]);
                $cita->estado = self::ESTADO_PENDIENTE;
            }
        });

        static::created(function ($cita) {
            \Log::info('Cita creada con estado: ' . $cita->estado, ['cita_id' => $cita->id]);
            \Log::info('Cita ' . $cita->id . ' creada - confirmación se enviará integrada en la notificación');

            // Citas de alta prioridad o emergencia: enviar cuestionario inmediatamente
            if (in_array($cita->prioridad, [self::PRIORIDAD_ALTA, self::PRIORIDAD_EMERGENCIA])) {
                \Log::info('Cita de alta prioridad/emergencia: enviando cuestionario inmediatamente', [
                    'cita_id'   => $cita->id,
                    'prioridad' => $cita->prioridad,
                ]);
                $cita->crearPreconsultaYEnviarWhatsApp();
            }
        });
    }
}
