<?php

namespace App\Livewire\Admin\Enfermeros;

use App\Models\Enfermero;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Pais;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use App\Services\WhatsAppService;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use App\Traits\HasDynamicLayout;

class Create extends Component
{
    use WithFileUploads, HasDynamicLayout;

    // Datos del enfermero
    public $nombres;
    public $apellidos;
    public $genero;
    public $documento_identidad;
    public $telefono;
    public $direccion;
    public $licencia_enfermeria;
    public $anios_experiencia = 0;
    public $nivel_experiencia = 'Básico';
    public $tipo_enfermero = 'General';
    public $especialidad_enfermeria;
    public $especialidades = []; // Array para múltiples especialidades
    public $nueva_especialidad = ''; // Para agregar nuevas especialidades
    public $email;
    public $password;
    
    // Empresa y sucursal (se asignarán automáticamente)
    public $empresa_id;
    public $sucursal_id;
    
    // Datos del país para el formato del teléfono
    public $pais;
    
    // Horarios del enfermero
    public $horarios = [];

    protected function rules()
    {
        $rules = [
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'genero' => 'nullable|string|max:20',
            'documento_identidad' => 'required|string|max:50|unique:enfermeros,documento_identidad',
            'telefono' => 'nullable|string|max:10',
            'direccion' => 'nullable|string|max:500',
            'licencia_enfermeria' => 'required|string|max:50|unique:enfermeros,licencia_enfermeria',
            'anios_experiencia' => 'required|integer|min:0|max:50',
            'nivel_experiencia' => 'required|in:Básico,Intermedio,Avanzado',
            'tipo_enfermero' => 'required|in:General,Especialista,Supervisor,Jefe de Servicio',
            'especialidades' => 'nullable|array|max:10', // Máximo 10 especialidades
            'especialidades.*' => 'required|string|max:100|min:2',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8',
            
            // Validación de horarios
            'horarios' => 'required|array',
            'horarios.*.activo' => 'boolean',
            'horarios.*.hora_inicio' => 'required_if:horarios.*.activo,true|date_format:H:i',
            'horarios.*.hora_fin' => 'required_if:horarios.*.activo,true|date_format:H:i|after:horarios.*.hora_inicio',
            'horarios.*.duracion_cita' => 'required_if:horarios.*.activo,true|integer|min:15|max:180|multiple_of:15',
        ];

        // Para usuarios normales, no incluir reglas de empresa/sucursal
        if (auth()->user()->hasRole('Super Administrador')) {
            $rules['empresa_id'] = 'required|exists:empresas,id';
            $rules['sucursal_id'] = 'required|exists:sucursales,id';
        }

        return $rules;
    }

    public function mount()
    {
        // Inicializar valores por defecto
        $this->password = '';
        
        // Inicializar horarios por defecto (Lunes a Viernes activos)
        $diasSemana = [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes',
            6 => 'Sábado', 7 => 'Domingo'
        ];
        
        foreach ($diasSemana as $dia => $nombre) {
            $this->horarios[$dia] = [
                'activo' => in_array($dia, [1, 2, 3, 4, 5]), // Lunes a Viernes activos
                'hora_inicio' => '08:00',
                'hora_fin' => '16:00',
                'duracion_cita' => 30,
            ];
        }

        // Obtener el país del usuario logueado para el formato del teléfono
        if (auth()->user()->empresa) {
            $this->pais = auth()->user()->empresa->pais;
        }
    }

