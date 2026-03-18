<?php

namespace App\Livewire\Admin\Categorias;

use App\Models\Categoria;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Show extends Component
{
    use HasDynamicLayout;

    public $categoria;

    public function mount(Categoria $categoria)
    {
        $this->categoria = $categoria;
    }

    public function render()
    {
        return view('livewire.admin.categorias.show')->layout($this->getLayout());
    }
}
