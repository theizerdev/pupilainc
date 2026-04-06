<?php

namespace App\Livewire\Admin\TipoConsultas;

use App\Models\TipoConsulta;
use Livewire\Component;
use App\Traits\HasDynamicLayout;
use Livewire\Attributes\Title;

#[Title('Ver Tipo de Atención')]
class Show extends Component
{
    use HasDynamicLayout;

    public $tipoConsulta;

    public function mount(TipoConsulta $tipoConsulta)
    {
        $this->tipoConsulta = $tipoConsulta->load(['empresa', 'sucursal', 'citas']);
    }

    public function getStatsProperty()
    {
        return [
            'total_citas' => $this->tipoConsulta->citas()->count(),
            'citas_activas' => $this->tipoConsulta->citas()->where('estado', 'activa')->count(),
            'citas_finalizadas' => $this->tipoConsulta->citas()->where('estado', 'finalizada')->count(),
            'citas_canceladas' => $this->tipoConsulta->citas()->where('estado', 'cancelada')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.tipo-consultas.show', [
            'stats' => $this->stats,
        ])->layout($this->getLayout());
    }
}