    public function store()
    {
        $this->authorize('create enfermeros');
        
        \Log::info('=== INICIANDO STORE DE ENFERMERO ===');
        \Log::info('Horarios antes de validación', ['horarios' => $this->horarios]);
        
        $validated = $this->validate();
        
        \Log::info('Horarios después de validación', ['horarios' => $this->horarios]);

        try {
           \DB::beginTransaction();

           $plainPassword = $validated['password'] ?: $validated['documento_identidad'];

            // Crear el usuario
            $user = User::create([
                'name' => $validated['nombres'] . ' ' . $validated['apellidos'],
                'email' => $validated['email'],
                'password' => Hash::make($plainPassword),
                'username' => $this->generarUsername($validated['nombres'], $validated['apellidos']),
                'empresa_id' => auth()->user()->hasRole('Super Administrador') ? $validated['empresa_id'] : auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->hasRole('Super Administrador') ? $validated['sucursal_id'] : auth()->user()->sucursal_id,
                'status' => true,
                'phone' => $validated['telefono'],
            ]);

           
            // Asignar el rol de Enfermería
            $rolEnfermero = Role::where('name', 'Enfermería')->first();
            if ($rolEnfermero) {
                $user->assignRole($rolEnfermero);
            }

            // Crear el enfermero
            $enfermero = Enfermero::create([
                'user_id' => $user->id,
                'nombres' => $validated['nombres'],
                'apellidos' => $validated['apellidos'],
                'genero' => $this->genero ?? null,
                'documento_identidad' => $validated['documento_identidad'],
                'telefono' => $validated['telefono'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'licencia_enfermeria' => $validated['licencia_enfermeria'],
                'anios_experiencia' => $validated['anios_experiencia'],
                'nivel_experiencia' => $validated['nivel_experiencia'],
                'tipo_enfermero' => $validated['tipo_enfermero'],
                'especialidad_enfermeria' => null, // Ya no usamos este campo
                'status' => true,
                'empresa_id' => $user->empresa_id,
                'sucursal_id' => $user->sucursal_id,
            ]);

             // Guardar especialidades
            foreach ($this->especialidades as $especialidad) {
                $enfermero->especialidades()->create([
                    'especialidad' => $especialidad,
                    'empresa_id' => $enfermero->empresa_id,
                    'sucursal_id' => $enfermero->sucursal_id,
                ]);
            }

            // Enviar mensaje de WhatsApp de bienvenida
            $enfermero->refresh(); // Recargar el modelo con las relaciones
            $this->enviarMensajeBienvenida($user, $enfermero, $plainPassword);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Enfermero/a '{$enfermero->nombres} {$enfermero->apellidos}' creado exitosamente. Se ha enviado un mensaje de bienvenida por WhatsApp.",
                'duration' => 5000
            ]);
              \DB::commit();
            
            return redirect()->to('admin/enfermeros/'.$enfermero->id.'/horarios');

        } catch (\Exception $e) {
            \DB::rollback();
            dd($e);
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear el enfermero: ' . $e->getMessage(),
                'duration' => 6000
            ]);
        }
    }

    public function agregarEspecialidad()
    {
        $this->validate([
            'nueva_especialidad' => 'required|string|max:100|min:2'
        ]);

        // Evitar duplicados
        if (!in_array($this->nueva_especialidad, $this->especialidades)) {
            $this->especialidades[] = $this->nueva_especialidad;
        }

        $this->nueva_especialidad = '';
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Especialidad agregada.',
            'duration' => 2000
        ]);
    }

    public function eliminarEspecialidad($index)
    {
        if (isset($this->especialidades[$index])) {
            array_splice($this->especialidades, $index, 1);
            $this->dispatch('notify', [
                'type' => 'info',
                'message' => 'Especialidad eliminada.',
                'duration' => 2000
            ]);
        }
    }

    private function generarUsername($nombres, $apellidos)
    {
        // Procesar nombres
        $nombresArray = explode(' ', strtolower(trim($nombres)));
        $apellidosArray = explode(' ', strtolower(trim($apellidos)));
        
        // Obtener la primera letra del primer nombre
        $inicialNombre = substr($nombresArray[0], 0, 1);
        
        // Obtener el primer apellido
        $primerApellido = $apellidosArray[0] ?? '';
        
        // Generar el username base: inicial + apellido
        $base = $inicialNombre . $primerApellido;
        $username = $base;
        $counter = 1;

        // Si existe, intentar con la inicial del segundo nombre si existe
        if (User::where('username', $username)->exists() && count($nombresArray) > 1) {
            $inicialSegundoNombre = substr($nombresArray[1], 0, 1);
            $username = $inicialNombre . $inicialSegundoNombre . $primerApellido;
            
            // Si aún existe, agregar números
            while (User::where('username', $username)->exists()) {
                $username = $base . $counter;
                $counter++;
            }
        } else {
            // Si el username base existe pero no hay segundo nombre, agregar números
            while (User::where('username', $username)->exists()) {
                $username = $base . $counter;
                $counter++;
            }
        }

        return $username;
    }

    private function enviarMensajeBienvenida($user, $enfermero, $plainPassword = null)
    {
        try {
            // Verificar que el enfermero tenga teléfono
            if (empty($enfermero->telefono)) {
                \Log::warning('No se puede enviar mensaje de WhatsApp: el enfermero no tiene teléfono registrado', [
                    'enfermero_id' => $enfermero->id,
                    'user_id' => $user->id
                ]);
                return;
            }

            // Formatear el número de teléfono (agregar +51 si es peruano)
            $telefono = $this->formatearTelefono($enfermero->telefono);
            
            // Crear el mensaje de bienvenida
            $mensaje = $this->crearMensajeBienvenida($user, $enfermero, $plainPassword);
            
            // Enviar mensaje por WhatsApp
            $whatsAppService = new WhatsAppService($user->empresa_id);
            
            if ($whatsAppService->isConfigured() && \App\Services\WhatsAppNotificationGate::allows($user->empresa_id, 'enfermeros', 'bienvenida', 'enfermero')) {
                $resultado = $whatsAppService->sendMessage($telefono, $mensaje, true);
                
                if ($resultado) {
                    \Log::info('Mensaje de bienvenida enviado por WhatsApp', [
                        'enfermero_id' => $enfermero->id,
                        'telefono' => $telefono,
                        'message_id' => $resultado['messageId'] ?? null
                    ]);
                } else {
                    \Log::warning('No se pudo enviar el mensaje de WhatsApp', [
                        'enfermero_id' => $enfermero->id,
                        'telefono' => $telefono
                    ]);
                }
            } else {
                \Log::warning('WhatsApp no está configurado para esta empresa', [
                    'empresa_id' => $user->empresa_id
                ]);
            }
            
        } catch (\Exception $e) {
            // Si falla el envío del mensaje, no debe afectar la creación del enfermero
            \Log::error('Error al enviar mensaje de WhatsApp al enfermero: ' . $e->getMessage(), [
                'enfermero_id' => $enfermero->id,
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


    private function crearMensajeBienvenida($user, $enfermero, $plainPassword = null)
    {
        $empresa = $user->empresa;
        $sucursal = $user->sucursal;
        $passwordToShow = $plainPassword ?: $enfermero->documento_identidad;
        
        // Mensaje de bienvenida personalizado
        $mensaje = "🏥 ¡Bienvenido/a {$enfermero->nombres} {$enfermero->apellidos}!\n\n";
        $mensaje .= "✅ Su cuenta ha sido creada exitosamente en nuestro sistema de salud.\n\n";
        $mensaje .= "📋 *Datos de acceso:*\n";
        $mensaje .= "• Usuario: {$user->username}\n";
        $mensaje .= "• Email: {$user->email}\n";
        $mensaje .= "• Contraseña temporal: {$passwordToShow}\n\n";
        $mensaje .= "🏥 *Información institucional:*\n";
        $mensaje .= "• Empresa: {$empresa->razon_social}\n";
        $mensaje .= "• Sucursal: {$sucursal->nombre}\n";
        $mensaje .= "• Tipo de enfermero: {$enfermero->tipo_enfermero}\n";
        
        // Obtener especialidades
        $especialidades = $enfermero->especialidades()->pluck('especialidad')->toArray();
        if (count($especialidades) > 0) {
            $mensaje .= "• Especialidades: " . implode(', ', $especialidades) . "\n";
        }
        $mensaje .= "\n🔐 *Importante:* Por seguridad, le recomendamos cambiar su contraseña al iniciar sesión.\n\n";
        $mensaje .= "📱 ¿Preguntas? Contáctenos al {$empresa->telefono}\n\n";
        $mensaje .= "¡Gracias por formar parte de nuestro equipo de enfermería! 🏥💉";
        
        return $mensaje;
    }

    public function getEmpresasProperty()
    {
        return Empresa::orderBy('razon_social')->get();
    }

    public function getSucursalesProperty()
    {
        if ($this->empresa_id) {
            return Sucursal::where('empresa_id', $this->empresa_id)->orderBy('nombre')->get();
        }
        return collect();
    }

    public function render()
    {
        return view('livewire.admin.enfermeros.create', [
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
        ])->layout($this->getLayout());
    }

    
    public function formatPhone()
    {
        if ($this->telefono) {
            $this->telefono = preg_replace('/[^0-9+]/', '', $this->telefono);
        }
    }
}
