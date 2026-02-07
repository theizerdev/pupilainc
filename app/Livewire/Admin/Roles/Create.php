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
    public $sectorPermissions = [];
    public $sectorStates = [];
    public $moduleStates = [];
    public $selectAll = false;
    public $activeSector = '';
    public $sectors = [];

    public function mount()
    {
        if (!Auth::user()->can('create roles')) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $this->loadPermissions();
    }

    public function loadPermissions()
    {
        $allPermissions = Permission::orderBy('sector')
            ->orderBy('module')
            ->orderBy('name')
            ->get();

        $this->sectorPermissions = [];
        $this->sectorStates = [];
        $this->moduleStates = [];

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
            $this->sectorStates[$sector] = false;
            foreach ($modules as $module => $permissions) {
                $this->moduleStates[$sector . '.' . $module] = false;
            }
        }

        $sectorKeys = array_keys($this->sectorPermissions);
        $this->activeSector = !empty($sectorKeys) ? $sectorKeys[0] : '';

        $this->sectors = getPermissionSectors();

        $this->selectedPermissions = [];
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
        }

        $this->selectedPermissions = array_values($this->selectedPermissions);
        $this->updatedSelectedPermissions();
    }

    public function toggleModulePermissions($sector, $module)
    {
        if (!isset($this->sectorPermissions[$sector][$module])) {
            return;
        }

        $moduleKey = $sector . '.' . $module;
        $allSelected = $this->moduleStates[$moduleKey] ?? false;

        foreach ($this->sectorPermissions[$sector][$module] as $permission) {
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

        $this->selectedPermissions = array_values($this->selectedPermissions);
        $this->updatedSelectedPermissions();
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedPermissions = [];
        } else {
            $this->selectedPermissions = [];
            foreach ($this->sectorPermissions as $sector => $modules) {
                foreach ($modules as $module => $permissions) {
                    foreach ($permissions as $permission) {
                        $this->selectedPermissions[] = $permission->id;
                    }
                }
            }
        }

        $this->updatedSelectedPermissions();
    }

    public function updatedSelectedPermissions()
    {
        $totalPermissions = 0;

        foreach ($this->sectorPermissions as $sector => $modules) {
            $sectorAllSelected = true;

            foreach ($modules as $module => $permissions) {
                $moduleAllSelected = true;

                foreach ($permissions as $permission) {
                    $totalPermissions++;
                    if (!in_array($permission->id, $this->selectedPermissions)) {
                        $moduleAllSelected = false;
                        $sectorAllSelected = false;
                    }
                }

                $this->moduleStates[$sector . '.' . $module] = $moduleAllSelected;
            }

            $this->sectorStates[$sector] = $sectorAllSelected;
        }

        $this->selectAll = count($this->selectedPermissions) == $totalPermissions && $totalPermissions > 0;
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

    public function save()
    {
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
            $role = Role::create(['name' => $this->name]);

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

    public function render()
    {
        return view('livewire.admin.roles.create')->layout($this->getLayout());
    }
}
