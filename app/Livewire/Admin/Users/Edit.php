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
    public $username; // Ahora editable

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->username = $user->username;
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

    /**
     * Generar username automáticamente a partir del nombre
     * Formato: primera letra del primer nombre + primer apellido
     */
    public function generateUsername()
    {
        if (empty($this->name)) {
            return;
        }

        // Limpiar el nombre: eliminar acentos y convertir a minúsculas
        $name = strtolower($this->name);
        $name = $this->removeAccents($name);
        
        // Dividir el nombre en palabras
        $words = explode(' ', trim($name));
        
        if (count($words) < 2) {
            return;
        }

        // Obtener la primera letra del primer nombre
        $firstInitial = substr($words[0], 0, 1);
        
        // Obtener el primer apellido (última palabra)
        $lastName = end($words);
        
        // Generar el username base
        $baseUsername = $firstInitial . $lastName;
        
        // Verificar si el username base existe (excluyendo el usuario actual)
        $username = $baseUsername;
        $counter = 1;
        
        while (User::where('username', $username)->where('id', '!=', $this->user->id)->exists()) {
            // Si existe y hay segundo nombre, agregar su inicial
            if (count($words) > 2 && $counter === 1) {
                $secondInitial = substr($words[1], 0, 1);
                $username = $firstInitial . $secondInitial . $lastName;
            } else {
                // Si aún existe, agregar número incremental
                $username = $baseUsername . $counter;
            }
            $counter++;
            
            // Prevenir bucle infinito
            if ($counter > 10) {
                break;
            }
        }
        
        $this->username = $username;
    }

    /**
     * Eliminar acentos de una cadena
     */
    private function removeAccents($string)
    {
        $search = ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'];
        $replace = ['a', 'e', 'i', 'o', 'u', 'n', 'u'];
        
        return str_replace($search, $replace, $string);
    }

    /**
     * Actualizar username cuando cambia el nombre
     */
    public function updatedName($value)
    {
        $this->generateUsername();
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('users', 'username')->ignore($this->user->id)],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user->id],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+()]+$/', \Illuminate\Validation\Rule::unique('users', 'phone')->ignore($this->user->id)],
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
            'username' => $this->username,
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
        $user->username = $this->username;
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