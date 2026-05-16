<?php

namespace App\Livewire\Admin\Mascotas;

use Livewire\Component;
use App\Models\Mascota;
use App\Traits\HasDynamicLayout;

class Show extends Component
{
    use HasDynamicLayout;

    public Mascota $mascota;

    public function mount(Mascota $mascota)
    {
        $this->mascota = $mascota->load(['especie', 'raza', 'propietario', 'citas', 'consultas']);
    }

    public function render()
    {
        // Obtener citas recientes
        $citasRecientes = $this->mascota->citas()
            ->with('medico')
            ->orderBy('fecha_inicio', 'desc')
            ->take(5)
            ->get();

        // Obtener consultas recientes
        $consultasRecientes = $this->mascota->consultas()
            ->with('medico')
            ->orderBy('fecha_consulta', 'desc')
            ->take(5)
            ->get();

        // Calcular estadísticas
        $stats = [
            'total_citas' => $this->mascota->citas()->count(),
            'total_consultas' => $this->mascota->consultas()->count(),
            'ultima_visita' => $this->mascota->citas()->latest('fecha_inicio')->first(),
        ];

        return view('livewire.admin.mascotas.show', [
            'citasRecientes' => $citasRecientes,
            'consultasRecientes' => $consultasRecientes,
            'stats' => $stats,
        ])->layout($this->getLayout());
    }
}
