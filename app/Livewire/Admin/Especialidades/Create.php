<?php

namespace App\Livewire\Admin\Especialidades;

use App\Models\Especialidad;
use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Create extends Component
{
    use HasDynamicLayout;

    public $nombre;
    public $descripcion;
    public $codigo;
    public $color = '#3B82F6';
    public $icono = 'fa-stethoscope';
    public $costo_consulta = 0;
    public $duracion_consulta = 30;
    public $requiere_cita_previa = true;
    public $empresa_id;
    public $sucursal_id;
    public $status = true;

    protected function rules()
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'codigo' => 'nullable|string|max:10|unique:especialidades',
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

    public function mount()
    {
        if (!auth()->user()->hasRole('Super Administrador')) {
            $this->empresa_id = auth()->user()->empresa_id;
            $this->sucursal_id = auth()->user()->sucursal_id;
        }
    }

    public function updatedEmpresaId($value)
    {
        $this->sucursal_id = null;
    }

    public function save()
    {
        $this->authorize('create especialidades');
        
        $this->validate();
        
        try {
            // Para usuarios normales, usar automáticamente su empresa y sucursal
            if (!auth()->user()->hasRole('Super Administrador')) {
                $this->empresa_id = auth()->user()->empresa_id;
                $this->sucursal_id = auth()->user()->sucursal_id;
            }
            
            $especialidad = Especialidad::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'codigo' => $this->codigo ?: Especialidad::generateCodigo(),
                'color' => $this->color,
                'icono' => $this->icono,
                'costo_consulta' => $this->costo_consulta,
                'duracion_consulta' => $this->duracion_consulta,
                'requiere_cita_previa' => $this->requiere_cita_previa,
                'status' => $this->status,
            ]);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => "Especialidad '{$this->nombre}' creada exitosamente.",
                'duration' => 4000
            ]);
            
            return redirect()->route('admin.especialidades.index');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear la especialidad: ' . $e->getMessage(),
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
        return view('livewire.admin.especialidades.create', [
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
            'iconos' => $this->iconos
        ])->layout($this->getLayout());
    }
}