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
use App\Services\WhatsAppService;

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
    public $phone;
    public $showPassword = false;
    public $showPasswordConfirmation = false;

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
            'role' => ['required', 'exists:roles,name'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone']
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

    /**
     * Generar contraseña segura automáticamente
     * Incluye mayúsculas, minúsculas, números y caracteres especiales
     */
    public function generatePassword()
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%&*';
        
        $password = '';
        
        // Asegurar al menos un carácter de cada tipo
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        
        // Completar con caracteres aleatorios hasta 12 caracteres
        $allChars = $lowercase . $uppercase . $numbers . $special;
        for ($i = 0; $i < 8; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Mezclar los caracteres para que no estén en orden predecible
        $password = str_shuffle($password);
        
        $this->password = $password;
        $this->password_confirmation = $password;
        
        // Mostrar notificación de que se generó la contraseña
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Contraseña segura generada: ' . $password,
            'duration' => 5000
        ]);
    }

    /**
     * Alternar la visibilidad del campo de contraseña
     */
    public function togglePasswordVisibility()
    {
        $this->showPassword = !$this->showPassword;
    }

    /**
     * Alternar la visibilidad del campo de confirmación de contraseña
     */
    public function togglePasswordConfirmationVisibility()
    {
        $this->showPasswordConfirmation = !$this->showPasswordConfirmation;
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
        $user->phone = $this->phone;
        $user->save();

        $user->assignRole($this->role);


        // Enviar mensaje de WhatsApp de bienvenida
        $this->enviarMensajeBienvenida($user, $plainPassword);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Usuario '{$user->name}' creado exitosamente! Se ha enviado un mensaje de  r WhatsApp.",
            'duration' => 5000
        ]);

        return redirect()->route('admin.users.index');
    }

    private function enviarMensajeBienvenida($user, $plainPassword = null)
    {
        try {
            // Verificar que el usuario tenga teléfono
            if (empty($user->phone)) {
                \Log::warning('No se puede enviar mensaje de WhatsApp: el usuario no tiene teléfono registrado', [
                    'user_id' => $user->id
                ]);
                return;
            }

            // Formatear el número de teléfono (agregar +51 si es peruano)
            $telefono = $this->formatearTelefono($user->phone);

            // Crear el mensaje de bienvenida
            $mensaje = $this->crearMensajeBienvenida($user, $plainPassword);

            // Enviar mensaje por WhatsApp
            $whatsAppService = new WhatsAppService($user->empresa_id);

            if ($whatsAppService->isConfigured()) {
                $resultado = $whatsAppService->sendMessage($telefono, $mensaje, true);

                if ($resultado) {
                    \Log::info('Mensaje de bienvenida enviado por WhatsApp', [
                        'user_id' => $user->id,
                        'telefono' => $telefono,
                        'message_id' => $resultado['messageId'] ?? null
                    ]);
                } else {
                    \Log::warning('No se pudo enviar el mensaje de WhatsApp', [
                        'user_id' => $user->id,
                        'telefono' => $telefono
                    ]);
                }
            } else {
                \Log::warning('WhatsApp no está configurado para esta empresa', [
                    'empresa_id' => $user->empresa_id
                ]);
            }

        } catch (\Exception $e) {
            // Si falla el envío del mensaje, no debe afectar la creación del usuario
            \Log::error('Error al enviar mensaje de WhatsApp al usuario: ' . $e->getMessage(), [
                'user_id' => $user->id
            ]);
        }
    }

    protected function formatearTelefono(string $telefono): string
    {
        $limpio = preg_replace('/\D/', '', $telefono);

        if (str_starts_with($limpio, '0')) {
            $limpio = substr($limpio, 1);
        }

        $codigo = $this->obtenerCodigoPais();

        if (!str_starts_with($limpio, $codigo) && strlen($limpio) >= 7 && strlen($limpio) <= 12) {
            $limpio = $codigo . $limpio;
        }

        return '+' . $limpio;
    }

    protected function obtenerCodigoPais(): string
    {
        $codigoPais = '51'; // Código por defecto para Perú

        $empId = auth()->user()->empresa_id;
        if (!$empId && auth()->check() && auth()->user()->empresa_id) {
            $empId = auth()->user()->empresa_id;
        }

        if ($empId) {
            $empresa = \DB::table('empresas')->where('id', $empId)->first();
            if ($empresa && $empresa->pais_id) {
                $pais = \DB::table('pais')->where('id', $empresa->pais_id)->first();
                if ($pais && $pais->codigo_telefonico) {
                    $codigoPais = ltrim($pais->codigo_telefonico, '+');
                }
            }
        }

        return $codigoPais;
    }

    private function crearMensajeBienvenida($user, $plainPassword = null)
    {
        $empresa = $user->empresa;
        $sucursal = $user->sucursal;
        $passwordToShow = $plainPassword ?: 'La contraseña que registraste';
 
        $mensaje = "🎉 *¡Bienvenido(a) a {$empresa->razon_social}!* 🎉\n\n";
        $mensaje .= "Hola *{$user->name}*,\n\n";
        $mensaje .= "Tu cuenta ha sido creada exitosamente. Aquí están tus credenciales de acceso:\n\n";
        $mensaje .= "*🌐 Plataforma:* " . env('APP_URL', 'http://localhost') . "\n";
        $mensaje .= "*👤 Usuario:* {$user->username}\n";
        $mensaje .= "*🔑 Contraseña:* {$passwordToShow}\n\n";
        


        $mensaje .= "*📱 ¿Necesitas ayuda?*\n";
        $mensaje .= "No dudes en contactarnos. ¡Estamos aquí para ayudarte!\n\n";
        $mensaje .= "*⚠️ Importante:* Por seguridad, te recomendamos cambiar tu contraseña en tu primer inicio de sesión.\n\n";
        $mensaje .= "*¡Gracias por unirte a nuestro equipo!* 🚀";

        return $mensaje;
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