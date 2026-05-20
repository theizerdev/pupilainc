<?php

namespace App\Livewire\Admin;

use App\Models\Cita;
use App\Models\Consulta;
use App\Models\Preconsulta;
use App\Models\Medico;
use App\Models\Mascota;
use App\Models\Especie;
use App\Models\Raza;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\Subespecialidad;
use App\Models\MedicoHorario;
use App\Models\TipoConsulta;
use App\Services\CitaNotificationService;
use App\Services\CitaConfirmationBotonesService;
use App\Services\CitaReagendamientoService;
use App\Services\CitaConsultaSyncService;
use App\Services\CitaPrioridadService;
use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class Calendario extends Component
{
    use HasDynamicLayout;

    public $mostrarCitas = true;
    public $filtroMedico = '';

    // Cita form properties
    public $citaId = null;
    public $mascota_id = '';
    public $especie_id = '';
    public $raza_id = '';
    public $especialidad_id = '';
    public $subespecialidad_id = '';
    public $medico_id = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $motivo = '';
    public $notas = '';
    public $estado = 'programada';
    public $tipo_consulta_id = '';
    public $tipo_atencion = 1; // ID de 'Consulta General' en tipos_atencion
    public $urgencia = 'normal'; // normal, urgente, emergencia

    public $filtroEstados = [];

    // Datos para selects
    public $mascotas = [];
    public $especies = [];
    public $razas = [];
    public $tiposAtencion = [];

    protected $listeners = [
        'refreshCalendario' => '$refresh',
        'refreshCalendar' => '$refresh',
        'mascotaSeleccionada' => 'actualizarMascotaId',
        'tipoAtencionSeleccionado' => 'actualizarTipoAtencion',
    ];

    /**
     * Listener para actualizar mascota_id desde JavaScript
     * (El select está dentro de wire:ignore, así que necesitamos esto)
     */
    public function actualizarMascotaId($mascotaId)
    {
        $this->mascota_id = $mascotaId;
        Log::info('Mascota ID actualizada desde JS:', ['mascota_id' => $mascotaId]);
    }

    /**
     * Listener para actualizar tipo_atencion desde JavaScript
     * (El select está dentro de wire:ignore, así que necesitamos esto)
     */
    public function actualizarTipoAtencion($tipoId)
    {
        $this->tipo_atencion = $tipoId;
        Log::info('Tipo de atención actualizado desde JS:', ['tipo_atencion' => $tipoId]);
    }

    protected $messages = [
        'mascota_id.required' => 'Seleccione una mascota.',
        'especialidad_id.required' => 'Seleccione una especialidad.',
        'medico_id.required' => 'Seleccione un veterinario.',
        'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
        'fecha_fin.required' => 'La fecha de fin es obligatoria.',
        'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        'motivo.required' => 'El motivo de la cita es obligatorio.',
    ];

    public function mount()
    {
        $this->filtroEstados = Cita::ESTADOS;
        $timezone = $this->getEmpresaTimezone();

        // Cargar especies para el select
        $this->especies = Especie::withoutGlobalScopes()
            ->activas()
            ->ordenadas()
            ->get();

        // Cargar tipos de atención veterinaria
        $this->tiposAtencion = \App\Models\TipoAtencion::activos()
            ->ordenados()
            ->get();
    }

    /**
     * Cargar razas cuando se selecciona una especie
     */
    public function updatedEspecieId()
    {
        $this->raza_id = '';
        $this->mascota_id = '';

        if ($this->especie_id) {
            $this->razas = Raza::withoutGlobalScopes()
                ->where('especie_id', $this->especie_id)
                ->activas()
                ->ordenadas()
                ->get();

            $this->mascotas = Mascota::forUser()
                ->activos()
                ->where('especie_id', $this->especie_id)
                ->orderBy('nombre')
                ->get();
        } else {
            $this->razas = [];
            $this->mascotas = Mascota::forUser()->activos()->orderBy('nombre')->get();
        }
    }

    /**
     * Endpoint AJAX para obtener razas por especie
     */
    #[\Livewire\Attributes\On('getRazasByEspecie')]
    public function getRazasByEspecie($especieId)
    {
        $razas = Raza::withoutGlobalScopes()
            ->where('especie_id', $especieId)
            ->activas()
            ->ordenadas()
            ->get(['id', 'nombre']);

        return $razas;
    }

    /**
     * Endpoint AJAX para obtener mascotas por especie/raza
     */
    #[\Livewire\Attributes\On('getMascotasByFilters')]
    public function getMascotasByFilters($especieId = null, $razaId = null)
    {
        $query = Mascota::forUser()->activos();

        if ($razaId) {
            $query->where('raza_id', $razaId);
        } elseif ($especieId) {
            $query->where('especie_id', $especieId);
        }

        $mascotas = $query->with(['especie', 'raza'])
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'especie_id', 'raza_id']);

        return $mascotas;
    }

    /**
     * Cargar mascotas cuando se selecciona una raza
     */
    public function updatedRazaId()
    {
        $this->mascota_id = '';

        if ($this->raza_id) {
            $this->mascotas = Mascota::forUser()
                ->activos()
                ->where('raza_id', $this->raza_id)
                ->orderBy('nombre')
                ->get();
        } elseif ($this->especie_id) {
            $this->mascotas = Mascota::forUser()
                ->activos()
                ->where('especie_id', $this->especie_id)
                ->orderBy('nombre')
                ->get();
        } else {
            $this->mascotas = Mascota::forUser()->activos()->orderBy('nombre')->get();
        }
    }

    // Métodos de formateo para el formulario de paciente rápido
    public function formatPhone($value)
    {
        // Eliminar todo excepto números y el signo +
        return preg_replace('/[^0-9+]/', '', $value);
    }

    public function formatDni($value)
    {
        // Eliminar todo excepto números y letras (para documento de identidad)
        return strtoupper(preg_replace('/[^0-9A-Z]/i', '', $value));
    }

    public function formatText($value)
    {
        // Eliminar etiquetas HTML y trim
        return strip_tags(trim($value));
    }

    protected function rules()
    {
        return [
            'mascota_id' => 'required|exists:mascotas,id',
            'especie_id' => 'nullable|exists:especies,id',
            'raza_id' => 'nullable|exists:razas,id',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'subespecialidad_id' => 'nullable|exists:subespecialidades,id',
            'medico_id' => 'required|exists:medicos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'motivo' => 'required|string|max:255',
            'notas' => 'nullable|string|max:1000',
            'estado' => 'required|in:' . implode(',', Cita::ESTADOS),
            'tipo_consulta_id' => 'nullable|exists:tipo_consultas,id',
            'tipo_atencion' => 'nullable|numeric|exists:tipos_atencion,id',
            'urgencia' => 'nullable|in:normal,urgente,emergencia',
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

        return $eventos;
    }

    protected function fetchCitas()
    {
        $citas = Cita::with(['paciente', 'medico', 'tipoConsulta', 'tipoAtencion', 'consulta.gotasAplicadas'])
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
            $citas = Cita::with(['paciente', 'medico', 'tipoConsulta', 'tipoAtencion', 'consulta.gotasAplicadas'])
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

        return $eventos;
    }

    public function toggleCitas()
    {
        $this->mostrarCitas = !$this->mostrarCitas;
        $this->dispatch('calendario-updated');
    }

    public function getStatsProperty()
    {
        $hoy = Carbon::today();

        return [
            'citas_hoy' => Cita::whereDate('fecha_inicio', $hoy)->count(),
            'citas_pendientes' => Cita::porEstado(Cita::ESTADO_PENDIENTE)->count(),
        ];
    }

    public function getMedicosProperty()
    {
        // Issue 6: Solo médicos que tienen citas registradas en el calendario
        $query = Medico::activos()
            ->forUser()
            ->whereHas('citas', function($q) {
                $q->forUser();

                // Considerar solo citas activas (no canceladas ni no asistidas)
                $q->whereNotIn('estado', [
                    \App\Models\Cita::ESTADO_CANCELADA,
                    \App\Models\Cita::ESTADO_NO_ASISTIO
                ]);
            })
            ->orderBy('nombres');

        return $query->get();
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
            ->get(['id', 'nombre', 'duracion_consulta', 'color']);

        return $especialidades->map(function ($e) {
            $estadosFlujo = EspecialidadPlantilla::estadosFlujoParaEspecialidad($e->id);
            return [
                'id'               => $e->id,
                'nombre'           => $e->nombre,
                'duracion_consulta'=> $e->duracion_consulta,
                'color'            => $e->color,
                'estados_flujo'    => $estadosFlujo,
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

    public function fetchMedicos($especialidadId = null, $subespecialidadId = null)
    {
        $query = Medico::activos()->forUser();

        if ($especialidadId) {
            $query->whereHas('especialidades', function ($q) use ($especialidadId) {
                $q->where('especialidad_id', $especialidadId)
                  ->where('medico_especialidad.status', true);
            });
        }

        if ($subespecialidadId) {
            $query->whereHas('subespecialidades', function ($q) use ($subespecialidadId) {
                $q->where('subespecialidad_id', $subespecialidadId)
                  ->where('medico_subespecialidad.status', true);
            });
        }

        return $query->orderBy('nombres')
            ->with(['especialidades' => fn($q) => $q->where('medico_especialidad.status', true)])
            ->get()
            ->map(function ($m) {
                $especialidades = $m->especialidades->map(function ($e) {
                    return [
                        'id'            => $e->id,
                        'nombre'        => $e->nombre,
                        'color'         => $e->color,
                        'estados_flujo' => EspecialidadPlantilla::estadosFlujoParaEspecialidad($e->id),
                    ];
                })->values()->toArray();

                return [
                    'id'            => $m->id,
                    'nombre'        => $m->nombre_completo,
                    'especialidades'=> $especialidades,
                    // Si solo tiene una especialidad, la exponemos directamente para auto-selección
                    'especialidad_id' => count($especialidades) === 1 ? $especialidades[0]['id'] : null,
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
            'horario_laboral' => [
                'hora_inicio' => Carbon::parse($horarios->first()->hora_inicio)->format('H:i'),
                'hora_fin' => Carbon::parse($horarios->first()->hora_fin)->format('H:i'),
            ],
        ];
    }

    // ===== CRUD =====

    public function saveCita($eventData)
    {

        // Rate limiting para guardar citas
        $rateLimitKey = 'calendario_save_cita_' . auth()->id();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'title' => 'Demasiados intentos',
                'message' => 'Demasiadas operaciones. Por favor, espere un momento.',
                'icon' => 'warning'
            ]);
            return;
        }
        RateLimiter::hit($rateLimitKey, 60); // 10 intentos por minuto

        //dd($eventData);

        // Validación de permisos para edición
        if ($this->citaId) {
            $citaExistente = Cita::find($this->citaId);
            if (!$citaExistente || \Gate::denies('edit citas')) {
                $this->dispatch('show-alert', [
                    'type' => 'error',
                    'title' => 'Permiso denegado',
                    'message' => 'No tienes permisos para editar esta cita.',
                    'icon' => 'error'
                ]);
                return;
            }
        }

        if (is_array($eventData)) {
            $this->mascota_id = $eventData['mascota_id'] ?? $this->mascota_id;
            $this->especie_id = $eventData['especie_id'] ?? $this->especie_id;
            $this->raza_id = $eventData['raza_id'] ?? $this->raza_id;
            $this->especialidad_id = $eventData['especialidad_id'] ?? $this->especialidad_id;
            $this->subespecialidad_id = $eventData['subespecialidad_id'] ?? $this->subespecialidad_id;
            $this->medico_id = $eventData['medico_id'] ?? $this->medico_id;
            $this->fecha_inicio = $eventData['start'] ?? $eventData['fecha_inicio'] ?? $this->fecha_inicio;
            $this->fecha_fin = $eventData['end'] ?? $eventData['fecha_fin'] ?? $this->fecha_fin;
            $this->motivo = $eventData['motivo'] ?? $this->motivo;
            $this->notas = $eventData['notas'] ?? $this->notas;
            $this->tipo_consulta_id = $eventData['tipo_consulta_id'] ?? $this->tipo_consulta_id;
            $this->estado = $eventData['estado'] ?? $this->estado;
            // Asegurar que tipo_atencion sea un entero (puede venir como 'tipo_atencion' o 'tipo_atencion_id')
            $tipoAtencionValue = $eventData['tipo_atencion_id'] ?? $eventData['tipo_atencion'] ?? $this->tipo_atencion;
            $this->tipo_atencion = is_numeric($tipoAtencionValue) ? (int) $tipoAtencionValue : 1;
            $this->urgencia = $eventData['urgencia'] ?? $this->urgencia;

            // Auto-seleccionar especie y raza si se sele cciona una mascota
            if ($this->mascota_id && (!$this->especie_id || !$this->raza_id)) {
                $mascota = Mascota::find($this->mascota_id);
                if ($mascota) {
                    $this->especie_id = $mascota->especie_id;
                    $this->raza_id = $mascota->raza_id;
                }
            }

            if (!$this->especialidad_id && $this->medico_id) {
                $medico = Medico::with(['especialidades', 'subespecialidades'])->find($this->medico_id);
                if ($medico) {
                    $this->especialidad_id = $medico->especialidad_id ?: ($medico->especialidades->first()->id ?? null);
                    if (!$this->subespecialidad_id) {
                        $this->subespecialidad_id = $medico->subespecialidades->first()->id ?? null;
                    }
                }
            }
        }

        try {
            //$this->validate();

            // Para citas veterinarias, tipo_consulta_id debe ser null (es campo de medicina humana)
            if ($this->mascota_id) {
                $this->tipo_consulta_id = null;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errores = collect($e->validator->errors()->all())->implode(' | ');
            $this->dispatch('show-alert', [
                'type' => 'error',
                'title' => 'Error de validación',
                'message' => $errores,
                'icon' => 'error'
            ]);
            return;
        }

        $timezone = $this->getEmpresaTimezone();

        // Parsear las fechas ISO 8601 enviadas desde el frontend
        // Las fechas vienen en formato ISO con zona horaria del navegador (UTC)
        // Debemos convertirlas a la zona horaria de la empresa
        $inicio = Carbon::parse($this->fecha_inicio)->timezone($timezone);
        $fin = Carbon::parse($this->fecha_fin)->timezone($timezone);

        $prioridad = $eventData['prioridad'] ?? 'normal';
        $esPrioridadAltaOEmergencia = in_array($prioridad, ['alta', 'emergencia']);

        $ahora    = Carbon::now($timezone);
        $inicioTz = Carbon::parse($this->fecha_inicio)->timezone($timezone);

        Log::info('Validando cita', [
            'fecha_inicio_raw' => $this->fecha_inicio,
            'inicio_tz' => $inicioTz->toDateTimeString(),
            'inicio_tz_name' => $inicioTz->timezoneName,
            'ahora' => $ahora->toDateTimeString(),
            'ahora_tz_name' => $ahora->timezoneName,
            'timestamp_inicio' => $inicioTz->timestamp,
            'timestamp_ahora' => $ahora->timestamp
        ]);

        // Validar que la cita no esté en el pasado (Comparación por timestamp para evitar errores de TZ)
        if ($inicioTz->timestamp < $ahora->timestamp) {
            $this->dispatch('show-alert', [
                'type'    => 'warning',
                'title'   => 'Fecha no permitida',
                'message' => 'No se permite en el pasado. (Cita: ' . $inicioTz->format('H:i') . ', Ahora: ' . $ahora->format('H:i') . ')',
                'icon'    => 'warning'
            ]);
            return;
        }

        // Validación de horario laboral - solo para prioridad normal
        if (!$esPrioridadAltaOEmergencia && !$this->validarHorarioMedico($this->medico_id, $inicio, $fin)) {
            $this->dispatch('show-alert', [
                'type' => 'warning',
                'title' => 'Fuera de horario',
                'message' => 'La cita está fuera del horario de atención del médico.',
                'icon' => 'warning'
            ]);
            return;
        }

        $data = [
            'mascota_id' => $this->mascota_id,
            'especie_id' => $this->especie_id ?: null,
            'raza_id' => $this->raza_id ?: null,
            'especialidad_id' => $this->especialidad_id ?: null,
            'subespecialidad_id' => $this->subespecialidad_id ?: null,
            'medico_id' => $this->medico_id,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'motivo' => $this->motivo,
            'notas' => $this->notas,
            'tipo_consulta_id' => $this->tipo_consulta_id ?: null,
            'estado' => $this->estado ?: null,
            'tipo_atencion' => is_numeric($this->tipo_atencion) ? (int) $this->tipo_atencion : 1, // Asegurar entero
            'urgencia' => $this->urgencia ?: 'normal',
        ];

        if ($this->citaId) {
            $cita = Cita::findOrFail($this->citaId);
            $estadoAnterior = $cita->estado;
            $fechaAnterior = $cita->fecha_inicio->toDateTimeString();
            $oldData = $cita->toArray();
            $cita->update($data);
            $this->logCitaAction('update', $cita, $oldData);

            if ($estadoAnterior !== $this->estado) {
                //$this->notificarCambioEstado($cita, $estadoAnterior);
                $cita->cambiarEstado($this->estado);
            }

            if ($fechaAnterior !== $cita->fecha_inicio->toDateTimeString()) {
                $cita->programarRecordatorios();
            }

            $this->dispatch('show-alert', [
                'type' => 'success',
                'title' => 'Éxito',
                'message' => 'Cita actualizada exitosamente.',
                'icon' => 'success'
            ]);
        } else {
            $data['created_by'] = auth()->id();

            if ($esPrioridadAltaOEmergencia) {
                $prioridadService = new CitaPrioridadService(true);
                $data['prioridad'] = $prioridad;

                if (isset($this->fecha_inicio) && isset($this->fecha_fin)) {
                    $data['fecha_inicio'] = $inicio;
                    $data['fecha_fin'] = $fin;
                }

                $resultado = $prioridadService->crearCitaConPrioridad($data);

                if ($resultado['success']) {
                    $cita = $resultado['cita'];
                    $mensaje = "Cita de prioridad {$prioridad} creada exitosamente.";
                    if ($resultado['consulta']) {
                        $mensaje .= " Consulta creada en sala de espera.";
                    }
                    if ($resultado['whatsapp_enviado']) {
                        $mensaje .= " Cuestionario enviado por WhatsApp.";
                    }
                    if ($resultado['solapamiento']) {
                        $mensaje .= " (Se registró solapamiento con cita existente)";
                    }

                    $this->dispatch('show-alert', [
                        'type' => 'success',
                        'title' => 'Cita creada',
                        'message' => $mensaje,
                        'icon' => 'success'
                    ]);
                } else {
                    $this->dispatch('show-alert', [
                        'type' => 'error',
                        'title' => 'Error',
                        'message' => 'No se pudo crear la cita: ' . implode(', ', $resultado['errores']),
                        'icon' => 'error'
                    ]);
                    return;
                }
            } else {
                $data['prioridad'] = $prioridad;

                // Si la prioridad es alta o emergencia, el estado debe ser sala de espera
                if ($esPrioridadAltaOEmergencia) {
                    $data['estado'] = Cita::ESTADO_SALA_ESPERA;
                }

                $cita = new Cita();
                $cita->fill($data);
                $cita->save();

                $this->logCitaAction('create', $cita);

                // Si es una cita de prioridad alta o emergencia, crear automáticamente la consulta en sala de espera
                if ($esPrioridadAltaOEmergencia) {
                    $cita->crearConsultaSiNoExiste(true);
                }

                $notificacion = $this->notificarNuevaCita($cita);
                $cita->programarRecordatorios();

                if ($notificacion['success'] && empty($notificacion['errors'])) {
                    $this->dispatch('show-alert', [
                        'type' => 'success',
                        'title' => 'Cita creada',
                        'message' => 'Cita creada exitosamente. Notificaciones enviadas.',
                        'icon' => 'success'
                    ]);
                } elseif ($notificacion['success'] && !empty($notificacion['errors'])) {
                    $this->dispatch('show-alert', [
                        'type' => 'warning',
                        'title' => 'Cita creada',
                        'message' => 'Cita creada. ' . $notificacion['message'],
                        'icon' => 'warning'
                    ]);
                } else {
                    $this->dispatch('show-alert', [
                        'type' => 'error',
                        'title' => 'Error al notificar',
                        'message' => 'Cita creada pero no se pudieron enviar las notificaciones: ' . implode(', ', $notificacion['errors']),
                        'icon' => 'error'
                    ]);
                }
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
            $this->dispatch('show-alert', [
                'type' => 'error',
                'title' => 'Demasiados intentos',
                'message' => 'Demasiados intentos de reprogramación. Por favor, espere un momento.',
                'icon' => 'warning'
            ]);
            $this->dispatch('cita-saved');
            return;
        }
        RateLimiter::hit($rateLimitKey, 60);

        $cita = Cita::with('consulta')->findOrFail($id);

        if (!$cita || \Gate::denies('edit citas')) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'title' => 'Permiso denegado',
                'message' => 'No tienes permisos para reprogramar esta cita.',
                'icon' => 'error'
            ]);
            $this->dispatch('cita-saved');
            return;
        }

        // NO convertir timezone - usar la fecha exactamente como se envía desde el navegador
        // Esto evita los errores de conversión de hora al guardar citas
        $inicio = Carbon::parse($start);
        $fin = Carbon::parse($end);

        // Validación de fecha pasada (usando hora local del servidor)
        $ahora = Carbon::now();

        if ($inicio->lt($ahora)) {
            $this->dispatch('show-alert', [
                'type' => 'warning',
                'title' => 'Fecha no permitida',
                'message' => 'No se permite en el pasado. (Cita: ' . $inicio->format('H:i') . ', Ahora: ' . $ahora->format('H:i') . ')',
                'icon' => 'warning'
            ]);
            $this->dispatch('cita-saved');
            return;
        }

        // Usar el servicio de sincronización
        $syncService = new CitaConsultaSyncService(true);
        $resultado = $syncService->reagendarSincronizado($cita, $inicio, $fin, 'Reagendada desde calendario');

        if ($resultado['success']) {
            $tieneConsulta = $resultado['consulta'] !== null;
            $this->reprogramarRecordatorios($resultado['cita']);

            $mensaje = $tieneConsulta
                ? 'Cita y consulta sincronizadas reprogramadas exitosamente.'
                : 'Cita reprogramada exitosamente.';

            if ($resultado['solapamiento'] ?? false) {
                $mensaje .= ' (Se registró solapamiento con cita existente)';
            }

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => $mensaje
            ]);
        } else {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al reprogramar: ' . implode(', ', $resultado['errores'])
            ]);
        }

        $this->dispatch('calendario-updated');
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
            $documento = !empty($data['documento_identidad'])
                ? preg_replace('/[^0-9A-Za-z]/', '', $data['documento_identidad'])
                : null;
            $telefono = !empty($data['telefono'])
                ? preg_replace('/[^0-9+\-\s\(\)]/', '', $data['telefono'])
                : null;

            // Validación de campos requeridos
            if (strlen($nombres) < 2 || strlen($apellidos) < 2) {
                $this->dispatch('paciente-creado', [
                    'success' => false,
                    'message' => 'Nombres y apellidos son obligatorios.',
                    'errors'  => ['general' => 'Nombres y apellidos son obligatorios.']
                ]);
                return;
            }

            $empresaId  = auth()->user()->empresa_id;
            $sucursalId = auth()->user()->sucursal_id;

            // Detección de duplicados
            // 1. Por documento si viene informado
            if ($documento) {
                $existePorDoc = Paciente::where('empresa_id', $empresaId)
                    ->where('documento_identidad', $documento)
                    ->first();
                if ($existePorDoc) {
                    $this->dispatch('paciente-creado', [
                        'success' => false,
                        'message' => "Ya existe un paciente con ese documento: {$existePorDoc->nombre_completo}.",
                        'errors'  => ['documento_identidad' => 'Este documento ya está registrado.']
                    ]);
                    return;
                }
            }

            // 2. Por nombre + apellido (coincidencia exacta, case-insensitive)
            $existePorNombre = Paciente::where('empresa_id', $empresaId)
                ->whereRaw('LOWER(nombres) = ?', [strtolower($nombres)])
                ->whereRaw('LOWER(apellidos) = ?', [strtolower($apellidos)])
                ->first();
            if ($existePorNombre) {
                $this->dispatch('paciente-creado', [
                    'success' => false,
                    'message' => "Ya existe un paciente con ese nombre: {$existePorNombre->nombre_completo}. Si es la misma persona, búsquela en el listado.",
                    'errors'  => ['nombres' => 'Ya existe un paciente con este nombre y apellido.']
                ]);
                return;
            }

            // 3. Por teléfono si viene informado
            if ($telefono) {
                $telefonoLimpio = preg_replace('/\D/', '', $telefono);
                $existePorTel = Paciente::where('empresa_id', $empresaId)
                    ->whereRaw("REGEXP_REPLACE(telefono, '[^0-9]', '') = ?", [$telefonoLimpio])
                    ->first();
                if ($existePorTel) {
                    $this->dispatch('paciente-creado', [
                        'success' => false,
                        'message' => "Ya existe un paciente con ese teléfono: {$existePorTel->nombre_completo}.",
                        'errors'  => ['telefono' => 'Este teléfono ya está registrado.']
                    ]);
                    return;
                }
            }

            $paciente = Paciente::create([
                'nombres'             => $nombres,
                'apellidos'           => $apellidos,
                'documento_identidad' => $documento,
                'telefono'            => $telefono,
                'fecha_nacimiento'    => !empty($data['fecha_nacimiento']) ? Carbon::parse($data['fecha_nacimiento']) : null,
                'empresa_id'          => $empresaId,
                'sucursal_id'         => $sucursalId,
                'status'              => true,
            ]);

            $esMenorFlag = (bool) ($data['es_menor'] ?? false);
            $esMenorFecha = $paciente->es_menor;

            if ($esMenorFlag || $esMenorFecha) {
                $tutorData = $data['tutor'] ?? [];
                if (!empty($tutorData['nombres']) || !empty($tutorData['apellidos']) || !empty($tutorData['telefono'])) {
                    // Sanitización de datos del tutor
                    $tutorNombres = strip_tags(trim($tutorData['nombres'] ?? ''));
                    $tutorApellidos = strip_tags(trim($tutorData['apellidos'] ?? ''));
                    $tutorTelefono = isset($tutorData['telefono']) ? preg_replace('/[^0-9+\-\s\(\)]/', '', $tutorData['telefono']) : null;

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
        $cita = Cita::findOrFail($citaId);
        $estadoAnterior = $cita->estado;
        $preconsultaResult = null;
        $esVeterinaria = $cita->mascota_id && !$cita->paciente_id;

        // Para citas humanas - Preconsulta en sala de espera
        if ($nuevoEstado === Cita::ESTADO_SALA_ESPERA && $estadoAnterior !== Cita::ESTADO_SALA_ESPERA) {
            if (!$esVeterinaria && $cita->estado_preconsulta === 'pendiente') {
                $preconsultaResult = $cita->crearPreconsultaYEnviarWhatsApp();
            }
        }

        // Para citas humanas - Preconsulta al confirmar
        if ($nuevoEstado === Cita::ESTADO_CONFIRMADA && $estadoAnterior !== Cita::ESTADO_CONFIRMADA) {
            if (!$esVeterinaria && $cita->estado_preconsulta === 'pendiente') {
                $preconsultaResult = $cita->crearPreconsultaYEnviarWhatsApp();
            }
        }

        // Para citas veterinarias - Crear consulta al cambiar a estado de flujo veterinario
        if ($esVeterinaria) {
            $estadosVeterinariosQueCreanConsulta = [
                Cita::ESTADO_EN_TRIAGE,
                Cita::ESTADO_EN_TRATAMIENTO,
                Cita::ESTADO_EN_PROCEDIMIENTO,
                Cita::ESTADO_PRE_QUIRURGICO,
                Cita::ESTADO_EN_CIRUGIA,
                Cita::ESTADO_RECUPERACION,
                Cita::ESTADO_EDUCACION_PROPIETARIO,
            ];

            // Si cambia a un estado veterinario y no existe consulta, crearla
            if (in_array($nuevoEstado, $estadosVeterinariosQueCreanConsulta)) {
                $consulta = $cita->consulta;
                
                if (!$consulta) {
                    // Crear consulta con el nuevo estado
                    $consulta = new \App\Models\Consulta();
                    $consulta->codigo = \App\Models\Consulta::generarCodigo();
                    $consulta->cita_id = $cita->id;
                    $consulta->mascota_id = $cita->mascota_id;
                    $consulta->medico_id = $cita->medico_id;
                    $consulta->especialidad_id = $cita->especialidad_id;
                    $consulta->fecha_consulta = $cita->fecha_inicio;
                    $consulta->estado = $nuevoEstado;
                    $consulta->empresa_id = $cita->empresa_id;
                    $consulta->sucursal_id = $cita->sucursal_id;
                    $consulta->created_by = auth()->id();
                    $consulta->save();
                    
                    $cita->update(['consulta_id' => $consulta->id]);
                    
                    // Enviar WhatsApp cuando cambia a En Triaje/Urgencias (similar a sala_espera para humanos)
                    if ($nuevoEstado === Cita::ESTADO_EN_TRIAGE && $cita->estado_preconsulta === 'pendiente') {
                        $preconsultaResult = $cita->crearPreconsultaYEnviarWhatsApp();
                    } else {
                        $preconsultaResult = [
                            'preconsulta_creada' => true,
                            'whatsapp_enviado' => false,
                            'consulta_creada' => true
                        ];
                    }
                } else {
                    // Actualizar estado de consulta existente para sincronizar con la cita
                    $consulta->update(['estado' => $nuevoEstado]);
                    $preconsultaResult = [
                        'preconsulta_creada' => false,
                        'whatsapp_enviado' => false,
                        'consulta_actualizada' => true
                    ];
                }
            } elseif ($cita->consulta) {
                // Si ya tiene consulta pero cambia a otro estado (ej: programada -> confirmada), actualizar estado
                $cita->consulta->update(['estado' => $nuevoEstado]);
            }
        }

        $cita->cambiarEstado($nuevoEstado);
        $notificacion = $this->notificarCambioEstado($cita, $estadoAnterior);

        $mensajeExtra = '';
        if ($preconsultaResult) {
            if ($preconsultaResult['whatsapp_enviado']) {
                $mensajeExtra = ' Cuestionario preconsulta enviado por WhatsApp.';
            } elseif ($preconsultaResult['preconsulta_creada']) {
                $mensajeExtra = ' Cuestionario preconsulta creado.';
            }
            if (isset($preconsultaResult['consulta_creada']) && $preconsultaResult['consulta_creada']) {
                $estadoLabel = \App\Models\Consulta::ESTADO_LABELS[$nuevoEstado] ?? $nuevoEstado;
                $mensajeExtra .= ' Consulta creada en estado: ' . $estadoLabel;
            }
            if (isset($preconsultaResult['consulta_actualizada']) && $preconsultaResult['consulta_actualizada']) {
                $estadoLabel = \App\Models\Consulta::ESTADO_LABELS[$nuevoEstado] ?? $nuevoEstado;
                $mensajeExtra .= ' Estado de consulta actualizado a: ' . $estadoLabel;
            }
        }

        if ($notificacion['success'] && empty($notificacion['errors'])) {
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Estado actualizado a: ' . Cita::ESTADO_LABELS[$nuevoEstado] . '. Notificaciones enviadas.' . $mensajeExtra
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

    public function guardarNotaCita($citaId, $nota)
    {
        $cita = Cita::findOrFail($citaId);
        $notasActuales = $cita->notas ? $cita->notas . "\n" : '';
        $cita->update(['notas' => $notasActuales . $nota]);
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
        $this->reset(['citaId', 'mascota_id', 'especie_id', 'raza_id', 'especialidad_id', 'subespecialidad_id', 'medico_id', 'fecha_inicio', 'fecha_fin', 'motivo', 'notas', 'tipo_consulta_id']);
        $this->estado = 'programada';
        $this->tipo_atencion = 1; // Reset a Consulta General
        $this->urgencia = 'normal';
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
            'estado' => $this->citaId ? (in_array($data['estado'] ?? '', Cita::ESTADOS) ? $data['estado'] : 'programada') : 'programada',
            'tipo_consulta_id' => (int) ($data['tipo_consulta_id'] ?? 0),
        ];
    }

    protected function logCitaAction(string $action, Cita $cita, array $oldData = []): void
    {
        // Determinar el nombre del paciente/mascota para el log
        if ($cita->mascota_id && $cita->mascota) {
            // Cita veterinaria
            $nombrePaciente = $cita->mascota->nombre;
            $especie = $cita->mascota->especie?->nombre ?? '';
            if ($especie) {
                $nombrePaciente .= " ({$especie})";
            }
        } else {
            // Cita humana
            $nombrePaciente = $cita->paciente?->nombre_completo ?? 'Sin paciente';
        }

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
            ->log("Cita {$action}: {$nombrePaciente}");
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
            'mascotas' => $this->mascotas ?: Mascota::forUser()->activos()->orderBy('nombre')->get(),
            'especies' => $this->especies,
            'razas' => $this->razas,
            'tiposAtencion' => $this->tiposAtencion,
            'estados' => Cita::ESTADOS,
            'estadoLabels' => Cita::ESTADO_LABELS,
            'estadoColores' => Cita::ESTADO_COLORES,
            'citaEstados' => Cita::ESTADOS,
            'citaEstadoLabels' => Cita::ESTADO_LABELS,
            'citaEstadoColores' => Cita::ESTADO_COLORES,
            'timezone' => $this->getEmpresaTimezone(),
        ])->layout($this->getLayout());
    }
}
