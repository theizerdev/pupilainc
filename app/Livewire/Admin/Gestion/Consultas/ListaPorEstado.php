<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use App\Models\Consulta;
use App\Models\Especialidad;
use App\Models\EspecialidadPlantilla;
use App\Models\PlantillaEstadoFormulario;
use App\Models\Medico;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;
use Carbon\Carbon;

class ListaPorEstado extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search          = '';
    public $filtroMedico    = '';
    public $filtroFecha     = '';
    public $filtroPaciente  = '';
    public $filtroEspecialidad = '';
    public $sortField       = 'fecha_consulta';
    public $sortDirection   = 'desc';
    public $perPage         = 15;

    public $estadosFiltro   = [];
    public $titulo          = '';

    // Estados resueltos dinámicamente
    public $estadosDisponibles = [];  // todos los estados válidos para cambiar
    public $estadoLabels       = [];
    public $estadoColores      = [];

    // Formularios configurados por estado: [especialidad_id => [estado => bool]]
    // Indica si un estado tiene formulario configurado para mostrar botón de acción
    protected $formulariosPorEspecialidad = [];

    protected $queryString = [
        'search'        => ['except' => ''],
        'sortField'     => ['except' => 'fecha_consulta'],
        'sortDirection' => ['except' => 'desc'],
        'filtroMedico'  => ['except' => ''],
        'filtroFecha'   => ['except' => ''],
        'filtroEspecialidad' => ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'gotas-actualizadas' => '$refresh',
    ];

    public function mount(): void
    {
        $routeMap = [
            'admin.gestion.consultas.sala-espera'   => ['estados' => ['sala_espera'],                                    'titulo' => 'Sala de Espera'],
            'admin.gestion.consultas.en-enfermeria' => ['estados' => ['en_enfermeria'],                                  'titulo' => 'En Enfermería'],
            'admin.gestion.consultas.en-consultorio'=> ['estados' => ['en_consultorio', 'en_consultorio_optometrista'],  'titulo' => 'En Consultorio'],
            'admin.gestion.consultas.en-gotas'      => ['estados' => ['en_gotas'],                                       'titulo' => 'En Gotas'],
            'admin.gestion.consultas.dilatado'      => ['estados' => ['dilatado'],                                       'titulo' => 'Dilatado'],
            'admin.gestion.consultas.en-optica'     => ['estados' => ['en_optica'],                                      'titulo' => 'En Óptica'],
            'admin.gestion.consultas.en-estudio'    => ['estados' => ['en_estudio'],                                     'titulo' => 'En Estudio'],
            'admin.gestion.consultas.finalizadas'   => ['estados' => ['finalizada'],                                     'titulo' => 'Finalizadas'],
            'admin.gestion.consultas.pagadas'       => ['estados' => ['pagada'],                                         'titulo' => 'Pagadas'],
        ];

        $routeName = request()->route()->getName();

        // Ruta dinámica por estado
        if ($routeName === 'admin.gestion.consultas.por-estado') {
            $estado = request()->route('estado');
            $label  = \App\Models\EspecialidadPlantilla::ESTADOS_DISPONIBLES[$estado]
                   ?? \App\Models\Consulta::ESTADO_LABELS[$estado]
                   ?? ucfirst(str_replace('_', ' ', $estado));
            $this->estadosFiltro = [$estado];
            $this->titulo        = $label;
        } else {
            $config              = $routeMap[$routeName] ?? ['estados' => array_keys(\App\Models\EspecialidadPlantilla::ESTADOS_DISPONIBLES), 'titulo' => 'Consultas'];
            $this->estadosFiltro = $config['estados'];
            $this->titulo        = $config['titulo'];
        }

        $this->resolverEstadosDisponibles();
    }

    // ── Resolución dinámica ───────────────────────────────────────────────────

    public function updatedFiltroEspecialidad(): void
    {
        $this->resolverEstadosDisponibles();
        $this->resetPage();
    }

    private function resolverEstadosDisponibles(): void
    {
        if ($this->filtroEspecialidad) {
            $plantilla = EspecialidadPlantilla::where('especialidad_id', $this->filtroEspecialidad)
                ->where('activo', true)->latest()->first();

            if ($plantilla) {
                $estados = $plantilla->getEstadosEfectivos();
                $this->estadosDisponibles = $estados;
                $this->estadoLabels       = array_intersect_key(EspecialidadPlantilla::ESTADOS_DISPONIBLES, array_flip($estados));
                $this->estadoColores      = array_intersect_key(Consulta::ESTADO_COLORES, array_flip($estados));
                return;
            }
        }

        $this->estadosDisponibles = array_keys(EspecialidadPlantilla::ESTADOS_DISPONIBLES);
        $this->estadoLabels       = EspecialidadPlantilla::ESTADOS_DISPONIBLES;
        $this->estadoColores      = Consulta::ESTADO_COLORES;
    }

    // ── Filtros ───────────────────────────────────────────────────────────────

    public function updatingSearch()         { $this->resetPage(); }
    public function updatingFiltroMedico()   { $this->resetPage(); }
    public function updatingFiltroFecha()    { $this->resetPage(); }
    public function updatingFiltroPaciente() { $this->resetPage(); }

    public function sortBy($field): void
    {
        $this->sortDirection = $this->sortField === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';
        $this->sortField = $field;
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filtroMedico', 'filtroFecha', 'filtroPaciente', 'filtroEspecialidad']);
        $this->resolverEstadosDisponibles();
        $this->resetPage();
    }

    // ── Cambio de estado ──────────────────────────────────────────────────────

    public function cambiarEstado($consultaId, $nuevoEstado): void
    {
        $consulta = Consulta::findOrFail($consultaId);

        // Validar contra el flujo de la plantilla de la especialidad de la consulta
        $estadosValidos = $this->obtenerEstadosValidosPara($consulta->especialidad_id);

        if (!in_array($nuevoEstado, $estadosValidos)) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Estado no válido para esta especialidad.']);
            return;
        }

        $consulta->cambiarEstado($nuevoEstado);

        $label = $this->estadoLabels[$nuevoEstado]
            ?? EspecialidadPlantilla::ESTADOS_DISPONIBLES[$nuevoEstado]
            ?? ucfirst($nuevoEstado);

        $this->dispatch('show-toast', ['type' => 'success', 'message' => "Estado actualizado a: {$label}"]);
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

    // ── Consultas ─────────────────────────────────────────────────────────────

    public function getConsultasProperty()
    {
        $query = Consulta::with(['paciente', 'medico', 'especialidad', 'gotasAplicadas', 'reposo', 'estadoDatos'])
            ->whereIn('estado', $this->estadosFiltro);

        if (auth()->user()->hasRole('Doctor')) {
            $medico = Medico::where('user_id', auth()->id())->first();
            if ($medico) {
                $query->where('medico_id', $medico->id);
            }
        }

        $query
            ->when($this->filtroEspecialidad, fn($q) => $q->where('especialidad_id', $this->filtroEspecialidad))
            ->when($this->search, fn($q) => $q->where(function ($sub) {
                $sub->whereHas('paciente', fn($p) => $p
                        ->where('nombres', 'like', "%{$this->search}%")
                        ->orWhere('apellidos', 'like', "%{$this->search}%")
                        ->orWhere('documento_identidad', 'like', "%{$this->search}%"))
                    ->orWhereHas('medico', fn($m) => $m->where('nombres', 'like', "%{$this->search}%"))
                    ->orWhere('codigo', 'like', "%{$this->search}%");
            }))
            ->when($this->filtroMedico,   fn($q) => $q->where('medico_id', $this->filtroMedico))
            ->when($this->filtroFecha,    fn($q) => $q->whereDate('fecha_consulta', $this->filtroFecha))
            ->when($this->filtroPaciente, fn($q) => $q->where('paciente_id', $this->filtroPaciente));

        return $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
    }

    public function getStatsProperty(): array
    {
        $base = Consulta::whereIn('estado', $this->estadosFiltro);

        if (auth()->user()->hasRole('Doctor')) {
            $medico = Medico::where('user_id', auth()->id())->first();
            if ($medico) {
                $base->where('medico_id', $medico->id);
            }
        }

        if ($this->filtroEspecialidad) {
            $base->where('especialidad_id', $this->filtroEspecialidad);
        }

        $hoy = Carbon::today();

        $porMedico = (clone $base)
            ->selectRaw('medico_id, count(*) as total')
            ->groupBy('medico_id')->with('medico')->get()
            ->mapWithKeys(fn($r) => [($r->medico->nombre_completo ?? 'Sin médico') => $r->total])
            ->toArray();

        return [
            'total'      => (clone $base)->count(),
            'total_hoy'  => (clone $base)->whereDate('fecha_consulta', $hoy)->count(),
            'por_medico' => $porMedico,
        ];
    }

    public function getMedicosProperty()
    {
        return Medico::activos()->orderBy('nombres')->get();
    }

    public function getEspecialidadesProperty()
    {
        return Especialidad::activas()->forUser()->orderBy('nombre')->get();
    }

    /**
     * Devuelve true si el estado de la consulta tiene un formulario de estado configurado.
     * Se usa en la vista para mostrar el botón de acción dinámica.
     */
    public function tieneFormularioEstado(Consulta $consulta): bool
    {
        if (!$consulta->especialidad_id) return false;

        if (!isset($this->formulariosPorEspecialidad[$consulta->especialidad_id])) {
            $plantilla = EspecialidadPlantilla::where('especialidad_id', $consulta->especialidad_id)
                ->where('activo', true)->latest()->first();

            $this->formulariosPorEspecialidad[$consulta->especialidad_id] = $plantilla
                ? PlantillaEstadoFormulario::where('plantilla_id', $plantilla->id)
                    ->where('activo', true)
                    ->pluck('estado')
                    ->flip()
                    ->toArray()
                : [];
        }

        return isset($this->formulariosPorEspecialidad[$consulta->especialidad_id][$consulta->estado]);
    }

    public function getTituloFormularioEstado(Consulta $consulta): ?string
    {
        if (!$consulta->especialidad_id) return null;

        $plantilla = EspecialidadPlantilla::where('especialidad_id', $consulta->especialidad_id)
            ->where('activo', true)->latest()->first();

        if (!$plantilla) return null;

        return PlantillaEstadoFormulario::where('plantilla_id', $plantilla->id)
            ->where('estado', $consulta->estado)
            ->where('activo', true)
            ->value('titulo');
    }

    protected function getPageTitle(): string { return $this->titulo; }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard'               => 'Dashboard',
            'admin.gestion.consultas.index' => 'Gestión de Consultas',
            ''                              => $this->titulo,
        ];
    }

    public function render()
    {
        return view('livewire.admin.gestion.consultas.lista-por-estado', [
            'consultas'          => $this->consultas,
            'stats'              => $this->stats,
            'medicos'            => $this->medicos,
            'especialidades'     => $this->especialidades,
            'estadosFiltro'      => $this->estadosFiltro,
            'titulo'             => $this->titulo,
            'estadosDisponibles' => $this->estadosDisponibles,
            'estadoLabels'       => $this->estadoLabels,
            'estadoColores'      => $this->estadoColores,
        ])->layout($this->getLayout());
    }
}
