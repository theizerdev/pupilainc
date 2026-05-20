<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use App\Models\Consulta;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class RegistrarEducacionPropietario extends Component
{
    public $consultaId;
    public $consulta;

    // Datos de educación y alta
    public $diagnostico_final = '';
    public $tratamiento_domiciliario = '';
    public $medicamentos_receta = '';
    public $dosis_instrucciones = '';
    public $duracion_tratamiento = '';
    public $cuidados_herida = '';
    public $alimentacion_recomendada = '';
    public $ejercicio_restricciones = '';
    public $signos_alerta = '';
    public $proxima_cita_control = '';
    public $vacunacion_pendiente = false;
    public $desparasitacion_pendiente = false;
    public $recomendaciones_nutricion = '';
    public $recomendaciones_higiene = '';
    public $instrucciones_emergencia = '';
    public $propietario_capacitado = false;
    public $material_entregado = '';
    public $observaciones_alta = '';

    protected $rules = [
        'diagnostico_final' => 'required|string|max:1000',
        'tratamiento_domiciliario' => 'nullable|string|max:2000',
        'medicamentos_receta' => 'nullable|string|max:1000',
        'dosis_instrucciones' => 'nullable|string|max:1000',
        'duracion_tratamiento' => 'nullable|string|max:200',
        'cuidados_herida' => 'nullable|string|max:1000',
        'alimentacion_recomendada' => 'nullable|string|max:1000',
        'ejercicio_restricciones' => 'nullable|string|max:500',
        'signos_alerta' => 'nullable|string|max:1000',
        'proxima_cita_control' => 'nullable|date',
        'vacunacion_pendiente' => 'boolean',
        'desparasitacion_pendiente' => 'boolean',
        'recomendaciones_nutricion' => 'nullable|string|max:1000',
        'recomendaciones_higiene' => 'nullable|string|max:1000',
        'instrucciones_emergencia' => 'nullable|string|max:1000',
        'propietario_capacitado' => 'boolean',
        'material_entregado' => 'nullable|string|max:500',
        'observaciones_alta' => 'nullable|string|max:2000',
    ];

    public function mount($consulta)
    {
        $this->consulta = $consulta;
        $this->consultaId = $consulta->id;
    }

    public function guardar()
    {
        $this->validate();

        try {
            $this->consulta->update([
                'diagnostico_final' => $this->diagnostico_final,
                'tratamiento_domiciliario' => $this->tratamiento_domiciliario,
                'receta_medicamentos' => $this->medicamentos_receta,
                'instrucciones_dosis' => $this->dosis_instrucciones,
                'duracion_tratamiento_dias' => $this->duracion_tratamiento,
                'cuidados_herida_alta' => $this->cuidados_herida,
                'alimentacion_alta' => $this->alimentacion_recomendada,
                'restricciones_ejercicio' => $this->ejercicio_restricciones,
                'signos_alerta_alta' => $this->signos_alerta,
                'fecha_control_alta' => $this->proxima_cita_control,
                'vacunacion_pendiente_alta' => $this->vacunacion_pendiente,
                'desparasitacion_pendiente_alta' => $this->desparasitacion_pendiente,
                'recomendaciones_nutricion' => $this->recomendaciones_nutricion,
                'recomendaciones_higiene' => $this->recomendaciones_higiene,
                'instrucciones_emergencia' => $this->instrucciones_emergencia,
                'propietario_educado' => $this->propietario_capacitado,
                'material_educativo_entregado' => $this->material_entregado,
                'observaciones_alta' => $this->observaciones_alta,
                'updated_by' => Auth::id(),
            ]);

            session()->flash('success', 'Educación al propietario y alta registradas exitosamente');

            return redirect()->route('admin.gestion.consultas.educacion-propietario');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.gestion.consultas.registrar-educacion-propietario')
            ->layout('layouts.admin');
    }
}
