<?php

namespace App\Livewire\Admin\Permissions;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class Create extends Component
{
    use HasDynamicLayout;


    public $name = '';
    public $module = '';
    public $guard_name = 'web';
    public $existingModules = [];

    protected $rules = [
        'name' => 'required|string|max:255|unique:permissions,name',
        'module' => 'required|string|max:255',
        'guard_name' => 'required|string|max:255'
    ];

    public function mount()
    {
        $this->loadExistingModules();
    }

    public function loadExistingModules()
    {
        $this->existingModules = Permission::select('module')
            ->distinct()
            ->whereNotNull('module')
            ->orderBy('module')
            ->pluck('module')
            ->toArray();
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            Permission::create([
                'name' => $this->name,
                'module' => $this->module,
                'guard_name' => $this->guard_name
            ]);
        });

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Permiso '{$this->name}' creado exitosamente.",
            'duration' => 4000
        ]);
        return redirect()->route('admin.permissions.index');
    }

    public function render()
    {
        return view('livewire.admin.permissions.create')->layout($this->getLayout());
    }
}