<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use Livewire\Component;
use App\Models\Consulta;
use Illuminate\Support\Facades\Auth;

class RegistrarSignosVitalesVeterinarios extends Component
{
    public $consultaId;
    public $consulta;
    public $esVeterinaria = false;

    // Campos del formulario veterinario
    public $temperatura_rectal;
    public $frecuencia_cardiaca;
    public $frecuencia_respiratoria;
    public $peso_actual_kg;
    public $bcs_score; // Body Condition Score (1-9)
    public $observaciones;

    protected $rules = [
        'temperatura_rectal' => 'nullable|numeric|min:35|max:43',
        'frecuencia_cardiaca' => 'nullable|integer|min:30|max:300',
        'frecuencia_respiratoria' => 'nullable|integer|min:5|max:120',
        'peso_actual_kg' => 'nullable|numeric|min:0.1|max:100',
        'bcs_score' => 'nullable|integer|min:1|max:9',
        'observaciones' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'temperatura_rectal.numeric' => 'La temperatura debe ser un número válido',
        'temperatura_rectal.min' => 'La temperatura mínima es 35°C',
        'temperatura_rectal.max' => 'La temperatura máxima es 43°C',
        'frecuencia_cardiaca.integer' => 'La frecuencia cardíaca debe ser un número entero',
        'frecuencia_cardiaca.min' => 'La frecuencia cardíaca mínima es 30 lpm',
        'frecuencia_cardiaca.max' => 'La frecuencia cardíaca máxima es 300 lpm',
        'peso_actual_kg.numeric' => 'El peso debe ser un número válido',
        'bcs_score.min' => 'El BCS debe ser entre 1 y 9',
        'bcs_score.max' => 'El BCS debe ser entre 1 y 9',
    ];

    public function mount($consultaId)
    {
        $this->consultaId = $consultaId;
        $this->consulta = Consulta::with(['mascota', 'especialidad'])->findOrFail($consultaId);

        // Verificar que es una consulta veterinaria
        $this->esVeterinaria = $this->consulta->mascota_id && !$this->consulta->paciente_id;

        if (!$this->esVeterinaria) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Esta consulta no es veterinaria'
            ]);
            return;
        }

        // Cargar datos existentes si los hay
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->temperatura_rectal = $this->consulta->temperatura_rectal;
        $this->frecuencia_cardiaca = $this->consulta->frecuencia_cardiaca;
        $this->frecuencia_respiratoria = $this->consulta->frecuencia_respiratoria;
        $this->peso_actual_kg = $this->consulta->peso_actual_kg;
        $this->bcs_score = $this->consulta->bcs_score;
        $this->observaciones = $this->consulta->observaciones_enfermeria ?? '';
    }

    public function guardar()
    {
        $this->validate();

        try {
            $this->consulta->update([
                'temperatura_rectal' => $this->temperatura_rectal,
                'frecuencia_cardiaca' => $this->frecuencia_cardiaca,
                'frecuencia_respiratoria' => $this->frecuencia_respiratoria,
                'peso_actual_kg' => $this->peso_actual_kg,
                'bcs_score' => $this->bcs_score,
                'observaciones_enfermeria' => $this->observaciones,
                'updated_by' => Auth::id(),
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Signos vitales veterinarios registrados correctamente'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.gestion.consultas.registrar-signos-vitales-veterinarios', [
            'mascota' => $this->consulta->mascota,
            'especialidad' => $this->consulta->especialidad,
        ]);
    }
}
