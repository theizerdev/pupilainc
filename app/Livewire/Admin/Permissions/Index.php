<?php

namespace App\Livewire\Admin\Permissions;
use App\Traits\HasDynamicLayout;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $module = '';
    public $guard = '';
    public $sortBy = 'module';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'module' => ['except' => ''],
        'guard' => ['except' => ''],
        'sortBy' => ['except' => 'module'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10]
    ];

    public function mount()
    {
        // Verificar permiso para ver permisos
        if (!Auth::user()->can('access permissions')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingModule()
    {
        $this->resetPage();
    }

    public function updatingGuard()
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

    public function deletePermission($permissionId)
    {
        // Verificar permiso para eliminar permisos
        if (!Auth::user()->can('delete permissions')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No tienes permiso para eliminar permisos.',
                'duration' => 4000
            ]);
            return;
        }

        $permission = Permission::findOrFail($permissionId);

        // Verificar si el permiso está asignado a algún rol
        if ($permission->roles()->count() > 0) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede eliminar el permiso porque está asignado a roles.',
                'duration' => 4000
            ]);
            return;
        }

        $nombrePermiso = $permission->name;
        $permission->delete();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Permiso '{$nombrePermiso}' eliminado exitosamente.",
            'duration' => 4000
        ]);
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->module = '';
        $this->guard = '';
        $this->sortBy = 'module';
        $this->sortDirection = 'asc';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function render()
    {
        $permissions = Permission::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('module', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->module, function ($query) {
                $query->where('module', $this->module);
            })
            ->when($this->guard, function ($query) {
                $query->where('guard_name', $this->guard);
            })
            ->with('roles')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        // Obtener módulos únicos para filtros
        $modules = Permission::distinct()->orderBy('module')->pluck('module');

        // Obtener guards únicos para filtros
        $guards = Permission::distinct()->orderBy('guard_name')->pluck('guard_name');

        // Calcular estadísticas
        $totalPermissions = Permission::count();
        $permissionsWithRoles = Permission::has('roles')->count();
        $permissionsWithoutRoles = Permission::doesntHave('roles')->count();
        $uniqueModules = Permission::distinct('module')->count('module');

        return view('livewire.admin.permissions.index', compact('permissions', 'modules', 'guards', 'totalPermissions', 'permissionsWithRoles', 'permissionsWithoutRoles', 'uniqueModules'))
            ->layout($this->getLayout(), [
                'title' => 'Lista de Permisos'
            ]);
    }
}
