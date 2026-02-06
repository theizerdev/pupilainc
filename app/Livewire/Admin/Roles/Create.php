<?php

namespace App\Livewire\Admin\Roles;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Create extends Component
{
    use HasDynamicLayout;


    public $name;
    public $permissions = [];
    public $selectedPermissions = [];

    // Propiedades para agrupar permisos por módulo
    public $groupedPermissions = [];
    public $moduleStates = []; // Para mantener el estado de los toggles
    public $selectAll = false; // Para seleccionar todos los permisos

    public function mount()
    {
        // Verificar permiso para crear roles
        if (!Auth::user()->can('create roles')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        // Cargar todos los permisos y agruparlos
        $this->loadPermissions();
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
            $this->moduleStates[$module] = false;
        }

        // Inicializar el array de permisos seleccionados
        $this->selectedPermissions = [];
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
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
        $allPermissions = [];
        foreach ($this->groupedPermissions as $permissions) {
            foreach ($permissions as $permission) {
                $allPermissions[] = $permission->id;
            }
        }

        $this->selectAll = count($this->selectedPermissions) == count($allPermissions);
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

    public function save()
    {
        // Verificar permiso para crear roles
        if (!Auth::user()->can('create roles')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No tienes permiso para crear roles.',
                'duration' => 4000
            ]);
            return;
        }

        $this->validate();

        try {
            // Crear el rol
            $role = Role::create(['name' => $this->name]);

            // Asignar permisos seleccionados
            if (!empty($this->selectedPermissions)) {
                $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
                $role->syncPermissions($permissions);
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Rol '{$role->name}' creado exitosamente.",
                'duration' => 4000
            ]);
            return redirect()->route('admin.roles.index');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Ocurrió un error al crear el rol: ' . $e->getMessage(),
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
        $this->updatedSelectedPermissions();
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            // Deseleccionar todos
            $this->selectedPermissions = [];

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

            // Actualizar estados de módulos
            foreach ($this->groupedPermissions as $module => $permissions) {
                $this->moduleStates[$module] = true;
            }
        }

        // Usar updatedSelectedPermissions para determinar el estado de selectAll
        // Esto asegura consistencia con la lógica de verificación
        $this->updatedSelectedPermissions();
    }

    public function render()
    {
        return view('livewire.admin.roles.create')->layout($this->getLayout());
    }
}