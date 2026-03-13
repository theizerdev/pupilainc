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


class Edit extends Component
{
    use WithFileUploads, HasDynamicLayout;

    public $enfermero;
    public $user;
    
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
    public $especialidades = [];
    public $nueva_especialidad = '';
    public $email;
    public $password;
    public $status;
    
    // Empresa y sucursal
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
            'documento_identidad' => [
                'required',
                'string',
                'max:50',
                Rule::unique('enfermeros')->ignore($this->enfermero->id)
            ],
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'licencia_enfermeria' => [
                'required',
                'string',
                'max:50',
                Rule::unique('enfermeros')->ignore($this->enfermero->id)
            ],
            'anios_experiencia' => 'required|integer|min:0|max:50',
            'nivel_experiencia' => 'required|in:Básico,Intermedio,Avanzado',
            'tipo_enfermero' => 'required|in:General,Especialista,Supervisor,Jefe de Servicio',
            'especialidades' => 'nullable|array',
            'especialidades.*' => 'string|max:100|min:2',
            'nueva_especialidad' => 'nullable|string|max:100|min:2',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($this->user->id)
            ],
            'password' => 'nullable|string|min:8',
            'status' => 'boolean',
        ];

        // Para usuarios normales, no incluir reglas de empresa/sucursal
        if (auth()->user()->hasRole('Super Administrador')) {
            $rules['empresa_id'] = 'required|exists:empresas,id';
            $rules['sucursal_id'] = 'required|exists:sucursales,id';
        }

        return $rules;
    }

    public function mount($id)
    {
        $this->enfermero = Enfermero::findOrFail($id);
        $this->user = $this->enfermero->user;
        
        // Cargar datos del enfermero
        $this->nombres = $this->enfermero->nombres;
        $this->apellidos = $this->enfermero->apellidos;
        $this->genero = $this->enfermero->genero;
        $this->documento_identidad = $this->enfermero->documento_identidad;
        $this->telefono = $this->enfermero->telefono;
        $this->direccion = $this->enfermero->direccion;
        $this->licencia_enfermeria = $this->enfermero->licencia_enfermeria;
        $this->anios_experiencia = $this->enfermero->anios_experiencia;
        $this->nivel_experiencia = $this->enfermero->nivel_experiencia;
        $this->tipo_enfermero = $this->enfermero->tipo_enfermero;
        $this->especialidad_enfermeria = $this->enfermero->especialidad_enfermeria;
        $this->especialidades = $this->enfermero->especialidades->pluck('especialidad')->toArray();
        $this->email = $this->user->email;
        $this->status = $this->enfermero->status;
        $this->empresa_id = $this->enfermero->empresa_id;
        $this->sucursal_id = $this->enfermero->sucursal_id;
        
        // Inicializar password vacío
        $this->password = '';
        
        // Cargar horarios existentes
        $this->cargarHorarios();

        // Obtener el país del usuario logueado para el formato del teléfono
        if (auth()->user()->empresa) {
            $this->pais = auth()->user()->empresa->pais;
        }
    }

    private function cargarHorarios()
    {
        $diasSemana = [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes',
            6 => 'Sábado', 7 => 'Domingo'
        ];
        
        foreach ($diasSemana as $dia => $nombre) {
            $horario = $this->enfermero->horarios()->where('dia_semana', $dia)->first();
            
            if ($horario) {
                $this->horarios[$dia] = [
                    'activo' => $horario->activo,
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin' => $horario->hora_fin,
                    'duracion_cita' => $horario->duracion_cita,
                ];
            } else {
                $this->horarios[$dia] = [
                    'activo' => in_array($dia, [1, 2, 3, 4, 5]), // Lunes a Viernes activos por defecto
                    'hora_inicio' => '08:00',
                    'hora_fin' => '16:00',
                    'duracion_cita' => 30,
                ];
            }
        }
    }

    public function actualizarEstado()
    {
        $this->authorize('edit enfermeros');
        
        try {
            // Actualizar el estado del enfermero
            $this->enfermero->update(['status' => $this->status]);
            
            // Actualizar el estado del usuario relacionado si existe
            if ($this->user) {
                $this->user->update(['status' => $this->status]);
            }
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Estado del enfermero/a actualizado exitosamente.',
                'duration' => 3000
            ]);
            
        } catch (\Exception $e) {
            // Revertir el cambio en caso de error
            $this->status = !$this->status;
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el estado: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function update()
    {
        $this->authorize('edit enfermeros');
        
        $validated = $this->validate();

        try {
            \DB::beginTransaction();

            // Actualizar el usuario si existe
            if ($this->user) {
                $userData = [
                    'name' => $validated['nombres'] . ' ' . $validated['apellidos'],
                    'email' => $validated['email'],
                    'phone' => $validated['telefono'],
                    'status' => $validated['status'],
                ];

                // Si se proporciona una nueva contraseña, actualizarla
                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $this->user->update($userData);
            }

            // Actualizar el enfermero
            $this->enfermero->update([
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
                'especialidad_enfermeria' => null, // Desactivar campo antiguo
                'status' => $validated['status'],
                'empresa_id' => auth()->user()->hasRole('Super Administrador') ? $validated['empresa_id'] : auth()->user()->empresa_id,
                'sucursal_id' => auth()->user()->hasRole('Super Administrador') ? $validated['sucursal_id'] : auth()->user()->sucursal_id,
            ]);

            // Actualizar especialidades
            $this->enfermero->especialidades()->delete();
            foreach ($this->especialidades as $especialidad) {
                $this->enfermero->especialidades()->create([
                    'especialidad' => $especialidad,
                    'empresa_id' => $this->enfermero->empresa_id,
                    'sucursal_id' => $this->enfermero->sucursal_id,
                ]);
            }

            // Actualizar horarios del enfermero
            foreach ($this->horarios as $dia => $horario) {
                $this->enfermero->horarios()->updateOrCreate(
                    ['dia_semana' => $dia],
                    [
                        'hora_inicio' => $horario['hora_inicio'],
                        'hora_fin' => $horario['hora_fin'],
                        'duracion_cita' => $horario['duracion_cita'],
                        'activo' => $horario['activo'],
                    ]
                );
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Enfermero/a '{$this->enfermero->nombres} {$this->enfermero->apellidos}' actualizado exitosamente.",
                'duration' => 5000
            ]);
              \DB::commit();
            
            return redirect()->route('admin.enfermeros.index');

        } catch (\Exception $e) {
            \DB::rollback();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el enfermero: ' . $e->getMessage(),
                'duration' => 6000
            ]);
        }
    }

    public function agregarEspecialidad()
    {
        $this->validate([
            'nueva_especialidad' => 'required|string|max:100|min:2'
        ]);

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
            unset($this->especialidades[$index]);
            $this->especialidades = array_values($this->especialidades);
        }
    }

    public function enviarMensajeBienvenida()
    {
        $this->authorize('edit enfermeros');
        
        try {
            $user = $this->enfermero->user;
            
            if (!$user) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'El enfermero no tiene usuario asociado.',
                    'duration' => 3000
                ]);
                return;
            }
            
            if (empty($this->enfermero->telefono)) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'El enfermero no tiene teléfono registrado.',
                    'duration' => 3000
                ]);
                return;
            }
            
            // Generar contraseña temporal (usando el documento de identidad)
            $plainPassword = $this->enfermero->documento_identidad;
            
            // Crear y enviar mensaje de bienvenida
            $mensaje = $this->crearMensajeBienvenida($user, $this->enfermero, $plainPassword);
            $telefonoOriginal = $this->enfermero->telefono;
            $telefono = $this->formatearTelefono($this->enfermero->telefono);
            
            // Log para depuración (se puede eliminar después)
            \Log::info('Formateo de teléfono', [
                'original' => $telefonoOriginal,
                'formateado' => $telefono,
                'pais' => auth()->user()?->empresa?->pais?->nombre ?? 'N/A',
                'codigo_pais' => auth()->user()?->empresa?->pais?->codigo_telefonico ?? 'N/A'
            ]);
            
            $whatsAppService = new WhatsAppService($user->empresa_id);
            
            if ($whatsAppService->isConfigured()) {
                $resultado = $whatsAppService->sendMessage($telefono, $mensaje, true);
                
                if ($resultado) {
                    $this->dispatch('notify', [
                        'type' => 'success',
                        'message' => 'Mensaje de bienvenida enviado exitosamente por WhatsApp.',
                        'duration' => 3000
                    ]);
                } else {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'No se pudo enviar el mensaje de WhatsApp. Verifique la configuración.',
                        'duration' => 3000
                    ]);
                }
            } else {
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'WhatsApp no está configurado para esta empresa.',
                    'duration' => 3000
                ]);
            }
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al enviar mensaje: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
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
    
    private function formatearTelefono($telefono)
    {
        // Eliminar espacios y caracteres no numéricos
        $telefono = preg_replace('/[^0-9]/', '', $telefono);
        
        // Obtener el país de la empresa del usuario logueado
        $pais = null;
        if (auth()->user() && auth()->user()->empresa) {
            $pais = auth()->user()->empresa->pais;
        }
        
        // Si no hay teléfono limpio, retornar vacío
        if (empty($telefono)) {
            return '';
        }
        
        // Agregar código del país si está disponible
        if ($pais && $pais->codigo_telefonico) {
            // Verificar si el teléfono ya incluye el código del país
            if (!str_starts_with($telefono, $pais->codigo_telefonico)) {
                $telefono = $pais->codigo_telefonico . $telefono;
            } else {
                $telefono = $telefono;
            }
        } else {
            // Si no hay código de país, intentar detectar por longitud (fallback)
            if (strlen($telefono) === 9 && $telefono[0] === '9') {
                $telefono = '+51' . $telefono; // Perú por defecto
            } elseif (strlen($telefono) === 10) {
                $telefono = '+52' . $telefono; // México por defecto
            }
        }
        
        return $telefono;
    }

    public function resetPassword()
    {
        $this->authorize('edit enfermeros');
        
        try {
            if (!$this->user) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'El enfermero no tiene usuario asociado.',
                    'duration' => 3000
                ]);
                return;
            }
            
            // Generar contraseña temporal usando el documento de identidad
            $plainPassword = $this->enfermero->documento_identidad;
            
            // Actualizar la contraseña del usuario
            $this->user->update([
                'password' => Hash::make($plainPassword)
            ]);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Contraseña restablecida exitosamente. La nueva contraseña temporal es: {$plainPassword}",
                'duration' => 5000
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al restablecer la contraseña: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
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
        return view('livewire.admin.enfermeros.edit', [
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
        ])->layout($this->getLayout());
    }
}