<?php

namespace App\Livewire\Admin\Pacientes;

use App\Models\Paciente;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Tutor;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use App\Traits\HasDynamicLayout;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Wizard extends Component
{
    use HasDynamicLayout, WithFileUploads;

    // Propiedades del wizard
    public $pasoActual = 1;
    public $pasoTotal = 3;
    public $esMenorEdad = false;
    public $pacienteId = null;
    public $modoEdicion = false;

    // Paso 1: Foto del paciente
    public $foto;
    public $fotoExistente = null;

    // Paso 2: Datos personales
    public $nombres;
    public $apellidos;
    public $documento_identidad;
    public $telefono;
    public $email;
    public $direccion;
    public $nickname;
    public $fecha_nacimiento;
    public $genero;
    public $estado_civil;
    public $ocupacion;
    public $nacionalidad;
    public $status = true;

    // Datos de empresa
    public $empresa_id;
    public $sucursal_id;
    public $pais;

    // Paso 3: Datos del tutor (solo si es menor de edad)
    public $tutor = [
        'nombres' => '',
        'apellidos' => '',
        'documento_identidad' => '',
        'parentesco' => '',
        'telefono' => '',
        'email' => '',
        'direccion' => '',
    ];

    // Opciones para select
    public $generos = ['Masculino', 'Femenino', 'Otro'];
    public $estadosCiviles = ['Soltero/a', 'Casado/a', 'Divorciado/a', 'Viudo/a', 'Unión libre'];
    public $parentescos = ['Padre', 'Madre', 'Tutor legal', 'Abuelo/a', 'Tío/a', 'Hermano/a', 'Otro'];

    // Propiedad computada para la edad
    protected ?string $edadCache = null;

    protected $queryString = [
        'pasoActual' => ['except' => 1],
        'empresa_id' => ['except' => ''],
        'sucursal_id' => ['except' => ''],
    ];

    protected $messages = [
        'nombres.required' => 'El nombre del paciente es obligatorio.',
        'apellidos.required' => 'Los apellidos son obligatorios.',
        'documento_identidad.unique' => 'Este documento de identidad ya está registrado.',
        'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
        'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        'empresa_id.required' => 'Debe seleccionar una empresa.',
        'sucursal_id.required' => 'Debe seleccionar una sucursal.',
        'tutor.nombres.required' => 'El nombre del tutor es obligatorio.',
        'tutor.apellidos.required' => 'Los apellidos del tutor son obligatorios.',
        'tutor.documento_identidad.required' => 'El documento del tutor es obligatorio.',
        'tutor.parentesco.required' => 'Debe seleccionar el parentesco.',
        'tutor.telefono.required' => 'El teléfono del tutor es obligatorio.',
        'telefono.regex' => 'El teléfono solo puede contener números, espacios, guiones, paréntesis y el signo +.',
        'tutor.telefono.regex' => 'El teléfono del tutor solo puede contener números, espacios, guiones, paréntesis y el signo +.',
        
        // Mensajes personalizados para validación de fotos - TODOS LOS CASOS
        'foto.image' => 'El archivo debe ser una imagen válida (JPG, PNG, GIF).',
        'foto.max' => 'La imagen no debe superar los 2MB de tamaño.',
        'foto.max.file' => 'La imagen no debe superar los 2MB de tamaño.',
        'foto.mimes' => 'El formato de imagen no es válido. Solo se permiten JPG, PNG y GIF.',
        'foto.dimensions' => 'Las dimensiones de la imagen no son válidas.',
    ];

    public function mount($pacienteId = null)
    {
        // Log para verificar el parámetro recibido
        Log::info('Mount Wizard - PacienteId recibido', ['pacienteId' => $pacienteId]);
        
        // Cambiar la autorización para permitir tanto creación como edición
        if ($pacienteId) {
            $this->authorize('edit pacientes');
        } else {
            $this->authorize('create pacientes');
        }

        // Configuración inicial de empresa
        if (!auth()->user()->hasRole('Super Administrador')) {
            $this->empresa_id = auth()->user()->empresa_id;
            $this->sucursal_id = auth()->user()->sucursal_id;
        }

        if (auth()->user()->empresa) {
            $this->pais = auth()->user()->empresa->pais;
        }

        // Si hay pacienteId, estamos en modo edición
        if ($pacienteId) {
            $this->modoEdicion = true;
            $this->pacienteId = $pacienteId;
            $this->cargarDatosPaciente($pacienteId);
            $this->determinarPasosTotales();
            
            // Depuración temporal
            Log::info('Modo edición activado', [
                'paciente_id' => $pacienteId,
                'nombres' => $this->nombres,
                'apellidos' => $this->apellidos,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'es_menor_edad' => $this->esMenorEdad
            ]);
        }
    }

    protected function cargarDatosPaciente($pacienteId)
    {
        try {
            $paciente = Paciente::with(['tutor'])->findOrFail($pacienteId);
            
            // Datos básicos
            $this->nombres = $paciente->nombres;
            $this->apellidos = $paciente->apellidos;
            $this->documento_identidad = $paciente->documento_identidad;
            $this->telefono = $paciente->telefono;
            $this->email = $paciente->email;
            $this->direccion = $paciente->direccion;
            $this->nickname = $paciente->nickname;
            
            // Manejar fecha de nacimiento con validación
            if ($paciente->fecha_nacimiento) {
                try {
                    $this->fecha_nacimiento = Carbon::parse($paciente->fecha_nacimiento)->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::warning('Error al parsear fecha de nacimiento: ' . $e->getMessage());
                    $this->fecha_nacimiento = null;
                }
            }
            
            $this->genero = $paciente->genero;
            $this->estado_civil = $paciente->estado_civil;
            $this->ocupacion = $paciente->ocupacion;
            $this->nacionalidad = $paciente->nacionalidad;
            $this->status = $paciente->status;
            $this->empresa_id = $paciente->empresa_id;
            $this->sucursal_id = $paciente->sucursal_id;
            
            // Foto existente
            $this->fotoExistente = $paciente->foto;
            
            // Verificar si es menor de edad y cargar tutor
            $this->verificarMenorEdad();
            
            if ($this->esMenorEdad && $paciente->tutor) {
                $this->tutor = [
                    'nombres' => $paciente->tutor->nombres,
                    'apellidos' => $paciente->tutor->apellidos,
                    'documento_identidad' => $paciente->tutor->documento_identidad,
                    'parentesco' => $paciente->tutor->parentesco,
                    'telefono' => $paciente->tutor->telefono,
                    'email' => $paciente->tutor->email,
                    'direccion' => $paciente->tutor->direccion,
                ];
            }
            
            Log::info('Datos del paciente cargados exitosamente', ['paciente_id' => $pacienteId]);
            
        } catch (\Exception $e) {
            Log::error('Error al cargar datos del paciente: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updatedFechaNacimiento($value)
    {
        $this->verificarMenorEdad();
        $this->determinarPasosTotales();
    }

    public function updatedFoto($value)
    {
        if (!$value) {
            return;
        }

        // Validar que sea una imagen
        if (!$value->isValid()) {
            $this->addError('foto', '❌ Error al subir el archivo. El archivo está corrupto o no es válido.');
            $this->foto = null;
            return;
        }

        // Obtener información del archivo
        $mimeType = $value->getMimeType();
        $sizeInKB = $value->getSize() / 1024;
        $fileName = $value->getClientOriginalName();
        
        // 🚫 VALIDACIÓN CRÍTICA: Detectar y rechazar videos explícitamente
        $videoMimeTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska', 'video/webm'];
        if (in_array($mimeType, $videoMimeTypes) || str_starts_with($mimeType, 'video/')) {
            $this->addError('foto', "🎬 ❌ NO se permiten archivos de video.\n\nArchivo detectado: {$fileName}\nTipo: {$mimeType}\n\nSolo se pueden subir imágenes (JPG, PNG, GIF, WebP).");
            $this->foto = null;
            return;
        }

        // ✅ Validar tipo MIME explícitamente - Solo imágenes
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            // Verificar si es otro tipo de archivo no permitido
            if (str_starts_with($mimeType, 'application/') || str_starts_with($mimeType, 'text/')) {
                $this->addError('foto', "📄 ❌ El archivo NO es una imagen.\n\nArchivo: {$fileName}\nTipo: {$mimeType}\n\nSolo se pueden subir imágenes (JPG, PNG, GIF, WebP).");
            } else {
                $this->addError('foto', "⚠️ El archivo debe ser una imagen válida (JPG, PNG, GIF o WebP).\n\nArchivo: {$fileName}\nTipo detectado: {$mimeType}");
            }
            $this->foto = null;
            return;
        }

        // 📏 Validar tamaño máximo (2MB = 2048 KB)
        $maxSize = 2048; // en KB
        
        if ($sizeInKB > $maxSize) {
            $sizeInMB = round($sizeInKB / 1024, 2);
            $this->addError('foto', "📏 La imagen es demasiado grande ({$sizeInMB} MB).\n\nTamaño actual: {$sizeInMB} MB\nTamaño máximo: 2 MB\n\nPor favor comprime la imagen o usa otra más pequeña.");
            $this->foto = null;
            return;
        }

        // ✅ Si pasa todas las validaciones, limpiar errores previos
        $this->resetErrorBag('foto');
        $this->resetValidation('foto');
        
        Log::info('✅ Imagen validada correctamente', [
            'nombre' => $fileName,
            'tipo' => $mimeType,
            'tamaño_kb' => $sizeInKB
        ]);
    }

    public function updatedDocumentoIdentidad($value)
    {
        $value = trim((string) $value);

        $this->resetErrorBag('documento_identidad');
        $this->resetValidation('documento_identidad');

        if (strlen($value) < 5 || !$this->empresa_id) {
            return;
        }

        $exists = Paciente::where('documento_identidad', $value)
            ->where('empresa_id', $this->empresa_id)
            ->when($this->pacienteId, fn ($q) => $q->where('id', '!=', $this->pacienteId))
            ->exists();

        if ($exists) {
            $this->addError('documento_identidad', 'Este documento ya está registrado en el sistema.');
        }
    }

    public function eliminarFoto()
    {
        $this->foto = null;
        $this->fotoExistente = null;
    }


    public function updatedTutor($value, $field)
    {
        if ($this->pasoActual === 3) {
            $this->validateOnly("tutor.{$field}");
        }
    }

    public function formatDocumento()
    {
        if ($this->documento_identidad) {
            $this->documento_identidad = strtoupper(trim($this->documento_identidad));
        }
    }

    public function formatPhone()
    {
        if ($this->telefono) {
            $this->telefono = preg_replace('/[^0-9+]/', '', $this->telefono);
        }
    }

    protected function verificarMenorEdad()
    {
        try {
            if ($this->fecha_nacimiento) {
                // Validar que la fecha tenga un formato válido
                $fechaNacimiento = Carbon::parse($this->fecha_nacimiento);
                $this->esMenorEdad = $fechaNacimiento->age < 18;
                Log::info('Verificación de menor de edad', ['fecha' => $this->fecha_nacimiento, 'es_menor' => $this->esMenorEdad]);
            } else {
                $this->esMenorEdad = false;
            }
        } catch (\Exception $e) {
            Log::error('Error al verificar menor de edad: ' . $e->getMessage());
            $this->esMenorEdad = false;
        }
    }

    protected function determinarPasosTotales()
    {
        $this->pasoTotal = $this->esMenorEdad ? 3 : 2;
    }

    // Reglas de validación por paso
    protected function getRulesPaso1()
    {
        return [
            'foto' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048', // Solo imágenes, 2MB máximo
        ];
    }

    protected function getRulesPaso2()
    {
        $rules = [
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'documento_identidad' => [
                'nullable',
                'string',
                'max:50',
                // Unique solo si no es null/vacío
                function ($attribute, $value, $fail) {
                    if (!empty($value)) {
                        $exists = \App\Models\Paciente::where('documento_identidad', $value)
                            ->where('empresa_id', auth()->user()->empresa_id)
                            ->when($this->pacienteId, fn($q) => $q->where('id', '!=', $this->pacienteId))
                            ->exists();
                        if ($exists) {
                            $fail('Este documento de identidad ya está registrado.');
                        }
                    }
                },
            ],
            'telefono' => 'nullable|regex:/^[\d\s\-\+\(\)]+$/|max:20',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:500',
            'nickname' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'required|date|before_or_equal:today',
            'genero' => 'nullable|in:' . implode(',', $this->generos),
            'estado_civil' => 'nullable|in:' . implode(',', $this->estadosCiviles),
            'ocupacion' => 'nullable|string|max:255',
            'nacionalidad' => 'nullable|string|max:100',
            'empresa_id' => 'required|exists:empresas,id',
            'sucursal_id' => 'required|exists:sucursales,id',
        ];

        if (auth()->user()->hasRole('Super Administrador')) {
            $rules['empresa_id'] = 'required|exists:empresas,id';
            $rules['sucursal_id'] = 'required|exists:sucursales,id';
        }

        return $rules;
    }

    protected function getRulesPaso3()
    {
        if (!$this->esMenorEdad) {
            return [];
        }

        return [
            'tutor.nombres' => 'required|string|max:255',
            'tutor.apellidos' => 'required|string|max:255',
            'tutor.documento_identidad' => 'required|string|max:50',
            'tutor.parentesco' => 'required|in:' . implode(',', $this->parentescos),
            'tutor.telefono' => 'required|regex:/^[\d\s\-\+\(\)]+$/|max:20',
            'tutor.email' => 'nullable|email|max:255',
            'tutor.direccion' => 'nullable|string|max:500',
        ];
    }

    // Navegación del wizard
    public function siguientePaso()
    {
        $this->validate($this->getRulesPasoActual());
        
        if ($this->pasoActual < $this->pasoTotal) {
            $this->dispatch('paso-cambiando', ['paso' => $this->pasoActual + 1]);
            $this->pasoActual++;
        }
    }

    public function pasoAnterior()
    {
        if ($this->pasoActual > 1) {
            $this->dispatch('paso-cambiando', ['paso' => $this->pasoActual - 1]);
            $this->pasoActual--;
        }
    }

    public function irAPaso($paso)
    {
        if ($paso < 1 || $paso > $this->pasoTotal) return;

        if ($paso < $this->pasoActual) {
            $this->dispatch('paso-cambiando', ['paso' => $paso]);
            $this->pasoActual = $paso;
            return;
        }

        for ($i = $this->pasoActual; $i < $paso; $i++) {
            $this->pasoActual = $i;
            $rules = $this->getRulesPasoActual();
            if (!empty($rules)) {
                $this->validate($rules);
            }
        }
        $this->dispatch('paso-cambiando', ['paso' => $paso]);
        $this->pasoActual = $paso;
    }

    protected function getRulesPasoActual()
    {
        return match($this->pasoActual) {
            1 => $this->getRulesPaso1(),
            2 => $this->getRulesPaso2(),
            3 => $this->getRulesPaso3(),
            default => [],
        };
    }

    public function guardar()
    {
        try {
            Log::info('Iniciando guardado de paciente', [
                'modoEdicion' => $this->modoEdicion,
                'esMenorEdad' => $this->esMenorEdad,
                'tutor' => $this->tutor
            ]);
            
            // Validar todos los pasos
            $rules = array_merge(
                $this->getRulesPaso1(),
                $this->getRulesPaso2(),
                $this->getRulesPaso3()
            );

            $this->validate($rules);
            
            // Datos del paciente
            $datosPaciente = [
                'nombres' => $this->nombres,
                'apellidos' => $this->apellidos,
                'documento_identidad' => $this->documento_identidad,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'direccion' => $this->direccion,
                'nickname' => $this->nickname,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'genero' => $this->genero,
                'estado_civil' => $this->estado_civil,
                'ocupacion' => $this->ocupacion,
                'nacionalidad' => $this->nacionalidad,
                'empresa_id' => $this->empresa_id,
                'sucursal_id' => $this->sucursal_id,
                'status' => $this->status,
            ];

            // Procesar foto si se subió una nueva
            if ($this->foto) {
                try {
                    // Eliminar foto anterior si existe
                    if ($this->modoEdicion && $this->fotoExistente) {
                        // Verificar que la foto existente no sea una URL externa
                        if (!filter_var($this->fotoExistente, FILTER_VALIDATE_URL)) {
                            Storage::disk('public')->delete($this->fotoExistente);
                        }
                    }
                    
                    // Guardar nueva foto
                    $path = $this->foto->store('pacientes/fotos', 'public');
                    $datosPaciente['foto'] = $path;
                } catch (\Exception $e) {
                    Log::warning('Error al procesar la foto del paciente: ' . $e->getMessage());
                    // Continuar sin la foto si hay un error
                }
            }

            if ($this->modoEdicion) {
                // Actualizar paciente existente
                $paciente = Paciente::findOrFail($this->pacienteId);
                $paciente->update($datosPaciente);
                
                // Actualizar tutor si es menor de edad
                if ($this->esMenorEdad) {
                    $this->actualizarTutor($paciente);
                } else {
                    // Si ya no es menor de edad, eliminar tutor
                    $paciente->tutor()->delete();
                }
                
                $mensaje = 'Paciente actualizado exitosamente.';
            } else {
                // Crear nuevo paciente
                $paciente = Paciente::create($datosPaciente);
                
                // Crear tutor si es menor de edad
                if ($this->esMenorEdad) {
                    $this->crearTutor($paciente);
                }
                
                $mensaje = 'Paciente creado exitosamente.';
            }

            // Verificar el tutor después de guardar
            if ($this->esMenorEdad) {
                $tutorActualizado = $paciente->tutor()->first();
                Log::info('Verificación final del tutor:', [
                    'tutor_encontrado' => $tutorActualizado ? true : false,
                    'tutor_datos' => $tutorActualizado ? $tutorActualizado->toArray() : null
                ]);
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $mensaje,
                'duration' => 3000
            ]);

            return redirect()->route('admin.pacientes.index');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Errores de validación - Livewire los maneja automáticamente
            return;
        } catch (\Exception $e) {
            Log::error('Error guardando paciente: ' . $e->getMessage());
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar el paciente: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    protected function crearTutor($paciente)
    {
        if ($this->esMenorEdad && !empty($this->tutor['nombres'])) {
            Log::info('Creando tutor con datos:', [
                'tutor' => $this->tutor,
                'empresa_id' => $this->empresa_id,
                'sucursal_id' => $this->sucursal_id
            ]);
            
            $tutor = $paciente->tutor()->create([
                'nombres' => $this->tutor['nombres'],
                'apellidos' => $this->tutor['apellidos'],
                'documento_identidad' => $this->tutor['documento_identidad'],
                'parentesco' => $this->tutor['parentesco'],
                'telefono' => $this->tutor['telefono'],
                'email' => $this->tutor['email'],
                'direccion' => $this->tutor['direccion'],
                'empresa_id' => $this->empresa_id,
                'sucursal_id' => $this->sucursal_id,
            ]);
            
            Log::info('Tutor creado exitosamente:', ['tutor_id' => $tutor->id, 'tutor' => $tutor->toArray()]);
        }
    }

    protected function actualizarTutor($paciente)
    {
        if ($this->esMenorEdad && !empty($this->tutor['nombres'])) {
            Log::info('Actualizando tutor con datos:', [
                'tutor' => $this->tutor,
                'paciente_id' => $paciente->id
            ]);
            
            $tutor = $paciente->tutor()->updateOrCreate(
                ['paciente_id' => $paciente->id],
                [
                    'nombres' => $this->tutor['nombres'],
                    'apellidos' => $this->tutor['apellidos'],
                    'documento_identidad' => $this->tutor['documento_identidad'],
                    'parentesco' => $this->tutor['parentesco'],
                    'telefono' => $this->tutor['telefono'],
                    'email' => $this->tutor['email'],
                    'direccion' => $this->tutor['direccion'],
                    'empresa_id' => $this->empresa_id,
                    'sucursal_id' => $this->sucursal_id,
                ]
            );
            
            Log::info('Tutor actualizado exitosamente:', ['tutor_id' => $tutor->id, 'tutor' => $tutor->toArray()]);
        }
    }

    public function cancelar()
    {
        return redirect()->route('admin.pacientes.index');
    }

    // Propiedades computadas
    public function getEdadProperty()
    {
        if ($this->fecha_nacimiento) {
            return Carbon::parse($this->fecha_nacimiento)->age;
        }
        return null;
    }

    public function getEdadFormateadaProperty(): ?string
    {
        if (empty($this->fecha_nacimiento)) {
            return null;
        }

        try {
            $years = Carbon::parse($this->fecha_nacimiento)->age;
            $months = Carbon::parse($this->fecha_nacimiento)->diffInMonths(now()) % 12;
            
            if ($years > 0) {
                return "{$years} año" . ($years > 1 ? 's' : '') . ($months > 0 ? " y {$months} mes" . ($months > 1 ? 'es' : '') : '');
            } elseif ($months > 0) {
                return "{$months} mes" . ($months > 1 ? 'es' : '');
            } else {
                return 'Recién nacido';
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getProgresoProperty()
    {
        return round(($this->pasoActual / $this->pasoTotal) * 100);
    }

    public function getSucursalesProperty()
    {
        if ($this->empresa_id) {
            return Sucursal::where('empresa_id', $this->empresa_id)
                ->where('status', true)
                ->orderBy('nombre')
                ->get();
        }
        return collect();
    }

    public function getEmpresasProperty()
    {
        if (auth()->user()->hasRole('Super Administrador')) {
            return Empresa::orderBy('nombre')->get();
        }
        return collect([auth()->user()->empresa]);
    }

    protected function getPageTitle(): string
    {
        return $this->modoEdicion ? 'Editar Paciente' : 'Nuevo Paciente';
    }

    protected function getBreadcrumb(): array
    {
        return [
            'admin.dashboard' => 'Dashboard',
            'admin.pacientes.index' => 'Pacientes',
            '#' => $this->modoEdicion ? 'Editar Paciente' : 'Nuevo Paciente',
        ];
    }

    public function render()
    {
        return view('livewire.admin.pacientes.wizard', [
            'edad' => $this->edad,
            'edadFormateada' => $this->edadFormateada,
            'progreso' => $this->progreso,
            'sucursales' => $this->sucursales,
            'empresas' => $this->empresas,
        ])->layout($this->getLayout());
    }
}
