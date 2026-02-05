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
            'total_medicos' => $this->especialidad->medicos()->where('status', true)->count(),
            'citas_mes' => \App\Models\Cita::where('especialidad_id', $this->especialidad->id)
                ->whereMonth('fecha_hora', now()->month)
                ->count(),
            'ingresos_mes' => \App\Models\Cita::where('especialidad_id', $this->especialidad->id)
                ->whereMonth('fecha_hora', now()->month)
                ->where('estado', 'completada')
                ->sum('monto_pago'),
            'citas_hoy' => \App\Models\Cita::where('especialidad_id', $this->especialidad->id)
                ->whereDate('fecha_hora', today())
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