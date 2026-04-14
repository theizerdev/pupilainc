<?php

namespace App\Livewire\Admin\Users;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Sucursal;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;

class Edit extends Component
{
    use HasDynamicLayout;


    public User $user;
    public $name;
    public $email;
    public $phone;
    public $whatsapp_verification_enabled;
    public $password;
    public $password_confirmation;
    public $empresa_id;
    public $sucursal_id;
    public $status;
    public $role;
    public $sucursales = [];
    public $username; // Solo para mostrar, no se edita

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->username = $user->username; // Cargar username para mostrar
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->whatsapp_verification_enabled = $user->whatsapp_verification_enabled;
        $this->empresa_id = $user->empresa_id;
        $this->sucursal_id = $user->sucursal_id;
        $this->status = $user->status;
        $this->role = $user->getRoleNames()->first();
        $this->sucursales = Sucursal::forUser()
            ->where('empresa_id', $user->empresa_id)
            ->where('status', true)
            ->get();
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()]+$/'],
            'whatsapp_verification_enabled' => ['boolean'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'empresa_id' => ['required', 'exists:empresas,id'],
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'status' => ['boolean'],
            'role' => ['required', 'exists:roles,name']
        ];
    }

    public function updatedEmpresaId($value)
    {
        $this->loadSucursales();
    }

    public function loadSucursales()
    {
        if ($this->empresa_id) {
            $this->sucursales = Sucursal::forUser()
                ->where('empresa_id', $this->empresa_id)
                ->where('status', true)
                ->get();
        } else {
            $this->sucursales = [];
        }
        $this->sucursal_id = null;
    }

    public function update()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'whatsapp_verification_enabled' => $this->whatsapp_verification_enabled,
            'empresa_id' => $this->empresa_id,
            'sucursal_id' => $this->sucursal_id,
            'status' => $this->status
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $user = User::find($this->user->id);
        $user->name = $this->name;
        $user->email = $this->email;
        $user->phone = $this->phone;
        $user->password = $data['password'] ?? $user->password;
        $user->empresa_id = $this->empresa_id;
        $user->sucursal_id = $this->sucursal_id;
        $user->status = $this->status;
        $user->save();

        // Sincronizar rol del usuario
        $user->syncRoles([$this->role]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Usuario '{$user->name}' actualizado exitosamente.",
            'duration' => 4000
        ]);

        return redirect()->route('admin.users.index');
    }

    public function render()
    {
        \Gate::authorize('edit users');

        $empresas = Empresa::forUser()->get();
        $sucursales = Sucursal::forUser()->where('status', 'active')
            ->when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            })
            ->get();

        $roles = Role::all();

        return view('livewire.admin.users.edit', [
            'user' => $this->user ?? null,
            'sessions' => $sessions ?? null,
            'username' => $this->username,
            'empresas' => $empresas,
            'sucursales' => $sucursales,
            'roles' => $roles
        ])->layout($this->getLayout(), [
            'title' => 'Detalles del Usuario'
        ]);
    }
}