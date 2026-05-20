<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use Livewire\Component;
use App\Models\Consulta;
use Illuminate\Support\Facades\Auth;

class RegistrarPreQuirurgico extends Component
{
    public $consultaId;
    public $consulta;
    public $esVeterinaria = false;

    // Campos del formulario
    public $ayuno_horas;
    public $ayuno_cumplido;
    public $pre_medicacion;
    public $medicamento_pre_anestesico;
    public $dosis_pre_anestesico;
    public $riesgos_identificados;
    public $examenes_preoperatorios;
    public $observaciones_pre_quirurgico;

    protected $rules = [
        'ayuno_horas' => 'nullable|integer|min:0|max:24',
        'ayuno_cumplido' => 'boolean',
        'pre_medicacion' => 'boolean',
        'medicamento_pre_anestesico' => 'nullable|string|max:200',
        'dosis_pre_anestesico' => 'nullable|string|max:100',
        'riesgos_identificados' => 'nullable|string|max:1000',
        'examenes_preoperatorios' => 'nullable|string|max:1000',
        'observaciones_pre_quirurgico' => 'nullable|string|max:1000',
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

        $this->ayuno_cumplido = false;
        $this->pre_medicacion = false;
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $this->observaciones_pre_quirurgico = $this->consulta->observaciones_pre_quirurgico ?? '';
    }

    public function guardar()
    {
        $this->validate();

        try {
            $this->consulta->update([
                'observaciones_pre_quirurgico' => $this->observaciones_pre_quirurgico,
                'updated_by' => Auth::id(),
            ]);

            \App\Models\PreQuirurgico::updateOrCreate(
                ['consulta_id' => $this->consultaId],
                [
                    'consulta_id' => $this->consultaId,
                    'ayuno_horas' => $this->ayuno_horas,
                    'ayuno_cumplido' => $this->ayuno_cumplido,
                    'pre_medicacion' => $this->pre_medicacion,
                    'medicamento_pre_anestesico' => $this->medicamento_pre_anestesico,
                    'dosis_pre_anestesico' => $this->dosis_pre_anestesico,
                    'riesgos_identificados' => $this->riesgos_identificados,
                    'examenes_preoperatorios' => $this->examenes_preoperatorios,
                    'observaciones' => $this->observaciones_pre_quirurgico,
                    'empresa_id' => $this->consulta->empresa_id,
                    'sucursal_id' => $this->consulta->sucursal_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            );

            $this->dispatch('notify', ['type' => 'success', 'message' => 'Evaluación pre-quirúrgica registrada']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.admin.gestion.consultas.registrar-pre-quirurgico', [
            'mascota' => $this->consulta->mascota,
            'especialidad' => $this->consulta->especialidad,
        ]);
    }
}
