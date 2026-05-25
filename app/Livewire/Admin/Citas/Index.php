<?php

namespace App\Livewire\Admin\Citas;

use App\Models\Cita;
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

class Index extends Component
{
    use HasDynamicLayout;

    public $citaId = null;
    public $paciente_id = '';
    public $especialidad_id = '';
    public $subespecialidad_id = '';
    public $medico_id = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $motivo = '';
    public $notas = '';
    public $estado = 'programada';
    public $tipo_consulta_id = '';
    public $prioridad = 'normal';

    public $filtroEstados = [];
    public $filtroMedico = '';

    protected $listeners = ['refreshCalendar' => '$refresh'];

    public function mount()
    {
        $this->filtroEstados = Cita::ESTADOS;
        return redirect()->to('admin/calendario');
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
            'prioridad' => 'required|in:' . implode(',', Cita::PRIORIDADES),
        ];
    }

    protected $messages = [
        'paciente_id.required' => 'Seleccione un paciente.',
        'especialidad_id.required' => 'Seleccione una especialidad.',
        'medico_id.required' => 'Seleccione un médico.',
        'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
        'fecha_fin.required' => 'La fecha de fin es obligatoria.',
        'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        'motivo.required' => 'El motivo de la cita es obligatorio.',
    ];

    // ===== DATA FETCHING FOR CASCADING SELECTS (called from JS) =====

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
        $citas = Cita::with(['paciente', 'medico', 'tipoConsulta'])
            ->forUser()
            ->when($this->filtroMedico, function ($q) {
                $q->porMedico($this->filtroMedico);
            })
            ->get();

        // Obtener consultas en sala de espera para mostrarlas también en el calendario
        $consultasEnSalaEspera = [];
        if (class_exists('App\\Models\\Consulta')) {
            $consultasEnSalaEspera = \App\Models\Consulta::with(['paciente', 'medico', 'especialidad'])
                ->where('estado', \App\Models\Consulta::ESTADO_SALA_ESPERA)
                ->where('fecha_consulta', '>=', now()->subDay())
                ->get()
                ->map(function ($consulta) {
                    return [
                        'id' => 'consulta_' . $consulta->id,
                        'title' => $consulta->paciente->nombre_completo . ' (' . $consulta->tiempo_espera_formateado . ')',
                        'start' => $consulta->fecha_consulta->toIso8601String(),
                        'end' => $consulta->fecha_consulta->copy()->addMinutes(30)->toIso8601String(),
                        'backgroundColor' => '#FFA500', // Naranja para sala de espera
                        'borderColor' => '#FF8C00',
                        'extendedProps' => [
                            'tipo' => 'consulta',
                            'calendar' => 'sala_espera',
                            'paciente' => $consulta->paciente->nombre_completo,
                            'medico' => $consulta->medico->nombre_completo ?? 'Sin médico',
                            'medico_id' => $consulta->medico_id,
                            'especialidad' => $consulta->especialidad->nombre ?? 'Sin especialidad',
                            'estado' => $consulta->estado,
                            'estado_label' => 'Sala de Espera',
                            'tiempo_espera' => $consulta->tiempo_sala_espera,
                            'tiempo_espera_formateado' => $consulta->tiempo_espera_formateado,
                            'motivo' => $consulta->motivo_consulta,
                        ],
                    ];
                })->toArray();
        }

        $eventosCitas = $citas->map(function ($cita) {
            return $cita->toFullCalendarEvent();
        })->toArray();

        // Combinar citas y consultas en sala de espera
        return array_merge($eventosCitas, $consultasEnSalaEspera);
    }

    public function fetchEventosRango($inicio, $fin)
    {
        try {
            $inicioCarbon = Carbon::parse($inicio);
            $finCarbon = Carbon::parse($fin);
        } catch (\Exception $e) {
            return [];
        }

        $citas = Cita::with(['paciente', 'medico', 'tipoConsulta'])
            ->forUser()
            ->enRango($inicioCarbon, $finCarbon)
            ->when($this->filtroMedico, function ($q) {
                $q->porMedico($this->filtroMedico);
            })
            ->get();

        return $citas->map(function ($cita) {
            return $cita->toFullCalendarEvent();
        })->toArray();
    }

    public function getPacientesProperty()
    {
        return Paciente::activos()
            ->forUser()
            ->orderBy('nombres')
            ->limit(50)
            ->get();
    }

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
            $timezone = $this->getEmpresaTimezone();
            $fechaCarbon = Carbon::parse($fecha, $timezone);
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
            $inicio = Carbon::parse($fechaCarbon->toDateString() . ' ' . Carbon::parse($horario->hora_inicio)->format('H:i'), $timezone);
            $finJornada = Carbon::parse($fechaCarbon->toDateString() . ' ' . Carbon::parse($horario->hora_fin)->format('H:i'), $timezone);

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

    public function getStatsProperty()
    {
        $base = Cita::forUser();
        $hoy = Carbon::today();

        return [
            'total_hoy' => (clone $base)->whereDate('fecha_inicio', $hoy)->count(),
            'pendientes' => (clone $base)->porEstado('programada')->count(),
            'confirmadas' => (clone $base)->porEstado('confirmada')->count(),
            'finalizadas_hoy' => (clone $base)->porEstado('finalizada')->whereDate('fecha_inicio', $hoy)->count(),
        ];
    }

    // ===== CRUD =====

    public function saveCita($eventData)
    {
        //dd($eventData);

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
            $this->prioridad = $eventData['prioridad'] ?? $this->prioridad;
        }

        $this->validate();

        // Parsear las fechas manteniendo la zona horaria especificada
        $timezone = $this->getEmpresaTimezone();
        $inicio = Carbon::parse($this->fecha_inicio, $timezone)->tz('UTC');
        $fin = Carbon::parse($this->fecha_fin, $timezone)->tz('UTC');

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
            'estado' => $this->citaId ? $this->estado : 'programada',
            'tipo_consulta_id' => $this->tipo_consulta_id ?: null,
            'prioridad' => $this->prioridad,
        ];

        if ($this->citaId) {
            $cita = Cita::findOrFail($this->citaId);
            $estadoAnterior = $cita->estado;
            $fechaAnterior = $cita->fecha_inicio->toDateTimeString();
            $cita->update($data);

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
        $cita = Cita::findOrFail($id);
        
        // Parsear las fechas con la zona horaria de la empresa antes de convertirlas a UTC
        $timezone = $this->getEmpresaTimezone();
        $inicio = Carbon::parse($start, $timezone)->tz('UTC');
        $fin = Carbon::parse($end, $timezone)->tz('UTC');

        $conflictos = Cita::sinConflicto($cita->medico_id, $inicio, $fin, $id)->count();
        if ($conflictos > 0) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'No se puede mover: conflicto de horario.'
            ]);
            $this->dispatch('cita-saved');
            return;
        }

        $cita->update([
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
        ]);

        $this->reprogramarRecordatorios($cita);

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Cita reprogramada exitosamente.'
        ]);
        $this->dispatch('cita-saved');
    }

    public function deleteCita($id)
    {
        $cita = Cita::findOrFail($id);
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
            $nombres = trim($data['nombres'] ?? '');
            $apellidos = trim($data['apellidos'] ?? '');
            if ($nombres === '' || $apellidos === '') {
                $this->dispatch('paciente-creado', [
                    'success' => false,
                    'message' => 'Nombres y apellidos son obligatorios.'
                ]);
                return;
            }

            $paciente = Paciente::create([
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'documento_identidad' => $data['documento_identidad'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'empresa_id' => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
                'status' => true,
            ]);

            $esMenorFlag = (bool) ($data['es_menor'] ?? false);
            $esMenorFecha = $paciente->es_menor;

            if ($esMenorFlag || $esMenorFecha) {
                $tutorData = $data['tutor'] ?? [];
                if (!empty($tutorData['nombres']) || !empty($tutorData['apellidos']) || !empty($tutorData['telefono'])) {
                    \App\Models\Tutor::create([
                        'paciente_id' => $paciente->id,
                        'nombres' => $tutorData['nombres'] ?? '',
                        'apellidos' => $tutorData['apellidos'] ?? '',
                        'telefono' => $tutorData['telefono'] ?? '',
                        'parentesco' => 'Tutor',
                    ]);
                }
            }

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
        $this->reset(['citaId', 'paciente_id', 'especialidad_id', 'subespecialidad_id', 'medico_id', 'fecha_inicio', 'fecha_fin', 'motivo', 'notas', 'tipo_consulta_id', 'prioridad']);
        $this->estado = 'programada';
        $this->prioridad = 'normal';
        $this->resetValidation();
    }

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

    protected function getEmpresaTimezone(): string
    {
        try {
            $empresaId = auth()->user()?->empresa_id;
            if ($empresaId) {
                $paisId = \DB::table('empresas')->where('id', $empresaId)->value('pais_id');
                if ($paisId) {
                    $tz = \DB::table('pais')->where('id', $paisId)->value('zona_horaria');
                    if ($tz) return $tz;
                }
            }
        } catch (\Exception $e) {
            Log::warning('No se pudo obtener timezone de empresa', ['error' => $e->getMessage()]);
        }
        return config('app.timezone', 'UTC');
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
        return 'Citas Médicas';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.citas.index' => 'Citas Médicas',
        ];
    }

    public function getTiposConsultaProperty()
    {
        return TipoConsulta::activos()
            ->orderBy('nombre')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.citas.index', [
            'eventos' => $this->eventos,
            'pacientes' => $this->pacientes,
            'stats' => $this->stats,
            'estados' => Cita::ESTADOS,
            'estadoLabels' => Cita::ESTADO_LABELS,
            'estadoColores' => Cita::ESTADO_COLORES,
            'tiposConsulta' => $this->tiposConsulta,
        ])->layout($this->getLayout());
    }
}
