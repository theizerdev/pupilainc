<?php

namespace App\Livewire\Admin\Baremo;

use App\Models\Baremo;
use Livewire\Component;
use App\Traits\HasDynamicLayout;

class Show extends Component
{
    use HasDynamicLayout;

    public $baremo;

    public function mount(Baremo $baremo)
    {
        $this->baremo = $baremo->load(['categoria', 'especialidad', 'empresa', 'sucursal']);
    }

    public function render()
    {
        return view('livewire.admin.baremo.show')->layout($this->getLayout());
    }
}
