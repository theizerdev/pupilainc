<?php

namespace App\Livewire\Admin\Especialidades;

use App\Models\Especialidad;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Show extends Component
{
    use HasDynamicLayout;

    public Especialidad $especialidad;

    public function mount(Especialidad $especialidad)
    {
        $this->especialidad = $especialidad;
    }

    public function getEstadisticasProperty()
    {
        return [
            'total_medicos' => $this->especialidad->medicos()->where('medicos.status', true)->count(),
            'citas_mes' => \App\Models\Cita::where('especialidad_id', $this->especialidad->id)
                ->whereMonth('created_at', now()->month)
                ->count(),
            'ingresos_mes' => 0.00, // Aquí podrías calcular los ingresos del mes si tienes esa información en tu modelo de Cita
            'citas_hoy' => \App\Models\Cita::where('especialidad_id', $this->especialidad->id)
                ->whereDate('created_at', today())
                ->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.especialidades.show', [
            'estadisticas' => $this->estadisticas
        ])->layout($this->getLayout());
    }
}