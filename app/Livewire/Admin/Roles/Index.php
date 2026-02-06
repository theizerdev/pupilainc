<?php

namespace App\Livewire\Admin\Roles;
use App\Traits\HasDynamicLayout;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $guard = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'guard' => ['except' => ''],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10]
    ];

    public function mount()
    {
        if (!Auth::user()->can('access roles') && !Auth::user()->can('view permissions')) {
            abort(403, 'No tienes permiso para ver roles.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingGuard()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function deleteRole($roleId)
    {
        if (!Auth::user()->can('delete roles')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No tienes permiso para eliminar roles.',
                'duration' => 4000
            ]);
            return;
        }

        $role = Role::findOrFail($roleId);

        // Verificar si es un rol del sistema
        if (in_array($role->name, ['admin', 'super-admin'])) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se pueden eliminar roles del sistema.',
                'duration' => 4000
            ]);
            return;
        }

        try {
            $nombreRol = $role->name;
            $role->delete();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Rol '{$nombreRol}' eliminado exitosamente.",
                'duration' => 4000
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el rol: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'guard', 'sortBy', 'sortDirection', 'perPage']);
        $this->sortBy = 'name';
        $this->sortDirection = 'asc';
        $this->perPage = 10;
    }

    public function render()
    {
        $query = Role::with(['permissions']);

        // Aplicar búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('guard_name', 'like', '%' . $this->search . '%');
            });
        }

        // Filtrar por guard
        if ($this->guard) {
            $query->where('guard_name', $this->guard);
        }

        // Ordenar
        $query->orderBy($this->sortBy, $this->sortDirection);

        // Obtener roles paginados
        $roles = $query->paginate($this->perPage);

        // Calcular estadísticas
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        $rolesWithPermissions = Role::has('permissions')->count();
        $rolesWithoutPermissions = Role::doesntHave('permissions')->count();
        $guards = Role::select('guard_name')->distinct()->pluck('guard_name');

        return view('livewire.admin.roles.index', [
            'roles' => $roles,
            'totalRoles' => $totalRoles,
            'totalPermissions' => $totalPermissions,
            'rolesWithPermissions' => $rolesWithPermissions,
            'rolesWithoutPermissions' => $rolesWithoutPermissions,
            'guards' => $guards
        ])->layout($this->getLayout(), ['title' => 'Roles']);
    }
}