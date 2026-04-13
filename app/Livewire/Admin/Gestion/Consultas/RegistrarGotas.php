<?php

namespace App\Livewire\Admin\Gestion\Consultas;

use App\Models\Consulta;
use App\Models\ConsultaGota;
use Livewire\Component;

class RegistrarGotas extends Component
{
    public $consultaId;
    public $consulta;
    public $tipo_gota = 'Estándar';
    public $gotas_od = 1;
    public $gotas_oi = 1;
    public $observaciones = '';

    public function mount($consultaId)
    {
        $this->consultaId = $consultaId;
        $this->consulta = Consulta::findOrFail($consultaId);
    }

    public function rules()
    {
        return [
            'tipo_gota' => 'nullable|string|max:255',
            'gotas_od' => 'required|integer|min:0',
            'gotas_oi' => 'required|integer|min:0',
            'observaciones' => 'nullable|string|max:1000',
        ];
    }

    public function registrar()
    {
        $this->validate();

        $this->consulta->gotasAplicadas()->create([
            'user_id' => auth()->id(),
            'tipo_gota' => $this->tipo_gota,
            'gotas_od' => $this->gotas_od,
            'gotas_oi' => $this->gotas_oi,
            'observaciones' => $this->observaciones,
        ]);

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => 'Gotas registradas correctamente',
        ]);

        $this->dispatch('gotas-actualizadas');
        
        $this->reset(['tipo_gota', 'gotas_od', 'gotas_oi', 'observaciones']);
        $this->gotas_od = 1;
        $this->gotas_oi = 1;
    }

    public function render()
    {
        $historial = $this->consulta->gotasAplicadas()->with('user')->latest()->get();
        return view('livewire.admin.gestion.consultas.registrar-gotas', [
            'historial' => $historial
        ]);
    }
}
