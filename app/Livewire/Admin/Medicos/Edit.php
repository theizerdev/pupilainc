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
use App\Traits\HasDynamicLayout;
use Illuminate\Support\Facades\Hash;
use App\Services\WhatsAppService;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    use WithFileUploads, HasDynamicLayout;

    // ID del médico que se está editando
    public $medico_id;
    public $user_id;

    // Datos del médico
    public $nombres;
    public $apellidos;
    public $genero;
    public $documento_identidad;
    public $telefono;
    public $direccion;
    public $licencia_medica;
    public $anios_experiencia = 0;
    public $nivel_experiencia = 'Básico';
    public $email;
    public $status = true;

    // Especialidades y subespecialidades
    public $especialidad_id;
    public $subespecialidades_seleccionadas = [];
    public $subespecialidades_data = []; // Array para almacenar experiencia y nivel por subespecialidad
    public $tarifa_consulta;
    public $horario_atencion = [];

    // Empresa y sucursal
    public $empresa_id;
    public $sucursal_id;

    // Horarios del médico
    public $horarios = [];

    // Datos del país para el formato del teléfono
    public $pais;

    // Modal subespecialidad
    public $showSubespecialidadModal = false;
    public $sub_nombre;
    public $sub_codigo;
    public $sub_descripcion;
    public $sub_costo_consulta = 0;
    public $sub_duracion_consulta = 30;
    public $sub_requiere_cita_previa = true;
    public $sub_color = '#3B82F6';
    public $sub_icono = 'fa-stethoscope';

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
                Rule::unique('medicos', 'documento_identidad')->ignore($this->medico_id)
            ],
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'licencia_medica' => [
                'required',
                'string',
                'max:50',
                Rule::unique('medicos', 'licencia_medica')->ignore($this->medico_id)
            ],
            'anios_experiencia' => 'required|integer|min:0|max:50',
            'nivel_experiencia' => 'required|in:Básico,Intermedio,Avanzado',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user_id)
            ],
            'status' => 'boolean',
            'especialidad_id' => 'required|exists:especialidades,id',
            'tarifa_consulta' => 'nullable|numeric|min:0|max:999999.99',
            'horario_atencion' => 'nullable|array',
            'subespecialidades_seleccionadas' => 'nullable|array',
            'subespecialidades_seleccionadas.*' => 'exists:subespecialidades,id',
            'subespecialidades_data' => 'nullable|array',
            'subespecialidades_data.*.experiencia_anios' => 'required|integer|min:0|max:50',
            'subespecialidades_data.*.nivel_experiencia' => 'required|in:Básico,Intermedio,Avanzado',
            'subespecialidades_data.*.tarifa_consulta' => 'nullable|numeric|min:0|max:999999.99',
            'horarios' => 'nullable|array',
            'horarios.*.activo' => 'boolean',
            'horarios.*.hora_inicio' => 'required_if:horarios.*.activo,true|date_format:H:i',
            'horarios.*.hora_fin' => 'required_if:horarios.*.activo,true|date_format:H:i|after:horarios.*.hora_inicio',
            'horarios.*.duracion_cita' => 'required_if:horarios.*.activo,true|integer|min:15|max:120',
            'empresa_id' => 'required|exists:empresas,id',
            'sucursal_id' => 'required|exists:sucursales,id',
        ];

        return $rules;
    }

    public function mount($id)
    {
        // Cargar datos del médico
        $medico = Medico::findOrFail($id);
        $this->medico_id = $medico->id;
        $this->user_id = $medico->user_id;
        $this->nombres = $medico->nombres;
        $this->apellidos = $medico->apellidos;
        $this->genero = $medico->genero;
        $this->documento_identidad = $medico->documento_identidad;
        $this->telefono = $medico->telefono;
        $this->direccion = $medico->direccion;
        $this->licencia_medica = $medico->licencia_medica;
        $this->anios_experiencia = $medico->anios_experiencia;
        $this->nivel_experiencia = $medico->nivel_experiencia;
        $this->email = $medico->user->email;
        $this->status = $medico->status;
        $this->empresa_id = $medico->empresa_id;
        $this->sucursal_id = $medico->sucursal_id;

        // Cargar especialidad principal
        $especialidadPrincipal = $medico->especialidades()->first();
        $this->especialidad_id = $especialidadPrincipal ? $especialidadPrincipal->id : null;
        $this->tarifa_consulta = $especialidadPrincipal ? $especialidadPrincipal->pivot->tarifa_consulta : null;

        // Cargar subespecialidades seleccionadas y sus datos
        $this->subespecialidades_seleccionadas = $medico->subespecialidades->pluck('id')->toArray();

        // Cargar datos de experiencia y nivel por subespecialidad
        foreach ($medico->subespecialidades as $subespecialidad) {
            $this->subespecialidades_data[$subespecialidad->id] = [
                'experiencia_anios' => $subespecialidad->pivot->experiencia_anios ?? 0,
                'nivel_experiencia' => $subespecialidad->pivot->nivel_experiencia ?? 'Básico',
                'tarifa_consulta' => $subespecialidad->pivot->tarifa_consulta ?? null,
            ];
        }

        // Cargar horarios existentes del médico
        $horariosExistentes = $medico->horarios->pluck('activo', 'dia_semana')->toArray();

        // Inicializar horarios por defecto
        $diasSemana = [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes',
            6 => 'Sábado', 7 => 'Domingo'
        ];

        foreach ($diasSemana as $dia => $nombre) {
            $horarioExistente = $medico->horarios->where('dia_semana', $dia)->first();

            if ($horarioExistente) {
                $this->horarios[$dia] = [
                    'activo' => $horarioExistente->activo,
                    'hora_inicio' => $horarioExistente->hora_inicio->format('H:i'),
                    'hora_fin' => $horarioExistente->hora_fin->format('H:i'),
                    'duracion_cita' => $horarioExistente->duracion_cita,
                ];
            } else {
                // Valores por defecto para días nuevos
                $this->horarios[$dia] = [
                    'activo' => in_array($dia, [1, 2, 3, 4, 5]), // Lunes a Viernes activos
                    'hora_inicio' => '09:00',
                    'hora_fin' => '17:00',
                    'duracion_cita' => 30,
                ];
            }
        }

        // Obtener el país del usuario logueado para el formato del teléfono
        if (auth()->user()->empresa) {
            $this->pais = auth()->user()->empresa->pais;
        }
    }

     public function updatedEspecialidadId($value)
    {
        $this->subespecialidades_seleccionadas = [];
        $this->subespecialidades_data = [];

        if ($value) {
            $this->subespecialidades_cache = Subespecialidad::forUser()
                ->where('especialidad_id', $value)
                ->where('status', true)
                ->orderBy('nombre')
                ->get()
                ->toArray();
        } else {
            $this->subespecialidades_cache = [];
        }
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

    // Caché de subespecialidades para actualización después de crear una nueva
    public $subespecialidades_cache = [];

    public function openSubespecialidadModal()
    {
        $this->sub_nombre = '';
        $this->sub_codigo = '';
        $this->sub_descripcion = '';
        $this->sub_costo_consulta = 0;
        $this->sub_duracion_consulta = 30;
        $this->sub_requiere_cita_previa = true;
        $this->sub_color = '#3B82F6';
        $this->sub_icono = 'fa-stethoscope';
        $this->resetValidation(['sub_nombre', 'sub_codigo', 'sub_descripcion', 'sub_costo_consulta', 'sub_duracion_consulta', 'sub_color', 'sub_icono']);
        $this->showSubespecialidadModal = true;
    }

    public function closeSubespecialidadModal()
    {
        $this->showSubespecialidadModal = false;
        $this->resetValidation(['sub_nombre', 'sub_codigo', 'sub_descripcion', 'sub_costo_consulta', 'sub_duracion_consulta', 'sub_color', 'sub_icono']);
    }

    public function storeSubespecialidad()
    {
        $validated = $this->validate([
            'sub_nombre' => 'required|string|max:255',
            'sub_codigo' => 'nullable|string|max:10|unique:subespecialidades,codigo',
            'sub_descripcion' => 'nullable|string|max:1000',
            'sub_color' => 'required|string|max:7',
            'sub_icono' => 'required|string|max:100',
            'sub_costo_consulta' => 'required|numeric|min:0',
            'sub_duracion_consulta' => 'required|integer|min:15|max:240',
            'sub_requiere_cita_previa' => 'boolean',
        ]);

        try {
            $codigo = $validated['sub_codigo'] ?: \App\Models\Subespecialidad::generateCodigo();

            $subespecialidad = \App\Models\Subespecialidad::create([
                'nombre' => $validated['sub_nombre'],
                'codigo' => $codigo,
                'descripcion' => $validated['sub_descripcion'],
                'color' => $validated['sub_color'],
                'icono' => $validated['sub_icono'],
                'costo_consulta' => $validated['sub_costo_consulta'],
                'duracion_consulta' => $validated['sub_duracion_consulta'],
                'requiere_cita_previa' => $validated['sub_requiere_cita_previa'],
                'especialidad_id' => $this->especialidad_id,
            ]);

            // Agregar automáticamente a las seleccionadas
            $this->subespecialidades_seleccionadas[] = $subespecialidad->id;
            $this->subespecialidades_data[$subespecialidad->id] = [
                'experiencia_anios' => 0,
                'nivel_experiencia' => 'Básico',
                'tarifa_consulta' => null,
            ];

            // Recargar caché
            $this->subespecialidades_cache = Subespecialidad::forUser()
                ->where('especialidad_id', $this->especialidad_id)
                ->where('status', true)
                ->orderBy('nombre')
                ->get()
                ->toArray();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Subespecialidad '{$validated['sub_nombre']}' creada exitosamente.",
                'duration' => 4000
            ]);

            $this->closeSubespecialidadModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear la subespecialidad: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function save()
    {
        $this->validate();

        try {
            // Actualizar datos del médico
            $medico = Medico::findOrFail($this->medico_id);
            $medico->update([
                'nombres' => $this->nombres,
                'apellidos' => $this->apellidos,
                'genero' => $this->genero,
                'documento_identidad' => $this->documento_identidad,
                'telefono' => $this->telefono,
                'direccion' => $this->direccion,
                'licencia_medica' => $this->licencia_medica,
                'anios_experiencia' => $this->anios_experiencia,
                'nivel_experiencia' => $this->nivel_experiencia,
                'status' => $this->status,
                'empresa_id' => $this->empresa_id,
                'sucursal_id' => $this->sucursal_id,
            ]);

            // Actualizar datos del usuario
            $user = $medico->user;
            $user->update([
                'name' => "{$this->nombres} {$this->apellidos}",
                'email' => $this->email,
                'status' => $this->status,
            ]);

            // Actualizar especialidad principal
            $medico->especialidades()->sync([
                $this->especialidad_id => [
                    'tarifa_consulta' => $this->tarifa_consulta,
                    'status' => true,
                ]
            ]);

            // Actualizar subespecialidades con experiencia y nivel
            $subespecialidades_sync = [];
            foreach ($this->subespecialidades_seleccionadas as $subespecialidadId) {
                $data = $this->subespecialidades_data[$subespecialidadId] ?? [
                    'experiencia_anios' => 0,
                    'nivel_experiencia' => 'Básico',
                    'tarifa_consulta' => null,
                ];

                $subespecialidades_sync[$subespecialidadId] = [
                    'experiencia_anios' => $data['experiencia_anios'] ?? 0,
                    'nivel_experiencia' => $data['nivel_experiencia'] ?? 'Básico',
                    'tarifa_consulta' => $data['tarifa_consulta'] ?? null,
                    'status' => true,
                ];
            }

            $medico->subespecialidades()->sync($subespecialidades_sync);

            // Actualizar horarios del médico
            $medico->horarios()->delete(); // Eliminar horarios anteriores

            foreach ($this->horarios as $dia => $horario) {
                $medico->horarios()->updateOrCreate(
                    ['dia_semana' => $dia],
                    [
                        'hora_inicio' => $horario['hora_inicio'],
                        'hora_fin' => $horario['hora_fin'],
                        'duracion_cita' => $horario['duracion_cita'],
                        'activo' => $horario['activo'],
                        'empresa_id' => $medico->empresa_id,
                        'sucursal_id' => $medico->sucursal_id,
                    ]
                );
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Horarios del médico actualizados exitosamente.',
                'duration' => 5000
            ]);

            return redirect()->route('admin.medicos.index');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el médico: ' . $e->getMessage());
        }
    }

    public function getEspecialidadesProperty()
    {
        return Especialidad::forUser()->where('status', true)->orderBy('nombre')->get();
    }

    public function getSubespecialidadesProperty()
    {
        // Si hay caché disponible, usarlo y convertir a objetos
        if (!empty($this->subespecialidades_cache)) {
            return collect(array_map(function($item) {
                return (object)$item;
            }, $this->subespecialidades_cache));
        }

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
        return view('livewire.admin.medicos.edit', [
            'especialidades' => $this->especialidades,
            'subespecialidades' => $this->subespecialidades,
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
        ])->layout($this->getLayout());
    }
}
