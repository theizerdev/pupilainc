<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use Livewire\Component;
use App\Models\Consulta;
use App\Models\SignosVitales;
use App\Models\Enfermero;
use Illuminate\Support\Facades\Auth;

class RegistrarSignosVitales extends Component
{
    public $consultaId;
    public $consulta;
    public $enfermeroId;
    
    // Campos del formulario
    public $presion_arterial_sistolica;
    public $presion_arterial_diastolica;
    public $frecuencia_cardiaca;
    public $frecuencia_respiratoria;
    public $temperatura;
    public $peso;
    public $talla;
    public $imc;
    public $saturacion_oxigeno;
    public $observaciones;

    protected $rules = [
        'presion_arterial_sistolica' => 'nullable|numeric|min:70|max:250',
        'presion_arterial_diastolica' => 'nullable|numeric|min:40|max:150',
        'frecuencia_cardiaca' => 'nullable|integer|min:40|max:200',
        'frecuencia_respiratoria' => 'nullable|integer|min:8|max:60',
        'temperatura' => 'nullable|numeric|min:35|max:42',
        'peso' => 'nullable|numeric|min:1|max:300',
        'talla' => 'nullable|numeric|min:50|max:250',
        'saturacion_oxigeno' => 'nullable|numeric|min:70|max:100',
        'observaciones' => 'nullable|string|max:1000',
    ];

    public function mount($consultaId)
    {
        $this->consultaId = $consultaId;
        $this->consulta = Consulta::with(['paciente', 'especialidad'])->findOrFail($consultaId);
        
        // Obtener el enfermero actual
        $user = Auth::user();
        $enfermero = Enfermero::where('user_id', $user->id)->first();
        
        if (!$enfermero) {
           $this->dispatch('notify', [
            'type' => 'error',
            'message' => 'No se encontró el enfermero asociado a su usuario'
        ]);
            return;
        } 
        
        $this->enfermeroId = $enfermero->id;
        
        // Cargar signos vitales existentes si los hay
        $signosVitales = SignosVitales::where('consulta_id', $consultaId)->first();
        if ($signosVitales) {
            $this->cargarSignosVitales($signosVitales);
        }
    }

    public function cargarSignosVitales($signosVitales)
    {
        $this->presion_arterial_sistolica = $signosVitales->presion_arterial_sistolica;
        $this->presion_arterial_diastolica = $signosVitales->presion_arterial_diastolica;
        $this->frecuencia_cardiaca = $signosVitales->frecuencia_cardiaca;
        $this->frecuencia_respiratoria = $signosVitales->frecuencia_respiratoria;
        $this->temperatura = $signosVitales->temperatura;
        $this->peso = $signosVitales->peso;
        $this->talla = $signosVitales->talla;
        $this->imc = $signosVitales->imc;
        $this->saturacion_oxigeno = $signosVitales->saturacion_oxigeno;
        $this->observaciones = $signosVitales->observaciones;
    }

    public function updatedPeso()
    {
        $this->calcularIMC();
    }

    public function updatedTalla()
    {
        $this->calcularIMC();
    }

    public function calcularIMC()
    {
        if ($this->peso && $this->talla) {
            $tallaMetros = $this->talla / 100;
            $this->imc = round($this->peso / ($tallaMetros * $tallaMetros), 1);
        }
    }

    public function guardar()
    {
        $this->validate();
        
        try {
            $signosVitales = SignosVitales::updateOrCreate(
                ['consulta_id' => $this->consultaId],
                [
                    'enfermero_id' => $this->enfermeroId,
                    'paciente_id' => $this->consulta->paciente_id,
                    'presion_arterial_sistolica' => $this->presion_arterial_sistolica,
                    'presion_arterial_diastolica' => $this->presion_arterial_diastolica,
                    'frecuencia_cardiaca' => $this->frecuencia_cardiaca,
                    'frecuencia_respiratoria' => $this->frecuencia_respiratoria,
                    'temperatura' => $this->temperatura,
                    'peso' => $this->peso,
                    'talla' => $this->talla,
                    'imc' => $this->imc,
                    'saturacion_oxigeno' => $this->saturacion_oxigeno,
                    'observaciones' => $this->observaciones,
                    'empresa_id' => $this->consulta->empresa_id,
                    'sucursal_id' => $this->consulta->sucursal_id,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            );

           $this->dispatch('notify', [
               'type' => 'success',
               'message' => 'Signos vitales registrados correctamente'
            ]);

          
        } catch (\Exception $e) {
         
            $this->dispatch('notify', [
            'type' => 'error',
            'message' => 'Error al guardar signos vitales: ' . $e->getMessage()
         ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.gestion.consultas.registrar-signos-vitales', [
            'paciente' => $this->consulta->paciente,
            'especialidad' => $this->consulta->especialidad,
        ]);
    }
}