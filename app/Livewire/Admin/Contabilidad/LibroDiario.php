<?php

namespace App\Livewire\Admin\Contabilidad;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AsientoContable;
use App\Models\CuentaContable;
use Carbon\Carbon;
use Livewire\Attributes\Computed;

class LibroDiario extends Component
{
    use HasDynamicLayout, WithPagination;

    public $fecha_desde;
    public $fecha_hasta;
    public $tipo = '';
    public $search = '';
    public $cuenta_id = '';
    public $usuario_id = '';
    public $mostrar_detalles = true;
    public $agrupar_por_fecha = false;
    public $perPage = 50;
    public $sortField = 'fecha';
    public $sortDirection = 'asc';

    protected $queryString = [
        'tipo' => ['except' => ''],
        'search' => ['except' => ''],
        'cuenta_id' => ['except' => ''],
        'usuario_id' => ['except' => ''],
        'mostrar_detalles' => ['except' => true],
        'agrupar_por_fecha' => ['except' => false],
        'sortField' => ['except' => 'fecha'],
        'sortDirection' => ['except' => 'asc'],
    ];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->authorize('access contabilidad');
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipo()
    {
        $this->resetPage();
    }

    public function updatingCuentaId()
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
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['tipo', 'search', 'cuenta_id', 'usuario_id', 'mostrar_detalles', 'agrupar_por_fecha']);
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    #[Computed]
    public function asientos()
    {
        $query = AsientoContable::with(['detalles.cuenta', 'user'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where('estado', 'aprobado')
            ->whereBetween('fecha', [$this->fecha_desde, $this->fecha_hasta]);

        // Filtros
        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('numero', 'like', "%{$this->search}%")
                  ->orWhere('descripcion', 'like', "%{$this->search}%")
                  ->orWhereHas('detalles.cuenta', fn($sq) => 
                      $sq->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('codigo', 'like', "%{$this->search}%")
                  );
            });
        }

        if ($this->cuenta_id) {
            $query->whereHas('detalles', fn($q) => $q->where('cuenta_id', $this->cuenta_id));
        }

        if ($this->usuario_id) {
            $query->where('user_id', $this->usuario_id);
        }

        // Ordenamiento
        $query->orderBy($this->sortField, $this->sortDirection);
        if ($this->sortField !== 'numero') {
            $query->orderBy('numero', 'asc');
        }

        return $query->paginate($this->perPage);
    }

    #[Computed]
    public function stats()
    {
        $query = AsientoContable::with('detalles')
            ->where('empresa_id', auth()->user()->empresa_id)
            ->where('estado', 'aprobado')
            ->whereBetween('fecha', [$this->fecha_desde, $this->fecha_hasta]);

        // Aplicar los mismos filtros que en asientos
        if ($this->tipo) {
            $query->where('tipo', $this->tipo);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('numero', 'like', "%{$this->search}%")
                  ->orWhere('descripcion', 'like', "%{$this->search}%")
                  ->orWhereHas('detalles.cuenta', fn($sq) => 
                      $sq->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('codigo', 'like', "%{$this->search}%")
                  );
            });
        }

        if ($this->cuenta_id) {
            $query->whereHas('detalles', fn($q) => $q->where('cuenta_id', $this->cuenta_id));
        }

        if ($this->usuario_id) {
            $query->where('user_id', $this->usuario_id);
        }

        $asientos = $query->get();
        
        return [
            'total_asientos' => $asientos->count(),
            'total_debe' => $asientos->sum(fn($a) => $a->detalles->sum('debe')),
            'total_haber' => $asientos->sum(fn($a) => $a->detalles->sum('haber')),
            'por_tipo' => $asientos->groupBy('tipo')->map->count(),
            'promedio_por_asiento' => $asientos->count() > 0 ? 
                $asientos->sum(fn($a) => $a->detalles->sum('debe')) / $asientos->count() : 0,
        ];
    }

    #[Computed]
    public function cuentas()
    {
        return CuentaContable::where('empresa_id', auth()->user()->empresa_id)
            ->where('activo', true)
            ->orderBy('codigo')
            ->get();
    }

    #[Computed]
    public function usuarios()
    {
        return \App\Models\User::whereHas('asientosContables', function($q) {
            $q->where('empresa_id', auth()->user()->empresa_id)
              ->whereBetween('fecha', [$this->fecha_desde, $this->fecha_hasta]);
        })->orderBy('name')->get();
    }

    public function exportarPdf()
    {
        return redirect()->route('admin.contabilidad.libro-diario.pdf', [
            'desde' => $this->fecha_desde,
            'hasta' => $this->fecha_hasta,
            'tipo' => $this->tipo,
            'cuenta_id' => $this->cuenta_id,
        ]);
    }

    public function exportarExcel()
    {
        return redirect()->route('admin.contabilidad.libro-diario.excel', [
            'desde' => $this->fecha_desde,
            'hasta' => $this->fecha_hasta,
            'tipo' => $this->tipo,
            'cuenta_id' => $this->cuenta_id,
        ]);
    }

    protected function getPageTitle(): string
    {
        return 'Libro Diario';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.contabilidad.libro-diario' => 'Libro Diario'
        ];
    }

    public function render()
    {
        return view('livewire.admin.contabilidad.libro-diario', [
            'asientos' => $this->asientos,
            'stats' => $this->stats,
            'cuentas' => $this->cuentas,
            'usuarios' => $this->usuarios,
        ])->layout($this->getLayout());
    }
}
