<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use App\Models\Consulta;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class RegistrarRecuperacion extends Component
{
    public $consultaId;
    public $consulta;

    // Datos de recuperación
    public $hora_ingreso_recuperacion = '';
    public $conciencia = '';
    public $reflejos_presentes = '';
    public $temperatura_actual = '';
    public $frecuencia_cardiaca = '';
    public $frecuencia_respiratoria = '';
    public $presion_arterial = '';
    public $oxigenacion_spo2 = '';
    public $dolor_evaluado = '';
    public $escala_dolor = 0;
    public $analgesia_administrada = '';
    public $fluidoterapia = '';
    public $tipo_fluido = '';
    public $volumen_ml_hr = 0;
    public $miccion_presente = false;
    public $deposicion_presente = false;
    public $apetito = '';
    public $movilidad = '';
    public $herida_quirurgica = '';
    public $sangrado_activo = false;
    public $complicaciones = '';
    public $observaciones = '';

    protected $rules = [
        'hora_ingreso_recuperacion' => 'nullable|date_format:H:i',
        'conciencia' => 'nullable|in:alerta,letargico,estuporoso,comatoso',
        'reflejos_presentes' => 'nullable|string|max:500',
        'temperatura_actual' => 'nullable|numeric|min:35|max:43',
        'frecuencia_cardiaca' => 'nullable|integer|min:30|max:300',
        'frecuencia_respiratoria' => 'nullable|integer|min:5|max:120',
        'presion_arterial' => 'nullable|string|max:100',
        'oxigenacion_spo2' => 'nullable|integer|min:80|max:100',
        'dolor_evaluado' => 'nullable|string|max:500',
        'escala_dolor' => 'nullable|integer|min:0|max:10',
        'analgesia_administrada' => 'nullable|string|max:1000',
        'fluidoterapia' => 'nullable|string|max:500',
        'tipo_fluido' => 'nullable|string|max:200',
        'volumen_ml_hr' => 'nullable|integer|min:0|max:1000',
        'miccion_presente' => 'boolean',
        'deposicion_presente' => 'boolean',
        'apetito' => 'nullable|in:normal,disminuido,ausente',
        'movilidad' => 'nullable|in:normal,limitada,no_puede_caminar',
        'herida_quirurgica' => 'nullable|string|max:1000',
        'sangrado_activo' => 'boolean',
        'complicaciones' => 'nullable|string|max:1000',
        'observaciones' => 'nullable|string|max:2000',
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
                'hora_recuperacion' => $this->hora_ingreso_recuperacion,
                'estado_conciencia' => $this->conciencia,
                'reflejos_post_operatorios' => $this->reflejos_presentes,
                'temperatura_recuperacion' => $this->temperatura_actual,
                'fc_recuperacion' => $this->frecuencia_cardiaca,
                'fr_recuperacion' => $this->frecuencia_respiratoria,
                'presion_arterial_recuperacion' => $this->presion_arterial,
                'spo2_recuperacion' => $this->oxigenacion_spo2,
                'evaluacion_dolor' => $this->dolor_evaluado,
                'escala_dolor' => $this->escala_dolor,
                'analgesia_recuperacion' => $this->analgesia_administrada,
                'fluidoterapia_recuperacion' => $this->fluidoterapia,
                'tipo_fluido_recuperacion' => $this->tipo_fluido,
                'volumen_fluido_ml_hr' => $this->volumen_ml_hr,
                'miccion_recuperacion' => $this->miccion_presente,
                'deposicion_recuperacion' => $this->deposicion_presente,
                'apetito_recuperacion' => $this->apetito,
                'movilidad_recuperacion' => $this->movilidad,
                'estado_herida_recuperacion' => $this->herida_quirurgica,
                'sangrado_activo_recuperacion' => $this->sangrado_activo,
                'complicaciones_recuperacion' => $this->complicaciones,
                'observaciones_recuperacion' => $this->observaciones,
                'updated_by' => Auth::id(),
            ]);

            session()->flash('success', 'Registro de recuperación guardado exitosamente');

            return redirect()->route('admin.gestion.consultas.recuperacion');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.gestion.consultas.registrar-recuperacion')
            ->layout('layouts.admin');
    }
}
