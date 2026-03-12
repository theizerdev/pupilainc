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

    /**
     * Verificar si una especialidad tiene relaciones activas
     */
    public function tieneRelaciones($especialidadId)
    {
        $especialidad = Especialidad::find($especialidadId);
        
        if (!$especialidad) {
            return false;
        }

        // Verificar si tiene médicos activos
        $tieneMedicos = $especialidad->medicos()->where('medicos.status', true)->exists();
        
        // Verificar si tiene subespecialidades activas
        $tieneSubespecialidades = $especialidad->subespecialidades()->where('status', true)->exists();
        
        // Verificar si tiene citas asociadas (consultas)
        $tieneCitas = \DB::table('citas')
            ->where('especialidad_id', $especialidadId)
            ->exists();

        return $tieneMedicos || $tieneSubespecialidades || $tieneCitas;
    }

    /**
     * Obtener información detallada sobre las relaciones de una especialidad
     */
    public function getInfoRelaciones($especialidadId)
    {
        $especialidad = Especialidad::find($especialidadId);
        
        if (!$especialidad) {
            return '';
        }

        $relaciones = [];

        // Contar médicos activos
        $medicosCount = $especialidad->medicos()->where('medicos.status', true)->count();
        if ($medicosCount > 0) {
            $relaciones[] = $medicosCount . ' médico(s) activo(s)';
        }

        // Contar subespecialidades activas
        $subespecialidadesCount = $especialidad->subespecialidades()->where('status', true)->count();
        if ($subespecialidadesCount > 0) {
            $relaciones[] = $subespecialidadesCount . ' subespecialidad(es) activa(s)';
        }

        // Contar citas asociadas
        $citasCount = \DB::table('citas')
            ->where('especialidad_id', $especialidadId)
            ->count();
        if ($citasCount > 0) {
            $relaciones[] = $citasCount . ' cita(s) asociada(s)';
        }

        return !empty($relaciones) ? 'No se puede eliminar: ' . implode(', ', $relaciones) : '';
    }

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
        $this->authorize('delete especialidades');

        try {
            $especialidad = Especialidad::findOrFail($id);
            $nombreEspecialidad = $especialidad->nombre;
            $especialidad->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Especialidad '{$nombreEspecialidad}' eliminada exitosamente.",
                'duration' => 4000
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Verificar si es un error de integridad referencial (foreign key constraint)
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'No se puede eliminar esta especialidad porque tiene consultas relacionadas. Primero debe eliminar o reasignar las consultas asociadas.',
                    'duration' => 6000
                ]);
            } else {
                // Otro error de base de datos
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Error al eliminar la especialidad: ' . $e->getMessage(),
                    'duration' => 5000
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar la especialidad: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit especialidades');

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

    public function deleteEspecialidad($especialidad)
    {
        try {
            $especialidad = Especialidad::findOrFail($especialidad);
            $nombreEspecialidad = $especialidad->nombre;
            $especialidad->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Especialidad '{$nombreEspecialidad}' eliminada exitosamente.",
                'duration' => 4000
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Verificar si es un error de integridad referencial (foreign key constraint)
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'No se puede eliminar esta especialidad porque tiene consultas relacionadas. Primero debe eliminar o reasignar las consultas asociadas.',
                    'duration' => 6000
                ]);
            } else {
                // Otro error de base de datos
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Error al eliminar la especialidad: ' . $e->getMessage(),
                    'duration' => 5000
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar la especialidad: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
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