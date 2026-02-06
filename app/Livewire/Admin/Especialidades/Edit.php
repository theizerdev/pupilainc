<?php

namespace App\Livewire\Admin\Especialidades;

use App\Models\Especialidad;
use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Edit extends Component
{
    use HasDynamicLayout;

    public Especialidad $especialidad;
    
    public $nombre;
    public $descripcion;
    public $codigo;
    public $color;
    public $icono;
    public $costo_consulta;
    public $duracion_consulta;
    public $requiere_cita_previa;
    public $empresa_id;
    public $sucursal_id;
    public $status;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string|max:1000',
        'codigo' => 'required|string|max:10|unique:especialidades,codigo',
        'color' => 'required|string|max:7',
        'icono' => 'required|string|max:50',
        'costo_consulta' => 'required|numeric|min:0',
        'duracion_consulta' => 'required|integer|min:15|max:240',
        'requiere_cita_previa' => 'boolean',
        'empresa_id' => 'required|exists:empresas,id',
        'sucursal_id' => 'required|exists:sucursales,id',
        'status' => 'boolean'
    ];

    public function mount(Especialidad $especialidad)
    {
        $this->especialidad = $especialidad;
        $this->nombre = $especialidad->nombre;
        $this->descripcion = $especialidad->descripcion;
        $this->codigo = $especialidad->codigo;
        $this->color = $especialidad->color;
        $this->icono = $especialidad->icono;
        $this->costo_consulta = $especialidad->costo_consulta;
        $this->duracion_consulta = $especialidad->duracion_consulta;
        $this->requiere_cita_previa = $especialidad->requiere_cita_previa;
        $this->empresa_id = $especialidad->empresa_id;
        $this->sucursal_id = $especialidad->sucursal_id;
        $this->status = $especialidad->status;
    }

    protected function rules()
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'codigo' => 'required|string|max:10|unique:especialidades,codigo,' . $this->especialidad->id,
            'color' => 'required|string|max:7',
            'icono' => 'required|string|max:50',
            'costo_consulta' => 'required|numeric|min:0',
            'duracion_consulta' => 'required|integer|min:15|max:240',
            'requiere_cita_previa' => 'boolean',
            'status' => 'boolean'
        ];

        // Solo requerir empresa_id y sucursal_id para super administradores
        if (auth()->user()->hasRole('Super Administrador')) {
            $rules['empresa_id'] = 'required|exists:empresas,id';
            $rules['sucursal_id'] = 'required|exists:sucursales,id';
        }

        return $rules;
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
        $this->authorize('edit especialidades');
        
        $this->validate();
        
        try {
            $data = [
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'codigo' => $this->codigo,
                'color' => $this->color,
                'icono' => $this->icono,
                'costo_consulta' => $this->costo_consulta,
                'duracion_consulta' => $this->duracion_consulta,
                'requiere_cita_previa' => $this->requiere_cita_previa,
                'status' => $this->status,
            ];
            
            // Solo super administradores pueden cambiar empresa y sucursal
            if (auth()->user()->hasRole('Super Administrador')) {
                $data['empresa_id'] = $this->empresa_id;
                $data['sucursal_id'] = $this->sucursal_id;
            }
            
            $this->especialidad->update($data);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Especialidad '{$this->nombre}' actualizada exitosamente.",
                'duration' => 4000
            ]);
            
            return redirect()->route('admin.especialidades.index');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar la especialidad: ' . $e->getMessage(),
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

    public function getIconosProperty()
    {
        return [
            'fa-stethoscope' => 'Estetoscopio',
            'fa-eye' => 'Ojo',
            'fa-heartbeat' => 'Cardíaco',
            'fa-user-md' => 'Médico',
            'fa-medkit' => 'Botiquín',
            'fa-wheelchair' => 'Silla de ruedas',
            'fa-ambulance' => 'Ambulancia',
            'fa-hospital' => 'Hospital',
            'fa-heart' => 'Corazón',
            'fa-brain' => 'Cerebro'
        ];
    }

    public function render()
    {
        return view('livewire.admin.especialidades.edit', [
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
            'iconos' => $this->iconos
        ])->layout($this->getLayout());
    }
}