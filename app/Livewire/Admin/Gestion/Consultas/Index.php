<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use App\Models\Consulta;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\Medico;
use App\Models\Paciente;
use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Carbon\Carbon;

class Index extends Component
{
    use HasDynamicLayout;

    public $filtroEstados    = [];
    public $filtroMedico     = '';
    public $filtroPaciente   = '';
    public $filtroEspecialidad = '';

    // Estados y labels resueltos dinámicamente
    public $estadosActivos   = [];  // estados del flujo según especialidad seleccionada
    public $estadoLabels     = [];  // label de cada estado activo
    public $estadoColores    = [];  // color de cada estado activo

    public function mount(): void
    {
        $this->resolverEstados();
        $this->filtroEstados = $this->estadosActivos;
    }

    // ── Resolución dinámica de estados ───────────────────────────────────────

    public function updatedFiltroEspecialidad(): void
    {
        $this->resolverEstados();
        $this->filtroEstados = $this->estadosActivos;
        $this->dispatch('estadosActualizados', estados: $this->estadosActivos);
    }

    private function resolverEstados(): void
    {
        if ($this->filtroEspecialidad) {
            $plantilla = EspecialidadPlantilla::where('especialidad_id', $this->filtroEspecialidad)
                ->where('activo', true)
                ->latest()
                ->first();

            if ($plantilla) {
                $this->estadosActivos = $plantilla->getEstadosEfectivos();
                $this->estadoLabels   = array_intersect_key(
                    EspecialidadPlantilla::ESTADOS_DISPONIBLES,
                    array_flip($this->estadosActivos)
                );
                $this->estadoColores  = array_intersect_key(
                    Consulta::ESTADO_COLORES,
                    array_flip($this->estadosActivos)
                );
                return;
            }
        }

        // Sin especialidad: todos los estados del sistema
        $this->estadosActivos = array_keys(EspecialidadPlantilla::ESTADOS_DISPONIBLES);
        $this->estadoLabels   = EspecialidadPlantilla::ESTADOS_DISPONIBLES;
        $this->estadoColores  = Consulta::ESTADO_COLORES;
    }

    // ── Query base ────────────────────────────────────────────────────────────

    protected function buildBaseQuery()
    {
        $query = Consulta::with(['paciente', 'medico', 'especialidad']);

        if (auth()->user()->hasRole('Doctor')) {
            $medico = Medico::where('user_id', auth()->id())->first();
            if ($medico) {
                $query->where('medico_id', $medico->id);
            }
        }

        return $query;
    }

    protected function mapConsultaToEvent($consulta): array
    {
        $title = $consulta->paciente->nombre_completo;

        if (!empty($consulta->paciente->nickname)) {
            $title = "({$consulta->paciente->nickname}) {$title}";
        }

        if ($consulta->estado === Consulta::ESTADO_SALA_ESPERA && $consulta->tiempo_sala_espera !== null) {
            $title .= ' [' . $consulta->tiempo_espera_formateado . ']';
        }

        if (in_array($consulta->estado, [Consulta::ESTADO_EN_GOTAS, Consulta::ESTADO_DILATADO])
            && $consulta->tiempo_gotas_formateado !== null) {
            $title .= ' [' . $consulta->tiempo_gotas_formateado . ']';
        }

        $color = $this->estadoColores[$consulta->estado]
            ?? Consulta::ESTADO_COLORES[$consulta->estado]
            ?? '#78909C';

        $label = $this->estadoLabels[$consulta->estado]
            ?? EspecialidadPlantilla::ESTADOS_DISPONIBLES[$consulta->estado]
            ?? ucfirst($consulta->estado);

        return [
            'id'              => $consulta->id,
            'title'           => $title,
            'start'           => $consulta->fecha_consulta->toIso8601String(),
            'end'             => $consulta->fecha_consulta->copy()->addMinutes(30)->toIso8601String(),
            'backgroundColor' => $color,
            'borderColor'     => $color,
            'extendedProps'   => [
                'calendar'               => $consulta->estado,
                'codigo'                 => $consulta->codigo,
                'paciente'               => $consulta->paciente->nombre_completo,
                'nickname'               => $consulta->paciente->nickname ?? '',
                'edad'                   => $consulta->paciente->edad !== null ? $consulta->paciente->edad . ' años' : '',
                'medico'                 => $consulta->medico->nombre_completo ?? 'Sin médico',
                'medico_full'            => $consulta->medico->nombre_completo ?? 'Sin médico',
                'medico_id'              => $consulta->medico_id,
                'especialidad'           => $consulta->especialidad->nombre ?? 'Sin especialidad',
                'especialidad_id'        => $consulta->especialidad_id,
                'estado'                 => $consulta->estado,
                'estado_label'           => $label,
                'estadoLabel'            => $label,
                'estado_changed_at'      => $consulta->estado_changed_at?->toIso8601String(),
                'motivo'                 => $consulta->motivo_consulta,
                'preconsulta'            => $consulta->preconsulta,
                'tiempo_espera'          => $consulta->tiempo_sala_espera,
                'tiempo_espera_formateado' => $consulta->tiempo_espera_formateado,
                'tiempo_gotas_formateado'  => $consulta->tiempo_gotas_formateado,
                // Estados disponibles para cambiar desde el calendario
                'estados_flujo'          => $this->estadosActivos,
                'estados_labels'         => $this->estadoLabels,
            ],
        ];
    }

