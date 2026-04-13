<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use App\Models\Consulta;
use App\Models\Medico;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;
use Carbon\Carbon;

class ListaPorEstado extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $filtroMedico = '';
    public $filtroFecha = '';
    public $filtroPaciente = '';
    public $sortField = 'fecha_consulta';
    public $sortDirection = 'desc';
    public $perPage = 15;

    public $estadosFiltro = [];
    public $titulo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'fecha_consulta'],
        'sortDirection' => ['except' => 'desc'],
        'filtroMedico' => ['except' => ''],
        'filtroFecha' => ['except' => ''],
    ];

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'success' => 'mostrarSuccess',
        'error' => 'mostrarError',
        'signos-vitales-guardado' => 'cerrarModalSignosVitales',
        'gotas-actualizadas' => '$refresh',
    ];

    public function mostrarSuccess($message)
    {
        $this->dispatchBrowserEvent('alert', ['type' => 'success', 'message' => $message]);
    }

    public function mostrarError($message)
    {
        $this->dispatchBrowserEvent('alert', ['type' => 'error', 'message' => $message]);
    }

    public function cerrarModalSignosVitales()
    {
        $this->dispatchBrowserEvent('cerrar-modal-signos-vitales');
    }

    public function mount()
    {
        $routeMap = [
            'admin.gestion.consultas.sala-espera' => [
                'estados' => [Consulta::ESTADO_SALA_ESPERA],
                'titulo' => 'Sala de Espera',
            ],
            'admin.gestion.consultas.en-enfermeria' => [
                'estados' => [Consulta::ESTADO_EN_ENFERMERIA],
                'titulo' => 'En Enfermería',
            ],
            'admin.gestion.consultas.en-consultorio' => [
                'estados' => [Consulta::ESTADO_EN_CONSULTORIO, Consulta::ESTADO_EN_CONSULTORIO_OPTOMETRISTA],
                'titulo' => 'En Consultorio',
            ],
            'admin.gestion.consultas.en-gotas' => [
                'estados' => [Consulta::ESTADO_EN_GOTAS],
                'titulo' => 'En Gotas',
            ],
            'admin.gestion.consultas.en-optica' => [
                'estados' => [Consulta::ESTADO_EN_OPTICA],
                'titulo' => 'En Óptica',
            ],
            'admin.gestion.consultas.en-estudio' => [
                'estados' => [Consulta::ESTADO_EN_ESTUDIO],
                'titulo' => 'En Estudio',
            ],
            'admin.gestion.consultas.finalizadas' => [
                'estados' => [Consulta::ESTADO_FINALIZADA],
                'titulo' => 'Finalizadas',
            ],
        ];

        $routeName = request()->route()->getName();
        $config = $routeMap[$routeName] ?? [
            'estados' => Consulta::ESTADOS,
            'titulo' => 'Consultas',
        ];

        $this->estadosFiltro = $config['estados'];
        $this->titulo = $config['titulo'];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroMedico()
    {
        $this->resetPage();
    }

    public function updatingFiltroFecha()
    {
        $this->resetPage();
    }

    public function updatingFiltroPaciente()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filtroMedico', 'filtroFecha', 'filtroPaciente']);
        $this->resetPage();
    }

    public function cambiarEstado($consultaId, $nuevoEstado)
    {
        $consulta = Consulta::findOrFail($consultaId);
        $consulta->cambiarEstado($nuevoEstado);

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Estado actualizado a: ' . $this->getEstadoLabel($nuevoEstado),
        ]);
    }

    public function getConsultasProperty()
    {
        $query = Consulta::with(['paciente', 'medico', 'especialidad', 'gotasAplicadas'])
            ->whereIn('estado', $this->estadosFiltro);

        if (auth()->user()->hasRole('Doctor')) {
            $medico = Medico::where('user_id', auth()->id())->first();
            if ($medico) {
                $query->where('medico_id', $medico->id);
            }
        }

        $query->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->whereHas('paciente', function ($p) {
                            $p->where('nombres', 'like', '%' . $this->search . '%')
                              ->orWhere('apellidos', 'like', '%' . $this->search . '%')
                              ->orWhere('documento_identidad', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('medico', function ($m) {
                            $m->where('nombres', 'like', '%' . $this->search . '%');
                        })
                        ->orWhere('codigo', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filtroMedico, function ($q) {
                $q->where('medico_id', $this->filtroMedico);
            })
            ->when($this->filtroFecha, function ($q) {
                $q->whereDate('fecha_consulta', $this->filtroFecha);
            })
            ->when($this->filtroPaciente, function ($q) {
                $q->where('paciente_id', $this->filtroPaciente);
            });

        return $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getStatsProperty()
    {
        $baseQuery = Consulta::whereIn('estado', $this->estadosFiltro);

        if (auth()->user()->hasRole('Doctor')) {
            $medico = Medico::where('user_id', auth()->id())->first();
            if ($medico) {
                $baseQuery->where('medico_id', $medico->id);
            }
        }

        $hoy = Carbon::today();

        $porMedico = (clone $baseQuery)
            ->selectRaw('medico_id, count(*) as total')
            ->groupBy('medico_id')
            ->with('medico')
            ->get()
            ->mapWithKeys(fn($row) => [
                ($row->medico->nombre_completo ?? 'Sin médico') => $row->total,
            ])
            ->toArray();

        return [
            'total' => (clone $baseQuery)->count(),
            'total_hoy' => (clone $baseQuery)->whereDate('fecha_consulta', $hoy)->count(),
            'por_medico' => $porMedico,
        ];
    }

    public function getMedicosProperty()
    {
        return Medico::activos()->orderBy('nombres')->get();
    }

    protected function getEstadoLabel($estado)
    {
        return Consulta::ESTADO_LABELS[$estado] ?? ucfirst($estado);
    }

    protected function getEstadoColor($estado)
    {
        return Consulta::ESTADO_COLORES[$estado] ?? '#78909C';
    }

    protected function getPageTitle(): string
    {
        return $this->titulo;
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.gestion.consultas.index' => 'Gestión de Consultas',
            '' => $this->titulo,
        ];
    }

    public function render()
    {
        return view('livewire.admin.gestion.consultas.lista-por-estado', [
            'consultas' => $this->consultas,
            'stats' => $this->stats,
            'medicos' => $this->medicos,
            'estadosFiltro' => $this->estadosFiltro,
            'titulo' => $this->titulo,
            'allEstados' => Consulta::ESTADOS,
            'estadoLabels' => Consulta::ESTADO_LABELS,
            'estadoColores' => Consulta::ESTADO_COLORES,
        ])->layout($this->getLayout());
    }
}
