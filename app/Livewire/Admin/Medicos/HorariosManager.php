<?php

namespace App\Livewire\Admin\Medicos;

use Livewire\Component;

class HorariosManager extends Component
{
    public $horarios = [];
    public $diasSemana = [
        1 => 'Lunes',
        2 => 'Martes', 
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];

    protected $rules = [
        'horarios.*.activo' => 'boolean',
        'horarios.*.hora_inicio' => 'required_if:horarios.*.activo,true|date_format:H:i',
        'horarios.*.hora_fin' => 'required_if:horarios.*.activo,true|date_format:H:i|after:horarios.*.hora_inicio',
        'horarios.*.duracion_cita' => 'required_if:horarios.*.activo,true|integer|min:15|max:120',
    ];

    public function mount()
    {
        // Inicializar horarios por defecto
        foreach ($this->diasSemana as $dia => $nombre) {
            $this->horarios[$dia] = [
                'activo' => in_array($dia, [1, 2, 3, 4, 5]), // Lunes a Viernes activos por defecto
                'hora_inicio' => '09:00',
                'hora_fin' => '17:00',
                'duracion_cita' => 30,
            ];
        }
    }

    public function updatedHorarios($value, $key)
    {
        $this->validateOnly("horarios.{$key}");
    }

    public function render()
    {
        return view('livewire.admin.medicos.horarios-manager');
    }

    public function getHorariosData()
    {
        return $this->horarios;
    }

    public function setHorariosData($horarios)
    {
        $this->horarios = $horarios;
    }
}