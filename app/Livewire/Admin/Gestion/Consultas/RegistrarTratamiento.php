<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use Livewire\Component;
use App\Models\Consulta;
use Illuminate\Support\Facades\Auth;

class RegistrarTratamiento extends Component
{
    public $consultaId;
    public $consulta;
    public $esVeterinaria = false;

    // Campos del formulario
    public $tipo_tratamiento; // inyeccion, intravenoso, oral, topico
    public $medicamento;
    public $dosis;
    public $via_administracion;
    public $frecuencia_administracion;
    public $observaciones_tratamiento;
    public $hora_administracion;

    protected $rules = [
        'tipo_tratamiento' => 'required|in:inyeccion,intravenoso,oral,topico',
        'medicamento' => 'required|string|max:200',
        'dosis' => 'required|string|max:100',
        'via_administracion' => 'required|string|max:100',
        'frecuencia_administracion' => 'nullable|string|max:100',
        'observaciones_tratamiento' => 'nullable|string|max:1000',
        'hora_administracion' => 'nullable|date_format:H:i',
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
        $this->observaciones_tratamiento = $this->consulta->observaciones_tratamiento ?? '';
    }

    public function guardar()
    {
        $this->validate();

        try {
            $this->consulta->update([
                'observaciones_tratamiento' => $this->observaciones_tratamiento,
                'updated_by' => Auth::id(),
            ]);

            \App\Models\TratamientoVeterinario::updateOrCreate(
                ['consulta_id' => $this->consultaId],
                [
                    'consulta_id' => $this->consultaId,
                    'tipo_tratamiento' => $this->tipo_tratamiento,
                    'medicamento' => $this->medicamento,
                    'dosis' => $this->dosis,
                    'via_administracion' => $this->via_administracion,
                    'frecuencia_administracion' => $this->frecuencia_administracion,
                    'hora_administracion' => $this->hora_administracion,
                    'observaciones' => $this->observaciones_tratamiento,
                    'empresa_id' => $this->consulta->empresa_id,
                    'sucursal_id' => $this->consulta->sucursal_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            );

            $this->dispatch('notify', ['type' => 'success', 'message' => 'Tratamiento registrado correctamente']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.admin.gestion.consultas.registrar-tratamiento', [
            'mascota' => $this->consulta->mascota,
            'especialidad' => $this->consulta->especialidad,
        ]);
    }
}
