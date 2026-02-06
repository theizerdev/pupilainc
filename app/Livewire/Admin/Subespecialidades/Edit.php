<?php

namespace App\Livewire\Admin\Subespecialidades;

use App\Models\Subespecialidad;
use App\Models\Especialidad;
use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Edit extends Component
{
    use HasDynamicLayout, AuthorizesRequests;

    public Subespecialidad $subespecialidad;
    
    public $nombre;
    public $descripcion;
    public $codigo;
    public $color;
    public $costo_consulta;
    public $duracion_consulta;
    public $requiere_cita_previa;
    public $especialidad_id;
    public $empresa_id;
    public $sucursal_id;
    public $status;

    protected function rules()
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'codigo' => 'required|string|max:10|unique:subespecialidades,codigo,' . $this->subespecialidad->id,
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

    public function mount(Subespecialidad $subespecialidad)
    {
        $this->authorize('edit subespecialidades');
        
        $this->subespecialidad = $subespecialidad;
        $this->nombre = $subespecialidad->nombre;
        $this->descripcion = $subespecialidad->descripcion;
        $this->codigo = $subespecialidad->codigo;
        $this->color = $subespecialidad->color;
        $this->costo_consulta = $subespecialidad->costo_consulta;
        $this->duracion_consulta = $subespecialidad->duracion_consulta;
        $this->requiere_cita_previa = $subespecialidad->requiere_cita_previa;
        $this->especialidad_id = $subespecialidad->especialidad_id;
        $this->empresa_id = $subespecialidad->empresa_id;
        $this->sucursal_id = $subespecialidad->sucursal_id;
        $this->status = $subespecialidad->status;
    }

    public function updatedEmpresaId($value)
    {
        if ($this->sucursal_id) {
            $sucursal = Sucursal::find($this->sucursal_id);
            if (!$sucursal || $sucursal->empresa_id != $value) {
                $this->sucursal_id = null;
            }
        }
    }

    public function update()
    {
        $this->authorize('edit subespecialidades');
        
        $this->validate();
        
        try {
            $data = [
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'codigo' => $this->codigo,
                'color' => $this->color,
                'costo_consulta' => $this->costo_consulta,
                'duracion_consulta' => $this->duracion_consulta,
                'requiere_cita_previa' => $this->requiere_cita_previa,
                'especialidad_id' => $this->especialidad_id,
                'status' => $this->status,
            ];
            
            // Solo super administradores pueden cambiar empresa y sucursal
            if (auth()->user()->hasRole('Super Administrador')) {
                $data['empresa_id'] = $this->empresa_id;
                $data['sucursal_id'] = $this->sucursal_id;
            }
            
            $this->subespecialidad->update($data);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Subespecialidad '{$this->nombre}' actualizada exitosamente.",
                'duration' => 4000
            ]);
            
            return redirect()->route('admin.subespecialidades.index');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar la subespecialidad: ' . $e->getMessage(),
                'duration' => 5000
            ]);
        }
    }

    public function getEmpresasProperty()
    {
        return Empresa::forUser()->get();
    }

    public function getSucursalesProperty()
    {
        if (!$this->empresa_id) {
            return collect();
        }
        
        return Sucursal::where('empresa_id', $this->empresa_id)
            ->forUser()
            ->get();
    }

    public function render()
    {
        $especialidades = Especialidad::forUser()->get();

        return view('livewire.admin.subespecialidades.edit', [
            'especialidades' => $especialidades,
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
        ])->layout($this->getLayout());
    }
}