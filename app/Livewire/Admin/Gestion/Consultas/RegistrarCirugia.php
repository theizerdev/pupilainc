<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use App\Models\Consulta;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class RegistrarCirugia extends Component
{
    public $consultaId;
    public $consulta;

    // Datos de cirugía
    public $tipo_cirugia = '';
    public $cirujano_responsable = '';
    public $anestesiologo = '';
    public $hora_inicio = '';
    public $protocolo_anestesico = '';
    public $medicamentos_administrados = '';
    public $monitoreo_fc = '';
    public $monitoreo_fr = '';
    public $monitoreo_temp = '';
    public $monitoreo_presion = '';
    public $monitoreo_spo2 = '';
    public $complicaciones = '';
    public $hallazgos_quirurgicos = '';
    public $material_implantado = '';
    public $suturas_utilizadas = '';
    public $hora_finalizacion = '';
    public $duracion_minutos = 0;
    public $estado_post_operatorio = '';
    public $observaciones = '';

    protected $rules = [
        'tipo_cirugia' => 'required|string|max:500',
        'cirujano_responsable' => 'required|string|max:200',
        'anestesiologo' => 'nullable|string|max:200',
        'hora_inicio' => 'required|date_format:H:i',
        'protocolo_anestesico' => 'nullable|string|max:1000',
        'medicamentos_administrados' => 'nullable|string|max:1000',
        'monitoreo_fc' => 'nullable|integer|min:30|max:300',
        'monitoreo_fr' => 'nullable|integer|min:5|max:120',
        'monitoreo_temp' => 'nullable|numeric|min:35|max:43',
        'monitoreo_presion' => 'nullable|string|max:100',
        'monitoreo_spo2' => 'nullable|integer|min:80|max:100',
        'complicaciones' => 'nullable|string|max:1000',
        'hallazgos_quirurgicos' => 'nullable|string|max:2000',
        'material_implantado' => 'nullable|string|max:500',
        'suturas_utilizadas' => 'nullable|string|max:500',
        'hora_finalizacion' => 'nullable|date_format:H:i',
        'duracion_minutos' => 'nullable|integer|min:0',
        'estado_post_operatorio' => 'nullable|in:estable,inestable,recuperacion_normal,complicaciones',
        'observaciones' => 'nullable|string|max:2000',
    ];

    public function mount($consulta)
    {
        $this->consulta = $consulta;
        $this->consultaId = $consulta->id;

        // Pre-llenar datos si existen
        if ($consulta->tipo_cirugia) {
            $this->tipo_cirugia = $consulta->tipo_cirugia;
        }
    }

    public function calcularDuracion()
    {
        if ($this->hora_inicio && $this->hora_finalizacion) {
            $inicio = \Carbon\Carbon::parse($this->hora_inicio);
            $fin = \Carbon\Carbon::parse($this->hora_finalizacion);

            if ($fin->greaterThan($inicio)) {
                $this->duracion_minutos = $inicio->diffInMinutes($fin);
            }
        }
    }

    public function guardar()
    {
        $this->validate();

        try {
            $this->calcularDuracion();

            $this->consulta->update([
                'tipo_cirugia' => $this->tipo_cirugia,
                'cirujano_responsable' => $this->cirujano_responsable,
                'anestesiologo' => $this->anestesiologo,
                'hora_inicio_cirugia' => $this->hora_inicio,
                'hora_fin_cirugia' => $this->hora_finalizacion,
                'duracion_cirugia_min' => $this->duracion_minutos,
                'protocolo_anestesico' => $this->protocolo_anestesico,
                'medicamentos_quirurgicos' => $this->medicamentos_administrados,
                'hallazgos_quirurgicos' => $this->hallazgos_quirurgicos,
                'material_implantado' => $this->material_implantado,
                'suturas_utilizadas' => $this->suturas_utilizadas,
                'complicaciones_quirurgicas' => $this->complicaciones,
                'estado_post_operatorio' => $this->estado_post_operatorio,
                'updated_by' => Auth::id(),
            ]);

            session()->flash('success', 'Registro quirúrgico guardado exitosamente');

            return redirect()->route('admin.gestion.consultas.en-cirugia');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.gestion.consultas.registrar-cirugia')
            ->layout('layouts.admin');
    }
}
