<?php

namespace App\Livewire\Admin\Medicos;

use App\Models\Medico;
use App\Models\Especialidad;
use App\Models\Subespecialidad;
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
    public $especialidad_id = '';
    public $nivel_experiencia = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'status' => ['except' => ''],
        'empresa_id' => ['except' => ''],
        'sucursal_id' => ['except' => ''],
        'especialidad_id' => ['except' => ''],
        'nivel_experiencia' => ['except' => '']
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

    public function updatingEspecialidadId()
    {
        $this->resetPage();
    }

    public function updatingNivelExperiencia()
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
        $this->reset(['search', 'status', 'empresa_id', 'sucursal_id', 'especialidad_id', 'nivel_experiencia']);
        $this->resetPage();
    }

    public function delete($id)
    {
        $this->authorize('delete medicos');
        
        try {
            $medico = Medico::findOrFail($id);
            $medico->delete();
            
            session()->flash('success', 'Médico eliminado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el médico: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit citas');
        
        try {
            $medico = Medico::findOrFail($id);
            $nombreMedico = $medico->nombres . ' ' . $medico->apellidos;
            $medico->status = !$medico->status;
            $medico->save();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Estado de '{$nombreMedico}' actualizado a " . ($medico->status ? 'Activo' : 'Inactivo'),
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

    public function getMedicosProperty()
    {
        return Medico::with(['user', 'empresa', 'sucursal', 'especialidades'])
            ->forUser()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombres', 'like', '%' . $this->search . '%')
                      ->orWhere('apellidos', 'like', '%' . $this->search . '%')
                      ->orWhere('documento_identidad', 'like', '%' . $this->search . '%')
                      ->orWhere('licencia_medica', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($q) {
                          $q->where('email', 'like', '%' . $this->search . '%');
                      });
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
            ->when($this->especialidad_id, function ($query) {
                $query->whereHas('especialidades', function ($q) {
                    $q->where('especialidad_id', $this->especialidad_id);
                });
            })
            ->when($this->nivel_experiencia, function ($query) {
                $query->where('nivel_experiencia', $this->nivel_experiencia);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function getStatsProperty()
    {
        $query = Medico::forUser();
        
        // Calcular promedio de experiencia solo para médicos con años > 0
        $promedioExperiencia = $query->clone()
            ->where('anios_experiencia', '>', 0)
            ->avg('anios_experiencia');
        
        return [
            'total' => $query->count(),
            'activos' => $query->where('status', true)->count(),
            'inactivos' => $query->where('status', false)->count(),
            'promedio_experiencia' => $promedioExperiencia ? round($promedioExperiencia, 1) : 0,
        ];
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

    public function getEspecialidadesProperty()
    {
        return Especialidad::forUser()->where('status', true)->orderBy('nombre')->get();
    }

    public function render()
    {
        return view('livewire.admin.medicos.index', [
            'medicos' => $this->medicos,
            'stats' => $this->stats,
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
            'especialidades' => $this->especialidades,
        ])->layout($this->getLayout());
    }
}