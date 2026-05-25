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
    public $sectorPermissions = [];
    public $sectorStates = [];
    public $moduleStates = [];
    public $selectAll = false;
    public $activeSector = '';
    public $sectors = [];

    public function mount(Role $role)
    {
        if (!Auth::user()->can('edit roles')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->loadPermissions();
    }

    public function loadPermissions()
    {
        $this->sectors = getPermissionSectors();

        $allPermissions = Permission::orderBy('sector')->orderBy('module')->orderBy('name')->get();

        foreach ($allPermissions as $permission) {
            $sector = $permission->sector ?? 'sistema';
            $module = $permission->module ?? 'general';

            if (!isset($this->sectorPermissions[$sector])) {
                $this->sectorPermissions[$sector] = [];
            }
            if (!isset($this->sectorPermissions[$sector][$module])) {
                $this->sectorPermissions[$sector][$module] = [];
            }

            $this->sectorPermissions[$sector][$module][] = $permission;
        }

        foreach ($this->sectorPermissions as $sector => $modules) {
            $allSectorSelected = true;
            foreach ($modules as $module => $permissions) {
                $allModuleSelected = true;
                foreach ($permissions as $permission) {
                    if (!in_array($permission->id, $this->selectedPermissions)) {
                        $allModuleSelected = false;
                        $allSectorSelected = false;
                    }
                }
                $this->moduleStates[$sector . '.' . $module] = $allModuleSelected;
            }
            $this->sectorStates[$sector] = $allSectorSelected;
        }

        $sectorKeys = array_keys($this->sectorPermissions);
        $this->activeSector = !empty($sectorKeys) ? $sectorKeys[0] : '';

        $this->updateSelectAllState();
    }

    public function setActiveSector($sector)
    {
        $this->activeSector = $sector;
    }

    public function toggleSectorPermissions($sector)
    {
        if (!isset($this->sectorPermissions[$sector])) {
            return;
        }

        $allSelected = $this->sectorStates[$sector] ?? false;

        foreach ($this->sectorPermissions[$sector] as $module => $permissions) {
            foreach ($permissions as $permission) {
                if ($allSelected) {
                    $key = array_search($permission->id, $this->selectedPermissions);
                    if ($key !== false) {
                        unset($this->selectedPermissions[$key]);
                    }
                } else {
                    if (!in_array($permission->id, $this->selectedPermissions)) {
                        $this->selectedPermissions[] = $permission->id;
                    }
                }
            }
            $this->moduleStates[$sector . '.' . $module] = !$allSelected;
        }

        $this->selectedPermissions = array_values($this->selectedPermissions);
        $this->sectorStates[$sector] = !$allSelected;
        $this->updateSelectAllState();
    }

    public function toggleModulePermissions($sector, $module)
    {
        if (!isset($this->sectorPermissions[$sector][$module])) {
            return;
        }

        $key = $sector . '.' . $module;
        $allSelected = $this->moduleStates[$key] ?? false;

        foreach ($this->sectorPermissions[$sector][$module] as $permission) {
            if ($allSelected) {
                $idx = array_search($permission->id, $this->selectedPermissions);
                if ($idx !== false) {
                    unset($this->selectedPermissions[$idx]);
                }
            } else {
                if (!in_array($permission->id, $this->selectedPermissions)) {
                    $this->selectedPermissions[] = $permission->id;
                }
            }
        }

        $this->selectedPermissions = array_values($this->selectedPermissions);
        $this->moduleStates[$key] = !$allSelected;

        $allSectorSelected = true;
        foreach ($this->sectorPermissions[$sector] as $mod => $permissions) {
            if (!($this->moduleStates[$sector . '.' . $mod] ?? false)) {
                $allSectorSelected = false;
                break;
            }
        }
        $this->sectorStates[$sector] = $allSectorSelected;
        $this->updateSelectAllState();
    }

    public function updatedSelectedPermissions()
    {
        foreach ($this->sectorPermissions as $sector => $modules) {
            $allSectorSelected = true;
            foreach ($modules as $module => $permissions) {
                $allModuleSelected = true;
                foreach ($permissions as $permission) {
                    if (!in_array($permission->id, $this->selectedPermissions)) {
                        $allModuleSelected = false;
                        $allSectorSelected = false;
                    }
                }
                $this->moduleStates[$sector . '.' . $module] = $allModuleSelected;
            }
            $this->sectorStates[$sector] = $allSectorSelected;
        }

        $this->updateSelectAllState();
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedPermissions = [];
            foreach ($this->sectorPermissions as $sector => $modules) {
                $this->sectorStates[$sector] = false;
                foreach ($modules as $module => $permissions) {
                    $this->moduleStates[$sector . '.' . $module] = false;
                }
            }
        } else {
            $this->selectedPermissions = [];
            foreach ($this->sectorPermissions as $sector => $modules) {
                foreach ($modules as $module => $permissions) {
                    foreach ($permissions as $permission) {
                        $this->selectedPermissions[] = $permission->id;
                    }
                    $this->moduleStates[$sector . '.' . $module] = true;
                }
                $this->sectorStates[$sector] = true;
            }
        }

        $this->updateSelectAllState();
    }

    private function updateSelectAllState()
    {
        $totalPermissions = 0;
        foreach ($this->sectorPermissions as $modules) {
            foreach ($modules as $permissions) {
                $totalPermissions += count($permissions);
            }
        }

        $this->selectAll = count($this->selectedPermissions) === $totalPermissions && $totalPermissions > 0;
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

    public function save()
    {
        if (!Auth::user()->can('edit roles')) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No tienes permiso para editar roles.',
                'duration' => 4000
            ]);
            return;
        }

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
            $this->role->update(['name' => $this->name]);

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
            return redirect()->to('admin/roles');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Ocurrió un error al actualizar el rol: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.roles.edit')->layout($this->getLayout());
    }
}
