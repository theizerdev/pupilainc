<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use Livewire\Component;
use App\Models\Consulta;
use Illuminate\Support\Facades\Auth;

class RegistrarTriaje extends Component
{
    public $consultaId;
    public $consulta;
    public $esVeterinaria = false;

    // Campos del formulario de triaje
    public $tipo_emergencia; // urgente, semi-urgente, no-urgente
    public $prioridad; // 1-alta, 2-media, 3-baja
    public $motivo_ingreso;
    public $duracion_sintomas;
    public $temperatura_rectal;
    public $frecuencia_cardiaca;
    public $frecuencia_respiratoria;
    public $peso_actual_kg;
    public $hemorragias_controladas;
    public $heridas_abiertas;
    public $fractura_sospechada;
    public $observaciones_triage;

    protected $rules = [
        'tipo_emergencia' => 'required|in:urgente,semi-urgente,no-urgente',
        'prioridad' => 'required|integer|min:1|max:3',
        'motivo_ingreso' => 'required|string|max:500',
        'duracion_sintomas' => 'nullable|string|max:200',
        'temperatura_rectal' => 'nullable|numeric|min:35|max:43',
        'frecuencia_cardiaca' => 'nullable|integer|min:30|max:300',
        'frecuencia_respiratoria' => 'nullable|integer|min:5|max:120',
        'peso_actual_kg' => 'nullable|numeric|min:0.1|max:100',
        'hemorragias_controladas' => 'boolean',
        'heridas_abiertas' => 'boolean',
        'fractura_sospechada' => 'boolean',
        'observaciones_triage' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'tipo_emergencia.required' => 'El tipo de emergencia es obligatorio',
        'prioridad.required' => 'La prioridad es obligatoria',
        'motivo_ingreso.required' => 'El motivo de ingreso es obligatorio',
    ];

    public function mount($consultaId)
    {
        $this->consultaId = $consultaId;
        $this->consulta = Consulta::with(['mascota', 'especialidad'])->findOrFail($consultaId);

        $this->esVeterinaria = $this->consulta->mascota_id && !$this->consulta->paciente_id;

        if (!$this->esVeterinaria) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Esta consulta no es veterinaria'
            ]);
            return;
        }

        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->temperatura_rectal = $this->consulta->temperatura_rectal;
        $this->frecuencia_cardiaca = $this->consulta->frecuencia_cardiaca;
        $this->frecuencia_respiratoria = $this->consulta->frecuencia_respiratoria;
        $this->peso_actual_kg = $this->consulta->peso_actual_kg;
        $this->motivo_ingreso = $this->consulta->motivo_consulta ?? '';
        $this->hemorragias_controladas = false;
        $this->heridas_abiertas = false;
        $this->fractura_sospechada = false;
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
                'observaciones_triage' => $this->observaciones_triage,
                'updated_by' => Auth::id(),
            ]);

            // Guardar datos de triaje en tabla dedicada
            \App\Models\TriajeRegistro::updateOrCreate(
                ['consulta_id' => $this->consultaId],
                [
                    'consulta_id' => $this->consultaId,
                    'tipo_emergencia' => $this->tipo_emergencia,
                    'prioridad' => $this->prioridad,
                    'motivo_ingreso' => $this->motivo_ingreso,
                    'duracion_sintomas' => $this->duracion_sintomas,
                    'hemorragias_controladas' => $this->hemorragias_controladas,
                    'heridas_abiertas' => $this->heridas_abiertas,
                    'fractura_sospechada' => $this->fractura_sospechada,
                    'observaciones' => $this->observaciones_triage,
                    'empresa_id' => $this->consulta->empresa_id,
                    'sucursal_id' => $this->consulta->sucursal_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            );

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Triaje registrado correctamente'
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
        return view('livewire.admin.gestion.consultas.registrar-triage', [
            'mascota' => $this->consulta->mascota,
            'especialidad' => $this->consulta->especialidad,
        ]);
    }
}