    protected function fetchEventos(): array
    {
        $consultas = $this->buildBaseQuery()
            ->when($this->filtroMedico,     fn($q) => $q->porMedico($this->filtroMedico))
            ->when($this->filtroPaciente,   fn($q) => $q->where('paciente_id', $this->filtroPaciente))
            ->when($this->filtroEspecialidad, fn($q) => $q->where('especialidad_id', $this->filtroEspecialidad))
            ->when(!empty($this->filtroEstados), fn($q) => $q->whereIn('estado', $this->filtroEstados))
            ->orderBy('fecha_consulta', 'desc')
            ->get();

        return $consultas->map(fn($c) => $this->mapConsultaToEvent($c))->toArray();
    }

    public function getEventosProperty(): array
    {
        return $this->fetchEventos();
    }

    public function getEventosFresh(): array
    {
        return $this->fetchEventos();
    }

    public function fetchEventosRango($inicio, $fin): array
    {
        try {
            $inicioCarbon = Carbon::parse($inicio);
            $finCarbon    = Carbon::parse($fin);
        } catch (\Exception) {
            return [];
        }

        $consultas = $this->buildBaseQuery()
            ->whereBetween('fecha_consulta', [$inicioCarbon, $finCarbon])
            ->when($this->filtroMedico,       fn($q) => $q->porMedico($this->filtroMedico))
            ->when($this->filtroPaciente,     fn($q) => $q->where('paciente_id', $this->filtroPaciente))
            ->when($this->filtroEspecialidad, fn($q) => $q->where('especialidad_id', $this->filtroEspecialidad))
            ->get();

        return $consultas->map(fn($c) => $this->mapConsultaToEvent($c))->toArray();
    }

    // ── Cambio de estado ──────────────────────────────────────────────────────

    public function cambiarEstado($consultaId, $nuevoEstado): void
    {
        $consulta = Consulta::with('especialidad')->findOrFail($consultaId);

        // Validar contra el flujo de la plantilla de la especialidad
        $estadosValidos = $this->obtenerEstadosValidosPara($consulta->especialidad_id);

        if (!in_array($nuevoEstado, $estadosValidos)) {
            $this->dispatch('show-toast', [
                'type'    => 'error',
                'message' => 'Estado no válido para esta especialidad.',
            ]);
            return;
        }

        $consulta->cambiarEstado($nuevoEstado);

        $label = EspecialidadPlantilla::ESTADOS_DISPONIBLES[$nuevoEstado] ?? ucfirst($nuevoEstado);

        $this->dispatch('show-toast', [
            'type'    => 'success',
            'message' => "Estado actualizado a: {$label}",
        ]);

        $this->dispatch('consulta-saved');
    }

    private function obtenerEstadosValidosPara(?int $especialidadId): array
    {
        if ($especialidadId) {
            $plantilla = EspecialidadPlantilla::where('especialidad_id', $especialidadId)
                ->where('activo', true)->latest()->first();

            if ($plantilla) {
                return $plantilla->getEstadosEfectivos();
            }
        }

        return array_keys(EspecialidadPlantilla::ESTADOS_DISPONIBLES);
    }

    // ── Propiedades computadas ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $query = Consulta::query();

        if (auth()->user()->hasRole('Doctor')) {
            $medico = Medico::where('user_id', auth()->id())->first();
            if ($medico) {
                $query->where('medico_id', $medico->id);
            }
        }

        if ($this->filtroEspecialidad) {
            $query->where('especialidad_id', $this->filtroEspecialidad);
        }

        $hoy = Carbon::today();

        // Conteo dinámico por cada estado activo
        $porEstado = [];
        foreach ($this->estadosActivos as $estado) {
            $porEstado[$estado] = (clone $query)->porEstado($estado)->count();
        }

        return [
            'total_hoy'       => (clone $query)->whereDate('fecha_consulta', $hoy)->count(),
            'sala_espera'     => (clone $query)->porEstado(Consulta::ESTADO_SALA_ESPERA)->count(),
            'en_consultorio'  => (clone $query)->porEstado(Consulta::ESTADO_EN_CONSULTORIO)->count(),
            'finalizadas_hoy' => (clone $query)->porEstado(Consulta::ESTADO_FINALIZADA)->whereDate('fecha_consulta', $hoy)->count(),
            'por_estado'      => $porEstado,
        ];
    }

    public function getMedicosProperty()
    {
        return Medico::activos()->orderBy('nombres')->get();
    }

    public function getPacientesProperty()
    {
        return Paciente::activos()->orderBy('nombres')->limit(50)->get();
    }

    public function getEspecialidadesProperty()
    {
        return Especialidad::activas()->forUser()->orderBy('nombre')->get();
    }

    protected function getPageTitle(): string
    {
        return 'Gestión de Consultas';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard'               => 'Dashboard',
            'admin.gestion.consultas.index' => 'Gestión de Consultas',
        ];
    }

    public function render()
    {
        return view('livewire.admin.gestion.consultas.index', [
            'eventos'        => $this->eventos,
            'stats'          => $this->stats,
            'medicos'        => $this->medicos,
            'pacientes'      => $this->pacientes,
            'especialidades' => $this->especialidades,
            'estados'        => $this->filtroEstados,
            'estadoLabels'   => $this->estadoLabels,
            'estadoColores'  => $this->estadoColores,
            'estadosActivos' => $this->estadosActivos,
        ])->layout($this->getLayout());
    }
}
