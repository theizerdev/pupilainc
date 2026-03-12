<?php

namespace App\Livewire\Admin\Subespecialidades;

use App\Models\Subespecialidad;
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
    public $especialidad_id = '';
    public $empresa_id = '';
    public $sucursal_id = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'status' => ['except' => ''],
        'especialidad_id' => ['except' => ''],
        'empresa_id' => ['except' => ''],
        'sucursal_id' => ['except' => '']
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEspecialidadId()
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

    /**
     * Limpiar todos los filtros
     */
    public function clearFilters()
    {
        $this->reset(['search', 'especialidad_id', 'empresa_id', 'sucursal_id', 'status']);
        $this->resetPage();
    }

    /**
     * Cambiar el estado de una subespecialidad
     */
    public function toggleStatus($id)
    {
        $this->authorize('edit subespecialidades');
        
        try {
            $subespecialidad = Subespecialidad::findOrFail($id);
            $subespecialidad->status = !$subespecialidad->status;
            $subespecialidad->save();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Estado de '{$subespecialidad->nombre}' actualizado a " . ($subespecialidad->status ? 'Activo' : 'Inactivo'),
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

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function deleteSubespecialidad($id)
    {
        $this->authorize('delete subespecialidades');
        
        try {
            $subespecialidad = Subespecialidad::findOrFail($id);
            $nombreSubespecialidad = $subespecialidad->nombre;
            $subespecialidad->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Subespecialidad '{$nombreSubespecialidad}' eliminada exitosamente.",
                'duration' => 4000
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar la subespecialidad: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function getSubespecialidadesProperty()
    {
        return Subespecialidad::with(['especialidad', 'empresa', 'sucursal'])
            ->forUser()
            ->when($this->search, function ($query) {
                $searchTerm = '%' . $this->search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('nombre', 'like', $searchTerm)
                      ->orWhere('descripcion', 'like', $searchTerm)
                      ->orWhere('codigo', 'like', $searchTerm);
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->especialidad_id, function ($query) {
                $query->where('especialidad_id', $this->especialidad_id);
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

    public function getEspecialidadesProperty()
    {
        return Especialidad::forUser()->get();
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
        $query = Subespecialidad::forUser();
        
        // Calcular el promedio solo de subespecialidades con costo_consulta válido
        $subespecialidadesConCosto = $query->whereNotNull('costo_consulta')->where('costo_consulta', '>', 0);
        $avgCost = $subespecialidadesConCosto->avg('costo_consulta');
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
        return view('livewire.admin.subespecialidades.index', [
            'subespecialidades' => $this->subespecialidades,
            'especialidades' => $this->especialidades,
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
            'stats' => $this->stats
        ])->layout($this->getLayout());
    }
}