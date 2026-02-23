<?php

namespace App\Livewire\Admin\Pagos;

use App\Traits\HasDynamicLayout;
use App\Traits\HasRegionalFormatting;
use Livewire\Component;
use App\Models\Pago;

class Show extends Component
{
    use HasDynamicLayout, HasRegionalFormatting;


    public $pago;

    public function mount(Pago $pago)
    {
        $this->pago = $pago->load(['consulta.paciente', 'consulta.medico', 'detalles.baremo', 'serieModel', 'clienteFiscal', 'user', 'caja']);
    }

    public function render()
    {
        return view('livewire.admin.pagos.show')->layout($this->getLayout());
    }
}
