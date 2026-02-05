<?php

namespace App\Livewire\Admin\Especialidades;

use App\Models\Especialidad;
use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $status = '';
    public $empresa_id = '';
    public $sucursal_id = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'status' => ['except' => ''],
        'empresa_id' => ['except' => ''],
        'sucursal_id' => ['except' => '']
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEmpresaId()
    {
        $this->resetPage();
        $this->sucursal_id = '';
    }

    public function updatingSucursalId()
    {
        $this->resetPage();
    }

    public function updatingStatus()
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

    public function delete($id)
    {
        $this->authorize('admin.especialidades.destroy');
        
        try {
            $especialidad = Especialidad::findOrFail($id);
            $especialidad->delete();
            
            session()->flash('success', 'Especialidad eliminada exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar la especialidad: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('admin.especialidades.edit');
        
        try {
            $especialidad = Especialidad::findOrFail($id);
            $especialidad->status = !$especialidad->status;
            $especialidad->save();
            
            session()->flash('success', 'Estado actualizado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }

    public function getEspecialidadesProperty()
    {
        return Especialidad::with(['empresa', 'sucursal'])
            ->forUser()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->search . '%')
                      ->orWhere('codigo', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            })
            ->when($this->sucursal_id, function ($query) {
                $query->where('sucursal_id', $this->sucursal_id);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function getEmpresasProperty()
    {
        return Empresa::forUser()->get();
    }

    public function getSucursalesProperty()
    {
        return Sucursal::when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            })
            ->forUser()
            ->get();
    }

    public function getStatsProperty()
    {
        $query = Especialidad::forUser();
        
        // Obtener el promedio y asegurar que sea un número válido
        $avgCost = $query->avg('costo_consulta');
        $promedioCosto = is_numeric($avgCost) ? round((float)$avgCost, 2) : 0;
        
        return [
            'total' => $query->count(),
            'activas' => $query->where('status', true)->count(),
            'inactivas' => $query->where('status', false)->count(),
            'promedio_costo' => $promedioCosto,
        ];
    }

    public function render()
    {
        return view('livewire.admin.especialidades.index', [
            'especialidades' => $this->especialidades,
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
            'stats' => $this->stats
        ])->layout($this->getLayout());
    }
}