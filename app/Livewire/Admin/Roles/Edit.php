<?php

namespace App\Livewire\Admin\Roles;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    use HasDynamicLayout;


    public $role;
    public $name;
    public $selectedPermissions = [];

    // Propiedades para agrupar permisos por módulo
    public $groupedPermissions = [];
    public $moduleStates = []; // Para mantener el estado de los toggles
    public $selectAll = false; // Para seleccionar todos los permisos

    public function mount(Role $role)
    {
        // Verificar permiso para editar roles
        if (!Auth::user()->can('edit roles')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $this->role = $role;
        $this->name = $role->name;

        // Cargar permisos actuales del rol
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();

        // Cargar todos los permisos y agruparlos
        $this->loadPermissions();

        // Verificar si todos los permisos están seleccionados inicialmente
        $this->checkSelectAllState();
    }

    public function loadPermissions()
    {
        $allPermissions = Permission::all();

        // Agrupar permisos por módulo usando el campo module
        foreach ($allPermissions as $permission) {
            $module = $permission->module ?? 'general';

            if (!isset($this->groupedPermissions[$module])) {
                $this->groupedPermissions[$module] = [];
            }

            $this->groupedPermissions[$module][] = $permission;
        }

        // Ordenar módulos alfabéticamente
        ksort($this->groupedPermissions);

        // Inicializar estados de módulos
        foreach ($this->groupedPermissions as $module => $permissions) {
            $this->checkModuleState($module);
        }
    }

    public function checkModuleState($module)
    {
        $modulePermissions = $this->groupedPermissions[$module];
        $allSelected = true;

        foreach ($modulePermissions as $permission) {
            if (!in_array($permission->id, $this->selectedPermissions)) {
                $allSelected = false;
                break;
            }
        }

        $this->moduleStates[$module] = $allSelected;
    }

    public function checkSelectAllState()
    {
        $allPermissions = [];
        foreach ($this->groupedPermissions as $permissions) {
            foreach ($permissions as $permission) {
                $allPermissions[] = $permission->id;
            }
        }

        $this->selectAll = count($this->selectedPermissions) == count($allPermissions);
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($this->role->id)],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['exists:permissions,id'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Ya existe un rol con este nombre.',
            'selectedPermissions.*.exists' => 'Uno o más permisos seleccionados no son válidos.',
        ];
    }

    public function updatedSelectedPermissions()
    {
        // Actualizar estados de los módulos cuando se seleccionan permisos individualmente
        foreach ($this->groupedPermissions as $module => $permissions) {
            $this->checkModuleState($module);
        }

        // Verificar si todos los permisos están seleccionados
        $this->checkSelectAllState();
    }

    public function save()
    {
        // Verificar permiso para editar roles
        if (!Auth::user()->can('edit roles')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No tienes permiso para editar roles.',
                'duration' => 4000
            ]);
            return;
        }

        // No permitir editar roles del sistema
        if (in_array($this->role->name, ['super-admin', 'admin', 'empresa-admin', 'user'])) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No puedes editar roles del sistema.',
                'duration' => 4000
            ]);
            return;
        }

        $this->validate();

        try {
            // Actualizar el rol
            $this->role->update(['name' => $this->name]);

            // Sincronizar permisos seleccionados
            if (!empty($this->selectedPermissions)) {
                $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
                $this->role->syncPermissions($permissions);
            } else {
                $this->role->syncPermissions([]);
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Rol '{$this->role->name}' actualizado exitosamente.",
                'duration' => 4000
            ]);
            return redirect()->route('admin.roles.index');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Ocurrió un error al actualizar el rol: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function toggleAllPermissions($module)
    {
        $modulePermissions = $this->groupedPermissions[$module];
        $allSelected = true;

        // Verificar si todos los permisos del módulo están seleccionados
        foreach ($modulePermissions as $permission) {
            if (!in_array($permission->id, $this->selectedPermissions)) {
                $allSelected = false;
                break;
            }
        }

        // Si todos están seleccionados, deseleccionarlos; de lo contrario, seleccionarlos todos
        if ($allSelected) {
            foreach ($modulePermissions as $permission) {
                $key = array_search($permission->id, $this->selectedPermissions);
                if ($key !== false) {
                    unset($this->selectedPermissions[$key]);
                }
            }
        } else {
            foreach ($modulePermissions as $permission) {
                if (!in_array($permission->id, $this->selectedPermissions)) {
                    $this->selectedPermissions[] = $permission->id;
                }
            }
        }

        // Reindexar el array
        $this->selectedPermissions = array_values($this->selectedPermissions);

        // Actualizar estado del módulo
        $this->moduleStates[$module] = !$allSelected;

        // Verificar estado de selección completa
        $this->checkSelectAllState();
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            // Deseleccionar todos
            $this->selectedPermissions = [];
            $this->selectAll = false;

            // Actualizar estados de módulos
            foreach ($this->groupedPermissions as $module => $permissions) {
                $this->moduleStates[$module] = false;
            }
        } else {
            // Seleccionar todos
            $this->selectedPermissions = [];
            foreach ($this->groupedPermissions as $permissions) {
                foreach ($permissions as $permission) {
                    $this->selectedPermissions[] = $permission->id;
                }
            }
            $this->selectAll = true;

            // Actualizar estados de módulos
            foreach ($this->groupedPermissions as $module => $permissions) {
                $this->moduleStates[$module] = true;
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.roles.edit')->layout($this->getLayout());
    }
}