<?php

namespace App\Livewire\Admin\Sucursales;
use App\Traits\HasDynamicLayout;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Sucursal;
use App\Models\Empresa;
use Illuminate\Support\Facades\Auth;
use App\Traits\Exportable;

class Index extends Component
{
    use WithPagination, Exportable, HasDynamicLayout;

    public $search = '';
    public $status = '';
    public $empresa_id = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'empresa_id' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10]
    ];

    public function mount()
    {
        // Verificar permiso para ver sucursales
        if (!Auth::user()->can('access sucursales')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingEmpresaId()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortBy = $field;
    }

    protected function getExportQuery()
    {
        return $this->getBaseQuery();
    }

    protected function getExportHeaders(): array
    {
        return ['ID', 'Empresa', 'Nombre', 'Teléfono', 'Dirección', 'Status'];
    }

    protected function formatExportRow($sucursal): array
    {
        return [
            $sucursal->id,
            $sucursal->empresa->razon_social ?? 'N/A',
            $sucursal->nombre,
            $sucursal->telefono,
            $sucursal->direccion,
            $sucursal->status ? 'Activo' : 'Inactivo'
        ];
    }

    private function getBaseQuery()
    {
        return Sucursal::forUser()->with('empresa')
            ->when($this->search, function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->when($this->status !== '', function ($query) {
                // Convertir string a booleano para la comparación
                $statusBool = $this->status === 'active' ? true : ($this->status === 'inactive' ? false : null);
                if ($statusBool !== null) {
                    $query->where('status', $statusBool);
                }
            })
            ->when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            });
    }

    public function render()
    {
        $sucursales = $this->getBaseQuery()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        $empresas = Empresa::forUser()->where('status', true)->get();

        // Calcular estadísticas
        $totalSucursales = Sucursal::forUser()->count();
        $sucursalesActivas = Sucursal::forUser()->where('status', true)->count();
        $sucursalesInactivas = Sucursal::forUser()->where('status', false)->count();

        return view('livewire.admin.sucursales.index', compact('sucursales', 'empresas', 'totalSucursales', 'sucursalesActivas', 'sucursalesInactivas'))
            ->layout($this->getLayout(), [
                'title' => 'Lista de Sucursales'
            ]);
    }

    public function toggleStatus(Sucursal $sucursal)
    {
        // Verificar permiso para editar sucursales
        if (!Auth::user()->can('edit sucursales')) {
            session()->flash('error', 'No tienes permiso para editar sucursales.');
            return;
        }

        // Alternar status como booleano
        $sucursal->status = !$sucursal->status;
        $sucursal->save();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Estado de sucursal actualizado correctamente.',
            'duration' => 4000
        ]);
    }

    public function delete(Sucursal $sucursal)
    {
        // Verificar permiso para eliminar sucursales
        if (!Auth::user()->can('delete sucursales')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No tienes permiso para eliminar sucursales.',
                'duration' => 4000
            ]);
            return;
        }

        $nombreSucursal = $sucursal->nombre;
        $sucursal->delete();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Sucursal '{$nombreSucursal}' eliminada exitosamente.",
            'duration' => 4000
        ]);
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->empresa_id = '';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->perPage = 10;
        $this->resetPage();
    }
}
