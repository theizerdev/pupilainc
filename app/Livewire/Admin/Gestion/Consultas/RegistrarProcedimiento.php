<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use Livewire\Component;
use App\Models\Consulta;
use Illuminate\Support\Facades\Auth;

class RegistrarProcedimiento extends Component
{
    public $consultaId;
    public $consulta;
    public $esVeterinaria = false;

    // Campos del formulario
    public $tipo_procedimiento; // cura, vendaje, higiene, limpieza, otro
    public $area_afectada;
    public $descripcion_herida;
    public $tipo_vendaje;
    public $productos_utilizados;
    public $peso_control_kg;
    public $observaciones_procedimiento;

    protected $rules = [
        'tipo_procedimiento' => 'required|in:cura,vendaje,higiene,limpieza,otro',
        'area_afectada' => 'nullable|string|max:200',
        'descripcion_herida' => 'nullable|string|max:500',
        'tipo_vendaje' => 'nullable|string|max:200',
        'productos_utilizados' => 'nullable|string|max:500',
        'peso_control_kg' => 'nullable|numeric|min:0.1|max:100',
        'observaciones_procedimiento' => 'nullable|string|max:1000',
    ];

    public function mount($consultaId)
    {
        $this->consultaId = $consultaId;
        $this->consulta = Consulta::with(['mascota', 'especialidad'])->findOrFail($consultaId);
        $this->esVeterinaria = $this->consulta->mascota_id && !$this->consulta->paciente_id;

        if (!$this->esVeterinaria) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Esta consulta no es veterinaria']);
            return;
        }

        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->observaciones_procedimiento = $this->consulta->observaciones_procedimiento ?? '';
    }

    public function guardar()
    {
        $this->validate();

        try {
            $this->consulta->update([
                'observaciones_procedimiento' => $this->observaciones_procedimiento,
                'peso_control_kg' => $this->peso_control_kg,
                'updated_by' => Auth::id(),
            ]);

            \App\Models\ProcedimientoVeterinario::updateOrCreate(
                ['consulta_id' => $this->consultaId],
                [
                    'consulta_id' => $this->consultaId,
                    'tipo_procedimiento' => $this->tipo_procedimiento,
                    'area_afectada' => $this->area_afectada,
                    'descripcion_herida' => $this->descripcion_herida,
                    'tipo_vendaje' => $this->tipo_vendaje,
                    'productos_utilizados' => $this->productos_utilizados,
                    'peso_control_kg' => $this->peso_control_kg,
                    'observaciones' => $this->observaciones_procedimiento,
                    'empresa_id' => $this->consulta->empresa_id,
                    'sucursal_id' => $this->consulta->sucursal_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            );

            $this->dispatch('notify', ['type' => 'success', 'message' => 'Procedimiento registrado correctamente']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.admin.gestion.consultas.registrar-procedimiento', [
            'mascota' => $this->consulta->mascota,
            'especialidad' => $this->consulta->especialidad,
        ]);
    }
}
