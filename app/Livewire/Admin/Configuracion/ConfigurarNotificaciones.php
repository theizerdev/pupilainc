<?php

namespace App\Livewire\Admin\Configuracion;

use App\Models\ConfiguracionNotificacion;
use App\Models\Empresa;
use Livewire\Component;
use App\Traits\HasDynamicLayout;
class ConfigurarNotificaciones extends Component
{
    use HasDynamicLayout;
    public $empresaId;
    public $configPaciente = [];
    public $configDoctor = [];
    public $estadosDisponibles = [];

    protected $rules = [
        'configPaciente.*' => 'boolean',
        'configDoctor.*' => 'boolean',
    ];

    public function mount()
    {
        $this->empresaId = auth()->user()->empresa_id ?? Empresa::first()->id;

        // Obtener todos los estados disponibles de Cita
        $this->estadosDisponibles = array_keys(\App\Models\Cita::ESTADO_LABELS);

        // Cargar configuración actual
        $this->cargarConfiguracion();
    }

    public function cargarConfiguracion()
    {
        $config = ConfiguracionNotificacion::obtenerConfiguracionEmpresa($this->empresaId);

        $this->configPaciente = $config['paciente'];
        $this->configDoctor = $config['doctor'];
    }

    public function guardarConfiguracion()
    {
        $this->validate();

        ConfiguracionNotificacion::guardarConfiguracion($this->empresaId, [
            'paciente' => $this->configPaciente,
            'doctor' => $this->configDoctor,
        ]);

        session()->flash('message', '✅ Configuración de notificaciones guardada exitosamente.');

        // Recargar configuración
        $this->cargarConfiguracion();
    }

    public function aplicarRecomendacion($tipo)
    {
        if ($tipo === 'paciente') {
            $this->configPaciente = [
                'confirmada' => true,
                'sala_espera' => false,
                'en_enfermeria' => false,
                'en_consultorio' => false,
                'en_consultorio_optometrista' => false,
                'en_gotas' => false,
                'dilatado' => false,
                'en_optica' => false,
                'en_estudio' => false,
                'finalizada' => false,
                'pagada' => true,
                'cancelada' => true,
                'no_asistio' => false,
            ];
        } elseif ($tipo === 'doctor') {
            $this->configDoctor = [
                'confirmada' => true,
                'sala_espera' => true,
                'en_enfermeria' => false,
                'en_consultorio' => false,
                'en_consultorio_optometrista' => false,
                'en_gotas' => false,
                'dilatado' => false,
                'en_optica' => false,
                'en_estudio' => false,
                'finalizada' => false,
                'pagada' => false,
                'cancelada' => true,
                'no_asistio' => false,
            ];
        }
    }

    public function activarTodas($tipo)
    {
        if ($tipo === 'paciente') {
            foreach ($this->estadosDisponibles as $estado) {
                $this->configPaciente[$estado] = true;
            }
        } elseif ($tipo === 'doctor') {
            foreach ($this->estadosDisponibles as $estado) {
                $this->configDoctor[$estado] = true;
            }
        }
    }

    public function desactivarTodas($tipo)
    {
        if ($tipo === 'paciente') {
            foreach ($this->estadosDisponibles as $estado) {
                $this->configPaciente[$estado] = false;
            }
        } elseif ($tipo === 'doctor') {
            foreach ($this->estadosDisponibles as $estado) {
                $this->configDoctor[$estado] = false;
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.configuracion.configurar-notificaciones', [
            'estadoLabels' => \App\Models\Cita::ESTADO_LABELS,
        ])->layout($this->getLayout());
    }
}
