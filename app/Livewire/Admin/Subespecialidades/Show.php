<?php

namespace App\Livewire\Admin\Subespecialidades;

use App\Models\Subespecialidad;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\HasDynamicLayout;

class Show extends Component
{
    use AuthorizesRequests, HasDynamicLayout;

    public $subespecialidad;

    public function mount($subespecialidad)
    {
        $this->authorize('access subespecialidades');
        $this->subespecialidad = Subespecialidad::with(['especialidad', 'empresa', 'sucursal', 'user'])->findOrFail($subespecialidad);
    }

    public function render()
    {
        return view('livewire.admin.subespecialidades.show')->layout($this->getLayout());
    }


    
}