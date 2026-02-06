<?php

namespace App\Livewire\Admin\Subespecialidades;

use App\Models\Subespecialidad;
use App\Models\Especialidad;
use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Create extends Component
{
    use HasDynamicLayout, AuthorizesRequests;

    public $nombre;
    public $descripcion;
    public $codigo;
    public $color = '#3B82F6';
    public $costo_consulta = 0;
    public $duracion_consulta = 30;
    public $requiere_cita_previa = true;
    public $especialidad_id;
    public $empresa_id;
    public $sucursal_id;
    public $status = true;

    protected function rules()
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'codigo' => 'nullable|string|max:10|unique:subespecialidades',
            'color' => 'required|string|max:7',
            'costo_consulta' => 'required|numeric|min:0',
            'duracion_consulta' => 'required|integer|min:15|max:240',
            'requiere_cita_previa' => 'boolean',
            'especialidad_id' => 'required|exists:especialidades,id',
            'status' => 'boolean'
        ];

        // Solo requerir empresa_id y sucursal_id para super administradores
        if (auth()->user()->hasRole('Super Administrador')) {
            $rules['empresa_id'] = 'required|exists:empresas,id';
            $rules['sucursal_id'] = 'required|exists:sucursales,id';
        }

        return $rules;
    }

    public function mount()
    {
        $this->authorize('create subespecialidades');
        
        if (!auth()->user()->hasRole('Super Administrador')) {
            $this->empresa_id = auth()->user()->empresa_id;
            $this->sucursal_id = auth()->user()->sucursal_id;
        }
    }

    public function updatedEspecialidadId($value)
    {
        if ($value) {
            $especialidad = Especialidad::find($value);
            if ($especialidad) {
                $this->costo_consulta = $especialidad->costo_consulta;
                $this->duracion_consulta = $especialidad->duracion_consulta;
                $this->color = $especialidad->color;
                $this->requiere_cita_previa = $especialidad->requiere_cita_previa;
            }
        }
    }

    public function updatedEmpresaId($value)
    {
        $this->sucursal_id = null;
    }

    public function store()
    {
        $this->authorize('create subespecialidades');

        $validated = $this->validate();

        if (empty($validated['codigo'])) {
            $validated['codigo'] = Subespecialidad::generateCodigo();
        }

        // Para usuarios normales, usar automáticamente su empresa y sucursal
        if (!auth()->user()->hasRole('Super Administrador')) {
            $validated['empresa_id'] = auth()->user()->empresa_id;
            $validated['sucursal_id'] = auth()->user()->sucursal_id;
        }

        try {
            Subespecialidad::create($validated);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Subespecialidad '{$this->nombre}' creada exitosamente.",
                'duration' => 4000
            ]);
            return redirect()->route('admin.subespecialidades.index');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear la subespecialidad: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function render()
    {
        $especialidades = Especialidad::forUser()->get();
        $empresas = \App\Models\Empresa::forUser()->get();
        $sucursales = collect();
        
        if ($this->empresa_id) {
            $sucursales = Sucursal::forUser()->where('empresa_id', $this->empresa_id)->get();
        }

        return view('livewire.admin.subespecialidades.create', [
            'especialidades' => $especialidades,
            'empresas' => $empresas,
            'sucursales' => $sucursales,
        ])->layout($this->getLayout());
    }
}