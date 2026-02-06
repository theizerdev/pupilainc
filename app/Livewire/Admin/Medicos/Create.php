<?php

namespace App\Livewire\Admin\Medicos;

use App\Models\Medico;
use App\Models\User;
use App\Models\Especialidad;
use App\Models\Subespecialidad;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Pais;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Services\WhatsAppService;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

use App\Traits\HasDynamicLayout;

class Create extends Component
{
    use WithFileUploads, HasDynamicLayout;

    // Datos del médico
    public $nombres;
    public $apellidos;
    public $documento_identidad;
    public $telefono;
    public $direccion;
    public $licencia_medica;
    public $anios_experiencia = 0;
    public $nivel_experiencia = 'Básico';
    public $email;
    public $password;
    
    // Especialidades y subespecialidades
    public $especialidad_id;
    public $subespecialidades_seleccionadas = [];
    public $tarifa_consulta;
    public $horario_atencion = [];
    
    // Datos de subespecialidades (experiencia, nivel, tarifa)
    public $subespecialidades_data = [];
    
    // Empresa y sucursal (se asignarán automáticamente)
    public $empresa_id;
    public $sucursal_id;
    
    // Datos del país para el formato del teléfono
    public $pais;
    
    // Horarios del médico
    public $horarios = [];

    protected function rules()
    {
        $rules = [
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'documento_identidad' => 'required|string|max:50|unique:medicos,documento_identidad',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'licencia_medica' => 'required|string|max:50|unique:medicos,licencia_medica',
            'anios_experiencia' => 'required|integer|min:0|max:50',
            'nivel_experiencia' => 'required|in:Básico,Intermedio,Avanzado',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8',
            'especialidad_id' => 'required|exists:especialidades,id',
            'tarifa_consulta' => 'nullable|numeric|min:0|max:999999.99',
            'subespecialidades_seleccionadas' => 'nullable|array',
            'subespecialidades_seleccionadas.*' => 'exists:subespecialidades,id',
            'subespecialidades_data' => 'nullable|array',
            'subespecialidades_data.*.experiencia_anios' => 'required|integer|min:0|max:50',
            'subespecialidades_data.*.nivel_experiencia' => 'required|in:Básico,Intermedio,Avanzado',
            'subespecialidades_data.*.tarifa_consulta' => 'nullable|numeric|min:0|max:999999.99',
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
        $this->subespecialidades_data = [];
        
        // Inicializar horarios por defecto (Lunes a Viernes activos)
        $diasSemana = [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes',
            6 => 'Sábado', 7 => 'Domingo'
        ];
        
        foreach ($diasSemana as $dia => $nombre) {
            $this->horarios[$dia] = [
                'activo' => in_array($dia, [1, 2, 3, 4, 5]), // Lunes a Viernes activos
                'hora_inicio' => '09:00',
                'hora_fin' => '17:00',
                'duracion_cita' => 30,
            ];
        }

        // Obtener el país del usuario logueado para el formato del teléfono
        if (auth()->user()->empresa) {
            $this->pais = auth()->user()->empresa->pais;
        }
    }

    public function updatedEspecialidadId($value)
    {
        $this->reset('subespecialidades_seleccionadas');
        $this->subespecialidades_data = [];
    }

    public function updatedSubespecialidadesSeleccionadas($value)
    {
        // Inicializar datos para las subespecialidades seleccionadas
        foreach ($this->subespecialidades_seleccionadas as $subespecialidad_id) {
            if (!array_key_exists($subespecialidad_id, $this->subespecialidades_data)) {
                $this->subespecialidades_data[$subespecialidad_id] = [
                    'experiencia_anios' => 0,
                    'nivel_experiencia' => 'Básico',
                    'tarifa_consulta' => null,
                ];
            }
        }
        
        // Limpiar datos de subespecialidades no seleccionadas
        foreach ($this->subespecialidades_data as $id => $data) {
            if (!in_array($id, $this->subespecialidades_seleccionadas)) {
                unset($this->subespecialidades_data[$id]);
            }
        }
    }

    public function store()
    {
        $this->authorize('create medicos');
        
        $validated = $this->validate();

        try {
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
            ]);

            // Asignar el rol de Médico
            $rolMedico = Role::where('name', 'Médico')->first();
            if ($rolMedico) {
                $user->assignRole($rolMedico);
            }

            // Crear el médico
            $medico = Medico::create([
                'user_id' => $user->id,
                'nombres' => $validated['nombres'],
                'apellidos' => $validated['apellidos'],
                'documento_identidad' => $validated['documento_identidad'],
                'telefono' => $validated['telefono'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'licencia_medica' => $validated['licencia_medica'],
                'anios_experiencia' => $validated['anios_experiencia'],
                'nivel_experiencia' => $validated['nivel_experiencia'],
                'status' => true,
                'empresa_id' => $user->empresa_id,
                'sucursal_id' => $user->sucursal_id,
            ]);

            // Asignar especialidad
            $medico->asignarEspecialidad(
                $validated['especialidad_id'],
                $validated['tarifa_consulta'] ?? null
            );

            // Asignar subespecialidades con datos de experiencia y nivel
            if (!empty($validated['subespecialidades_seleccionadas'])) {
                // Log para depuración
                \Log::info('Datos de subespecialidades:', [
                    'seleccionadas' => $validated['subespecialidades_seleccionadas'],
                    'data' => $this->subespecialidades_data,
                    'data_keys' => array_keys($this->subespecialidades_data)
                ]);
                
                foreach ($validated['subespecialidades_seleccionadas'] as $subespecialidadId) {
                    // Verificar si existe el dato para esta subespecialidad
                    if (!isset($this->subespecialidades_data[$subespecialidadId])) {
                        \Log::warning('No se encontraron datos para subespecialidad: ' . $subespecialidadId);
                        continue;
                    }
                    
                    $subData = $this->subespecialidades_data[$subespecialidadId];
                    
                    \Log::info('Asignando subespecialidad:', [
                        'id' => $subespecialidadId,
                        'data' => $subData
                    ]);
                    
                    $medico->asignarSubespecialidad(
                        $subespecialidadId,
                        $subData['tarifa_consulta'] ?? null,
                        $subData['experiencia_anios'] ?? 0,
                        $subData['nivel_experiencia'] ?? 'Básico'
                    );
                }
            }

            // Guardar horarios del médico
            foreach ($this->horarios as $dia => $horario) {
                if ($horario['activo']) {
                    $medico->horarios()->create([
                        'dia_semana' => $dia,
                        'hora_inicio' => $horario['hora_inicio'],
                        'hora_fin' => $horario['hora_fin'],
                        'duracion_cita' => $horario['duracion_cita'],
                        'activo' => true,
                    ]);
                }
            }

            // Enviar mensaje de WhatsApp de bienvenida
            $this->enviarMensajeBienvenida($user, $medico, $plainPassword);

            session()->flash('success', 'Médico creado exitosamente. Se ha enviado un mensaje de bienvenida por WhatsApp.');
            
            return redirect()->route('admin.medicos.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al crear el médico: ' . $e->getMessage());
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

    private function enviarMensajeBienvenida($user, $medico, $plainPassword = null)
    {
        try {
            // Verificar que el médico tenga teléfono
            if (empty($medico->telefono)) {
                \Log::warning('No se puede enviar mensaje de WhatsApp: el médico no tiene teléfono registrado', [
                    'medico_id' => $medico->id,
                    'user_id' => $user->id
                ]);
                return;
            }

            // Formatear el número de teléfono (agregar +51 si es peruano)
            $telefono = $this->formatearTelefono($medico->telefono);
            
            // Crear el mensaje de bienvenida
            $mensaje = $this->crearMensajeBienvenida($user, $medico, $plainPassword);
            
            // Enviar mensaje por WhatsApp
            $whatsAppService = new WhatsAppService($user->empresa_id);
            
            if ($whatsAppService->isConfigured()) {
                $resultado = $whatsAppService->sendMessage($telefono, $mensaje, true);
                
                if ($resultado) {
                    \Log::info('Mensaje de bienvenida enviado por WhatsApp', [
                        'medico_id' => $medico->id,
                        'telefono' => $telefono,
                        'message_id' => $resultado['messageId'] ?? null
                    ]);
                } else {
                    \Log::warning('No se pudo enviar el mensaje de WhatsApp', [
                        'medico_id' => $medico->id,
                        'telefono' => $telefono
                    ]);
                }
            } else {
                \Log::warning('WhatsApp no está configurado para esta empresa', [
                    'empresa_id' => $user->empresa_id
                ]);
            }
            
        } catch (\Exception $e) {
            // Si falla el envío del mensaje, no debe afectar la creación del médico
            \Log::error('Error al enviar mensaje de WhatsApp al médico: ' . $e->getMessage(), [
                'medico_id' => $medico->id,
                'user_id' => $user->id
            ]);
        }
    }

    private function formatearTelefono($telefono)
    {
        // Eliminar espacios y caracteres no numéricos
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        
        // Si es un número peruano (9 dígitos y empieza con 9), agregar +51
        if (strlen($telefono) === 9 && $telefono[0] === '9') {
            return '+51' . $telefono;
        }
        
        // Si ya tiene código de país, dejarlo así
        if (strlen($telefono) > 10) {
            return '+' . $telefono;
        }
        
        // Por defecto, retornar el número limpio
        return $telefono;
    }

    private function crearMensajeBienvenida($user, $medico, $plainPassword = null)
    {
        $empresa = $user->empresa;
        $sucursal = $user->sucursal;
        $passwordToShow = $plainPassword ?: $medico->documento_identidad;
        
        // Obtener especialidades del médico
        $especialidades = $medico->especialidades->pluck('nombre')->implode(', ');
        if (empty($especialidades)) {
            $especialidades = 'No asignada';
        }
        
        // Mensaje de bienvenida personalizado
        $mensaje = "🩺 ¡Bienvenido/a Dr./Dra. {$medico->nombres} {$medico->apellidos}!\n\n";
        $mensaje .= "✅ Su cuenta ha sido creada exitosamente en nuestro sistema médico.\n\n";
        $mensaje .= "📋 *Datos de acceso:*\n";
        $mensaje .= "• Usuario: {$user->username}\n";
        $mensaje .= "• Email: {$user->email}\n";
        $mensaje .= "• Contraseña temporal: {$passwordToShow}\n\n";
        $mensaje .= "🏥 *Información institucional:*\n";
        $mensaje .= "• Empresa: {$empresa->razon_social}\n";
        $mensaje .= "• Sucursal: {$sucursal->nombre}\n";
        $mensaje .= "• Especialidad: {$especialidades}\n\n";
        $mensaje .= "🔐 *Importante:* Por seguridad, le recomendamos cambiar su contraseña al iniciar sesión.\n\n";
        $mensaje .= "📱 ¿Preguntas? Contáctenos al {$empresa->telefono}\n\n";
        $mensaje .= "¡Gracias por formar parte de nuestro equipo médico! 🏥✨";
        
        return $mensaje;
    }

    public function getEspecialidadesProperty()
    {
        return Especialidad::forUser()->where('status', true)->orderBy('nombre')->get();
    }

    public function getSubespecialidadesProperty()
    {
        if ($this->especialidad_id) {
            return Subespecialidad::forUser()
                ->where('especialidad_id', $this->especialidad_id)
                ->where('status', true)
                ->orderBy('nombre')
                ->get();
        }
        return collect();
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
        return view('livewire.admin.medicos.create', [
            'especialidades' => $this->especialidades,
            'subespecialidades' => $this->subespecialidades,
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
        ])->layout($this->getLayout());
    }
}