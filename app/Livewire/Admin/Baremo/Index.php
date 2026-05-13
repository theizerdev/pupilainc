<?php

namespace App\Livewire\Admin\Baremo;

use App\Models\Baremo;
use App\Models\Categoria;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $sortField = 'nombre_servicio';
    public $sortDirection = 'asc';
    public $activo = '';
    public $categoria_id = '';
    public $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'nombre_servicio'],
        'sortDirection' => ['except' => 'asc'],
        'activo' => ['except' => ''],
        'categoria_id' => ['except' => '']
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingActivo()
    {
        $this->resetPage();
    }

    public function updatingCategoriaId()
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

    public function toggleEstado($id)
    {
        try {
            $this->authorize('edit baremos');

            $baremo = Baremo::findOrFail($id);
            $baremo->activo = !$baremo->activo;
            $baremo->save();

            $estado = $baremo->activo ? 'activado' : 'desactivado';
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Servicio {$estado} exitosamente.",
                'duration' => 4000
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el estado: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function deleteBaremo($baremo)
    {
        $this->authorize('admin.baremos.destroy');

        try {
            $baremo = Baremo::find($baremo);
            $nombreServicio = $baremo->nombre_servicio;
            $baremo->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Servicio '{$nombreServicio}' eliminado exitosamente.",
                'duration' => 4000
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el servicio: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function getBaremosProperty()
    {
        return Baremo::with(['empresa', 'sucursal', 'especialidad', 'categoria'])
            ->forUser()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre_servicio', 'like', '%' . $this->search . '%')
                      ->orWhere('codigo', 'like', '%' . $this->search . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->activo !== '', function ($query) {
                $query->where('activo', $this->activo);
            })
            ->when($this->categoria_id, function ($query) {
                $query->where('categoria_id', $this->categoria_id);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getCategoriasProperty()
    {
        return Categoria::forUser()->activos()->orderBy('nombre')->get();
    }

    public function getStatsProperty()
    {
        $query = Baremo::forUser();

        return [
            'total' => $query->count(),
            'activos' => $query->where('activo', true)->count(),
            'inactivos' => $query->where('activo', false)->count(),
        ];
    }

    protected function getPageTitle(): string
    {
        return 'Baremos';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.baremos.index' => 'Baremos'
        ];
    }

    public function render()
    {
        return view('livewire.admin.baremo.index', [
            'baremos' => $this->baremos,
            'categorias' => $this->categorias,
            'stats' => $this->stats
        ])->layout($this->getLayout());
    }
}
