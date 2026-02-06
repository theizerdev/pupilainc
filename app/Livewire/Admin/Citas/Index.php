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
    public $estado = 'pendiente';
    public $tipo_consulta_id = '';

    public $filtroEstados = [];
    public $filtroMedico = '';

    protected $listeners = ['refreshCalendar' => '$refresh'];

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
           
            ->orderBy('nombres')
            ->limit(50)
            ->get();
    }

    public function fetchEspecialidades()
    {
        $especialidades = Especialidad::where('status', true)
           
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

    public function getStatsProperty()
    {
        $base = Cita::forUser();
        $hoy = Carbon::today();

        return [
            'total_hoy' => (clone $base)->whereDate('fecha_inicio', $hoy)->count(),
            'pendientes' => (clone $base)->porEstado('pendiente')->count(),
            'confirmadas' => (clone $base)->porEstado('confirmada')->count(),
            'completadas_hoy' => (clone $base)->porEstado('completada')->whereDate('fecha_inicio', $hoy)->count(),
        ];
    }

    // ===== CRUD =====

    public function saveCita($eventData)
    {
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
            $cita->update($data);

            if ($estadoAnterior !== $this->estado) {
                $this->notificarCambioEstado($cita, $estadoAnterior);
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

            $this->notificarNuevaCita($cita);

            // Programar recordatorios automáticamente
            $cita->programarRecordatorios();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Cita creada exitosamente.'
            ]);
        }

        $this->resetForm();
        $this->dispatch('cita-saved');
    }

    public function updateCitaFechas($id, $start, $end)
    {
        $cita = Cita::findOrFail($id);
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
        $this->notificarCancelacion($cita);
        $cita->delete();
        $this->resetForm();
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Cita eliminada exitosamente.'
        ]);
        $this->dispatch('cita-saved');
    }

    public function cambiarEstado($citaId, $nuevoEstado)
    {
        $cita = Cita::findOrFail($citaId);
        $estadoAnterior = $cita->estado;
        $cita->cambiarEstado($nuevoEstado);
        $this->notificarCambioEstado($cita, $estadoAnterior);
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Estado actualizado a: ' . Cita::ESTADO_LABELS[$nuevoEstado]
        ]);
        $this->dispatch('cita-saved');
    }

    public function enviarRecordatorio($citaId)
    {
        $cita = Cita::findOrFail($citaId);
        try {
            $service = CitaNotificationService::forCompany($cita->empresa_id);
            $sent = $service->enviarRecordatorio($cita);
            $this->dispatch('show-toast', [
                'type' => $sent ? 'success' : 'warning',
                'message' => $sent
                    ? 'Recordatorio enviado por WhatsApp.'
                    : 'No se pudo enviar el recordatorio. Verifique la conexión de WhatsApp.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando recordatorio', ['error' => $e->getMessage()]);
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al enviar el recordatorio.'
            ]);
        }
    }

    public function resetForm()
    {
        $this->reset(['citaId', 'paciente_id', 'especialidad_id', 'subespecialidad_id', 'medico_id', 'fecha_inicio', 'fecha_fin', 'motivo', 'notas', 'tipo_consulta_id']);
        $this->estado = 'pendiente';
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

    protected function notificarNuevaCita(Cita $cita): void
    {
        try {
            $service = CitaNotificationService::forCompany($cita->empresa_id);
            $service->notificarNuevaCita($cita);
        } catch (\Exception $e) {
            Log::error('Error notificando nueva cita', ['error' => $e->getMessage()]);
        }
    }

    protected function notificarCambioEstado(Cita $cita, string $estadoAnterior): void
    {
        try {
            $service = CitaNotificationService::forCompany($cita->empresa_id);
            if ($cita->estado === Cita::ESTADO_CANCELADA) {
                $service->notificarCancelacion($cita);
            } else {
                $service->notificarCambioEstado($cita, $estadoAnterior);
            }
        } catch (\Exception $e) {
            Log::error('Error notificando cambio de estado', ['error' => $e->getMessage()]);
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

    protected function notificarCancelacion(Cita $cita): void
    {
        try {
            $service = CitaNotificationService::forCompany($cita->empresa_id);
            $service->notificarCancelacion($cita);
        } catch (\Exception $e) {
            Log::error('Error notificando cancelación', ['error' => $e->getMessage()]);
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