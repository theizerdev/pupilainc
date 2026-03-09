<?php

namespace App\Livewire\Admin;

use App\Models\Cita;
use App\Models\Consulta;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Especialidad;
use App\Models\Subespecialidad;
use App\Models\MedicoHorario;
use App\Models\TipoConsulta;
use App\Services\CitaNotificationService;
use App\Services\CitaConfirmationBotonesService;
use App\Services\CitaReagendamientoService;
use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class Calendario extends Component
{
    use HasDynamicLayout;

    public $mostrarCitas = true;
    public $mostrarConsultas = true;
    public $filtroMedico = '';

    // Cita form properties
    public $citaId = null;
    public $paciente_id = '';
    public $especialidad_id = '';
    public $subespecialidad_id = '';
    public $medico_id = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $motivo = '';
    public $notas = '';
    public $estado = 'pendiente';
    public $tipo_consulta_id = '';

    public $filtroEstados = [];

    protected $listeners = [
        'refreshCalendario' => '$refresh',
        'refreshCalendar' => '$refresh',
    ];

    protected $messages = [
        'paciente_id.required' => 'Seleccione un paciente.',
        'especialidad_id.required' => 'Seleccione una especialidad.',
        'medico_id.required' => 'Seleccione un médico.',
        'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
        'fecha_fin.required' => 'La fecha de fin es obligatoria.',
        'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        'motivo.required' => 'El motivo de la cita es obligatorio.',
    ];

    public function mount()
    {
        $this->filtroEstados = Cita::ESTADOS;
    }

    protected function rules()
    {
        return [
            'paciente_id' => 'required|exists:pacientes,id',
            'especialidad_id' => 'required|exists:especialidades,id',
            'subespecialidad_id' => 'nullable|exists:subespecialidades,id',
            'medico_id' => 'required|exists:medicos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'motivo' => 'required|string|max:255',
            'notas' => 'nullable|string|max:1000',
            'estado' => 'required|in:' . implode(',', Cita::ESTADOS),
            'tipo_consulta_id' => 'nullable|exists:tipo_consultas,id',
        ];
    }

    public function getEventosProperty()
    {
        return $this->fetchEventos();
    }

    public function getEventosFresh()
    {
        return $this->fetchEventos();
    }

    protected function fetchEventos()
    {
        $eventos = [];

        if ($this->mostrarCitas) {
            $eventos = array_merge($eventos, $this->fetchCitas());
        }

        if ($this->mostrarConsultas) {
            $eventos = array_merge($eventos, $this->fetchConsultas());
        }

        return $eventos;
    }

    protected function fetchCitas()
    {
        $citas = Cita::with(['paciente', 'medico', 'tipoConsulta'])
            ->forUser()
            ->when($this->filtroMedico, fn($q) => $q->porMedico($this->filtroMedico))
            ->get();

        return $citas->map(function ($cita) {
            $event = $cita->toFullCalendarEvent();
            $event['extendedProps']['tipo_evento'] = 'cita';
            $event['id'] = 'cita_' . $cita->id;
            return $event;
        })->toArray();
    }

    protected function fetchConsultas()
    {
        $query = Consulta::with(['paciente', 'medico', 'especialidad']);

        if (auth()->user()->hasRole('Doctor')) {
            $medico = Medico::where('user_id', auth()->id())->first();
            if ($medico) {
                $query->where('medico_id', $medico->id);
            }
        }

        $consultas = $query
            ->when($this->filtroMedico, fn($q) => $q->porMedico($this->filtroMedico))
            ->get();

        return $consultas->map(function ($consulta) {
            return $this->mapConsultaToEvent($consulta);
        })->toArray();
    }

    protected function mapConsultaToEvent($consulta)
    {
        $nickname = $consulta->paciente->nickname ?? '';
        $nombreCompleto = $consulta->paciente->nombre_completo;
        $edad = $consulta->paciente->edad;
        $edadTexto = $edad !== null ? (int) $edad . ' años' : '';

        $title = $nombreCompleto;
        if (!empty($nickname)) {
            $title = "({$nickname}) {$title}";
        }

        if ($consulta->estado === Consulta::ESTADO_SALA_ESPERA && $consulta->tiempo_sala_espera !== null) {
            $title .= ' [' . $consulta->tiempo_espera_formateado . ']';
        }

        return [
            'id' => 'consulta_' . $consulta->id,
            'title' => $title,
            'start' => $consulta->fecha_consulta->toIso8601String(),
            'end' => $consulta->fecha_consulta->copy()->addMinutes(30)->toIso8601String(),
            'backgroundColor' => Consulta::ESTADO_COLORES[$consulta->estado] ?? '#78909C',
            'borderColor' => Consulta::ESTADO_COLORES[$consulta->estado] ?? '#78909C',
            'extendedProps' => [
                'tipo_evento' => 'consulta',
                'calendar' => $consulta->estado,
                'codigo' => $consulta->codigo,
                'paciente' => $nombreCompleto,
                'nickname' => $nickname,
                'edad' => $edadTexto,
                'medico' => $consulta->medico->nombre_completo ?? 'Sin médico',
                'medico_full' => $consulta->medico->nombre_completo ?? 'Sin médico',
                'medico_id' => $consulta->medico_id,
                'especialidad' => $consulta->especialidad->nombre ?? 'Sin especialidad',
                'estado' => $consulta->estado,
                'estado_label' => Consulta::ESTADO_LABELS[$consulta->estado] ?? ucfirst($consulta->estado),
                'motivo' => $consulta->motivo_consulta,
                'tiempo_espera_formateado' => $consulta->tiempo_espera_formateado,
            ],
        ];
    }

    public function fetchEventosRango($inicio, $fin)
    {
        try {
            $inicioCarbon = Carbon::parse($inicio);
            $finCarbon = Carbon::parse($fin);
        } catch (\Exception $e) {
            return [];
        }

        $eventos = [];

        if ($this->mostrarCitas) {
            $citas = Cita::with(['paciente', 'medico', 'tipoConsulta'])
                ->forUser()
                ->enRango($inicioCarbon, $finCarbon)
                ->when($this->filtroMedico, fn($q) => $q->porMedico($this->filtroMedico))
                ->get();

            $eventos = $citas->map(function ($cita) {
                $event = $cita->toFullCalendarEvent();
                $event['extendedProps']['tipo_evento'] = 'cita';
                $event['id'] = 'cita_' . $cita->id;
                return $event;
            })->toArray();
        }

        if ($this->mostrarConsultas) {
            $query = Consulta::with(['paciente', 'medico', 'especialidad'])
                ->whereBetween('fecha_consulta', [$inicioCarbon, $finCarbon])
                ->when($this->filtroMedico, fn($q) => $q->porMedico($this->filtroMedico));

            if (auth()->user()->hasRole('Doctor')) {
                $medico = Medico::where('user_id', auth()->id())->first();
                if ($medico) {
                    $query->where('medico_id', $medico->id);
                }
            }

            $consultasEvents = $query->get()->map(fn($c) => $this->mapConsultaToEvent($c))->toArray();
            $eventos = array_merge($eventos, $consultasEvents);
        }

        return $eventos;
    }

    public function toggleCitas()
    {
        $this->mostrarCitas = !$this->mostrarCitas;
        $this->dispatch('calendario-updated');
    }

    public function toggleConsultas()
    {
        $this->mostrarConsultas = !$this->mostrarConsultas;
        $this->dispatch('calendario-updated');
    }

    public function getStatsProperty()
    {
        $hoy = Carbon::today();

        return [
            'citas_hoy' => Cita::whereDate('fecha_inicio', $hoy)->count(),
            'consultas_hoy' => Consulta::whereDate('fecha_consulta', $hoy)->count(),
            'citas_pendientes' => Cita::porEstado(Cita::ESTADO_PENDIENTE)->count(),
            'consultas_en_espera' => Consulta::porEstado(Consulta::ESTADO_SALA_ESPERA)->count(),
        ];
    }

    public function getMedicosProperty()
    {
        return Medico::activos()->orderBy('nombres')->get();
    }

    // ===== COMPUTED PROPERTIES =====

    public function getPacientesProperty()
    {
        return Paciente::activos()
            ->forUser()
            ->orderBy('nombres')
            ->limit(50)
            ->get();
    }

    public function getTiposConsultaProperty()
    {
        return TipoConsulta::activos()
            ->orderBy('nombre')
            ->get();
    }

    // ===== DATA FETCHING FOR CASCADING SELECTS =====

    public function fetchEspecialidades()
    {
        $especialidades = Especialidad::where('status', true)
            ->forUser()
            ->whereHas('medicos', function ($q) {
                $q->where('medico_especialidad.status', true);
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'duracion_consulta']);

        return $especialidades->map(function ($e) {
            return [
                'id' => $e->id,
                'nombre' => $e->nombre,
                'duracion_consulta' => $e->duracion_consulta,
            ];
        })->toArray();
    }

    public function fetchSubespecialidades($especialidadId)
    {
        if (!$especialidadId) return [];

        $subs = Subespecialidad::where('especialidad_id', $especialidadId)
            ->where('status', true)
            ->forUser()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'duracion_consulta']);

        return $subs->map(function ($s) {
            return [
                'id' => $s->id,
                'nombre' => $s->nombre,
                'duracion_consulta' => $s->duracion_consulta,
            ];
        })->toArray();
    }

    public function fetchMedicos($especialidadId, $subespecialidadId = null)
    {
        if (!$especialidadId) return [];

        $query = Medico::activos()
            ->forUser()
            ->whereHas('especialidades', function ($q) use ($especialidadId) {
                $q->where('especialidad_id', $especialidadId)
                  ->where('medico_especialidad.status', true);
            });

        if ($subespecialidadId) {
            $query->whereHas('subespecialidades', function ($q) use ($subespecialidadId) {
                $q->where('subespecialidad_id', $subespecialidadId)
                  ->where('medico_subespecialidad.status', true);
            });
        }

        $medicos = $query->orderBy('nombres')->get();

        return $medicos->map(function ($m) {
            return [
                'id' => $m->id,
                'nombre' => $m->nombre_completo,
            ];
        })->toArray();
    }

    public function fetchHorariosDisponibles($medicoId, $fecha)
    {
        if (!$medicoId || !$fecha) return [];

        try {
            $fechaCarbon = Carbon::parse($fecha);
        } catch (\Exception $e) {
            return [];
        }

        $diaSemana = $fechaCarbon->dayOfWeekIso;

        $horarios = MedicoHorario::where('medico_id', $medicoId)
            ->activos()
            ->porDia($diaSemana)
            ->vigentes()
            ->get();

        if ($horarios->isEmpty()) {
            return ['disponible' => false, 'mensaje' => 'El médico no atiende este día.', 'slots' => []];
        }

        $citasDelDia = Cita::where('medico_id', $medicoId)
            ->whereDate('fecha_inicio', $fechaCarbon->toDateString())
            ->activas()
            ->when($this->citaId, function ($q) {
                $q->where('id', '!=', $this->citaId);
            })
            ->get(['fecha_inicio', 'fecha_fin']);

        $slots = [];

        foreach ($horarios as $horario) {
            $duracion = $horario->duracion_cita ?? 30;
            $inicio = Carbon::parse($fechaCarbon->toDateString() . ' ' . Carbon::parse($horario->hora_inicio)->format('H:i'));
            $finJornada = Carbon::parse($fechaCarbon->toDateString() . ' ' . Carbon::parse($horario->hora_fin)->format('H:i'));

            while ($inicio->copy()->addMinutes($duracion)->lte($finJornada)) {
                $finSlot = $inicio->copy()->addMinutes($duracion);

                $ocupado = false;
                foreach ($citasDelDia as $cita) {
                    if ($inicio->lt($cita->fecha_fin) && $finSlot->gt($cita->fecha_inicio)) {
                        $ocupado = true;
                        break;
                    }
                }

                $slots[] = [
                    'inicio' => $inicio->format('H:i'),
                    'fin' => $finSlot->format('H:i'),
                    'inicio_full' => $inicio->format('Y-m-d H:i'),
                    'fin_full' => $finSlot->format('Y-m-d H:i'),
                    'disponible' => !$ocupado,
                    'label' => $inicio->format('h:i A') . ' - ' . $finSlot->format('h:i A'),
                ];

                $inicio->addMinutes($duracion);
            }
        }

        $disponibles = array_filter($slots, fn($s) => $s['disponible']);
        $primerDisponible = !empty($disponibles) ? array_values($disponibles)[0] : null;

        return [
            'disponible' => !empty($disponibles),
            'mensaje' => empty($disponibles) ? 'No hay horarios disponibles para este día.' : '',
            'slots' => $slots,
            'primer_disponible' => $primerDisponible,
            'duracion_cita' => $horarios->first()->duracion_cita ?? 30,
        ];
    }

    // ===== CRUD =====

    public function saveCita($eventData)
    {
        // Rate limiting para guardar citas
        $rateLimitKey = 'calendario_save_cita_' . auth()->id();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Demasiadas operaciones. Por favor, espere un momento.'
            ]);
            return;
        }
        RateLimiter::hit($rateLimitKey, 60); // 10 intentos por minuto

        //dd($eventData);

        // Validación de permisos para edición
        if ($this->citaId) {
            $citaExistente = Cita::find($this->citaId);
            if (!$citaExistente || !$this->authorizeCitaAction($citaExistente, 'update')) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'No tienes permisos para editar esta cita.'
                ]);
                return;
            }
        }

        if (is_array($eventData)) {
            $this->paciente_id = $eventData['paciente_id'] ?? $this->paciente_id;
            $this->especialidad_id = $eventData['especialidad_id'] ?? $this->especialidad_id;
            $this->subespecialidad_id = $eventData['subespecialidad_id'] ?? $this->subespecialidad_id;
            $this->medico_id = $eventData['medico_id'] ?? $this->medico_id;
            $this->fecha_inicio = $eventData['start'] ?? $eventData['fecha_inicio'] ?? $this->fecha_inicio;
            $this->fecha_fin = $eventData['end'] ?? $eventData['fecha_fin'] ?? $this->fecha_fin;
            $this->motivo = $eventData['motivo'] ?? $this->motivo;
            $this->notas = $eventData['notas'] ?? $this->notas;
            $this->estado = $eventData['estado'] ?? $this->estado;
            $this->tipo_consulta_id = $eventData['tipo_consulta_id'] ?? $this->tipo_consulta_id;
        }

        $this->validate();

        $inicio = Carbon::parse($this->fecha_inicio);
        $fin = Carbon::parse($this->fecha_fin);

        $conflictos = Cita::sinConflicto($this->medico_id, $inicio, $fin, $this->citaId)->count();
        if ($conflictos > 0) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'El médico ya tiene una cita programada en ese horario.'
            ]);
            return;
        }

        if (!$this->validarHorarioMedico($this->medico_id, $inicio, $fin)) {
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => 'La cita está fuera del horario de atención del médico.'
            ]);
        }

        $data = [
            'paciente_id' => $this->paciente_id,
            'especialidad_id' => $this->especialidad_id ?: null,
            'subespecialidad_id' => $this->subespecialidad_id ?: null,
            'medico_id' => $this->medico_id,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'motivo' => $this->motivo,
            'notas' => $this->notas,
            'estado' => $this->estado,
            'tipo_consulta_id' => $this->tipo_consulta_id ?: null,
        ];

        if ($this->citaId) {
            $cita = Cita::findOrFail($this->citaId);
            $estadoAnterior = $cita->estado;
            $fechaAnterior = $cita->fecha_inicio->toDateTimeString();
            
            // Log de auditoría para actualización
            $oldData = $cita->toArray();
            $cita->update($data);
            $this->logCitaAction('update', $cita, $oldData);

            if ($estadoAnterior !== $this->estado) {
                $this->notificarCambioEstado($cita, $estadoAnterior);
            }

             if ($estadoAnterior !== $this->estado) {
                $cita->cambiarEstado($this->estado);
             }

            if ($fechaAnterior !== $cita->fecha_inicio->toDateTimeString()) {
                // Reprogramar recordatorios si cambió la fecha
                $cita->programarRecordatorios();
            }

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cita actualizada exitosamente.'
            ]);
        } else {
            $data['created_by'] = auth()->id();
            $cita = Cita::create($data);
            
            // Log de auditoría para creación
            $this->logCitaAction('create', $cita);

            // Notificar y capturar errores
            $notificacion = $this->notificarNuevaCita($cita);

            // Programar recordatorios automáticamente
            $cita->programarRecordatorios();

            // Mostrar mensaje apropiado según el resultado de la notificación
            if ($notificacion['success'] && empty($notificacion['errors'])) {
                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Cita creada exitosamente. Notificaciones enviadas.'
                ]);
            } elseif ($notificacion['success'] && !empty($notificacion['errors'])) {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'Cita creada. ' . $notificacion['message']
                ]);
            } else {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Cita creada pero no se pudieron enviar las notificaciones: ' . implode(', ', $notificacion['errors'])
                ]);
            }
        }

        $this->resetForm();
        $this->dispatch('cita-saved');
    }

    public function updateCitaFechas($id, $start, $end)
    {
        // Rate limiting para actualizar fechas
        $rateLimitKey = 'calendario_update_fechas_' . auth()->id() . '_' . $id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Demasiados intentos de reprogramación. Por favor, espere un momento.'
            ]);
            $this->dispatch('cita-saved');
            return;
        }
        RateLimiter::hit($rateLimitKey, 60); // 10 intentos por minuto

        $cita = Cita::findOrFail($id);
        
        // Validación de permisos para actualizar fechas
        if (!$this->authorizeCitaAction($cita, 'update')) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No tienes permisos para reprogramar esta cita.'
            ]);
            $this->dispatch('cita-saved');
            return;
        }
        
        $inicio = Carbon::parse($start);
        $fin = Carbon::parse($end);

        $conflictos = Cita::sinConflicto($cita->medico_id, $inicio, $fin, $id)->count();
        if ($conflictos > 0) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se puede mover: conflicto de horario.'
            ]);
            $this->dispatch('cita-saved');
            return;
        }

        // Log de auditoría para reprogramación
        $oldData = $cita->toArray();
        $cita->update([
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
        ]);
        $this->logCitaAction('reschedule', $cita, $oldData);

        $this->reprogramarRecordatorios($cita);

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Cita reprogramada exitosamente.'
        ]);
        $this->dispatch('cita-saved');
    }

    public function deleteCita($id)
    {
        // Rate limiting para eliminar citas
        $rateLimitKey = 'calendario_delete_cita_' . auth()->id() . '_' . $id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Demasiados intentos de eliminación. Por favor, espere un momento.'
            ]);
            return;
        }
        RateLimiter::hit($rateLimitKey, 300); // 5 intentos en 5 minutos

        $cita = Cita::findOrFail($id);
        
        // Validación de permisos para eliminar
        if (!$this->authorizeCitaAction($cita, 'delete')) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No tienes permisos para eliminar esta cita.'
            ]);
            return;
        }
        
        // Log de auditoría antes de eliminar
        $this->logCitaAction('delete', $cita);
        
        $notificacion = $this->notificarCancelacion($cita);
        $cita->delete();
        $this->resetForm();
        
        // Mostrar mensaje apropiado según el resultado de la notificación
        if ($notificacion['success'] && empty($notificacion['errors'])) {
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cita eliminada exitosamente. Notificación de cancelación enviada.'
            ]);
        } elseif ($notificacion['success'] && !empty($notificacion['errors'])) {
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => 'Cita eliminada. ' . $notificacion['message']
            ]);
        } else {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Cita eliminada pero no se pudieron enviar las notificaciones: ' . implode(', ', $notificacion['errors'])
            ]);
        }
        
        $this->dispatch('cita-saved');
    }

    public function crearPacienteRapido($data): void
    {
        try {
            // Rate limiting para crear pacientes rápidos
            $rateLimitKey = 'calendario_crear_paciente_' . auth()->id();
            if (RateLimiter::tooManyAttempts($rateLimitKey, 15)) {
                $this->dispatch('paciente-creado', [
                    'success' => false, 
                    'message' => 'Demasiados intentos de crear pacientes. Por favor, espere un momento.',
                    'errors' => ['general' => 'Demasiados intentos. Por favor, espere.']
                ]);
                return;
            }
            RateLimiter::hit($rateLimitKey, 300); // 15 intentos en 5 minutos

            
    

            // Sanitización de datos de entrada
            $nombres = strip_tags(trim($data['nombres'] ?? ''));
            $apellidos = strip_tags(trim($data['apellidos'] ?? ''));

            $paciente = Paciente::create([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'documento_identidad' => isset($data['documento_identidad']) ? preg_replace('/[^0-9]/', '', $data['documento_identidad']) : null,
                'telefono' => isset($data['telefono']) ? preg_replace('/[^0-9]/', '', $data['telefono']) : null,
                'fecha_nacimiento' => isset($data['fecha_nacimiento']) && $data['fecha_nacimiento'] ? Carbon::parse($data['fecha_nacimiento']) : null,
                'empresa_id' =>  auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
                'status' => true,
            ]);

            $esMenorFlag = (bool) ($data['es_menor'] ?? false);
            $esMenorFecha = $paciente->es_menor;

            if ($esMenorFlag || $esMenorFecha) {
                $tutorData = $data['tutor'] ?? [];
                if (!empty($tutorData['nombres']) || !empty($tutorData['apellidos']) || !empty($tutorData['telefono'])) {
                    // Sanitización de datos del tutor
                    $tutorNombres = strip_tags(trim($tutorData['nombres'] ?? ''));
                    $tutorApellidos = strip_tags(trim($tutorData['apellidos'] ?? ''));
                    $tutorTelefono = isset($tutorData['telefono']) ? preg_replace('/[^0-9]/', '', $tutorData['telefono']) : null;
                    
                    // Validación de longitud para tutor
                    if (strlen($tutorNombres) <= 100 && strlen($tutorApellidos) <= 100) {
                        \App\Models\Tutor::create([
                            'paciente_id' => $paciente->id,
                            'nombres' => $tutorNombres,
                            'apellidos' => $tutorApellidos,
                            'telefono' => $tutorTelefono,
                            'parentesco' => 'Tutor',
                        ]);
                    }
                }
            }

            // Log de auditoría para creación de paciente
            activity()
                ->causedBy(auth()->user())
                ->performedOn($paciente)
                ->withProperties([
                    'nombres' => $nombres,
                    'apellidos' => $apellidos,
                    'documento_identidad' => $paciente->documento_identidad,
                    'telefono' => $paciente->telefono,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log("Paciente creado desde calendario: {$paciente->nombre_completo}");

            $this->dispatch('paciente-creado', [
                'success' => true,
                'message' => 'Paciente creado exitosamente.',
                'paciente' => [
                    'id' => $paciente->id,
                    'nombre' => $paciente->nombre_completo,
                    'documento_identidad' => $paciente->documento_identidad,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error creando paciente rápido', ['error' => $e->getMessage()]);
            $this->dispatch('paciente-creado', [
                'success' => false, 
                'message' => 'Error al crear el paciente: ' . $e->getMessage()
            ]);
        }
    }

    public function cambiarEstado($citaId, $nuevoEstado)
    {
        dd($nuevoEstado);
        $cita = Cita::findOrFail($citaId);
        $estadoAnterior = $cita->estado;
        $cita->cambiarEstado($nuevoEstado);
        $notificacion = $this->notificarCambioEstado($cita, $estadoAnterior);
        
        // Mostrar mensaje apropiado según el resultado de la notificación
        if ($notificacion['success'] && empty($notificacion['errors'])) {
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Estado actualizado a: ' . Cita::ESTADO_LABELS[$nuevoEstado] . '. Notificaciones enviadas.'
            ]);
        } elseif ($notificacion['success'] && !empty($notificacion['errors'])) {
            $this->dispatch('show-toast', [
                'type' => 'warning',
                'message' => 'Estado actualizado. ' . $notificacion['message']
            ]);
        } else {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Estado actualizado pero no se pudieron enviar las notificaciones: ' . implode(', ', $notificacion['errors'])
            ]);
        }
        
        $this->dispatch('cita-saved');
    }

    public function enviarRecordatorio($citaId)
    {
        $cita = Cita::findOrFail($citaId);
        try {
            $service = CitaNotificationService::forCompany($cita->empresa_id);
            $notificacion = $service->enviarRecordatorio($cita);
            
            // Mostrar mensaje apropiado según el resultado de la notificación
            if ($notificacion['success'] && empty($notificacion['errors'])) {
                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Recordatorio enviado por WhatsApp.'
                ]);
            } elseif ($notificacion['success'] && !empty($notificacion['errors'])) {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => $notificacion['message']
                ]);
            } else {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'No se pudo enviar el recordatorio: ' . implode(', ', $notificacion['errors'])
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error enviando recordatorio', ['error' => $e->getMessage()]);
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al enviar el recordatorio.'
            ]);
        }
    }

    public function sugerirReagendamiento($citaId)
    {
        try {
            $cita = Cita::with(['paciente', 'medico', 'especialidad'])->findOrFail($citaId);
            $service = CitaReagendamientoService::forCompany($cita->empresa_id);
            return $service->buscarHorariosDisponibles($cita, 7);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function reagendarManualmenteDesdeCalendario($citaId, $fechaHora)
    {
        try {
            $cita = Cita::with(['paciente', 'medico', 'especialidad'])->findOrFail($citaId);
            $service = CitaReagendamientoService::forCompany($cita->empresa_id);
            $nuevaCita = $service->crearNuevaCita($cita, [
                'fecha_hora' => $fechaHora,
                'fecha_formateada' => Carbon::parse($fechaHora)->format('d/m/Y H:i'),
                'duracion' => $cita->fecha_inicio->diffInMinutes($cita->fecha_fin),
            ]);
            $nuevaCita->programarRecordatorios();
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cita re-agendada para ' . Carbon::parse($fechaHora)->format('d/m/Y H:i')
            ]);
            $this->dispatch('cita-saved');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al re-agendar: ' . $e->getMessage()
            ]);
        }
    }

    public function reagendarAutomaticamenteDesdeCalendario($citaId)
    {
        try {
            $cita = Cita::findOrFail($citaId);
            $service = CitaReagendamientoService::forCompany($cita->empresa_id);
            $nuevaCita = $service->reagendarAutomaticamente($cita);
            if ($nuevaCita) {
                $nuevaCita->programarRecordatorios();
                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Cita re-agendada automáticamente para ' . $nuevaCita->fecha_inicio->format('d/m/Y H:i')
                ]);
            } else {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'No se encontraron horarios disponibles para re-agendar automáticamente.'
                ]);
            }
            $this->dispatch('cita-saved');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error en re-agendamiento automático: ' . $e->getMessage()
            ]);
        }
    }

    public function resetForm()
    {
        $this->reset(['citaId', 'paciente_id', 'especialidad_id', 'subespecialidad_id', 'medico_id', 'fecha_inicio', 'fecha_fin', 'motivo', 'notas', 'tipo_consulta_id']);
        $this->estado = 'pendiente';
        $this->resetValidation();
    }

    // ===== PROTECTED HELPERS =====

    protected function validarHorarioMedico($medicoId, Carbon $inicio, Carbon $fin): bool
    {
        $diaSemana = $inicio->dayOfWeekIso;

        $horarios = MedicoHorario::where('medico_id', $medicoId)
            ->activos()
            ->porDia($diaSemana)
            ->vigentes()
            ->get();

        if ($horarios->isEmpty()) {
            return false;
        }

        foreach ($horarios as $horario) {
            $horaInicio = Carbon::parse($horario->hora_inicio)->format('H:i');
            $horaFin = Carbon::parse($horario->hora_fin)->format('H:i');
            $citaInicio = $inicio->format('H:i');
            $citaFin = $fin->format('H:i');

            if ($citaInicio >= $horaInicio && $citaFin <= $horaFin) {
                return true;
            }
        }

        return false;
    }

    protected function notificarNuevaCita(Cita $cita): array
    {
        try {
            $service = CitaNotificationService::forCompany($cita->empresa_id);
            return $service->notificarNuevaCita($cita);
        } catch (\Exception $e) {
            Log::error('Error notificando nueva cita', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'message' => 'Error al notificar nueva cita: ' . $e->getMessage()
            ];
        }
    }

    protected function notificarCambioEstado(Cita $cita, string $estadoAnterior): array
    {
        try {
            $service = CitaNotificationService::forCompany($cita->empresa_id);
            if ($cita->estado === Cita::ESTADO_CANCELADA) {
                $ok = $service->notificarCancelacion($cita);
                return [
                    'success' => (bool) $ok,
                    'errors' => $ok ? [] : ['No se pudo notificar la cancelación'],
                    'message' => $ok ? 'Notificación de cancelación enviada' : 'Fallo al notificar cancelación'
                ];
            } else {
                $ok = $service->notificarCambioEstado($cita, $estadoAnterior);
                return [
                    'success' => (bool) $ok,
                    'errors' => $ok ? [] : ['No se pudo notificar el cambio de estado'],
                    'message' => $ok ? 'Notificación de cambio de estado enviada' : 'Fallo al notificar cambio de estado'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error notificando cambio de estado', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'message' => 'Error al notificar cambio de estado: ' . $e->getMessage()
            ];
        }
    }

    protected function authorizeCitaAction(Cita $cita, string $action): bool
    {
        return match($action) {
            'update' => auth()->user()->can('update-cita', $cita),
            'delete' => auth()->user()->can('delete-cita', $cita),
            'change-status' => auth()->user()->can('change-status-cita', $cita),
            default => false
        };
    }

    protected function sanitizeInput(array $data): array
    {
        return [
            'paciente_id' => (int) ($data['paciente_id'] ?? 0),
            'especialidad_id' => (int) ($data['especialidad_id'] ?? 0),
            'subespecialidad_id' => (int) ($data['subespecialidad_id'] ?? 0),
            'medico_id' => (int) ($data['medico_id'] ?? 0),
            'fecha_inicio' => Carbon::parse($data['fecha_inicio'] ?? now()),
            'fecha_fin' => Carbon::parse($data['fecha_fin'] ?? now()->addHour()),
            'motivo' => strip_tags(trim($data['motivo'] ?? '')),
            'notas' => strip_tags(trim($data['notas'] ?? '')),
            'estado' => in_array($data['estado'] ?? '', Cita::ESTADOS) ? $data['estado'] : 'pendiente',
            'tipo_consulta_id' => (int) ($data['tipo_consulta_id'] ?? 0),
        ];
    }

    protected function logCitaAction(string $action, Cita $cita, array $oldData = []): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($cita)
            ->withProperties([
                'action' => $action,
                'old_data' => $oldData,
                'new_data' => $cita->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log("Cita {$action}: {$cita->paciente->nombre_completo}");
    }

    protected function reprogramarRecordatorios(Cita $cita): void
    {
        try {
            $service = CitaNotificationService::forCompany($cita->empresa_id);
            $service->reprogramarRecordatorios($cita);
        } catch (\Exception $e) {
            Log::error('Error reprogramando recordatorios', ['error' => $e->getMessage()]);
        }
    }

    protected function notificarCancelacion(Cita $cita): array
    {
        try {
            $service = CitaNotificationService::forCompany($cita->empresa_id);
            return $service->notificarCancelacion($cita);
        } catch (\Exception $e) {
            Log::error('Error notificando cancelación', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'message' => 'Error al notificar cancelación: ' . $e->getMessage()
            ];
        }
    }

    protected function getPageTitle(): string
    {
        return 'Calendario General';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.calendario' => 'Calendario General',
        ];
    }

    public function render()
    {
        return view('livewire.admin.calendario', [
            'eventos' => $this->eventos,
            'stats' => $this->stats,
            'medicos' => $this->medicos,
            'pacientes' => $this->pacientes,
            'estados' => Cita::ESTADOS,
            'estadoLabels' => Cita::ESTADO_LABELS,
            'estadoColores' => Cita::ESTADO_COLORES,
            'tiposConsulta' => $this->tiposConsulta,
            'citaEstados' => Cita::ESTADOS,
            'citaEstadoLabels' => Cita::ESTADO_LABELS,
            'citaEstadoColores' => Cita::ESTADO_COLORES,
            'consultaEstados' => array_keys(Consulta::ESTADO_LABELS),
            'consultaEstadoLabels' => Consulta::ESTADO_LABELS,
            'consultaEstadoColores' => Consulta::ESTADO_COLORES,
        ])->layout($this->getLayout());
    }
}
