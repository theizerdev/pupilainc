<?php

namespace App\Livewire\Admin\Impuestos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ImpuestoConfiguracion;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $filtro_activo = '';
    public $showModal = false;
    public $editingId = null;
    public $sortField = 'codigo';
    public $sortDirection = 'asc';
    public $perPage = 20;

    public $codigo;
    public $nombre;
    public $porcentaje;
    public $descripcion;
    public $activo = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'filtro_activo' => ['except' => ''],
        'sortField' => ['except' => 'codigo'],
        'sortDirection' => ['except' => 'asc'],
    ];

    protected $paginationTheme = 'bootstrap';

    protected function rules()
    {
        return [
            'codigo' => 'required|unique:impuestos_configuracion,codigo,' . $this->editingId,
            'nombre' => 'required|min:3',
            'porcentaje' => 'required|numeric|min:0|max:100',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroActivo()
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
        $this->reset(['search', 'filtro_activo']);
        $this->resetPage();
    }

    public function crear()
    {
        $this->authorize('create impuestos');
        $this->resetForm();
        $this->showModal = true;
    }

    public function editar($id)
    {
        $this->authorize('edit impuestos');
        $impuesto = ImpuestoConfiguracion::findOrFail($id);
        $this->editingId = $impuesto->id;
        $this->codigo = $impuesto->codigo;
        $this->nombre = $impuesto->nombre;
        $this->porcentaje = $impuesto->porcentaje;
        $this->descripcion = $impuesto->descripcion;
        $this->activo = $impuesto->activo;
        $this->showModal = true;
    }

    public function guardar()
    {
        if ($this->editingId) {
            $this->authorize('edit impuestos');
        } else {
            $this->authorize('create impuestos');
        }

        $this->validate();

        try {
            $data = [
                'codigo' => strtoupper(trim($this->codigo)),
                'nombre' => trim($this->nombre),
                'porcentaje' => $this->porcentaje,
                'descripcion' => $this->descripcion,
                'activo' => $this->activo,
                'empresa_id' => auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->sucursal_id,
            ];

            if ($this->editingId) {
                ImpuestoConfiguracion::find($this->editingId)->update($data);
                $mensaje = 'Impuesto actualizado exitosamente';
            } else {
                ImpuestoConfiguracion::create($data);
                $mensaje = 'Impuesto creado exitosamente';
            }

            $this->closeModal();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $mensaje,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar el impuesto: ' . $e->getMessage(),
            ]);
        }
    }

    public function toggleActivo($id)
    {
        $impuesto = ImpuestoConfiguracion::find($id);
        if (!$impuesto) return;

        $this->authorize($impuesto->activo ? 'deactivate impuestos' : 'activate impuestos');

        try {
            $impuesto->activo = !$impuesto->activo;
            $impuesto->save();

            $estado = $impuesto->activo ? 'activado' : 'desactivado';
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Impuesto {$estado} exitosamente",
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el estado: ' . $e->getMessage(),
            ]);
        }
    }

    public function eliminar($id)
    {
        $this->authorize('delete impuestos');

        try {
            $impuesto = ImpuestoConfiguracion::find($id);
            $nombreImpuesto = $impuesto ? $impuesto->codigo . ' - ' . $impuesto->nombre : '';
            $impuesto?->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Impuesto '{$nombreImpuesto}' eliminado exitosamente",
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el impuesto: ' . $e->getMessage(),
            ]);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm()
    {
        $this->reset(['editingId', 'codigo', 'nombre', 'porcentaje', 'descripcion']);
        $this->activo = true;
    }

    public function getImpuestosProperty()
    {
        return ImpuestoConfiguracion::query()
            ->when($this->search, fn($q) => $q->where('codigo', 'like', "%{$this->search}%")
                ->orWhere('nombre', 'like', "%{$this->search}%"))
            ->when($this->filtro_activo !== '', fn($q) => $q->where('activo', $this->filtro_activo))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getStatsProperty()
    {
        return [
            'total' => ImpuestoConfiguracion::count(),
            'activos' => ImpuestoConfiguracion::where('activo', true)->count(),
            'inactivos' => ImpuestoConfiguracion::where('activo', false)->count(),
        ];
    }

    protected function getPageTitle(): string
    {
        return 'Configuración de Impuestos';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.impuestos.index' => 'Configuración de Impuestos'
        ];
    }

    public function render()
    {
        return view('livewire.admin.impuestos.index', [
            'impuestos' => $this->impuestos,
            'stats' => $this->stats,
        ])->layout($this->getLayout());
    }
}
