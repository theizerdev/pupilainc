<?php

namespace App\Livewire\Admin\Especialidades;

use App\Models\Especialidad;
use App\Traits\HasPlantillaTemplate;
use Livewire\Component;

class PlantillaDashboard extends Component
{
    use HasPlantillaTemplate;

    public function mount(Especialidad $especialidad): void
    {
        parent::mount($especialidad);
    }

    public function render()
    {
        return view('livewire.admin.especialidades.plantilla-dashboard')
            ->layout('layouts.admin');
    }
}
