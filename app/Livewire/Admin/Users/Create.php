<?php

namespace App\Livewire\Admin\Users;

use App\Traits\HasDynamicLayout;
use Livewire\Component;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Sucursal;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserWelcomeMail;
use Illuminate\Validation\Rules;

class Create extends Component
{
    use HasDynamicLayout;


    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $empresa_id;
    public $sucursal_id;
    public $status = true;
    public $role;
    public $sucursales = [];
    public $username;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
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

    /**
     * Generar username automáticamente a partir del nombre
     * Formato: primera letra del primer nombre + primer apellido
     * Si existe, agregar inicial del segundo nombre
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
        
        // Verificar si el username base existe
        $username = $baseUsername;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
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

    public function save()
    {
        $this->validate();

        $plainPassword = $this->password;

        $user = new User();
        $user->name = $this->name;
        $user->username = $this->username;
        $user->email = $this->email;
        $user->password = Hash::make($plainPassword);
        $user->empresa_id = $this->empresa_id;
        $user->sucursal_id = $this->sucursal_id;
        $user->status = $this->status;
        $user->save();

        $user->assignRole($this->role);

        // Enviar correo de bienvenida
        try {
            Mail::to($user->email)->send(new UserWelcomeMail($user, $plainPassword));
            $mensajeCorreo = ' Se ha enviado un correo con las credenciales.';
        } catch (\Exception $e) {
            \Log::error('Error enviando correo de bienvenida: ' . $e->getMessage());
            $mensajeCorreo = '';
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Usuario '{$user->name}' creado exitosamente.{$mensajeCorreo}",
            'duration' => 5000
        ]);

        return redirect()->route('admin.users.index');
    }

    public function render()
    {
         \Gate::authorize('create users');

        $empresas = Empresa::forUser()->get();
        $sucursales = Sucursal::forUser()->where('status', 'active')
            ->when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            })
            ->get();

        $roles = Role::all();

        // Calcular estadísticas
        $totalUsers = User::forUser()->count();
        $activeUsers = User::forUser()->where('status', 1)->count();
        $pendingUsers = 0;
        $inactiveUsers = User::forUser()->where('status', 0)->count();

        return $this->renderWithLayout('livewire.admin.users.create', compact('empresas', 'sucursales', 'roles', 'totalUsers', 'activeUsers', 'pendingUsers', 'inactiveUsers'), [
            'title' => 'Lista de Usuarios',
            'breadcrumb' => [
                'admin.dashboard' => 'Dashboard',
                'admin.users.index' => 'Usuarios'
            ]
        ]);
    }
}